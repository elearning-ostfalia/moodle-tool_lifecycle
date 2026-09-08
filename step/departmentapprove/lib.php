<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Step subplugin for sending out emails to the department.
 * @package lifecyclestep_departmentapprove
 * @copyright  2025 Tobias Reischmann WWU/Justus Dieckmann WWU/Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use core_customfield\api;
use core_user;
use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\local\manager\step_manager;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../email/lib.php');


/**
 * Step subplugin for sending out emails to the teacher.
 * @package    lifecyclestep_approval
 * @copyright  2017 Tobias Reischmann WWU/2025 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class departmentapprove extends libbase {

    const initial_state = 0;
    const waiting_for_approval = 1;
    const approved = 2;
    const rejected = -10;

    /**
     * Processes the course and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param object $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        // Store information for later use in approval.
        global $DB;
        $record = $DB->get_record('lifecyclestep_departmentapprove',
            ['courseid' => $course->id, 'processid' => $processid]);


        $contact = $this->get_contact_for_course($instanceid, $course);
        if (!validate_email($contact)) {
            throw new \moodle_exception('invalidtypeforcontactfield',
                'lifecyclestep_departmentapprove', '', $contact);
        }

        if ($record == false) {
            // Get contact for course.
            echo "trigger email to {$contact} for approval";
            $record = new \stdClass();
            $record->contact = $contact;
            $record->courseid = $course->id;
            $record->instanceid = $instanceid;
            $record->processid = $processid;
            $record->status = self::initial_state;
            $record->confirmtoken = random_string(32);
            $expires = new \DateTime('NOW');
            $expires->add(new \DateInterval('PT30M'));
            $record->confirmtokenexpires = $expires->getTimestamp();
            $record->id = $DB->insert_record('lifecyclestep_departmentapprove', $record);
        }

        switch ($record->status) {
            case self::initial_state:
                // Maybe something went wrong with sending email?
                $record->contact = $contact;
                $this->send_mail($contact, $instanceid, $course, $record->confirmtoken);
                $record->status = self::waiting_for_approval;
                $DB->update_record('lifecyclestep_departmentapprove', $record);
                break;
        }
        return step_response::waiting();
    }

    /**
     * Rollback a course.
     * @param int $processid
     * @param int $instanceid
     * @param stdClass $course
     * @return void
     * @throws \dml_exception
     */
    public function rollback_course($processid, $instanceid, $course) {
        global $DB;
        $DB->delete_records('lifecyclestep_departmentapprove', ['processid' => $processid]);
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'departmentapprove';
    }
    /**
     * Processes the course in status waiting and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param object $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_waiting_course($processid, $instanceid, $course) {
        global $DB;
        mtrace("process_waiting_course");
        $record = $DB->get_record('lifecyclestep_departmentapprove', ['processid' => $processid]);
        switch ($record->status) {
            case self::waiting_for_approval:
                if ($record->confirmtokenexpires < time()) {
                    // department has not yet confirmed.
                    $siteadmins = get_admins();
                    foreach ($siteadmins as $siteadmin) {
                        $success = email_to_user($siteadmin,
                                \core_user::get_noreply_user(),
                                get_string('admin_subject_no_reaction', 'lifecyclestep_departmentapprove'),
                                get_string('admin_body_no_reaction', 'lifecyclestep_departmentapprove') .
                                '<br><br>' . $course->fullname);
                    }
                }
                return step_response::waiting();
            case self::approved:
                mtrace("handle approved course");
                $DB->delete_records('lifecyclestep_departmentapprove', ['processid' => $processid]);
                return step_response::proceed();
            case self::rejected:
                mtrace("handle rejected course");
                $DB->delete_records('lifecyclestep_departmentapprove', ['processid' => $processid]);
                return step_response::rollback();
            default:
                mtrace("keep on waiting");
                return step_response::waiting();
        }
    }

    /**
     * Replaces certain placeholders within the mail template.
     * @param string[] $strings array of mail templates.
     * @param int $stepid Id of the step instance.
     * @param array[] $mailentries Array consisting of course entries from the database.
     * @return string[] array of mail text.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function replace_mail_placeholders($strings, $mailentries) {
        $patterns = [];
        $replacements = [];

        // Replace link to interaction page.
        $patterns[] = '##link##';
        $replacements[] = $strings['link'];

        // Replace html link to interaction page.
        $patterns[] = '##link-html##';
        $replacements[] = \html_writer::link($strings['link'], $strings['link']);

        $courses = [];
        foreach ($mailentries as $entry) {
            $courses[] = get_course($entry->courseid);
        }

        // Replace courses list.
        $coursesstrings = [];
        foreach ($courses as $course) {
            $coursesstrings[] = $course->fullname;
        }
        $patterns[] = '##courses##';
        $replacements[] = join("\r\n", $coursesstrings);
        $patterns[] = '##courses-html##';
        $replacements[] = '<ul><li>' . join("</li><li>", $coursesstrings) . '</li></ul>';

        // Replace short courses list (not actually needed).
        $coursesstrings = [];
        foreach ($courses as $course) {
            $coursesstrings[] = $course->shortname;
        }
        $patterns[] = '##shortcourses##';
        $replacements[] = join("\r\n", $coursesstrings);
        $patterns[] = '##shortcourses-html##';
        $replacements[] = join("<br>", $coursesstrings);

        // Replace link to interaction page.
        $patterns[] = '##sender##';
        $replacements[] = $strings['emailsender'];
        $patterns[] = '##sender-html##';
        $replacements[] = '<a href="mailto:' . $strings['emailsender'] . '">Moodle Administrator</a>';
        
        return str_ireplace($patterns, $replacements, $strings);
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('responsetimeout', PARAM_INT, false),
            new instance_setting('subject', PARAM_TEXT, true),
            new instance_setting('content', PARAM_RAW, true),
            new instance_setting('contenthtml', PARAM_RAW, true),
            new instance_setting('contactfield', PARAM_TEXT, true),
            new instance_setting('timestampfield', PARAM_TEXT, true),
            new instance_setting('emailsender', PARAM_TEXT, true),
        ];
    }

    /**
     * This method can be overriden, to add form elements to the form_step_instance.
     * It is called in definition().
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'responsetimeout';
        $mform->addElement('duration', $elementname, get_string('email_responsetimeout', 'lifecyclestep_departmentapprove'));
        $mform->setType($elementname, PARAM_INT);
        $mform->setDefault('responsetimeout', 2592000);

        $elementname = 'subject';
        $mform->addElement('text', $elementname, get_string('email_subject', 'lifecyclestep_departmentapprove'),
                ['size' => '80']);
        $mform->addHelpButton($elementname, 'email_subject', 'lifecyclestep_departmentapprove');
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'content';
        $mform->addElement('textarea', $elementname, get_string('email_content', 'lifecyclestep_departmentapprove'),
            'wrap="virtual" rows="15" cols="50"');
        $mform->addHelpButton($elementname, 'email_content', 'lifecyclestep_departmentapprove');
        $mform->setType($elementname, PARAM_TEXT);

        $elementname = 'contenthtml';
        $mform->addElement('editor', $elementname, get_string('email_content_html', 'lifecyclestep_departmentapprove'));
        $mform->addHelpButton($elementname, 'email_content_html', 'lifecyclestep_departmentapprove');
        $mform->setType($elementname, PARAM_RAW);

        $elementname = 'emailsender';
        $mform->addElement('text', $elementname, get_string('email_sender', 'lifecyclestep_departmentapprove'),
                ['size' => '80']);
        $mform->addHelpButton($elementname, 'email_sender', 'lifecyclestep_departmentapprove');
        $mform->setType($elementname, PARAM_EMAIL);
        $mform->addRule($elementname, null, 'required');

        $mform->setDefault('subject', get_string('email_subject_default', 'lifecyclestep_departmentapprove'));
        $mform->setDefault('content', get_string('email_content_default', 'lifecyclestep_departmentapprove'));
        $mform->setDefault('contenthtml', get_string('email_content_html_default', 'lifecyclestep_departmentapprove'));

        global $DB;
        $fields = $DB->get_records('customfield_field', ['type' => 'text']);
        $choices = [];
        foreach ($fields as $field) {
            $choices[$field->shortname] = $field->name;
        }
        if ($choices) {
            $mform->addElement('select', 'contactfield',
                    get_string('contact_customfield', 'lifecyclestep_departmentapprove'), $choices);
            $mform->addHelpButton('contactfield', 'contact_customfield',
                    'lifecyclestep_departmentapprove');
        } else {
            $mform->addElement('static', 'nocustomfields',
                    get_string('nocustomfields_warning', 'lifecycletrigger_customfielddelay'),
                    \html_writer::link(new \moodle_url('/course/customfield.php'),
                            get_string('nocustomfields_link', 'lifecycletrigger_customfielddelay')));
        }

        $fields = $DB->get_records('customfield_field', ['type' => 'date']);
        $choices = [];
        foreach ($fields as $field) {
            $choices[$field->shortname] = $field->name;
        }
        if ($choices) {
            $mform->addElement('select', 'timestampfield',
                    get_string('timestamp_customfield', 'lifecyclestep_departmentapprove'), $choices);
            $mform->addHelpButton('timestampfield', 'timestamp_customfield',
                    'lifecyclestep_departmentapprove');
        } else {
            $mform->addElement('static', 'nocustomfields',
                    get_string('nocustomfields_warning', 'lifecycletrigger_customfielddelay'),
                    \html_writer::link(new \moodle_url('/course/customfield.php'),
                            get_string('nocustomfields_link', 'lifecycletrigger_customfielddelay')));
        }
    }

    /**
     * This method can be overriden, to set default values to the form_step_instance.
     * It is called in definition_after_data().
     * @param \MoodleQuickForm $mform
     * @param array $settings array containing the settings from the db.
     */
    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        $mform->setDefault('contenthtml',
                ['text' => isset($settings['contenthtml']) ? $settings['contenthtml'] : '', 'format' => FORMAT_HTML]);
    }

    /**
     * read custom field for course contact from database
     *
     * @param int $instanceid
     * @param \moodle_database|null $DB
     * @param object $course
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function get_contact_for_course(int $instanceid, object $course) {
        $fieldname = settings_manager::get_settings($instanceid, settings_type::STEP)['contactfield'];
        $fields = \core_course\customfield\course_handler::create()->get_fields();
        $records = api::get_instance_fields_data($fields, $course->id);

        // Find matching course field.
        foreach ($records as $d) {
            if ($d->get_field()->get('shortname') !== $fieldname) {
                continue;
            }
            // Found.
            $field = $d->get_field();
            // Check field type.
            if ($field->get('type') != 'text') {
                throw new \moodle_exception('invalidtypeforcontactfield',
                    'lifecyclestep_departmentapprove', '', $fieldname);
            }
            // Read value.
            $contact = $d->get('value');
            if (empty($contact) || strlen(trim($contact)) == 0) {
                throw new \moodle_exception('nocontact',
                    'lifecyclestep_departmentapprove', '', $fieldname);
            }
            if (!validate_email($contact)) {
                throw new \moodle_exception('invalidmail',
                        'lifecyclestep_departmentapprove', '', $fieldname, $contact);
            }
            return  $contact;
        }

        throw new \moodle_exception('missingcontactfield',
            'lifecyclestep_departmentapprove', '', $fieldname);

    }

    /**
     * This is called when a course and the
     * corresponding process get deleted.
     * @param process $process the process that was aborted.
     */
    public function abort_course($process) {
        global $DB;
        $DB->delete_records('lifecyclestep_departmentapprove', ['processid' => $process->id]);
    }

    /**
     * Returns the string of the specific icon for this trigger.
     * @return string icon string
     */
    public function get_icon() {
        return 'i/email';
    }

    /**
     * @param \tool_lifecycle\local\entity\step_subplugin $step
     * @param \moodle_page $PAGE
     * @param \moodle_database|null $DB
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \moodle_exception
     */
    private function send_mail($contact, $instanceid, $course, $token): void {
        global $PAGE, $CFG;
        $settings = settings_manager::get_settings($instanceid, settings_type::STEP);
        // Set system context, since format_text needs a context.
        $PAGE->set_context(\context_system::instance());
        // Format the raw string in the DB to FORMAT_HTML.
        $settings['contenthtml'] = format_text($settings['contenthtml'], FORMAT_HTML);
        $confirmurl = new \moodle_url($CFG->wwwroot . '/admin/tool/lifecycle/step/departmentapprove/department_confirm.php',
            ['token' => $token, 'lang' => 'de']);
        $settings['link'] = $confirmurl;

        // Do not cache the dummy user record to avoid language internationalization issues.
        $dummyuser = core_user::get_noreply_user();
        $dummyuser->email = $contact;
        $dummyuser->firstname = '';
        $dummyuser->maildisplay = '1'; // Show to all.
        $dummyuser->emailstop = 1;
        $dummyuser->mailformat = 1; // HTML

        $parsedsettings = $this->replace_mail_placeholders($settings, [(object) ['courseid' => $course->id]]);
        $subject = $parsedsettings['subject'];
        $content = $parsedsettings['content'];
        $contenthtml = $parsedsettings['contenthtml'];
        if (!empty($contenthtml)) {
            $dummyuser->mailformat = 1; // HTML
        }
        $success = email_to_user($dummyuser, \core_user::get_noreply_user(), $subject, $content,
            $contenthtml, '', '', true,
            $parsedsettings['emailsender'], 'Moodle Administrator');
        if (!$success) {
            throw new \moodle_exception('cannotsendmail',
                'lifecyclestep_departmentapprove', '', '', $contact);
        }
    }
}
