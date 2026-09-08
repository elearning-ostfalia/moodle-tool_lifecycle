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
 * Trigger test for empty custom field trigger.
 *
 * @package    lifecycletrigger_customfieldempty
 * @group      lifecycletrigger
 * @copyright  2020 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace lifecycletrigger_customfieldempty;

use tool_lifecycle\local\entity\trigger_subplugin;
use tool_lifecycle\processor;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

/**
 * Trigger test for empty custom field trigger.
 *
 * @package    lifecycletrigger_customfieldempty
 * @group      lifecycletrigger
 * @copyright  2020 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class trigger_test extends \advanced_testcase {

    /** @var $datetrigger trigger_subplugin Instance of the trigger. */
    private $datetrigger;
    /** @var $texttrigger trigger_subplugin Instance of the trigger. */
    private $texttrigger;
    /** @var $numbertrigger trigger_subplugin Instance of the trigger. */
    private $numbertrigger;

    /** @var $datetriggerinverted trigger_subplugin Instance of the trigger. */
    private $datetriggerinverted;
    /** @var $texttriggerinverted trigger_subplugin Instance of the trigger. */
    private $texttriggerinverted;
    /** @var $numbertriggerinverted trigger_subplugin Instance of the trigger. */
    private $numbertriggerinverted;


    /** @var $processor processor Instance of the lifecycle processor. */
    private $processor;

    /**
     * @var \core_customfield\category_controller
     */
    private $fieldcategory;

    /**
     * Setup for the Tests.
     * @return void
     * @throws \moodle_exception
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $this->processor = new processor();

        $this->fieldcategory = self::getDataGenerator()->create_custom_field_category(['name' => 'Other fields']);

        // Create fields with different types.
        $customfield = ['shortname' => 'test_date', 'name' => 'Custom field 1', 'type' => 'date',
            'categoryid' => $this->fieldcategory->get('id')];
        self::getDataGenerator()->create_custom_field($customfield);
        $this->datetrigger = generator::create_trigger_with_workflow(
            $customfield['shortname'], 0);
        $this->datetriggerinverted = generator::create_trigger_with_workflow(
            $customfield['shortname'], 1);

        $customfield = ['shortname' => 'test_text', 'name' => 'Custom field 2', 'type' => 'text',
            'categoryid' => $this->fieldcategory->get('id')];
        self::getDataGenerator()->create_custom_field($customfield);
        $this->texttrigger = generator::create_trigger_with_workflow(
            $customfield['shortname'], 0);
        $this->texttriggerinverted = generator::create_trigger_with_workflow(
            $customfield['shortname'], 1);

        $customfield = ['shortname' => 'test_number', 'name' => 'Custom field 4', 'type' => 'number',
            'categoryid' => $this->fieldcategory->get('id')];
        self::getDataGenerator()->create_custom_field($customfield);
        $this->numbertrigger = generator::create_trigger_with_workflow(
            $customfield['shortname'], 0);
        $this->numbertriggerinverted = generator::create_trigger_with_workflow(
            $customfield['shortname'], 1);
    }


    /**
     * Tests if courses, that have no date customfield and are triggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_no_date_custom_field(): void {
        $course = $this->getDataGenerator()->create_course();
        $this->check_trigger($course, $this->datetrigger, true);
        $this->check_trigger($course, $this->datetriggerinverted, false);
    }

    /**
     * Tests if courses, which have no text customfield are riggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_no_text_custom_field(): void {
        $course = $this->getDataGenerator()->create_course();
        $this->check_trigger($course, $this->texttrigger, true);
        $this->check_trigger($course, $this->texttriggerinverted, false);
    }

    /**
     * Tests if courses, which have no number customfield are riggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_no_number_custom_field(): void {
        $course = $this->getDataGenerator()->create_course();
        $this->check_trigger($course, $this->numbertrigger, true);
        $this->check_trigger($course, $this->numbertriggerinverted, false);
    }


    /**
     * Tests if courses, which have empty date customfield are riggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_empty_date_custom_field(): void {
        $customfieldvalue = ['shortname' => 'test_date', 'value' => 0];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);
        $this->check_trigger($course, $this->datetrigger, true);
        $this->check_trigger($course, $this->datetriggerinverted, false);
    }

    /**
     * Tests if courses, which have empty text customfield are riggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_empty_text_custom_field(): void {
        $customfieldvalue = ['shortname' => 'test_text', 'value' => ''];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);
        $this->check_trigger($course, $this->texttrigger, true);
        $this->check_trigger($course, $this->texttriggerinverted, false);
    }


    /**
     * Tests if courses, which have empty number customfield are riggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_empty_number_custom_field(): void {
        $customfieldvalue = ['shortname' => 'test_number', 'value' => ''];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);
        $this->check_trigger($course, $this->numbertrigger, true);
        $this->check_trigger($course, $this->numbertriggerinverted, false);
    }


    /**
     * Tests if courses, which have date customfield set are not triggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_set_date_custom_field(): void {
        $customfieldvalue = ['shortname' => 'test_date', 'value' => time()];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);
        $this->check_trigger($course, $this->datetrigger, false);
        $this->check_trigger($course, $this->datetriggerinverted, true);
    }

    /**
     * Tests if courses, which have text customfield set are not triggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_set_text_custom_field(): void {
        $customfieldvalue = ['shortname' => 'test_text', 'value' => 'abc'];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);
        $this->check_trigger($course, $this->texttrigger, false);
        $this->check_trigger($course, $this->texttriggerinverted, true);
    }


    /**
     * Tests if courses, which have number customfield set are not triggered by this plugin.
     * @covers \tool_lifecycle\trigger\customfieldempty
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_set_number_custom_field(): void {
        $customfieldvalue = ['shortname' => 'test_number', 'value' => 10];
        $course = $this->getDataGenerator()->create_course(['customfields' => [$customfieldvalue]]);

        $this->check_trigger($course, $this->numbertrigger, false);
        $this->check_trigger($course, $this->numbertriggerinverted, true);
    }


    /**
     * checks if course is triggered
     *
     * @param \stdClass $course course object
     * @param $trigger object
     * @param boolean $expectedfound expect course to be triggered?
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function check_trigger(\stdClass $course, $trigger, $expectedfound): void {
        $recordset = $this->processor->get_course_recordset([$trigger]);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id) {
                $found = true;
                break;
            }
        }
        $this->assertEquals($expectedfound, $found, $expectedfound ?
            'The course should have been triggered' : 'The course should have been not triggered');
    }
}
