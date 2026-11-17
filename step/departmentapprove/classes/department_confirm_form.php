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
 * Forgot password page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Reset forgotten password form definition.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2006 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class department_confirm_form extends moodleform {

    /**
     * Define the forgot password form.
     */
    function definition() {

        $mform = $this->_form;

        $mform->addElement('text', 'fullname', get_string('fullnamecourse'),
            'size="100"');
        $mform->setType('fullname', PARAM_RAW_TRIMMED);

        $mform->addElement('static', 'summary', get_string('coursesummary'),
            'size="100"');
        $mform->setType('summary', PARAM_RAW_TRIMMED);


        $mform->addElement('text', 'teachers', get_string('teachers', 'lifecyclestep_departmentapprove'),
            'size="100"');
        $mform->setType('teachers', PARAM_RAW_TRIMMED);

        $mform->addElement('date_time_selector', 'startdate', get_string('startdate'));
        $mform->setType('startdate', PARAM_RAW_TRIMMED);
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate'));
        $mform->setType('enddate', PARAM_RAW_TRIMMED);

        foreach ($mform->_elements as $element) {
            $mform->disabledIf($element->getName(), 'teachers', 'neq', 'xxx');
        }
        $mform->disabledIf('teachers', 'fullname', 'neq', 'xxx');

        // Metadata from the course request (block_om_course_request), if available.
        // Static elements only (read-only), so they are added after the disabledIf loop.
        $request = $this->_customdata['courserequest'] ?? null;
        if ($request) {
            $this->add_request_metadata($request);
        }

        // When two elements we need a group.
        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'confirm',
            get_string('approve', 'lifecyclestep_departmentapprove'), ['class' => 'form-submit']);
        $buttonarray[] = &$mform->createElement('cancel', 'cancel',
            get_string('reject', 'lifecyclestep_departmentapprove') . '...', ['class' => 'btn-danger']); // todo: use danger...
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Add read-only information from the original course request.
     *
     * @param stdClass $request record from table em_course_request
     */
    protected function add_request_metadata(stdClass $request) {
        $mform = $this->_form;
        $component = 'lifecyclestep_departmentapprove';

        $mform->addElement('header', 'requestheader',
            get_string('requestdata', $component, userdate($request->timecreated, get_string('strftimedate'))));
        $mform->setExpanded('requestheader', true);

        // Paid course.
        $mform->addElement('static', 'req_withcosts', get_string('req_withcosts', $component),
            self::yesno($request->withcosts));

        // External partners.
        $mform->addElement('static', 'req_partners', get_string('req_partners', $component),
            self::yesno($request->partners));

        // Participants/teachers with residence outside Germany.
        $mform->addElement('static', 'req_foreign', get_string('req_foreign', $component),
            self::any_yes($request->foreignstudents, $request->foreignteachers));

        // Participants/teachers with residence outside the EU.
        $mform->addElement('static', 'req_noneu', get_string('req_noneu', $component),
            self::any_yes($request->noneu_students, $request->noneu_teachers));

        // Countries (main country from select + further countries as free text).
        $countries = [];
        if (!empty($request->country)) {
            $list = get_string_manager()->get_list_of_countries();
            $countries[] = $list[$request->country] ?? $request->country;
        }
        if (!empty(trim($request->countries ?? ''))) {
            $countries[] = trim($request->countries);
        }
        $mform->addElement('static', 'req_countries', get_string('req_countries', $component),
            $countries ? s(implode(', ', $countries)) : '-');
    }

    /**
     * Format a boolean request value.
     *
     * @param mixed $value 0, 1 or null
     * @return string
     */
    protected static function yesno($value): string {
        if ($value === null || $value === '') {
            return '-';
        }
        return $value ? get_string('yes') : get_string('no');
    }

    /**
     * Combine values for students and teachers: yes if at least one of them is yes.
     *
     * @param mixed $students 0, 1 or null
     * @param mixed $teachers 0, 1 or null
     * @return string
     */
    protected static function any_yes($students, $teachers): string {
        if (!empty($students) || !empty($teachers)) {
            return get_string('yes');
        }
        $answered = array_filter([$students, $teachers], fn($v) => $v !== null && $v !== '');
        return $answered ? get_string('no') : '-';
    }
}
