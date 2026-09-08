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
 * Unit tests for step lifecyclestep_moveparentcategory
 *
 * @package    lifecyclestep_moveparentcategory
 * @copyright  2025 Ostfalia.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lifecyclestep_moveparentcategory;

defined('MOODLE_INTERNAL') || die();

/*
 * if you want more debugging output then remove comment:
define('DEBUG_HIERARCHY', 'x');
*/

require_once(__DIR__ . '/../../../tests/generator/lib.php');

use tool_lifecycle\local\manager\process_manager;
use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\manager\workflow_manager;
use tool_lifecycle\processor;


/**
 * Simple helper class to make debugging easier.
 *
 * @package    lifecyclestep_moveparentcategory
 * @group      lifecyclestep_moveparentcategory
 * @category   test
 * @copyright  2025 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_category {
    /** @var array|mixed  child course category ids */
    public $children = [];
    /** @var array|mixed course ids */
    public $courses = [];

    /**
     * constructor
     * @param $children
     * @param $courses
     */
    public function __construct($children = null, $courses = null) {
        if (!empty($children)) {
            $this->children = $children;
        }
        if (!empty($courses)) {
            $this->courses = $courses;
        }
    }
}


/**
 * Tests the make invisible step.
 *
 * @package    lifecyclestep_moveparentcategory
 * @group      lifecyclestep_moveparentcategory
 * @category   test
 * @copyright  2025 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class make_move_test extends \advanced_testcase {

    /** @var \core_course_category|false|null category 1 */
    private $category1;
    /** @var \core_course_category|false|null category 2 */
    private $category2;
    /** @var \core_course_category|false|null category 3 */
    private $category3;
    /** @var \core_course_category|false|null category archive */
    private $categorya;

    /**
     * Setup the testcase.
     */
    public function setUp(): void {
        global $USER;

        parent::setUp();

        $this->resetAfterTest(true);

        // Create course categories.
        $this->category1 = \core_course_category::create(['name' => 'Cat1']);
        $this->category2 = \core_course_category::create(['name' => 'Cat2', 'parent' => $this->category1->id]);
        $this->category3 = \core_course_category::create(['name' => 'Cat3', 'parent' => $this->category2->id]);
        $this->categorya = \core_course_category::create(['name' => 'Archive']);

        // We do not need a sesskey check in theses tests.
        $USER->ignoresesskey = true;
    }

    /**
     * Start and trigger workflow
     * @param $data
     * @param $courses
     * @return array[]
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \dml_transaction_exception
     * @throws \moodle_exception
     */
    private function start_and_trigger_workflow($data, $courses) {
        self::print_hierarchy('*** Hierarchy before starting workflow ***');
        $hierachy1 = self::categories_as_array();

        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');
        $workflow = $generator->create_workflow([], []);
        $trigger = $generator->create_trigger('manual', 'manual', $workflow->id);
        $step1 = $generator->create_step('moveparentcategory', 'moveparentcategory', $workflow->id);

        settings_manager::save_settings($step1->id, \tool_lifecycle\settings_type::STEP, 'moveparentcategory', $data);

        $step2 = $generator->create_step('email', 'email', $workflow->id);
        settings_manager::save_settings($step2->id, \tool_lifecycle\settings_type::STEP, 'email', null);
        workflow_manager::handle_action(\tool_lifecycle\action::WORKFLOW_ACTIVATE, $workflow->id);

        foreach ($courses as $course) {
            process_manager::manually_trigger_process($course->id, $trigger->id);
        }
        $processor = new processor();
        $processor->process_courses();
        self::print_hierarchy('*** Hierarchy after test ***');
        $hierachy2 = self::categories_as_array();
        return [$hierachy1, $hierachy2];
    }

    /**
     * create category as test object
     * @param $cat
     * @param $result
     * @return test_category
     */
    private static function category_as_object($cat, &$result): test_category {
        $courses = $cat->get_courses();
        $coursearray = [];
        foreach ($courses as $course) {
            $coursearray[] = $course->shortname;
        }

        $catchildren = $cat->get_children();
        $childrenarray = [];
        foreach ($catchildren as $child) {
            $childrenarray[$child->name] = self::category_as_object($child, $childrenarray);
        }

        return new test_category($childrenarray, $coursearray);
    }

    /**
     * create current course hierarchy as test object structure
     * @return array
     */
    private static function categories_as_array() {
        $result = [];

        $options = [];
        $options['returnhidden'] = true;
        $cats = \core_course_category::get_all($options);
        foreach ($cats as $cat) {
            if (empty($cat->get_parents())) {
                // Skip non root categories.
                $result[$cat->name] = self::category_as_object($cat, $result);
            }
        }
        return $result;
    }

    /**
     * output of course category for one category
     * @param $cat
     * @param $index
     * @return void
     */
    private static function print_tree($cat, $index = 0) {
        $courses = $cat->get_courses();

        $indent = str_repeat(" ", $index);
        echo $indent . $cat->name . ' (' . $cat->id . '):';
        foreach ($courses as $course) {
            echo ' ' . $course->shortname . ' (' . $course->id . ')';
        }
        echo PHP_EOL;

        $children = $cat->get_children();
        foreach ($children as $child) {
            self::print_tree($child, $index + 4);
        }
    }

    /**
     * output of course category for all current categories
     * @param $text
     * @return void
     */
    private static function print_hierarchy($text) {
        if (!defined('DEBUG_HIERARCHY')) {
            return;
        }
        if (defined('DEBUG_HIERARCHY')) {
            echo $text . PHP_EOL;
        }
        $options = [];
        $options['returnhidden'] = true;
        $cats = \core_course_category::get_all($options);
        foreach ($cats as $cat) {
            if (empty($cat->get_parents())) {
                // Skip non root categories.
                self::print_tree($cat, 0);
            }
        }
    }

    /* T E S T S */

    /**
     * Test moving to archive:
     * - Archive
     *  - Cat1
     *    - Cat2
     *      - Cat3

     *  =>
     *  - Cat1
     *    - Cat2
     *      - Cat3
     *  - Archive
     *    - Cat1
     *      - Cat2
     *        - Cat3
     *
     * @covers \tool_lifecycle\step\moveparentcategory
     * @return void
     */
    public function test_move_full_hierachy_under_archive(): void {
        $course3 = $this->getDataGenerator()->create_course(['category' => $this->category3->id]);

        $data = new \stdClass();
        $data->categorytomoveto = $this->categorya->id;
        $data->createparents = true;
        $data->notoplevel = false;
        $data->maxdepth = 0;

        [$hierachy1, $hierachy2] = $this->start_and_trigger_workflow($data, [$course3]);

        $expectedcat3 = new test_category();
        $exceptedcat2 = new test_category(['Cat3' => $expectedcat3]);
        $expectedcat1 = new test_category(['Cat2' => $exceptedcat2]);

        $expectedcat4arch = new test_category([], [$course3->shortname]);
        $expectedcat3arch = new test_category(['Cat3' => $expectedcat4arch]);
        $exceptedcat2arch = new test_category(['Cat2' => $expectedcat3arch]);
        $expectedcat1arch = new test_category(['Cat1' => $exceptedcat2arch]);

        // Modify hierarchy1 so that it fits the expected result.
        $hierachy1['Archive'] = $expectedcat1arch;
        $hierachy1['Cat1'] = $expectedcat1;

        $this->assertEquals($hierachy1, $hierachy2);
    }

    /**
     * Test moving to archive:
     * - Archive
     *  - Cat1
     *    - Cat2
     *      - Cat3

     *  =>
     *  - Cat1
     *    - Cat2
     *      - Cat3
     *  - Archive
     *    - Cat2
     *      - Cat3
     *
     * @covers \tool_lifecycle\step\moveparentcategory
     * @return void
     */
    public function test_move_no_top_level(): void {
        $course3 = $this->getDataGenerator()->create_course(['category' => $this->category3->id]);

        $data = new \stdClass();
        $data->categorytomoveto = $this->categorya->id;
        $data->createparents = true;
        $data->notoplevel = true;
        $data->maxdepth = 0;

        [$hierachy1, $hierachy2] = $this->start_and_trigger_workflow($data, [$course3]);

        $expectedcat3 = new test_category();
        $exceptedcat2 = new test_category(['Cat3' => $expectedcat3]);
        $expectedcat1 = new test_category(['Cat2' => $exceptedcat2]);

        $expectedcat4arch = new test_category([], [$course3->shortname]);
        $expectedcat3arch = new test_category(['Cat3' => $expectedcat4arch]);
        $exceptedcat2arch = new test_category(['Cat2' => $expectedcat3arch]);

        // Modify hierarchy1 so that it fits the expected result.
        $hierachy1['Archive'] = $exceptedcat2arch;
        $hierachy1['Cat1'] = $expectedcat1;

        $this->assertEquals($hierachy1, $hierachy2);
    }

    /**
     * Test moving to archive:
     * - Archive
     *  - Cat1
     *    - Cat2
     *      - Cat3: tc_1

     *  =>
     *  - Cat1
     *    - Cat2
     *      - Cat3
     *  - Archive
     *    - Cat1
     *
     * @covers \tool_lifecycle\step\moveparentcategory
     * @return void
     */
    public function test_move_truncate(): void {
        $course3 = $this->getDataGenerator()->create_course(['category' => $this->category3->id]);

        $data = new \stdClass();
        $data->categorytomoveto = $this->categorya->id;
        $data->createparents = true;
        $data->notoplevel = false;
        $data->maxdepth = 1;

        [$hierachy1, $hierachy2] = $this->start_and_trigger_workflow($data, [$course3]);

        $expectedcat3 = new test_category();
        $exceptedcat2 = new test_category(['Cat3' => $expectedcat3]);
        $expectedcat1 = new test_category(['Cat2' => $exceptedcat2]);

        $expectedcat4arch = new test_category([], [$course3->shortname]);
        $exceptedcat2arch = new test_category(['Cat1' => $expectedcat4arch]);

        // Modify hierarchy1 so that it fits the expected result.
        $hierachy1['Archive'] = $exceptedcat2arch;
        $hierachy1['Cat1'] = $expectedcat1;

        $this->assertEquals($hierachy1, $hierachy2);
    }


    /**
     * Test moving to archive:
     * - Archive
     *  - Cat1
     *    - Cat2
     *      - Cat3: tc_1

     *  =>
     *  - Cat1
     *    - Cat2
     *      - Cat3
     *  - Archive
     *    - Cat2
     *
     * @covers \tool_lifecycle\step\moveparentcategory
     * @return void
     */
    public function test_move_truncate_and_no_toplevel(): void {
        $course3 = $this->getDataGenerator()->create_course(['category' => $this->category3->id]);

        $data = new \stdClass();
        $data->categorytomoveto = $this->categorya->id;
        $data->createparents = true;
        $data->notoplevel = true;
        $data->maxdepth = 1;

        [$hierachy1, $hierachy2] = $this->start_and_trigger_workflow($data, [$course3]);

        $expectedcat3 = new test_category();
        $exceptedcat2 = new test_category(['Cat3' => $expectedcat3]);
        $expectedcat1 = new test_category(['Cat2' => $exceptedcat2]);

        $expectedcat4arch = new test_category([], [$course3->shortname]);
        $exceptedcat2arch = new test_category(['Cat2' => $expectedcat4arch]);

        // Modify hierarchy1 so that it fits the expected result.
        $hierachy1['Archive'] = $exceptedcat2arch;
        $hierachy1['Cat1'] = $expectedcat1;

        $this->assertEquals($hierachy1, $hierachy2);
    }


    /**
     * add one course to each category and move all courses
     *  Test moving to archive:
     *  - Archive
     *   - Cat1
     *     - Cat2
     *       - Cat3
     *
     *   =>
     *   - Cat1
     *     - Cat2
     *       - Cat3
     *   - Archive
     *     - Cat1
     *       - Cat2
     *         - Cat3
     *
     * @return void
     * @covers \tool_lifecycle\step\moveparentcategory
     * @throws \moodle_exception
     */
    public function test_move_full_hierachy_under_archive_more_courses(): void {
        $course1 = $this->getDataGenerator()->create_course(['category' => $this->category1->id]);
        $course2 = $this->getDataGenerator()->create_course(['category' => $this->category2->id]);
        $course3 = $this->getDataGenerator()->create_course(['category' => $this->category3->id]);

        $data = new \stdClass();
        $data->categorytomoveto = $this->categorya->id;
        $data->createparents = true;
        $data->notoplevel = false;
        $data->maxdepth = 0;
        [$hierachy1, $hierachy2] = $this->start_and_trigger_workflow($data, [$course3, $course2, $course1]);

        // Check hierarchy: /Archive/Cat1/Cat2/Cat3.

        $expectedcat3 = new test_category();
        $exceptedcat2 = new test_category(['Cat3' => $expectedcat3]);
        $expectedcat1 = new test_category(['Cat2' => $exceptedcat2]);

        $expectedcat4arch = new test_category([], [$course3->shortname]);
        $expectedcat3arch = new test_category(['Cat3' => $expectedcat4arch], [$course2->shortname]);
        $exceptedcat2arch = new test_category(['Cat2' => $expectedcat3arch], [$course1->shortname]);
        $expectedcat1arch = new test_category(['Cat1' => $exceptedcat2arch]);

        // Modify hierarchy1 so that it fits the expected result.
        $hierachy1['Archive'] = $expectedcat1arch;
        $hierachy1['Cat1'] = $expectedcat1;

        $this->assertEquals($hierachy1, $hierachy2);
    }

    /**
     * Test moving to archive:
     * - Archive
     *  - Cat1
     *    - Cat2
     *      - Cat3

     *  =>
     *  - Cat1
     *    - Cat2
     *      - Cat3
     *  - Archive
     *    - Cat1
     *      - Cat2
     *        - Cat3
     *
     * @covers \tool_lifecycle\step\moveparentcategory
     * @return void
     */
    public function test_move_full_hierachy_under_archive_already_exists(): void {
        $category1 = \core_course_category::create(['name' => 'Cat1', 'parent' => $this->categorya->id]);
        $category2 = \core_course_category::create(['name' => 'Cat2', 'parent' => $category1->id]);
        $category3 = \core_course_category::create(['name' => 'Cat3', 'parent' => $category2->id]);

        $course3 = $this->getDataGenerator()->create_course(['category' => $this->category3->id]);

        $data = new \stdClass();
        $data->categorytomoveto = $this->categorya->id;
        $data->createparents = true;
        $data->notoplevel = false;
        $data->maxdepth = 0;

        [$hierachy1, $hierachy2] = $this->start_and_trigger_workflow($data, [$course3]);

        $expectedcat3 = new test_category();
        $exceptedcat2 = new test_category(['Cat3' => $expectedcat3]);
        $expectedcat1 = new test_category(['Cat2' => $exceptedcat2]);

        $expectedcat4arch = new test_category([], [$course3->shortname]);
        $expectedcat3arch = new test_category(['Cat3' => $expectedcat4arch]);
        $exceptedcat2arch = new test_category(['Cat2' => $expectedcat3arch]);
        $expectedcat1arch = new test_category(['Cat1' => $exceptedcat2arch]);

        // Modify hierarchy1 so that it fits the expected result.
        $hierachy1['Archive'] = $expectedcat1arch;
        $hierachy1['Cat1'] = $expectedcat1;

        $this->assertEquals($hierachy1, $hierachy2);
    }


    /**
     * add one course to each category and move all courses
     * @return void
     * @covers \tool_lifecycle\step\moveparentcategory
     * @throws \moodle_exception
     */
    public function test_move_without_hierarchy(): void {
        $course3 = $this->getDataGenerator()->create_course(['category' => $this->category3->id]);

        // Configuration data for step.
        $data = new \stdClass();
        $data->categorytomoveto = $this->categorya->id;
        $data->createparents = false;
        $data->notoplevel = false;

        // Start and trigger workflow.
        [$hierachy1, $hierachy2] = $this->start_and_trigger_workflow($data, [$course3]);

        // Check hierarchy: /Archive.

        // Modify hierarchy1 so that it fits the expected result.
        $expectedcat3 = new test_category();
        $exceptedcat2 = new test_category(['Cat3' => $expectedcat3]);
        $expectedcat1 = new test_category(['Cat2' => $exceptedcat2]);

        $hierachy1['Archive'] = new test_category([], [$course3->shortname]);
        $hierachy1['Cat1'] = $expectedcat1;

        $this->assertEquals($hierachy1, $hierachy2);
    }


    /**
     * add one course to each category and move all courses
     * @return void
     * @covers \tool_lifecycle\step\moveparentcategory
     * @throws \moodle_exception
     */
    public function test_move_without_hierarchy2(): void {
        // Hierarchy: /Archive/Arch2.
        $categoryarch2 = \core_course_category::create(['name' => 'Arch2', 'parent' => $this->categorya->id]);
        $course3 = $this->getDataGenerator()->create_course(['category' => $this->category3->id]);

        // Configuration data for step.
        $data = new \stdClass();
        $data->categorytomoveto = $categoryarch2->id;
        $data->createparents = false;
        $data->notoplevel = false;

        // Start and trigger workflow.
        [$hierachy1, $hierachy2] = $this->start_and_trigger_workflow($data, [$course3]);

        // Check hierarchy: /Archive/Arch2.

        // Modify hierarchy1 so that it fits the expected result.
        $expectedcat3 = new test_category();
        $exceptedcat2 = new test_category(['Cat3' => $expectedcat3]);
        $expectedcat1 = new test_category(['Cat2' => $exceptedcat2]);

        $expectedcat2arch = new test_category([], [$course3->shortname]);
        $expectedcat1arch = new test_category(['Arch2' => $expectedcat2arch]);

        $hierachy1['Archive'] = $expectedcat1arch;
        $hierachy1['Cat1'] = $expectedcat1;

        $this->assertEquals($hierachy1, $hierachy2);
    }
}

