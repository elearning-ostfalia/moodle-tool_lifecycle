<?php
// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ProFormA Question Type for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat extensions for lifecyclestep_departmentapprove
 *
 * @package    lifecyclestep_departmentapprove
 * @copyright  2026 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Mink\Exception\ExpectationException as ExpectationException;

class behat_departmentapprove extends behat_base {

    /**
     * open link as department person
     *
     * @When I visit department approval link
     * @param
     */
    public function i_visit_department_approval_link() {
        global $DB;
        $records = $DB->get_records('lifecyclestep_departmentapprove');
        if (!$records) {
            throw new ExpectationException('No approval record found', $this->getSession());
        }
        $record = reset($records);
        $token = $record->confirmtoken;

        $this->execute('behat_general::i_visit', '/admin/tool/lifecycle/step/departmentapprove/department_confirm.php?token=' . $token);
    }

}
