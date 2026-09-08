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
 * Interface for the subplugintype step
 * It has to be implemented by all subplugins.
 *
 * @package    lifecyclestep_moveparentcategory
 * @copyright  2019 Yorick Reum JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_lifecycle\step;

use core\exception\coding_exception;
use core\exception\moodle_exception;
use stdClass;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Class for processing courses.
 */
class moveparentcategory extends libbase {

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
     * evaluate id of target course category (used recursion)
     *
     * @param array $sourceparentids parent category ids of source category including source cat id
     * @param mixed $targetcat taget category
     * @return mixed
     * @throws \core\exception\moodle_exception
     */
    private static function get_target_category_id($sourceparentids, $targetcat) {
        if (count($sourceparentids) == 0) {
            // No parents => we are finished.
            if (!isset($targetcat)) {
                throw new coding_exception("invalidparameter");
            }

            return $targetcat->id;
        }

        // Get top level category.
        $sourceroot = \core_course_category::get($sourceparentids[0]);
        // Remove first element (sourceroot) from parent list.
        array_shift($sourceparentids);

        // Look for top level category in target.
        $targetchildren = $targetcat->get_children();

        foreach ($targetchildren as $targetchild) {
            if ($targetchild->id == $sourceroot->id ||
                    strcmp($targetchild->name, $sourceroot->name) == 0) {
                // Top level category already exists in target
                // => recursion.
                return self::get_target_category_id($sourceparentids, $targetchild);
            }
        }

        if (!isset($targetcat)) {
            throw new coding_exception("invalidparameter");
        }

        // Not found => create new category.
        // Course category does not yet exist => create it.
        $catdata = new stdClass();
        $catdata->name = $sourceroot->name;
        $catdata->description = $sourceroot->description;
        $catdata->descriptionformat = $sourceroot->descriptionformat;
        $targetchild = \core_course_category::create($catdata);
        $targetchild->change_parent($targetcat);
        return self::get_target_category_id($sourceparentids, $targetchild);
    }

    /**
     * Processes the course and returns a response.
     * The response tells either
     *  - that the subplugin is finished processing.
     *  - that the subplugin is not yet finished processing.
     *  - that a rollback for this course is necessary.
     *
     * Sample:
     * 1 -> 2 -> 3
     * => 3->parents() = [1,2]
     * @param int $processid of the respective process.
     * @param int $instanceid of the step instance.
     * @param stdClass $course to be processed.
     * @return step_response
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function process_course($processid, $instanceid, $course) {
        $categoryid = self::get_setting($instanceid, 'categorytomoveto');
        $createparents = self::get_setting($instanceid, 'createparents');
        if ($createparents) {
            $notoplevel = self::get_setting($instanceid, 'notoplevel');
            $maxdepth = self::get_setting($instanceid, 'maxdepth');
            $categoryid = self::create_hierarchy_for_moving_course($course, $notoplevel, $categoryid, $maxdepth);
        }

        $success = move_courses(
            [$course->id], $categoryid
        );

        if ($success) {
            return step_response::proceed();
        } else {
            return step_response::rollback();
        }
    }

    /**
     * Settings for the moveparentcategory step
     *
     * @return instance_setting[] categorymoveto
     */
    public function instance_settings() {
        return [
            new instance_setting('categorytomoveto', PARAM_INT),
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

        $elementname = 'categorytomoveto';

        // Fetch a complete list of courses and let it be shown in a flat hierarchical view with all parent branches.
        $cats = \core_course_category::make_categories_list('moodle/course:changecategory');
        $displaylist = [0 => '('. get_string('toplevel', 'lifecyclestep_moveparentcategory') .')'] +
            $cats;

        $options = [
            'multiple' => false,
            'noselectionstring' => get_string('toplevel', 'lifecyclestep_moveparentcategory'),
        ];

        $mform->addElement('autocomplete', $elementname,
            get_string('categorytomoveto', 'lifecyclestep_moveparentcategory'), $displaylist, $options);
        $mform->setDefault($elementname, 0);
        $mform->addHelpButton($elementname, 'categorytomoveto', 'lifecyclestep_moveparentcategory');
        $mform->setType($elementname, PARAM_INT);

        $elementname = 'createparents';
        $mform->addElement('advcheckbox', $elementname,
            get_string($elementname, 'lifecyclestep_moveparentcategory'), '', null, [0, 1]);
        $mform->setDefault($elementname, 0);
        $mform->addHelpButton($elementname, $elementname, 'lifecyclestep_moveparentcategory');
        $mform->setType($elementname, PARAM_BOOL);

        $elementname = 'notoplevel';
        $mform->addElement('advcheckbox', $elementname,
            get_string($elementname, 'lifecyclestep_moveparentcategory'), '', null, [0, 1]);
        $mform->setDefault($elementname, 0);
        $mform->addHelpButton($elementname, $elementname, 'lifecyclestep_moveparentcategory');
        $mform->setType($elementname, PARAM_BOOL);
        $mform->hideIf($elementname, 'createparents', 'notchecked');

        $elementname = 'maxdepth';
        $mform->addElement('text', $elementname,
            get_string($elementname, 'lifecyclestep_moveparentcategory'));
        $mform->addHelpButton($elementname, $elementname, 'lifecyclestep_moveparentcategory');
        $mform->setType($elementname, PARAM_INT);
        $mform->hideIf($elementname, 'createparents', 'notchecked');

    }

    /**
     * Function to return subpluginname
     *
     * @return string subpluginname
     */
    public function get_subpluginname() {
        return 'moveparentcategory';
    }

    /**
     * create hierarchy for moving a course to another category
     * @param stdClass $course
     * @param mixed $notoplevel
     * @param mixed $categoryid
     * @return array
     * @throws moodle_exception
     */
    public static function create_hierarchy_for_moving_course(stdClass $course, mixed $notoplevel, mixed $categoryid, $maxdepth) {
        $sourcecat = \core_course_category::get($course->category);
        $sourceparentids = $sourcecat->get_parents();
        // Add current category for course.
        $sourceparentids[] = $sourcecat->id;
        if ($notoplevel) {
            // If top level category shall be ignored then remove it.
            array_shift($sourceparentids);
        }

        if ($maxdepth > 0) {
            // Truncate source categories.
            $sourceparentids = array_slice($sourceparentids, 0, $maxdepth);
        }

        if (isset($categoryid) && $categoryid > 0) {
            // Get target category.
            $targetcat = \core_course_category::get($categoryid);
        } else {
            // Special case:
            // Move from archive to normal category space.
            // => Remove first category from source,
            // which is supposed to be archive category.
            $targetcat = \core_course_category::top();
        }
        $categoryid = self::get_target_category_id($sourceparentids, $targetcat);
        return $categoryid;
    }
}
