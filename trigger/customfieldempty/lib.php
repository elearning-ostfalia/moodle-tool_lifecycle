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

namespace tool_lifecycle\trigger;

use moodle_url;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\trigger_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../../lib.php');

/**
 * Trigger which triggers if a custom field is empty.
 * @package lifecycletrigger_customfieldempty
 * @copyright  2025 Thomas Niedermaier University Münster
 * @copyright  2020 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customfieldempty extends base_automatic {

    /**
     * If check_course_code() returns true, code to check the given course is placed here
     * @param object $course
     * @param int $triggerid
     * @return trigger_response
     */
    public function check_course($course, $triggerid) {
        return trigger_response::trigger();
    }

    /**
     * Returns whether the lib function check_course contains particular selection code per course or not.
     * @return bool
     */
    public function check_course_code() {
        return false;
    }

    /**
     * Add sql comparing the current date to the start date of a course in combination with the specified delay.
     * @param int $triggerid Id of the trigger.
     * @return array A list containing the constructed sql fragment and an array of parameters.
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_course_recordset_where($triggerid) {
        global $DB;
        $fieldname = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['customfield'];
        if (!($field = $DB->get_record('customfield_field', ['shortname' => $fieldname]))) {
            throw new \moodle_exception('missingfield',
                'lifecycletrigger_customfieldempty', '', $fieldname);
        }
        $invert = settings_manager::get_settings($triggerid, settings_type::TRIGGER)['invert'];
        $notemptyselect = "(select cxt.instanceid
                from {context} cxt
                join {customfield_data} d ON d.contextid = cxt.id AND cxt.contextlevel=" . CONTEXT_COURSE . "
                WHERE d.fieldid = :customfielid AND d.value IS NOT NULL AND d.value != '0' AND d.value != '')";
        $where = "c.id " . ($invert?"":" not ") . " in " . $notemptyselect;

        $params = ["customfielid" => $field->id];
        return [$where, $params];
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'customfieldempty';
    }

    /**
     * Defines which settings each instance of the subplugin offers for the user to define.
     * @return instance_setting[] containing settings keys and PARAM_TYPES
     */
    public function instance_settings() {
        return [
            new instance_setting('customfield', PARAM_TEXT),
            new instance_setting('invert', PARAM_BOOL),
        ];
    }

    /**
     * At the delay since the start date of a course.
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function extend_add_instance_form_definition($mform) {
        global $DB;
        $where = "type = 'date' or type = 'text' or type = 'number'";
        $fields = $DB->get_records_select('customfield_field', $where);
        $choices = [];
        foreach ($fields as $field) {
            $choices[$field->shortname] = $field->name;
        }
        if ($choices) {
            $mform->addElement('select', 'customfield',
                get_string('customfield', 'lifecycletrigger_customfieldempty'), $choices);
            $mform->addHelpButton('customfield', 'customfield',
                'lifecycletrigger_customfieldempty');

            $mform->addElement('advcheckbox', 'invert',
                get_string('invert', 'lifecycletrigger_customfieldempty'),
                ' ');
            $mform->addHelpButton('invert', 'invert',
                'lifecycletrigger_customfieldempty');

        } else {
            $mform->addElement('static', 'nocustomfields',
                get_string('nocustomfields_warning', 'lifecycletrigger_customfieldempty'),
                    \html_writer::link(new moodle_url('/course/customfield.php'),
                        get_string('nocustomfields_link', 'lifecycletrigger_customfieldempty')));
        }
    }

}
