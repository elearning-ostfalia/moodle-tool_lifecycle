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
$string['course_approved'] = 'Course approval extended';
$string['course_not_approved'] = 'Course offering to be discontinued';
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
$string['email_subject_default'] = 'Extension of course approval required';
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

$string['departmentapproval'] = 'Extension of course approval';
$string['teachers'] = 'Teachers';
$string['approve'] = 'Extend approval';
$string['reallyreject'] = 'Do you really want to discontinue the course offering "{$a}"?';
$string['reject'] = 'Discontinue course offering';
$string['rejectedbydepartment'] = 'Your notice that the course offering is to be discontinued was forwarded.';
$string['approvedbydepartment'] = 'The course approval has been extended. The course can continue to be offered.';
$string['invalidtoken'] = 'The request has already been processed, or the link is invalid.';
$string['invalidcourse'] = 'Course was not found.';
$string['alreadyapproved'] = 'The approval for this course has already been extended. It cannot be processed again.';
$string['alreadyrejected'] = 'It has already been reported that this course offering is to be discontinued. It cannot be processed again.';
$string['admin_subject_rejected'] = 'Open Moodle course offering TO BE DISCONTINUED by department';
$string['admin_subject_approved'] = 'Open Moodle course approval extended';
$string['admin_subject_no_reaction'] = 'Open Moodle course approval: no response from department';
$string['admin_body_rejected'] = 'The department has reported that the course offering is to be discontinued. The following course MUST NOT be offered anymore:';
$string['admin_body_approved'] = 'The approval has been extended. The following course will continue:';
$string['admin_body_no_reaction'] = 'The responsible department ({$a->contact}) has not responded to the request for extending the course approval within the deadline. A reminder has just been sent to the department, the link remains valid. If there is no response by {$a->nextdeadline}, another reminder will be sent. The following course is affected:';
$string['reminder_subject_prefix'] = 'Reminder: ';

$string['coursereasonforrejecting'] = 'Your reasons for discontinuing the course offering';
$string['coursereasonforrejectingemail'] = 'This will be emailed to the course responsible person';

$string['contact_customfield'] = 'Contact custom course field ';
$string['contact_customfield_help'] = 'Contact custom course field, should be type text';
$string['timestamp_customfield'] = 'Department approval custom course field ';
$string['timestamp_customfield_help'] = 'Custom course field, should be type datetime';


$string['requestdata'] = 'Information from course request ({$a})';
$string['req_withcosts'] = 'Paid course';
$string['req_partners'] = 'External partners';
$string['req_foreign'] = 'Externals with residence outside Germany';
$string['req_noneu'] = 'Externals with residence outside the EU';
$string['req_countries'] = 'Countries';
