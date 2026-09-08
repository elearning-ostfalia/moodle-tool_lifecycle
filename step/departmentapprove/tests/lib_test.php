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
 * Unit tests for the lifecyclestep_email.
 *
 * @package    lifecyclestep_email
 * @copyright  2024 Justus Dieckmann, University of Münster.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_lifecycle\step\email;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Unit tests for the lifecyclestep_departmentapprove lib.php.
 *
 * @package    lifecyclestep_departmentapprove
 * @category   test
 * @coversDefaultClass \tool_lifecycle\step\email
 * @copyright  2024 Justus Dieckmann, University of Münster.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {

    /**
     * Tests \tool_lifecycle\step\departmentapprove::replace_placeholders.
     */
    public function test_replace_placeholders(): void {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user(['firstname' => 'Jane', 'lastname' => 'Doe']);
        $course1 = $this->getDataGenerator()->create_course(['fullname' => 'Course 1', 'shortname' => 'C1']);
        $course2 = $this->getDataGenerator()->create_course(['fullname' => 'Course 2', 'shortname' => 'C2']);
        $course3 = $this->getDataGenerator()->create_course(['fullname' => 'Course 3', 'shortname' => 'C3']);
        $lib = new tool_lifecycle\step\departmentapprove();
        $strings = [];
        $strings['emailsender'] = 'x@y.org';
        $strings['link'] = 'https://moodle.test.org';
        $strings['content'] = "##sender##\n##courses##\n";
        $strings['contenthtml'] = "##sender-html##<br>##courses-html##";
        $response = $lib->replace_mail_placeholders($strings,
            [
                (object) ['courseid' => $course1->id],
                (object) ['courseid' => $course2->id],
                (object) ['courseid' => $course3->id],
            ]
        );
        $this->assertCount(4, $response);
        $this->assertEquals("https://moodle.test.org", $response['link']);
        $this->assertEquals("x@y.org", $response['emailsender']);
        $this->assertEquals("x@y.org\nCourse 1\r\nCourse 2\r\nCourse 3\n", $response['content']);
        $this->assertEquals('<a href="mailto:x@y.org">Moodle Administrator</a><br><ul><li>Course 1</li><li>Course 2</li><li>Course 3</li></ul>', $response['contenthtml']);
    }
}
