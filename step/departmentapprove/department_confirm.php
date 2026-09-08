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
 * Page for confirming courses by the department (with email link))
 */

use core_customfield\api;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\settings_type;
use tool_lifecycle\step\departmentapprove;

require('../../../../../config.php');
require_once(__DIR__ . '/classes/department_confirm_form.php');
require_once(__DIR__ . '/classes/reject_course_form.php');
require_once(__DIR__ . '/lib.php');

/**
 * update customfield timestamp to match now
 * @param int $instanceid
 * @param int $courseid
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
function update_timestamp(int $instanceid, int $courseid) {
    $fieldname = settings_manager::get_settings($instanceid, settings_type::STEP)['timestampfield'];

    $fields = \core_course\customfield\course_handler::create()->get_fields();
    $context = \context_course::instance($courseid);
    $records = api::get_instance_fields_data($fields, $courseid);

    foreach ($records as $d) {
        $field = $d->get_field();
        if ($field->get('shortname') !== $fieldname) {
            continue;
        }

        $value = time();
        $d->set($d->datafield(), $value);
        $d->set('value', $value);
        $d->set('valuetrust', 0);
        $d->set('contextid', $context->id);
        $d->save();
        return;
    }

    throw new \coding_exception('could not find custom field ' . $fieldname);
}

$token   = required_param('token', PARAM_ALPHANUM);
$reject  = optional_param('reject', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);   //md5 confirmation hash
$rejectform  = optional_param('rejectform', 0, PARAM_INT);

global $PAGE, $COURSE, $DB, $OUTPUT;
$baseurl = '/admin/tool/lifecycle/step/departmentapprove/department_confirm.php';
$PAGE->set_url($baseurl);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

// setup text strings
$strtitle = get_string('departmentapproval', 'lifecyclestep_departmentapprove');

$PAGE->set_pagelayout('base');
$PAGE->set_title($strtitle);
$PAGE->set_heading($COURSE->fullname);

if (!$roleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'))) {
    throw new Exception('The specified role with shortname "editingteacher" does not exist');
}

// Get data from database
$record = $DB->get_record('lifecyclestep_departmentapprove', ['confirmtoken' => $token], '*');
if (!$record) {
    // Token is invalid.
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('invalidtoken', 'lifecyclestep_departmentapprove'),
        \core\output\notification::NOTIFY_ERROR);
    echo $OUTPUT->footer();
    die;
}

$course = get_course($record->courseid);
if (!$course) {
    // Token is invalid.
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('invalidcourse', 'lifecyclestep_departmentapprove'),
        \core\output\notification::NOTIFY_ERROR);
    echo $OUTPUT->footer();
    die;
}

$returnurl = new moodle_url($baseurl, ['token' => $token]);
if (!$rejectform) {
    $form = new department_confirm_form(action: $returnurl);
}

$adminuser = core_user::get_noreply_user();
$adminuser->email  = settings_manager::get_settings($record->instanceid, settings_type::STEP)['emailsender'];
$adminuser->maildisplay = '1'; // Show to all.
$adminuser->emailstop = 1;
$adminuser->mailformat = 1; // HTML
$adminuser->firstname = 'Open Moodle-Admin';


$departmentuser = core_user::get_noreply_user();
$departmentuser->email = trim($record->contact);
$departmentuser->maildisplay = '1'; // Show to all.
$departmentuser->emailstop = 1;
$departmentuser->mailformat = 1; // HTML
$departmentuser->firstname = '';
if ($rejectform || $form->is_cancelled()) {
    // Request is rejected in form => start confirmation dialog.
    $fullname = $course->fullname;
    $reject = $token;
    $options = ['reject'=>$reject, 'confirm'=>md5($reject), 'token'=>$token];
    $rejecturl = new moodle_url($baseurl, $options);

    $options = ['token'=>$token, 'rejectform' => 1];
    $rejectformurl = new moodle_url($baseurl, $options);

    // Prepare the form.
    $rejectform = new reject_course_form($rejectformurl);
    $default = new stdClass();
    $default->reject = $fullname; //$course->id;
    $rejectform->set_data($default);
    // Standard form processing if statement.
    if ($rejectform->is_cancelled()) {
        redirect($returnurl);
    } else if ($data = $rejectform->get_data()) {
    	// User really wants to reject request.
    	$rejected = true;
	    $record->status = departmentapprove::rejected;
	    $DB->update_record('lifecyclestep_departmentapprove', $record);

	    $url = new moodle_url('/course/view.php', ['id' => $course->id]);
        $mailtext = get_string('admin_body_rejected', 'lifecyclestep_departmentapprove', $record) .
                '<br><br>'. $course->fullname . '<br>' . $url;
        // Send email to admin.
	    $sendmail = email_to_user(
	        user: $adminuser,
	        from: core_user::get_noreply_user(),
	        subject: get_string('admin_subject_rejected', 'lifecyclestep_departmentapprove'),
	        messagetext: $mailtext,
	    );
        // Send mail to department.
        $sendmail = email_to_user(
                user: $departmentuser,
                from: core_user::get_noreply_user(),
                subject: get_string('admin_subject_rejected', 'lifecyclestep_departmentapprove'),
                messagetext: get_string('admin_body_rejected', 'lifecyclestep_departmentapprove', $record) .
                '<br><br>'. $course->fullname,
        );

        // Send email to teachers.
        $context  = context_course::instance($record->courseid);
        $roleusers = get_role_users($roleid, $context, false, 'u.id, u.lastname, u.firstname');
        foreach ($roleusers as $roleuser) {
            $sendmail = email_to_user(
                    user: \core_user::get_user($roleuser->id),
                    from: core_user::get_noreply_user(),
                    subject: get_string('admin_subject_rejected', 'lifecyclestep_departmentapprove'),
                    messagetext: $mailtext,
            );
        }

        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('rejectedbydepartment', 'lifecyclestep_departmentapprove'),
                \core\output\notification::NOTIFY_INFO);
        echo $OUTPUT->footer();
        die;
	}

    // Display the form for giving a reason for rejecting the request.
    echo $OUTPUT->header($rejectform->focus());
    $rejectform->display();
    echo $OUTPUT->footer();
    exit;
}

if ($data = $form->get_data()) {
    // Course is approved.
    $record->status = departmentapprove::approved;
    $DB->update_record('lifecyclestep_departmentapprove', $record);

    update_timestamp($record->instanceid, $course->id);

    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('approvedbydepartment', 'lifecyclestep_departmentapprove'),
        \core\output\notification::NOTIFY_SUCCESS);
    echo $OUTPUT->footer();

    $sendmail = email_to_user(
        user: $adminuser,
        from: core_user::get_noreply_user(),
        subject: get_string('admin_subject_approved', 'lifecyclestep_departmentapprove'),
        messagetext: get_string('admin_body_approved', 'lifecyclestep_departmentapprove') . '<br><br>' .
        $course->fullname,
    );

    $contactmail = core_user::get_noreply_user();
    $contactmail->email  = $record->contact;
    $contactmail->maildisplay = '1'; // Show to all.
    $contactmail->emailstop = 1;
    $contactmail->firstname = '';
    $contactmail->mailformat = 1; // HTML
    $sendmail = email_to_user(
        user: $contactmail,
        from: core_user::get_noreply_user(),
        subject: get_string('admin_subject_approved', 'lifecyclestep_departmentapprove'),
        messagetext: get_string('admin_body_approved', 'lifecyclestep_departmentapprove') . '
         
        '. $course->fullname,
    );

    die;
}

// No action yet => set data for displaying form.
$context  = context_course::instance($record->courseid);
$roleusers = get_role_users($roleid, $context, false, 'u.id, u.firstname, u.lastname');
$teachers = '';
foreach ($roleusers as $roleuser) {
    $teachers .= $roleuser->firstname . ' ' . $roleuser->lastname . ' ';
}

$coursefields = new \stdClass();
$coursefields->fullname = $course->fullname;
$coursefields->summary = $course->summary;
$coursefields->startdate = $course->startdate;
$coursefields->enddate = $course->enddate;
$coursefields->teachers = $teachers;

$form->set_data($coursefields);

echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
$form->display();
echo $OUTPUT->footer();
