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
 * Lang strings for department approve
 *
 * @package lifecyclestep_departmentapprove
 * @copyright  2026 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action_prevented_deletion'] = '{$a} prevented deletion';
$string['course_approved'] = 'Course approved';
$string['course_not_approved'] = 'Course not approved';
$string['email_content'] = 'Content plain text template';
$string['email_content_default'] = 'Dear ...';
$string['email_content_help'] = 'Set the template for the content of the email (plain text, alternatively you can use HTML template for HTML email below)' . '<p>' . 'You can use the following placeholders:'
        . '<br>' . 'First name of recipient: ##firstname##'
        . '<br>' . 'Last name of recipient: ##lastname##'
        . '<br>' . 'Link to response page: ##link##'
        . '<br>' . 'Impacted courses: ##courses##'
        . '<br>' . 'Short names of impacted courses: ##shortcourses##'
        . '<br>' . 'Sender: ##sender##'
        . '</p>';
$string['email_content_html'] = 'Content HTML Template';
$string['email_content_html_default'] = 'Dear ...';
$string['email_content_html_help'] = 'Set the html template for the content of the email (HTML email, will be used instead of plaintext field if not empty!)' . '<p>' .  'You can use the following placeholders:'
        . '<br>' . 'First name of recipient: ##firstname##'
        . '<br>' . 'Last name of recipient: ##lastname##'
        . '<br>' . 'Link to response page: ##link-html##'
        . '<br>' . 'Impacted courses: ##courses-html##'
        . '<br>' . 'Short names of impacted courses: ##shortcourses-html##'
        . '<br>' . 'Sender with mailto link: ##sender-html##'
        . '</p>';

$string['email_responsetimeout'] = 'Time the user has for the response';
$string['email_subject'] = 'Subject template';
$string['email_subject_default'] = 'Course approval required';
$string['email_subject_help'] = 'Set the template for the subject of the email.' . '<p>' . 'You can use the following placeholders:'
        . '<br>' . 'First name of recipient: ##firstname##'
        . '<br>' . 'Last name of recipient: ##lastname##'
        . '<br>' . 'Link to response page: ##link##'
        . '<br>' . 'Impacted courses: ##courses##'
        . '</p>';
$string['plugindescription'] = 'Asks the course approval contact for approval of a triggered course.';
$string['pluginname'] = 'Department approval step';
$string['email_sender'] = 'Reply-to email address';
$string['email_sender_help'] = 'Email address used as sender for email so that the user can directly reply to the address';


$string['status_message_requiresattention'] = 'Course is marked for approval';

$string['departmentapproval'] = 'Renewal of course approval';
$string['teachers'] = 'Teachers';
$string['approve'] = 'Approve course';
$string['reallyreject'] = 'Is the course "{$a}" really no longer being offered?';
$string['reject'] = 'Do not approve the course any further';
$string['rejectedbydepartment'] = 'The denial of the course approval was forwarded.';
$string['approvedbydepartment'] = 'The course has been approved and can now continue to be offered.';
$string['invalidtoken'] = 'The request has already been processed, or the link is invalid.';
$string['invalidcourse'] = 'Course was not found.';
$string['admin_subject_rejected'] = 'Open Moodle course approval REJECTED by department';
$string['admin_subject_approved'] = 'Open Moodle course approval';
$string['admin_subject_no_reaction'] = 'Open Moodle course approval missing';
$string['admin_body_rejected'] = 'The following course MUST NOT be offered anymore:';
$string['admin_body_approved'] = 'The following course will continue:';
$string['admin_body_no_reaction'] = 'The following course is not yet approved by department:';

$string['coursereasonforrejecting'] = 'Your reasons for rejecting this course';
$string['coursereasonforrejectingemail'] = 'This will be emailed to the course responsible person';

$string['contact_customfield'] = 'Contact custom course field ';
$string['contact_customfield_help'] = 'Contact custom course field, should be type text';
$string['timestamp_customfield'] = 'Department approval custom course field ';
$string['timestamp_customfield_help'] = 'Custom course field, should be type datetime';

