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
 * Step subplugin for course duplication.
 *
 * @package    lifecyclestep_duplicate2
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lifecycle\step;

use stdClass;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\local\manager\process_data_manager;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../duplicate/lib.php');
require_once(__DIR__ . '/../moveparentcategory/lib.php');

/**
 * Step subplugin for course duplication.
 *
 * @package    lifecyclestep_duplicate2
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class duplicate2 extends duplicate {

    /**
     * helper function to improve readability
     * @param $instanceid
     * @param $key
     * @return mixed
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private static function get_setting($instanceid, $key) {
        return  settings_manager::get_settings(
            $instanceid, settings_type::STEP
        )[$key];
    }

    /**
     * Processes the course and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param stdClass $course to be processed.
     * @return step_response
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        $course = get_course($course->id);

        $move = self::get_setting($instanceid, 'move');
        $fullname = process_data_manager::get_process_data($processid, $instanceid, self::PROC_DATA_COURSEFULLNAME);
        $shortname = process_data_manager::get_process_data($processid, $instanceid, self::PROC_DATA_COURSESHORTNAME);
        if (!empty($fullname) && !empty($shortname)) {
            try {
                if ($move) {
                    $targetcategory = self::get_setting($instanceid, 'targetcategory');
                    $createparents = self::get_setting($instanceid, 'createparents');
                    if ($createparents) {
                        $notoplevel = self::get_setting($instanceid, 'notoplevel');
                        $maxdepth = self::get_setting($instanceid, 'maxdepth');
                        $categoryid = moveparentcategory::create_hierarchy_for_moving_course($course,
                            $notoplevel, $targetcategory, $maxdepth);
                    } else {
                        $categoryid = $targetcategory;
                    }
                } else {
                    $categoryid = $course->category;
                }
                $this->duplicate_course(
                    $course->id,
                    $fullname,
                    $shortname,
                    $categoryid,
                    $course->visible,
                    []);
            } catch (\moodle_exception $e) {
                if ($e->getCode() == 'shortnametaken') {
                    process_data_manager::set_process_data($processid, $instanceid, self::PROC_DATA_COURSESHORTNAME, '');
                    return step_response::waiting();
                }
            }
            return step_response::proceed();
        }
        return step_response::waiting();
    }


    /**
     * Settings for the moveparentcategory step
     *
     * @return instance_setting[] categorymoveto
     */
    public function instance_settings() {
        return [
            new instance_setting('move', PARAM_BOOL),
            new instance_setting('targetcategory', PARAM_INT),
            new instance_setting('createparents', PARAM_BOOL),
            new instance_setting('notoplevel', PARAM_BOOL),
            new instance_setting('maxdepth', PARAM_INT),
        ];
    }

    /**
     * Form elements for the instance.
     *
     * @param \MoodleQuickForm $mform
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function extend_add_instance_form_definition($mform) {
        $elementname = 'move';
        $mform->addElement('advcheckbox', $elementname,
                get_string($elementname, 'lifecyclestep_duplicate2'), '', null, [0, 1]);
        $mform->setDefault($elementname, 0);
        $mform->addHelpButton($elementname, $elementname, 'lifecyclestep_duplicate2');
        $mform->setType($elementname, PARAM_BOOL);

        $elementname = 'targetcategory';
        // Fetch a complete list of courses and let it be shown in a flat hierarchical view with all parent branches.
        $cats = \core_course_category::make_categories_list('moodle/course:changecategory');
        $displaylist = [0 => '('. get_string('toplevel', 'lifecyclestep_moveparentcategory') .')'] +
            $cats;
        $options = [
                'multiple' => false,
                'noselectionstring' => get_string('toplevel', 'lifecyclestep_moveparentcategory'),
        ];

        $mform->addElement('autocomplete', $elementname,
                get_string($elementname, 'lifecyclestep_duplicate2'), $displaylist, $options);
        $mform->setDefault($elementname, 0);
        $mform->addHelpButton($elementname, $elementname, 'lifecyclestep_duplicate2');
        $mform->setType($elementname, PARAM_INT);
        $mform->hideIf($elementname, 'move', 'notchecked');

        $elementname = 'createparents';
        $mform->addElement('advcheckbox', $elementname,
                get_string($elementname, 'lifecyclestep_duplicate2'), '', null, [0, 1]);
        $mform->setDefault($elementname, 0);
        $mform->addHelpButton($elementname, $elementname, 'lifecyclestep_duplicate2');
        $mform->setType($elementname, PARAM_BOOL);
        $mform->hideIf($elementname, 'move', 'notchecked');

        $elementname = 'notoplevel';
        $mform->addElement('advcheckbox', $elementname,
                get_string($elementname, 'lifecyclestep_duplicate2'), '', null, [0, 1]);
        $mform->setDefault($elementname, 0);
        $mform->addHelpButton($elementname, $elementname, 'lifecyclestep_duplicate2');
        $mform->setType($elementname, PARAM_BOOL);
        $mform->hideIf($elementname, 'move', 'notchecked');
        $mform->hideIf($elementname, 'createparents', 'notchecked');

        $elementname = 'maxdepth';
        $mform->addElement('text', $elementname,
            get_string($elementname, 'lifecyclestep_moveparentcategory'));
        $mform->addHelpButton($elementname, $elementname, 'lifecyclestep_moveparentcategory');
        $mform->setType($elementname, PARAM_INT);
        $mform->hideIf($elementname, 'move', 'notchecked');
        $mform->hideIf($elementname, 'createparents', 'notchecked');
    }

    /**
     * The return value should be equivalent with the name of the subplugin folder.
     * @return string technical name of the subplugin
     */
    public function get_subpluginname() {
        return 'duplicate2';
    }
}
