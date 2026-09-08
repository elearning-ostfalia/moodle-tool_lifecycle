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
 * Step definition for life cycle step duplicate2.
 *
 * @package    lifecyclestep_duplicate2
 * @category   test
 * @copyright  2025 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../../../lib/behat/behat_base.php');

/**
 * Step definition for life cycle.
 *
 * @package    lifecyclestep_duplicate2
 * @category   test
 * @copyright  2025 Ostfalia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_category extends behat_base {
    /**
     * Checks course category path.
     *
     * @Given /^the category for "(?P<course_string>(?:[^"]|\\")*)" is "(?P<path_string>(?:[^"]|\\")*)"$/
     */
    public function category_for_is($course, $path) {
        global $DB;
        $categoryid = $DB->get_field('course', 'category', ['fullname' => $course]);
        $actualpath = $DB->get_field('course_categories', 'path', ['id' => $categoryid]);
        // Convert to array, remove empty strings.
        $actualpath = array_filter(explode('/', $actualpath));
        $names = [];
        // Convert to string with names instead of ids.
        foreach ($actualpath as $category) {
            $names[] = $DB->get_field('course_categories', 'name', ['id' => $category]);
        }
        $names = '/' . implode('/', $names);
        // Compare expected path with actual path.
        if ($path != $names) {
            throw new Exception("Actual category path is " . $names . ', ' . $path . ' expected');
        }

    }
}
