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
 * Scheduled task to refresh the reporting analytics data warehouse.
 *
 * This task truncates and repopulates the local_reporting_status table
 * with pre-computed enrollment statuses for all users and courses.
 * This dramatically improves dashboard query performance.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_additionaluserdetails\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task class for refreshing analytics data warehouse.
 */
class refresh_analytics extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('refreshanalytics', 'local_additionaluserdetails');
    }

    /**
     * Execute the scheduled task.
     *
     * This method:
     * 1. Truncates the local_reporting_status table
     * 2. Computes enrollment statuses using bulk INSERT INTO ... SELECT
     * 3. Handles completion via course_completions and customcert_issues
     *
     * Status Logic:
     * - 'enrolled': User enrolled but no completion record exists
     * - 'inprogress': User has completion record but timecompleted is NULL
     * - 'completed': User has timecompleted OR has customcert issued
     * - 'failed': Same as inprogress (completion record with NULL timecompleted)
     */
    public function execute() {
        global $DB;

        $starttime = microtime(true);
        mtrace('Starting analytics data warehouse refresh...');

        // Step 1: Truncate the reporting table for a clean slate.
        mtrace('  -> Truncating local_reporting_status table...');
        $DB->execute('TRUNCATE TABLE {local_reporting_status}');
        mtrace('  -> Table truncated successfully.');

        // Step 2: Insert 'completed' records (from course_completions with timecompleted).
        mtrace('  -> Inserting COMPLETED records from course_completions...');
        $completedSql = "
            INSERT INTO {local_reporting_status} (userid, courseid, status, timeenrolled, timecompleted)
            SELECT DISTINCT
                cc.userid,
                cc.course AS courseid,
                'completed' AS status,
                cc.timeenrolled,
                cc.timecompleted
            FROM {course_completions} cc
            WHERE cc.timecompleted IS NOT NULL
        ";
        $completedCount = $DB->execute($completedSql);
        mtrace("  -> Inserted records from course_completions.");

        // Step 3: Insert 'completed' records from customcert_issues (users who got certificates).
        // Only insert if not already marked as completed from course_completions.
        mtrace('  -> Inserting COMPLETED records from customcert_issues...');
        $certCompletedSql = "
            INSERT INTO {local_reporting_status} (userid, courseid, status, timeenrolled, timecompleted)
            SELECT DISTINCT
                ci.userid,
                c.course AS courseid,
                'completed' AS status,
                ue.timecreated AS timeenrolled,
                ci.timecreated AS timecompleted
            FROM {customcert_issues} ci
            INNER JOIN {customcert} c ON ci.customcertid = c.id
            LEFT JOIN {user_enrolments} ue ON ue.userid = ci.userid
            LEFT JOIN {enrol} e ON ue.enrolid = e.id AND e.courseid = c.course
            WHERE NOT EXISTS (
                SELECT 1 FROM {local_reporting_status} lrs
                WHERE lrs.userid = ci.userid
                  AND lrs.courseid = c.course
                  AND lrs.status = 'completed'
            )
        ";
        $certCompletedCount = $DB->execute($certCompletedSql);
        mtrace("  -> Inserted records from customcert_issues.");

        // Step 4: Insert 'inprogress' or 'failed' records (course_completions with NULL timecompleted).
        // These are users who started but haven't finished.
        mtrace('  -> Inserting INPROGRESS/FAILED records from course_completions...');
        $inprogressSql = "
            INSERT INTO {local_reporting_status} (userid, courseid, status, timeenrolled, timecompleted)
            SELECT DISTINCT
                cc.userid,
                cc.course AS courseid,
                'inprogress' AS status,
                cc.timeenrolled,
                NULL AS timecompleted
            FROM {course_completions} cc
            WHERE cc.timecompleted IS NULL
              AND NOT EXISTS (
                  SELECT 1 FROM {local_reporting_status} lrs
                  WHERE lrs.userid = cc.userid
                    AND lrs.courseid = cc.course
              )
        ";
        $inprogressCount = $DB->execute($inprogressSql);
        mtrace("  -> Inserted in-progress records.");

        // Step 5: Insert 'enrolled' records (users enrolled but no completion record).
        mtrace('  -> Inserting ENROLLED records (no completion tracking started)...');
        $enrolledSql = "
            INSERT INTO {local_reporting_status} (userid, courseid, status, timeenrolled, timecompleted)
            SELECT DISTINCT
                ue.userid,
                e.courseid,
                'enrolled' AS status,
                ue.timecreated AS timeenrolled,
                NULL AS timecompleted
            FROM {user_enrolments} ue
            INNER JOIN {enrol} e ON ue.enrolid = e.id
            WHERE NOT EXISTS (
                SELECT 1 FROM {local_reporting_status} lrs
                WHERE lrs.userid = ue.userid
                  AND lrs.courseid = e.courseid
            )
        ";
        $enrolledCount = $DB->execute($enrolledSql);
        mtrace("  -> Inserted enrolled records.");

        // Final statistics.
        $totalRecords = $DB->count_records('local_reporting_status');
        $duration = round(microtime(true) - $starttime, 2);

        mtrace('');
        mtrace('=== Analytics Data Warehouse Refresh Complete ===');
        mtrace("  Total records in warehouse: {$totalRecords}");
        mtrace("  Execution time: {$duration} seconds");
        mtrace('==================================================');
    }
}
