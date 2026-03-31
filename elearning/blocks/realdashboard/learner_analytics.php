<?php
/**
 * Learner Analytics - Monthly summary for the logged-in user.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * This file provides monthly analytics for the current learner, including
 * total counts and breakdowns by status.
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/dml/moodle_database.php');

global $DB, $USER;

// Get the current logged in user id.
$userid = $USER->id;

// Helper to get grade for a user in a course
function get_user_course_grade($userid, $courseid) {
    global $DB;
    $grade = $DB->get_record_sql(
        "SELECT finalgrade
           FROM {grade_grades} gg
           JOIN {grade_items} gi ON gi.id = gg.itemid
          WHERE gi.courseid = :courseid
            AND gi.itemtype = 'course'
            AND gg.userid = :userid",
        ['courseid' => $courseid, 'userid' => $userid]
    );
    return ($grade && $grade->finalgrade !== null) ? floatval($grade->finalgrade) : null;
}

// Get all user enrolments with course and enrollment time
$enrolments = $DB->get_records_sql(
    "SELECT c.id as courseid, c.fullname as coursename, ue.timecreated as enrollmenttime
       FROM {user_enrolments} ue
       JOIN {enrol} e ON ue.enrolid = e.id
       JOIN {course} c ON c.id = e.courseid
      WHERE ue.userid = :userid",
    ['userid' => $userid]
);

// Initialize counters
$totalEnrolments = 0;
$totalCompleted = 0;
$totalFailed = 0;
$totalInprogress = 0;

// Analytics by month
$analyticsByMonth = [];

// Helper: get month key (YYYY-MM)
function get_month_key($timestamp) {
    return date('Y-m', $timestamp);
}

foreach ($enrolments as $course) {
    $totalEnrolments++;
    $grades = get_user_course_grade($userid, $course->courseid);

    // Course completion status and completion time
    $completion = $DB->get_record('course_completions', [
        'userid' => $userid,
        'course' => $course->courseid,
    ]);

    $enrollmenttime = isset($course->enrollmenttime) ? $course->enrollmenttime : null;
    $monthKey = $enrollmenttime ? get_month_key($enrollmenttime) : null;

    if (!isset($analyticsByMonth[$monthKey])) {
        $analyticsByMonth[$monthKey] = [
            'month' => $monthKey,
            'numberOfEnrolments' => 0,
            'numberOfcomplted' => 0,
            'numberOfFailed' => 0,
            'numberOfInprogress' => 0
        ];
    }
    $analyticsByMonth[$monthKey]['numberOfEnrolments']++;

    if ($completion && $completion->timecompleted) {
        // Completed
        if ($grades !== null && $grades < 50) {
            $totalFailed++;
            $analyticsByMonth[$monthKey]['numberOfFailed']++;
        } else {
            $totalCompleted++;
            $analyticsByMonth[$monthKey]['numberOfcomplted']++;
        }
    } else {
        // In progress
        $totalInprogress++;
        $analyticsByMonth[$monthKey]['numberOfInprogress']++;
    }
}

// Sort months ascending
ksort($analyticsByMonth);

echo json_encode([
    'totalEnrolments' => $totalEnrolments,
    'totalCompleted' => $totalCompleted,
    'totalFailed' => $totalFailed,
    'totalInprogress' => $totalInprogress,
    'Analytics' => array_values($analyticsByMonth)
]);
exit;