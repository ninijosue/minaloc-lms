<?php
/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * This file provides analytics for user enrollment and learning, supporting various filters.
 * Reference image for database structure: ![image2](image2)
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/dml/moodle_database.php');

global $DB, $USER, $SESSION;

// Get params (all optional, default to blank/empty if not set)
$userid = required_param('userid', PARAM_INT);

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

// All enrolled courses with enrollment time
$enrolments = $DB->get_records_sql(
    "SELECT c.id as courseid, c.fullname as coursename, ue.timecreated as enrollmenttime
       FROM {user_enrolments} ue
       JOIN {enrol} e ON ue.enrolid = e.id
       JOIN {course} c ON c.id = e.courseid
      WHERE ue.userid = :userid",
    ['userid' => $userid]
);

$allCoursesInrolements = [];
$inprogressCourses = [];
$completedCourses = [];
$failedCourses = [];

foreach ($enrolments as $course) {
    $grades = get_user_course_grade($userid, $course->courseid);

    // Check course_completions table for any entry (completed or failed)
    $completion_records = $DB->get_records('course_completions', [
        'userid' => $userid,
        'course' => $course->courseid,
    ]);

    // Use the most recent completion record if multiple exist (or the first one if you prefer)
    $completion_record = null;
    if (!empty($completion_records)) {
        // Sort by timecompleted DESC, pick the latest (can pick first if you want)
        usort($completion_records, function($a, $b) {
            return ($b->timecompleted ?? 0) <=> ($a->timecompleted ?? 0);
        });
        $completion_record = reset($completion_records);
    }

    // Fix: get all certificate issues, not just one (may be multiple)
    $cert_issued_records = $DB->get_records_sql("
        SELECT ci.id, ci.timecreated
        FROM {customcert_issues} ci
        JOIN {customcert} c ON ci.customcertid = c.id
        WHERE ci.userid = :userid AND c.course = :courseid
    ", ['userid' => $userid, 'courseid' => $course->courseid]);
    
    // If multiple, pick the latest issued certificate
    $cert_issued = null;
    if (!empty($cert_issued_records)) {
        usort($cert_issued_records, function($a, $b) {
            return ($b->timecreated ?? 0) <=> ($a->timecreated ?? 0);
        });
        $cert_issued = reset($cert_issued_records);
    }

    // Determine status and completion time
    $is_completed = ($completion_record && $completion_record->timecompleted) || !empty($cert_issued);
    $is_failed = ($completion_record && $completion_record->timecompleted === null);

    $status = 'inprogress';
    $completiontime = null;

    if ($is_failed) {
        $status = 'failed';
    } elseif ($is_completed) {
        $status = 'completed';
        if ($completion_record && $completion_record->timecompleted) {
            $completiontime = $completion_record->timecompleted;
        } elseif (!empty($cert_issued)) {
            $completiontime = $cert_issued->timecreated;
        }
    }

    $enrollmenttime = isset($course->enrollmenttime) ? $course->enrollmenttime : null;
    $score = $grades;

    $courseData = [
        'coursename'      => $course->coursename,
        'courseid'        => $course->courseid,
        'grades'          => $grades,
        'enrollmenttime'  => $enrollmenttime,
        'completiontime'  => $completiontime,
        'status'          => $status,
        'score'           => $score,
    ];

    $allCoursesInrolements[] = $courseData;

    if ($status === 'completed') {
        $completedCourses[] = $courseData;
    } elseif ($status === 'failed') {
        $failedCourses[] = $courseData;
    } else {
        $inprogressCourses[] = $courseData;
    }
}

echo json_encode([
    'allCoursesInrolements' => $allCoursesInrolements,
    'inprogressCourses' => $inprogressCourses,
    'completedCourses' => $completedCourses,
    'failedCourses' => $failedCourses,
]);
exit;