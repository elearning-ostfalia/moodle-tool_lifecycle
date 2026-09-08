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
 * Update script for lifecyclestep_departmentapprove plugin
 *
 * @package lifecyclestep_departmentapprove
 * @copyright  2025 Thomas Niedermaier University of Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Update script for lifecyclestep_departmentapprove.
 * @param int $oldversion Version id of the previously installed version.
 * @return bool
 * @throws ddl_exception
 * @throws ddl_field_missing_exception
 * @throws ddl_table_missing_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_lifecyclestep_departmentapprove_upgrade($oldversion) {

    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2026071600) {

        // Drop table lifecyclestep_departmentapprove_notified.
        $table = new xmldb_table('lifecyclestep_departmentapprove_notified');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        // Rename table
        $table = new xmldb_table('lifecyclestep_departmentapprove_mail');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'lifecyclestep_departmentapprove');
        }
        // Add new attributes.
        $table = new xmldb_table('lifecyclestep_departmentapprove');
        $field = new xmldb_field('confirmtoken', XMLDB_TYPE_CHAR, '64', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('confirmtokenexpires', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('processid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2026071600, 'lifecyclestep', 'adminapprove');
    }
    return true;
}
