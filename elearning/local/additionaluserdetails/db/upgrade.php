<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Database upgrade steps for local_additionaluserdetails plugin.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute local_additionaluserdetails upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_additionaluserdetails_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Add local_reporting_status table for data warehouse.
    if ($oldversion < 2025072201) {

        // Define table local_reporting_status to be created.
        $table = new xmldb_table('local_reporting_status');

        // Adding fields to table local_reporting_status.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeenrolled', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('timecompleted', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_reporting_status.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_reporting_status.
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('userid_courseid', XMLDB_INDEX_NOTUNIQUE, ['userid', 'courseid']);
        $table->add_index('userid_status', XMLDB_INDEX_NOTUNIQUE, ['userid', 'status']);
        $table->add_index('courseid_status', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'status']);

        // Conditionally launch create table for local_reporting_status.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Additionaluserdetails savepoint reached.
        upgrade_plugin_savepoint(true, 2025072201, 'local', 'additionaluserdetails');
    }

    return true;
}
