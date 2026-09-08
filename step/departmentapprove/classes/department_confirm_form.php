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

        // When two elements we need a group.
        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'confirm',
            get_string('approve', 'lifecyclestep_departmentapprove'), ['class' => 'form-submit']);
        $buttonarray[] = &$mform->createElement('cancel', 'cancel',
            get_string('reject', 'lifecyclestep_departmentapprove') . '...', ['class' => 'btn-danger']); // todo: use danger...
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

}
