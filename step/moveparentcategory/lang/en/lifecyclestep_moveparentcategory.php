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
 * Lang strings for moveparentcategory step
 *
 * @package lifecyclestep_moveparentcategory
 * @copyright  2019 Yorick Reum JMU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['categorytomoveto'] = 'Target Category';
$string['categorytomoveto_help'] = 'Set the category which you want to move the courses to. Leave blank if target is top level.';
$string['createparents'] = 'Consider parent category';
$string['createparents_help'] = 'Create source parent category levels on target: If Original course is located at A->B->C and D is the new target category, then the new course will be craeted at D->B->C.';
$string['maxdepth'] = 'Maximum levels of source categories';
$string['maxdepth_help'] = 'Maximum number of source category levels created on target.';
$string['notoplevel'] = 'Do not create top level category on target';
$string['notoplevel_help'] = 'If Original course is located at A->B->C and D is the new target category, then the new course will be craeted at D->A->B->C.';
$string['plugindescription'] = 'Moves all triggered courses to a defined course category. ';
$string['pluginname'] = 'Move Category Step (keep parent category)';
$string['privacy:metadata'] = 'The lifecyclestep_moveparentcategory plugin does not store any personal data.';
$string['toplevel'] = 'Top level';
