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
 * Lang strings for duplicate step
 *
 * @package    lifecyclestep_duplicate2
 * @copyright  2018 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['createparents'] = 'Consider parent category';
$string['createparents_help'] = 'Create source parent category levels on target: If Original course is located at A->B->C and D is the new target category, then the new course will be craeted at D->B->C.';
$string['maxdepth'] = 'Maximum levels of source categories';
$string['maxdepth_help'] = 'Maximum number of source category levels created on target (empty or 0 to copy all levels)';
$string['move'] = 'Move to different course category';
$string['move_help'] = 'If not checked, the duplicated course is created at the same location (course category) as the original course.';
$string['notoplevel'] = 'Do not create top level category on target';
$string['notoplevel_help'] = 'If Original course is located at A->B->C and D is the new target category, then the new course will be craeted at D->A->B->C.';
$string['plugindescription'] = 'Duplicates each triggered course and uses new course for continuing workflow.';
$string['pluginname'] = 'Enhanced duplicate step';
$string['privacy:metadata'] = 'This subplugin does not store any personal data.';
$string['targetcategory'] = 'Target course category';
$string['targetcategory_help'] = 'Course category for new course.';
