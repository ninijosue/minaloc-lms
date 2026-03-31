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
$startDate = optional_param('startdate', '', PARAM_TEXT);
$endDate = optional_param('enddate', '', PARAM_TEXT);
$dateofbirth = optional_param('dateofbirth', '', PARAM_TEXT);
$gender = optional_param('sex', '', PARAM_TEXT);
$servicepointcategory = optional_param('servicepointcategory', '', PARAM_TEXT);
$district = optional_param('district', '', PARAM_TEXT);
$sector = optional_param('sector', '', PARAM_TEXT);
$cell = optional_param('cell', '', PARAM_TEXT);
$village = optional_param('village', '', PARAM_TEXT);
$healthfacility = optional_param('healthfacility', '', PARAM_TEXT);
$servicecountry = optional_param('servicecountry', '', PARAM_TEXT);
$otherspecified = optional_param('otherspecified', '', PARAM_TEXT);
$phonenumber = optional_param('phonenumber', '', PARAM_TEXT);
$email = optional_param('email', '', PARAM_TEXT);
$position = optional_param('position', '', PARAM_TEXT);
$selectedCourses = optional_param('selectedCourses', '', PARAM_TEXT);
$courseCategory = optional_param('courseCategory', '', PARAM_TEXT);

// Build dynamic filters for additional_user_details
$filters = [];
if ($dateofbirth !== '') $filters['dateofbirth'] = $dateofbirth;
if ($gender !== '') $filters['gender'] = $gender;
if ($servicepointcategory !== '') $filters['servicepointcategory'] = $servicepointcategory;
if ($district !== '') $filters['district'] = $district;
if ($sector !== '') $filters['sector'] = $sector;
if ($cell !== '') $filters['cell'] = $cell;
if ($healthfacility !== '') $filters['healthfacility'] = $healthfacility;
if ($servicecountry !== '') $filters['servicecountry'] = $servicecountry;
if ($otherspecified !== '') $filters['otherspecified'] = $otherspecified;
if ($phonenumber !== '') $filters['phonenumber'] = $phonenumber;
if ($village !== '') $filters['village'] = $village;
if ($position !== '') $filters['position'] = $position;
if ($email !== '') $filters['email'] = $email;

// Process selected courses
$courseIds = [];
if (!empty($selectedCourses)) {
    // Attempt to decode as JSON array
    $decodedCourses = json_decode($selectedCourses);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedCourses)) {
        $courseIds = array_map('intval', $decodedCourses); // Ensure integer IDs
    } else if (is_numeric($selectedCourses)) {
        // Handle single numeric course ID
        $courseIds = [intval($selectedCourses)];
    } else if (is_string($selectedCourses) && strpos($selectedCourses, ',') !== false) {
        // Handle comma-separated string of course IDs
        $parts = explode(',', $selectedCourses);
        $courseIds = array_map('intval', array_filter($parts, 'is_numeric')); // Filter out non-numeric parts
    } else if (is_array($selectedCourses)) {
        // If it's already an array (e.g., from form submission directly)
        $courseIds = array_map('intval', $selectedCourses);
    }
}

// If selectedCourses were not provided (or were invalid format), and courseCategory is provided, use courseCategory
if (empty($courseIds) && !empty($courseCategory)) {
    $courseIds = $DB->get_fieldset_sql("
        SELECT id FROM {course} 
        WHERE category = :category",
        ['category' => $courseCategory]
    );
}

// Get filtered users from additional_user_details
$where = [];
$params = [];
foreach ($filters as $field => $val) {
    $where[] = $field . ' = :' . $field;
    $params[$field] = $val;
}
$whereSQL = $where ? "WHERE " . implode(' AND ', $where) : "";

$userDetails = $DB->get_records_list(
    'additional_user_details',
    'id',
    $DB->get_fieldset_sql("SELECT id FROM {additional_user_details} $whereSQL", $params),
    '',
    'id, userid, dateofbirth, gender, district, sector, nationalid, cell, village, healthfacility, servicepointcategory, servicecountry, otherspecified, phonenumber, email, position, names'
);

// Deduplicate userDetails by userid
$userMap = [];
foreach ($userDetails as $ud) {
    if (!isset($userMap[$ud->userid])) {
        $userMap[$ud->userid] = $ud;
    }
}
$userDetails = array_values($userMap);

$userids = [];
foreach ($userDetails as $ud) {
    $userids[] = $ud->userid;
}

if (empty($userids)) {
    $totalAnalytics = [];
    $enrollmentAnalytics = [];
    $learningAnalytics = [];
    $studentsData = [];
    echo json_encode([
        'totalAnalytics' => $totalAnalytics,
        'enrollmentAnalytics' => $enrollmentAnalytics,
        'learningAnalytics' => $learningAnalytics,
        'studentsData' => $studentsData
    ]);
    exit;
}

// Prepare course filters for date range and courseid
$dateFilter = '';
$dateParams = [];
if ($startDate !== '' && $endDate !== '') {
    $dateFilter = " AND ue.timecreated >= :startdate AND ue.timecreated <= :enddate";
    $dateParams['startdate'] = strtotime($startDate);
    $dateParams['enddate'] = strtotime($endDate . ' 23:59:59');
} elseif ($startDate !== '') {
    $dateFilter = " AND ue.timecreated >= :startdate";
    $dateParams['startdate'] = strtotime($startDate);
} elseif ($endDate !== '') {
    $dateFilter = " AND ue.timecreated <= :enddate";
    $dateParams['enddate'] = strtotime($endDate . ' 23:59:59');
}

// Course filter based on selected courses
$courseFilterSQL = '';
$courseParams = [];
if (!empty($courseIds)) {
    list($courseSql, $courseParams) = $DB->get_in_or_equal($courseIds, SQL_PARAMS_NAMED, 'course');
    $courseFilterSQL = " AND e.courseid $courseSql";
}

$courseFilterSQLForCustomCert = '';
if (!empty($courseIds)) {
    // It's crucial to use unique parameter names if merging multiple get_in_or_equal calls
    list($courseSql, $customCertCourseParams) = $DB->get_in_or_equal($courseIds, SQL_PARAMS_NAMED, 'certcourse');
    $courseFilterSQLForCustomCert = " AND c.course $courseSql";
    $courseParams = array_merge($courseParams, $customCertCourseParams);
}


// Combine all params
$allParams = array_merge($dateParams, $courseParams);

// ---- Location Field Selection (Dynamic) ----
$locationField = 'district';
if ($village !== '') $locationField = 'village';
elseif ($cell !== '') $locationField = 'cell';
elseif ($sector !== '') $locationField = 'sector';
elseif ($district !== '') $locationField = 'district';

// ---- Age Groups ----
$ageGroups = [
    '<25' => [0, 24],
    '25-34' => [25, 34],
    '35-44' => [35, 44],
    '45+'   => [45, 200]
];
$now = time();

// ---- Get userids filtered by courses if provided ----
$filteredUserids = $userids;
// This logic implicitly filters by courseIds and dateFilter.
// We need to make sure $courseIds is populated correctly before this.
// The modification at the top handles this.
if (!empty($courseIds) || !empty($dateFilter)) {
    $userFilterSql = "
        SELECT DISTINCT ue.userid FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        WHERE ue.userid IN (" . implode(',', $userids) . ")
        $courseFilterSQL
        $dateFilter
    ";
    $filteredUserids = $DB->get_fieldset_sql($userFilterSql, $allParams);
    
    // If none, all analytics must be empty
    if (empty($filteredUserids)) {
        $totalAnalytics = [];
        $enrollmentAnalytics = [];
        $learningAnalytics = [];
        $studentsData = [];
        echo json_encode([
            'totalAnalytics' => $totalAnalytics,
            'enrollmentAnalytics' => $enrollmentAnalytics,
            'learningAnalytics' => $learningAnalytics,
            'studentsData' => $studentsData
        ]);
        exit;
    }
}

// Always compute totalAnalytics using all userids without date/course filters
$allUserids = $userids;
$courseFilterSQL_total = '';
$courseFilterSQLForCustomCert_total = '';
$dateFilter_total = '';
$allParams_total = [];

// ---- Student By Location total ----
$locationUsers_total = [];
foreach ($userDetails as $ud) {
    $loc = $ud->$locationField ?: 'Other';
    if (!isset($locationUsers_total[$loc])) $locationUsers_total[$loc] = [];
    $locationUsers_total[$loc][] = $ud;
}

$studentByLocation_total = [];
foreach ($locationUsers_total as $loc => $usersInLoc) {
    $males = 0;
    $females = 0;
    $ageDist = [];
    foreach ($ageGroups as $group => $_) {
        $ageDist[$group] = 0;
    }
    foreach ($usersInLoc as $ud) {
        // Gender
        if (isset($ud->gender)) {
            $gend = strtolower(trim($ud->gender));
            if ($gend === 'm' || $gend === 'male') $males++;
            elseif ($gend === 'f' || $gend === 'female') $females++;
        }
        // Age
        if (!empty($ud->dateofbirth)) {
            $birthSeconds = (strlen($ud->dateofbirth) > 10) ? intval($ud->dateofbirth / 1000) : intval($ud->dateofbirth);
            $age = floor(($now - $birthSeconds) / (365.25 * 24 * 3600));
            foreach ($ageGroups as $group => [$min, $max]) {
                if (($age >= $min && $age <= $max) || ($group === '45+' && $age >= $min)) {
                    $ageDist[$group]++;
                    break;
                }
            }
        }
    }
    $ageDistArr = [];
    foreach ($ageGroups as $group => $_) {
        $ageDistArr[] = [
            'ageGroup' => $group,
            'count' => $ageDist[$group]
        ];
    }
    $studentByLocation_total[] = [
        'location' => $loc,
        'count' => count($usersInLoc),
        'males' => $males,
        'females' => $females,
        'ageDistribution' => $ageDistArr
    ];
}

// --- Age Distribution total ---
$ageCounts_total = [];
foreach ($ageGroups as $group => $_) {
    $ageCounts_total[$group] = 0;
}
foreach ($userDetails as $ud) {
    if (!empty($ud->dateofbirth)) {
        $birthSeconds = (strlen($ud->dateofbirth) > 10) ? intval($ud->dateofbirth / 1000) : intval($ud->dateofbirth);
        $age = floor(($now - $birthSeconds) / (365.25 * 24 * 3600));
        foreach ($ageGroups as $group => [$min, $max]) {
            if ($age >= $min && $age <= $max) {
                $ageCounts_total[$group]++;
                break;
            }
            if ($group === '45+' && $age >= $min) {
                $ageCounts_total[$group]++;
                break;
            }
        }
    }
}
$ageDistribution_total = [];
foreach ($ageGroups as $group => $_) {
    $ageDistribution_total[] = [
        'ageGroup' => $group,
        'count' => $ageCounts_total[$group]
    ];
}

// --- studentJoiningCourses total ---
$joiningCourses_total = [];
// Base SQL for getting enrollment counts per month/year, and min/max timecreated
$joiningCourseSql_total = "
    SELECT 
        " . $DB->sql_concat('FROM_UNIXTIME(ue.timecreated, \'%Y-%m\')') . " AS yyyymm_key, -- Unique key for get_records_sql
        FROM_UNIXTIME(ue.timecreated, '%m') AS month,
        FROM_UNIXTIME(ue.timecreated, '%Y') AS year,
        COUNT(DISTINCT " . $DB->sql_concat('ue.userid', "'-'", 'e.courseid') . ") AS count,
        MIN(ue.timecreated) as min_timecreated_data,
        MAX(ue.timecreated) as max_timecreated_data
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    WHERE ue.userid IN (" . implode(',', $allUserids) . ")
    $courseFilterSQL_total
    $dateFilter_total
";

// Execute the main query to get monthly data and the actual min/max timecreated from the data
$joiningRecords_total = $DB->get_records_sql($joiningCourseSql_total . " GROUP BY year, month ORDER BY year, month", $allParams_total);

$monthNames = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$monthlyData_total = [];
$minDataTime_total = PHP_INT_MAX; // Initialize with a very large number
$maxDataTime_total = 0; // Initialize with 0 for max

foreach ($joiningRecords_total as $record) {
    // Ensure month and year are valid before using them
    if (empty($record->year) || empty($record->month)) {
        continue;
    }
    // We can use the yyyymm_key from the record or construct it.
    // The key here is for internal data mapping, not the get_records_sql key.
    $monthlyData_total[$record->year . '-' . (int)$record->month] = (int)$record->count;

    // Collect min/max timecreated from the records retrieved
    // Using the aggregated min_timecreated_data and max_timecreated_data from the SQL result
    if ($record->min_timecreated_data < $minDataTime_total) {
        $minDataTime_total = $record->min_timecreated_data;
    }
    if ($record->max_timecreated_data > $maxDataTime_total) {
        $maxDataTime_total = $record->max_timecreated_data;
    }
}

// Determine the start and end dates for the trend graph to ensure all months are included
$graphStartTime_total = null;
$graphEndTime_total = null;

// For total, use full data range or current year (no param priority)
if ($minDataTime_total < PHP_INT_MAX && $maxDataTime_total > 0) {
    $graphStartTime_total = $minDataTime_total;
    $graphEndTime_total = $maxDataTime_total;
}

// Default to current year if no data
if ($graphStartTime_total === null || $graphEndTime_total === null) {
    $currentYear = date('Y');
    $graphStartTime_total = strtotime($currentYear . '-01-01');
    $graphEndTime_total = strtotime($currentYear . '-12-31 23:59:59');
}

// Ensure $graphEndTime_total is always at least $graphStartTime_total if it somehow got smaller
if ($graphEndTime_total < $graphStartTime_total) {
    $graphEndTime_total = $graphStartTime_total;
}

// Adjust graph start/end to the first day of the month for DatePeriod
$startGraph_total = new DateTime(date('Y-m-01', $graphStartTime_total));
$endGraph_total = new DateTime(date('Y-m-01', $graphEndTime_total));
$endGraph_total->modify('+1 month'); // Ensure the last month is included in the period

$interval = new DateInterval('P1M');
$period_total = new DatePeriod($startGraph_total, $interval, $endGraph_total);

foreach ($period_total as $dt) {
    $year = $dt->format('Y');
    $month = (int)$dt->format('n'); // 'n' for month without leading zeros
    $key = $year . '-' . $month;
    $joiningCourses_total[] = [
        'month' => $monthNames[$month],
        'year' => (int)$year,
        'count' => isset($monthlyData_total[$key]) ? $monthlyData_total[$key] : 0
    ];
}

// ---- Enrollment Metrics total ----
$totalStudents_total = count($allUserids);
$enrolledStudents_total = $DB->count_records_sql("
    SELECT COUNT(*) FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    WHERE ue.userid IN (" . implode(',', $allUserids) . ")
    $courseFilterSQL_total
    $dateFilter_total
", $allParams_total);

$completedCourses_total = $DB->count_records_sql("
    SELECT COUNT(*) FROM (
        SELECT cc.userid, cc.course FROM {course_completions} cc
        JOIN {enrol} e ON cc.course = e.courseid
        WHERE cc.userid IN (" . implode(',', $allUserids) . ") AND cc.timecompleted IS NOT NULL
        $courseFilterSQL_total
        UNION
        SELECT ci.userid, c.course FROM {customcert_issues} ci
        JOIN {customcert} c ON ci.customcertid = c.id
        JOIN {enrol} e ON c.course = e.courseid
        WHERE ci.userid IN (" . implode(',', $allUserids) . ")
        $courseFilterSQLForCustomCert_total
    ) AS completions
", $allParams_total);

$excelledStudents_total = $DB->count_records_sql("
    SELECT COUNT(DISTINCT ue.userid)
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    LEFT JOIN {course_completions} cc ON cc.course = e.courseid AND cc.userid = ue.userid
    LEFT JOIN {customcert} custcert ON custcert.course = e.courseid
    LEFT JOIN {customcert_issues} cert_issue ON cert_issue.customcertid = custcert.id AND cert_issue.userid = ue.userid
    WHERE ue.userid IN (" . implode(',', $allUserids) . ")
    AND (cc.timecompleted IS NOT NULL OR cert_issue.id IS NOT NULL)
    $courseFilterSQL_total
", $allParams_total);

$failedCourses_total = $DB->count_records_sql("
    SELECT COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") FROM {course_completions} cc
    JOIN {enrol} e ON cc.course = e.courseid
    WHERE cc.userid IN (" . implode(',', $allUserids) . ") AND cc.timecompleted IS NULL
    $courseFilterSQL_total
", $allParams_total);

$notStarted_total = $DB->count_records_sql("
    SELECT COUNT(DISTINCT ue.userid)
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    LEFT JOIN {course_completions} cc ON ue.userid = cc.userid AND e.courseid = cc.course
    WHERE ue.userid IN (" . implode(',', $allUserids) . ") AND cc.id IS NULL
    $courseFilterSQL_total
    $dateFilter_total
", $allParams_total);
$maleCount_total = $DB->count_records_sql("
    SELECT COUNT(*) FROM {additional_user_details}
    WHERE userid IN (" . implode(',', $allUserids) . ") AND LOWER(gender) IN ('m', 'male')
");
$femaleCount_total = $DB->count_records_sql("
    SELECT COUNT(*) FROM {additional_user_details}
    WHERE userid IN (" . implode(',', $allUserids) . ") AND LOWER(gender) IN ('f', 'female')
");

$totalAnalytics = [
    'studentJoiningCourses' => $joiningCourses_total,
    'ageDistribution' => $ageDistribution_total,
    'studentByLocation' => $studentByLocation_total,
    'metrics' => [
        'totalStudents' => $totalStudents_total,
        'enrolledStudents' => $enrolledStudents_total,
        'excelledStudents' => $excelledStudents_total,
        'completedCourses' => $completedCourses_total,
        'failedCourses' => $failedCourses_total,
        'notStarted' => $notStarted_total,
        'sexMetrics' => [
            'males' => $maleCount_total,
            'females' => $femaleCount_total
        ]
    ]
];

// ---- Students Data Array ----
$studentsData = [];
if (!empty($filteredUserids)) {
    // Get all Moodle emails for users using their userid as foreign key
    $moodleEmails = [];
    if (!empty($filteredUserids)) {
        list($in_sql, $in_params) = $DB->get_in_or_equal($filteredUserids, SQL_PARAMS_NAMED, 'stu_');
        $emailRecords = $DB->get_records_sql("SELECT id, email FROM {user} WHERE id $in_sql", $in_params);
        foreach ($emailRecords as $record) {
            $moodleEmails[$record->id] = $record->email;
        }
    }
    foreach ($userDetails as $ud) {
        if (!in_array($ud->userid, $filteredUserids)) continue;

        // ----------- DATE FILTER LOGIC APPLY HERE -----------
        $enrollmentDateFilter = '';
        $enrollmentDateParams = [];
        if ($startDate !== '' && $endDate !== '') {
            $enrollmentDateFilter = " AND ue.timecreated >= :startdate AND ue.timecreated <= :enddate";
            $enrollmentDateParams['startdate'] = strtotime($startDate);
            $enrollmentDateParams['enddate'] = strtotime($endDate . ' 23:59:59');
        } elseif ($startDate !== '') {
            $enrollmentDateFilter = " AND ue.timecreated >= :startdate";
            $enrollmentDateParams['startdate'] = strtotime($startDate);
        } elseif ($endDate !== '') {
            $enrollmentDateFilter = " AND ue.timecreated <= :enddate";
            $enrollmentDateParams['enddate'] = strtotime($endDate . ' 23:59:59');
        }

        $studentAllParams = array_merge($courseParams, $enrollmentDateParams, ['userid' => $ud->userid]);

        // Enrollments count
        $enrollments = $DB->count_records_sql("
            SELECT COUNT(*) FROM {user_enrolments} ue
            JOIN {enrol} e ON ue.enrolid = e.id
            WHERE ue.userid = :userid
            $courseFilterSQL
            $enrollmentDateFilter
        ", $studentAllParams);

        // Completions count
        $completed = $DB->count_records_sql("
            SELECT COUNT(DISTINCT e.courseid)
            FROM {enrol} e
            LEFT JOIN {course_completions} cc ON cc.course = e.courseid AND cc.userid = :userid1
            LEFT JOIN {customcert} custcert ON custcert.course = e.courseid
            LEFT JOIN {customcert_issues} cert_issue ON cert_issue.customcertid = custcert.id AND cert_issue.userid = :userid2
            WHERE e.courseid IN (
                SELECT e_inner.courseid FROM {user_enrolments} ue_inner
                JOIN {enrol} e_inner ON ue_inner.enrolid = e_inner.id
                WHERE ue_inner.userid = :userid3
            )
            AND (cc.timecompleted IS NOT NULL OR cert_issue.id IS NOT NULL)
            $courseFilterSQL
        ", array_merge($courseParams, ['userid1' => $ud->userid, 'userid2' => $ud->userid, 'userid3' => $ud->userid]));

        // Failed count
        $failed = $DB->count_records_sql("
            SELECT COUNT(*) FROM {course_completions} cc
            JOIN {enrol} e ON cc.course = e.courseid
            WHERE cc.userid = :userid AND cc.timecompleted IS NULL
            $courseFilterSQL
        ", array_merge($courseParams, ['userid' => $ud->userid]));

        // In progress
        $inprogress = $enrollments - ($completed + $failed);

        $studentsData[] = [
            'names' => $ud->names ?? '',
            'id' => $ud->id ?? '',
            'userid' => $ud->userid ?? '',
            'nationalid' => $ud->nationalid ?? '',
            'dateofbirth' => $ud->dateofbirth ?? '',
            'servicepointcategory' => $ud->servicepointcategory ?? '',
            'district' => $ud->district ?? '',
            'sector' => $ud->sector ?? '',
            'cell' => $ud->cell ?? '',
            'village' => $ud->village ?? '',
            'healthfacility' => $ud->healthfacility ?? '',
            'position' => $ud->position ?? '',
            'servicecountry' => $ud->servicecountry ?? '',
            'otherspecified' => $ud->otherspecified ?? '',
            'phoneNumber' => $ud->phonenumber ?? '',
            'email' => isset($moodleEmails[$ud->userid]) ? $moodleEmails[$ud->userid] : '',
            'sex' => $ud->gender ?? '',
            'enrollments' => $enrollments,
            'completed' => $completed,
            'failed' => $failed,
            'inprogress' => $inprogress
        ];
    }
}

// Aggregate totals from studentsData for consistency
$totalEnrollments = 0;
$totalCompleted = 0;
$totalFailed = 0;
foreach ($studentsData as $student) {
    $totalEnrollments += $student['enrollments'];
    $totalCompleted += $student['completed'];
    $totalFailed += $student['failed'];
}

// Build student map for aggregations
$studentMap = [];
foreach ($studentsData as $student) {
    $studentMap[$student['userid']] = $student;
}

// Compute enrollments by sex for enrollmentAnalytics
$maleEnroll = 0;
$femaleEnroll = 0;
foreach ($studentsData as $s) {
    $enr = $s['enrollments'];
    $g = strtolower(trim($s['sex'] ?? ''));
    if ($g === 'm' || $g === 'male') {
        $maleEnroll += $enr;
    } elseif ($g === 'f' || $g === 'female') {
        $femaleEnroll += $enr;
    }
}

// ---- Student By Location with Males, Females, Age Distribution ----
$locationUsers = [];
foreach ($userDetails as $ud) {
    if (!in_array($ud->userid, $filteredUserids)) continue;
    $loc = $ud->$locationField ?: 'Other';
    if (!isset($locationUsers[$loc])) $locationUsers[$loc] = [];
    $locationUsers[$loc][] = $ud;
}

$studentByLocation = [];
foreach ($locationUsers as $loc => $usersInLoc) {
    $locEnroll = 0;
    $males = 0;
    $females = 0;
    $ageDist = array_fill_keys(array_keys($ageGroups), 0);
    foreach ($usersInLoc as $ud) {
        if (!isset($studentMap[$ud->userid])) continue;
        $s = $studentMap[$ud->userid];
        $enr = $s['enrollments'];
        $locEnroll += $enr;
        $gend = strtolower(trim($s['sex']));
        if ($gend === 'm' || $gend === 'male') $males += $enr;
        elseif ($gend === 'f' || $gend === 'female') $females += $enr;
        // Age
        if (!empty($s['dateofbirth'])) {
            $birthSeconds = (strlen($s['dateofbirth']) > 10) ? intval($s['dateofbirth'] / 1000) : intval($s['dateofbirth']);
            $age = floor(($now - $birthSeconds) / (365.25 * 24 * 3600));
            $group = null;
            foreach ($ageGroups as $g => [$min, $max]) {
                if (($age >= $min && $age <= $max) || ($g === '45+' && $age >= $min)) {
                    $group = $g;
                    break;
                }
            }
            if ($group) {
                $ageDist[$group] += $enr;
            }
        }
    }
    $ageDistArr = [];
    foreach ($ageGroups as $group => $_) {
        $ageDistArr[] = [
            'ageGroup' => $group,
            'count' => $ageDist[$group]
        ];
    }
    $studentByLocation[] = [
        'location' => $loc,
        'count' => $locEnroll,
        'males' => $males,
        'females' => $females,
        'ageDistribution' => $ageDistArr
    ];
}

// --- Age Distribution (ENROLLMENT) based on filteredUserids ---
$ageCounts = array_fill_keys(array_keys($ageGroups), 0);
foreach ($studentsData as $student) {
    if (!empty($student['dateofbirth'])) {
        $birthSeconds = (strlen($student['dateofbirth']) > 10) ? intval($student['dateofbirth'] / 1000) : intval($student['dateofbirth']);
        $age = floor(($now - $birthSeconds) / (365.25 * 24 * 3600));
        $group = null;
        foreach ($ageGroups as $g => [$min, $max]) {
            if ($age >= $min && $age <= $max) {
                $group = $g;
                break;
            }
            if ($g === '45+' && $age >= $min) {
                $group = $g;
                break;
            }
        }
        if ($group) {
            $ageCounts[$group] += $student['enrollments'];
        }
    }
}
$ageDistribution = [];
foreach ($ageGroups as $group => $_) {
    $ageDistribution[] = [
        'ageGroup' => $group,
        'count' => $ageCounts[$group]
    ];
}

// --- studentJoiningCourses ---
$joiningCourses = [];
// Base SQL for getting enrollment counts per month/year, and min/max timecreated
$joiningCourseSql = "
    SELECT 
        " . $DB->sql_concat('FROM_UNIXTIME(ue.timecreated, \'%Y-%m\')') . " AS yyyymm_key, -- Unique key for get_records_sql
        FROM_UNIXTIME(ue.timecreated, '%m') AS month,
        FROM_UNIXTIME(ue.timecreated, '%Y') AS year,
        COUNT(DISTINCT " . $DB->sql_concat('ue.userid', "'-'", 'e.courseid') . ") AS count,
        MIN(ue.timecreated) as min_timecreated_data,
        MAX(ue.timecreated) as max_timecreated_data
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    WHERE ue.userid IN (" . implode(',', $filteredUserids) . ")
    $courseFilterSQL
    $dateFilter
";

// Execute the main query to get monthly data and the actual min/max timecreated from the data
$joiningRecords = $DB->get_records_sql($joiningCourseSql . " GROUP BY year, month ORDER BY year, month", $allParams);

$monthlyData = [];
$minDataTime = PHP_INT_MAX; // Initialize with a very large number
$maxDataTime = 0; // Initialize with 0 for max

foreach ($joiningRecords as $record) {
    // Ensure month and year are valid before using them
    if (empty($record->year) || empty($record->month)) {
        continue;
    }
    // We can use the yyyymm_key from the record or construct it.
    // The key here is for internal data mapping, not the get_records_sql key.
    $monthlyData[$record->year . '-' . (int)$record->month] = (int)$record->count;

    // Collect min/max timecreated from the records retrieved
    // Using the aggregated min_timecreated_data and max_timecreated_data from the SQL result
    if ($record->min_timecreated_data < $minDataTime) {
        $minDataTime = $record->min_timecreated_data;
    }
    if ($record->max_timecreated_data > $maxDataTime) {
        $maxDataTime = $record->max_timecreated_data;
    }
}

// Determine the start and end dates for the trend graph to ensure all months are included
$graphStartTime = null;
$graphEndTime = null;

// 1. Prioritize explicit startDate and endDate parameters
if ($startDate !== '' && $endDate !== '') {
    $graphStartTime = strtotime($startDate);
    $graphEndTime = strtotime($endDate . ' 23:59:59');
} elseif ($startDate !== '') {
    $graphStartTime = strtotime($startDate);
    // If only startDate is given, infer end from data or default to end of year
    if ($maxDataTime > 0 && $maxDataTime >= $graphStartTime) { // Ensure data max is not before start date
        $graphEndTime = $maxDataTime;
    } else {
        $graphEndTime = strtotime(date('Y-12-31 23:59:59', $graphStartTime)); // End of year for start date
    }
} elseif ($endDate !== '') {
    $graphEndTime = strtotime($endDate . ' 23:59:59');
    // If only endDate is given, infer start from data or default to start of year
    if ($minDataTime < PHP_INT_MAX && $minDataTime <= $graphEndTime) { // Ensure data min is not after end date
        $graphStartTime = $minDataTime;
    } else {
        $graphStartTime = strtotime(date('Y-01-01', $graphEndTime)); // Start of year for end date
    }
}

// 2. If no explicit dates, infer from actual data
if ($graphStartTime === null && $graphEndTime === null) {
    if ($minDataTime < PHP_INT_MAX && $maxDataTime > 0) {
        $graphStartTime = $minDataTime;
        $graphEndTime = $maxDataTime;
    }
}

// 3. Default to current year if no dates are determined yet (no data, no explicit filters)
if ($graphStartTime === null || $graphEndTime === null) {
    $currentYear = date('Y');
    $graphStartTime = strtotime($currentYear . '-01-01');
    $graphEndTime = strtotime($currentYear . '-12-31 23:59:59');
}

// Ensure $graphEndTime is always at least $graphStartTime if it somehow got smaller
if ($graphEndTime < $graphStartTime) {
    $graphEndTime = $graphStartTime;
}

// Adjust graph start/end to the first day of the month for DatePeriod
$startGraph = new DateTime(date('Y-m-01', $graphStartTime));
$endGraph = new DateTime(date('Y-m-01', $graphEndTime));
$endGraph->modify('+1 month'); // Ensure the last month is included in the period

$interval = new DateInterval('P1M');
$period = new DatePeriod($startGraph, $interval, $endGraph);

foreach ($period as $dt) {
    $year = $dt->format('Y');
    $month = (int)$dt->format('n'); // 'n' for month without leading zeros
    $key = $year . '-' . $month;
    $joiningCourses[] = [
        'month' => $monthNames[$month],
        'year' => (int)$year,
        'count' => isset($monthlyData[$key]) ? $monthlyData[$key] : 0
    ];
}


// ---- Enrollment Metrics ----
$totalStudents = count($filteredUserids);
$enrolledStudents = $DB->count_records_sql("
    SELECT COUNT(*) FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    WHERE ue.userid IN (" . implode(',', $filteredUserids) . ")
    $courseFilterSQL
    $dateFilter
", $allParams);

$completedCourses = $DB->count_records_sql("
    SELECT COUNT(*) FROM (
        SELECT cc.userid, cc.course FROM {course_completions} cc
        JOIN {enrol} e ON cc.course = e.courseid
        WHERE cc.userid IN (" . implode(',', $filteredUserids) . ") AND cc.timecompleted IS NOT NULL
        $courseFilterSQL
        UNION
        SELECT ci.userid, c.course FROM {customcert_issues} ci
        JOIN {customcert} c ON ci.customcertid = c.id
        JOIN {enrol} e ON c.course = e.courseid
        WHERE ci.userid IN (" . implode(',', $filteredUserids) . ")
        $courseFilterSQLForCustomCert
    ) AS completions
", $courseParams);

$excelledStudents = $DB->count_records_sql("
    SELECT COUNT(DISTINCT ue.userid)
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    LEFT JOIN {course_completions} cc ON cc.course = e.courseid AND cc.userid = ue.userid
    LEFT JOIN {customcert} custcert ON custcert.course = e.courseid
    LEFT JOIN {customcert_issues} cert_issue ON cert_issue.customcertid = custcert.id AND cert_issue.userid = ue.userid
    WHERE ue.userid IN (" . implode(',', $filteredUserids) . ")
    AND (cc.timecompleted IS NOT NULL OR cert_issue.id IS NOT NULL)
    $courseFilterSQL
", $courseParams);

$failedCourses = $DB->count_records_sql("
    SELECT COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") FROM {course_completions} cc
    JOIN {enrol} e ON cc.course = e.courseid
    WHERE cc.userid IN (" . implode(',', $filteredUserids) . ") AND cc.timecompleted IS NULL
    $courseFilterSQL
", $courseParams);

$notStarted = $DB->count_records_sql("
    SELECT COUNT(DISTINCT ue.userid)
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    LEFT JOIN {course_completions} cc ON ue.userid = cc.userid AND e.courseid = cc.course
    WHERE ue.userid IN (" . implode(',', $filteredUserids) . ") AND cc.id IS NULL
    $courseFilterSQL
    $dateFilter
", $allParams);

$enrollmentAnalytics = [
    'studentJoiningCourses' => $joiningCourses,
    'ageDistribution' => $ageDistribution,
    'studentByLocation' => $studentByLocation,
    'metrics' => [
        'totalEnrollments' => $enrolledStudents,
        'enrolledStudents' => $enrolledStudents,
        'excelledStudents' => $excelledStudents,
        'completedCourses' => $completedCourses,
        'failedCourses' => $failedCourses,
        'notStarted' => $notStarted,
        'sexMetrics' => [
            'males' => $maleEnroll,
            'females' => $femaleEnroll
        ]
    ]
];

// ---- Position Analytics ----
// -- FIXED IMPLEMENTATION: ensure completion counts are accurate as for other analytics --
$positionData = [];
$positions = [];

// Get all unique positions
foreach ($userDetails as $ud) {
    if (!in_array($ud->userid, $filteredUserids)) continue;
    if (!empty($ud->position) && !isset($positions[$ud->position])) {
        $positions[$ud->position] = true;
    }
}

// For each position, get enrollments, completions, failures (same logic as other analytics)
foreach (array_keys($positions) as $posName) {
    // Get users with this position
    $positionUserIds = [];
    foreach ($userDetails as $ud) {
        if (!in_array($ud->userid, $filteredUserids)) continue;
        if ($ud->position === $posName) {
            $positionUserIds[] = $ud->userid;
        }
    }
    if (empty($positionUserIds)) continue;
    $positionUserIdsStr = implode(',', $positionUserIds);

    // Enrollments for this position
    $enrollments = $DB->count_records_sql("
        SELECT COUNT(*) FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        WHERE ue.userid IN ($positionUserIdsStr) $courseFilterSQL $dateFilter", $allParams);

    // Completions for this position
    $completions = $DB->count_records_sql("
        SELECT COUNT(*) FROM (
            SELECT cc.userid, cc.course FROM {course_completions} cc JOIN {enrol} e ON cc.course = e.courseid WHERE cc.userid IN ($positionUserIdsStr) AND cc.timecompleted IS NOT NULL $courseFilterSQL
            UNION
            SELECT ci.userid, c.course FROM {customcert_issues} ci JOIN {customcert} c ON ci.customcertid = c.id JOIN {enrol} e ON c.course = e.courseid WHERE ci.userid IN ($positionUserIdsStr) $courseFilterSQLForCustomCert
        ) AS completions", $courseParams);

    // Failures for this position
    $failures = $DB->count_records_sql("
        SELECT COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") FROM {course_completions} cc JOIN {enrol} e ON cc.course = e.courseid
        WHERE cc.userid IN ($positionUserIdsStr) AND cc.timecompleted IS NULL $courseFilterSQL", $courseParams);

    $positionData[] = [
        'name' => $posName,
        'enrollments' => $enrollments,
        'completions' => $completions,
        'failures' => $failures
    ];
}

// ---- Courses Analytics ----
$coursesAnalytics = [];

// Determine courses to analyze based on priority: selectedCourses -> courseCategory -> all courses with enrollments
$coursesToAnalyze = [];

if (!empty($courseIds)) {
    // If courseIds were already populated by selectedCourses or courseCategory earlier, use them directly
    $coursesToAnalyze = $courseIds;
} else {
    // If neither selectedCourses nor courseCategory were provided,
    // get all courses that filtered users are enrolled in (within date range if specified)
    // This is the "default" if no course-specific filters are active.
    if (!empty($filteredUserids)) {
        $coursesToAnalyze = $DB->get_fieldset_sql("
            SELECT DISTINCT e.courseid FROM {user_enrolments} ue
            JOIN {enrol} e ON ue.enrolid = e.id
            WHERE ue.userid IN (" . implode(',', $filteredUserids) . ")
            $dateFilter",
            $dateParams
        );
    }
}

// Get course data if we have courses to analyze
if (!empty($coursesToAnalyze)) {
    list($inSql, $courseAnalysisParams) = $DB->get_in_or_equal($coursesToAnalyze, SQL_PARAMS_NAMED, 'crs');
    $courseRecords = $DB->get_records_sql("
        SELECT id, fullname 
        FROM {course} 
        WHERE id $inSql",
        $courseAnalysisParams
    );
    
    foreach ($courseRecords as $course) {
        // Enrollments for this course
        $enrollments = $DB->count_records_sql("
            SELECT COUNT(DISTINCT ue.userid) FROM {user_enrolments} ue
            JOIN {enrol} e ON ue.enrolid = e.id
            WHERE e.courseid = :courseid AND ue.userid IN (" . implode(',', $filteredUserids) . ")
            $dateFilter
        ", array_merge($dateParams, ['courseid' => $course->id]));
        
        // Completions for this course
        $completions = $DB->count_records_sql("
            SELECT COUNT(DISTINCT userid) FROM (
                SELECT cc.userid FROM {course_completions} cc
                WHERE cc.course = :courseid1 AND cc.userid IN (" . implode(',', $filteredUserids) . ") AND cc.timecompleted IS NOT NULL
                UNION
                SELECT ci.userid FROM {customcert_issues} ci
                JOIN {customcert} c ON ci.customcertid = c.id
                WHERE c.course = :courseid2 AND ci.userid IN (" . implode(',', $filteredUserids) . ")
            ) AS completions
        ", ['courseid1' => $course->id, 'courseid2' => $course->id]);
        
        // Failures for this course
        $failures = $DB->count_records_sql("
            SELECT COUNT(DISTINCT cc.userid) FROM {course_completions} cc
            WHERE cc.course = :courseid AND cc.userid IN (" . implode(',', $filteredUserids) . ") AND cc.timecompleted IS NULL
        ", ['courseid' => $course->id]);
        
        $coursesAnalytics[] = [
            'name' => $course->fullname,
            'id' => $course->id,
            'enrollments' => $enrollments,
            'completions' => $completions,
            'failures' => $failures
        ];
    }
}

// ---- Categories Analytics (NEW) ----
$categoriesAnalytics = [];
// Get all unique servicepointcategories from filtered users
$uniqueCategories = [];
foreach ($userDetails as $ud) {
    if (!in_array($ud->userid, $filteredUserids)) continue;
    $cat = $ud->servicepointcategory;
    if ($cat !== null && $cat !== '') {
        $uniqueCategories[$cat] = true;
    }
}
foreach (array_keys($uniqueCategories) as $categoryName) {
    // Users in this category
    $categoryUserIds = [];
    foreach ($userDetails as $ud) {
        if (!in_array($ud->userid, $filteredUserids)) continue;
        if ($ud->servicepointcategory === $categoryName) {
            $categoryUserIds[] = $ud->userid;
        }
    }
    if (empty($categoryUserIds)) continue;
    $categoryUserIdsStr = implode(',', $categoryUserIds);

    // Enrollments for this category
    $enrollments = $DB->count_records_sql("
        SELECT COUNT(*) FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        WHERE ue.userid IN ($categoryUserIdsStr) $courseFilterSQL $dateFilter", $allParams);

    // Completions for this category
    $completions = $DB->count_records_sql("
        SELECT COUNT(*) FROM (
            SELECT cc.userid, cc.course FROM {course_completions} cc JOIN {enrol} e ON cc.course = e.courseid WHERE cc.userid IN ($categoryUserIdsStr) AND cc.timecompleted IS NOT NULL $courseFilterSQL
            UNION
            SELECT ci.userid, c.course FROM {customcert_issues} ci JOIN {customcert} c ON ci.customcertid = c.id JOIN {enrol} e ON c.course = e.courseid WHERE ci.userid IN ($categoryUserIdsStr) $courseFilterSQLForCustomCert
        ) AS completions", $courseParams);

    // Failures for this category
    $failures = $DB->count_records_sql("
        SELECT COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") FROM {course_completions} cc JOIN {enrol} e ON cc.course = e.courseid
        WHERE cc.userid IN ($categoryUserIdsStr) AND cc.timecompleted IS NULL $courseFilterSQL", $courseParams);

    $categoriesAnalytics[] = [
        'name' => $categoryName,
        'enrollments' => $enrollments,
        'completions' => $completions,
        'failures' => $failures
    ];
}

$enrollmentAnalytics['metrics']['enrolledStudents'] = $enrolledStudents; // Ensure it's set

// ---- Sex Failling Metrics ----
$maleFaillerCount = $DB->count_records_sql("
    SELECT COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") FROM {course_completions} cc
    JOIN {enrol} e ON cc.course = e.courseid
    WHERE LOWER((SELECT gender FROM {additional_user_details} WHERE userid = cc.userid LIMIT 1)) IN ('m', 'male') AND cc.timecompleted IS NULL AND cc.userid IN (" . implode(',', $filteredUserids) . ")
    $courseFilterSQL
", $courseParams);
$femaleFaillerCount = $DB->count_records_sql("
    SELECT COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") FROM {course_completions} cc
    JOIN {enrol} e ON cc.course = e.courseid
    WHERE LOWER((SELECT gender FROM {additional_user_details} WHERE userid = cc.userid LIMIT 1)) IN ('f', 'female') AND cc.timecompleted IS NULL AND cc.userid IN (" . implode(',', $filteredUserids) . ")
    $courseFilterSQL
", $courseParams);

// ---- Performance Location Trend ----
$performanceLocationTrend = [];
foreach ($locationUsers as $loc => $usersInLoc) {
    $locUserIds = array_map(function($ud) { return $ud->userid; }, $usersInLoc);
    if (empty($locUserIds)) continue;

    // Count all course enrollments per location
    $enrolementCount = $DB->count_records_sql("
        SELECT COUNT(*) FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        WHERE ue.userid IN (" . implode(',', $locUserIds) . ")
        $courseFilterSQL
        $dateFilter
    ", $allParams);

    // Count all course completions per location
    $completionCount = $DB->count_records_sql("
        SELECT COUNT(*) FROM (
            SELECT cc.userid, cc.course FROM {course_completions} cc
            JOIN {enrol} e ON cc.course = e.courseid
            WHERE cc.userid IN (" . implode(',', $locUserIds) . ") AND cc.timecompleted IS NOT NULL
            $courseFilterSQL
            UNION
            SELECT ci.userid, c.course FROM {customcert_issues} ci
            JOIN {customcert} c ON ci.customcertid = c.id
            JOIN {enrol} e ON c.course = e.courseid
            WHERE ci.userid IN (" . implode(',', $locUserIds) . ")
            $courseFilterSQLForCustomCert
        ) AS completions
    ", $courseParams);

    // Count all course failures per location
    $faillerCount = $DB->count_records_sql("
        SELECT COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") FROM {course_completions} cc
        JOIN {enrol} e ON cc.course = e.courseid
        WHERE cc.userid IN (" . implode(',', $locUserIds) . ") AND cc.timecompleted IS NULL
        $courseFilterSQL
    ", $courseParams);
    
    $successRate = $enrolementCount > 0 ? round(($completionCount / $enrolementCount) * 100, 2) : 0;

    $performanceLocationTrend[] = [
        'location' => $loc,
        'enrolementCount' => $enrolementCount,
        'completionCount' => $completionCount,
        'faillerCount' => $faillerCount,
        'successRate' => $successRate
    ];
}

// ---- Performance Trend ----
$performanceTrend = [];
// Build SQL for enrollment data with date filtering
$perfTrendEnrollmentSql = "
    SELECT
        " . $DB->sql_concat('FROM_UNIXTIME(ue.timecreated, \'%Y-%m\')') . " AS yyyymm_key,
        FROM_UNIXTIME(ue.timecreated, '%m') AS month,
        FROM_UNIXTIME(ue.timecreated, '%Y') AS year,
        COUNT(DISTINCT " . $DB->sql_concat('ue.userid', "'-'", 'e.courseid') . ") AS enrolementCount
    FROM {user_enrolments} ue
    JOIN {enrol} e ON ue.enrolid = e.id
    WHERE ue.userid IN (" . implode(',', $filteredUserids) . ")
    $courseFilterSQL
    $dateFilter
";
$perfTrendEnrollmentSql .= " GROUP BY year, month ORDER BY year, month";
$monthlyEnrolments = $DB->get_records_sql($perfTrendEnrollmentSql, $allParams);

// Get completions data with proper date filtering
$perfTrendCompletionSql = "
    SELECT
        " . $DB->sql_concat('FROM_UNIXTIME(completion_time, \'%Y-%m\')') . " AS yyyymm_key,
        FROM_UNIXTIME(completion_time, '%m') AS month,
        FROM_UNIXTIME(completion_time, '%Y') AS year,
        COUNT(DISTINCT " . $DB->sql_concat('userid', "'-'", 'course') . ") AS completionCount
    FROM (
        SELECT cc.userid, cc.course, cc.timecompleted AS completion_time
        FROM {course_completions} cc
        JOIN {enrol} e ON cc.course = e.courseid
        WHERE cc.userid IN (" . implode(',', $filteredUserids) . ")
        AND cc.timecompleted IS NOT NULL
        $courseFilterSQL

        UNION

        SELECT ci.userid, c.course, ci.timecreated AS completion_time
        FROM {customcert_issues} ci
        JOIN {customcert} c ON ci.customcertid = c.id
        JOIN {enrol} e ON c.course = e.courseid
        WHERE ci.userid IN (" . implode(',', $filteredUserids) . ")
        $courseFilterSQLForCustomCert
    ) AS all_completions
    WHERE completion_time IS NOT NULL
";
if (!empty($dateFilter)) { // Apply date filter if present
    // The dateFilter refers to ue.timecreated, but for completions we need to filter completion_time
    // Re-evaluate date range for completion_time here based on $startDate and $endDate
    if ($startDate !== '' && $endDate !== '') {
        $perfTrendCompletionSql .= " AND completion_time >= :startdate AND completion_time <= :enddate";
    } elseif ($startDate !== '') {
        $perfTrendCompletionSql .= " AND completion_time >= :startdate";
    } elseif ($endDate !== '') {
        $perfTrendCompletionSql .= " AND completion_time <= :enddate";
    }
}
$perfTrendCompletionSql .= " GROUP BY year, month ORDER BY year, month";
$monthlyCompletions = $DB->get_records_sql($perfTrendCompletionSql, $allParams);


// Get failures data with proper date filtering
$perfTrendFailersSql = "
    SELECT
        " . $DB->sql_concat('FROM_UNIXTIME(cc.timeenrolled, \'%Y-%m\')') . " AS yyyymm_key,
        FROM_UNIXTIME(cc.timeenrolled, '%m') AS month,
        FROM_UNIXTIME(cc.timeenrolled, '%Y') AS year,
        COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") AS faillerCount
    FROM {course_completions} cc
    JOIN {enrol} e ON cc.course = e.courseid
    WHERE cc.userid IN (" . implode(',', $filteredUserids) . ")
    AND cc.timecompleted IS NULL
    $courseFilterSQL
";
// Similar to completions, apply date filter to timeenrolled if available
if (!empty($dateFilter)) {
    if ($startDate !== '' && $endDate !== '') {
        $perfTrendFailersSql .= " AND cc.timeenrolled >= :startdate AND cc.timeenrolled <= :enddate";
    } elseif ($startDate !== '') {
        $perfTrendFailersSql .= " AND cc.timeenrolled >= :startdate";
    } elseif ($endDate !== '') {
        $perfTrendFailersSql .= " AND cc.timeenrolled <= :enddate";
    }
}
$perfTrendFailersSql .= " GROUP BY year, month ORDER BY year, month";
$monthlyFailers = $DB->get_records_sql($perfTrendFailersSql, $allParams);

// Combine all data for performance trend
$allTrendData = [];
$perfMinDataTime = PHP_INT_MAX;
$perfMaxDataTime = 0;

foreach($monthlyEnrolments as $rec) {
    if (empty($rec->year)) continue;
    $key = $rec->year.'-'.(int)$rec->month;
    $allTrendData[$key]['enrolementCount'] = (int)$rec->enrolementcount;
    if (!isset($allTrendData[$key]['completionCount'])) $allTrendData[$key]['completionCount'] = 0;
    if (!isset($allTrendData[$key]['faillerCount'])) $allTrendData[$key]['faillerCount'] = 0;
    $ts = strtotime("{$rec->year}-{$rec->month}-01");
    if ($ts < $perfMinDataTime) $perfMinDataTime = $ts;
    if ($ts > $perfMaxDataTime) $perfMaxDataTime = $ts;
}
foreach($monthlyCompletions as $rec) {
    if (empty($rec->year)) continue;
    $key = $rec->year.'-'.(int)$rec->month;
    $allTrendData[$key]['completionCount'] = (int)$rec->completioncount;
    if (!isset($allTrendData[$key]['enrolementCount'])) $allTrendData[$key]['enrolementCount'] = 0;
    if (!isset($allTrendData[$key]['faillerCount'])) $allTrendData[$key]['faillerCount'] = 0;
    $ts = strtotime("{$rec->year}-{$rec->month}-01");
    if ($ts < $perfMinDataTime) $perfMinDataTime = $ts;
    if ($ts > $perfMaxDataTime) $perfMaxDataTime = $ts;
}
foreach($monthlyFailers as $rec) {
    if (empty($rec->year)) continue;
    $key = $rec->year.'-'.(int)$rec->month;
    $allTrendData[$key]['faillerCount'] = (int)$rec->faillercount;
    if (!isset($allTrendData[$key]['enrolementCount'])) $allTrendData[$key]['enrolementCount'] = 0;
    if (!isset($allTrendData[$key]['completionCount'])) $allTrendData[$key]['completionCount'] = 0;
    $ts = strtotime("{$rec->year}-{$rec->month}-01");
    if ($ts < $perfMinDataTime) $perfMinDataTime = $ts;
    if ($ts > $perfMaxDataTime) $perfMaxDataTime = $ts;
}

// Determine the date range for the performance trend graph (similar logic to studentJoiningCourses)
$perfGraphStartTime = null;
$perfGraphEndTime = null;

// 1. Prioritize explicit startDate and endDate parameters
if ($startDate !== '' && $endDate !== '') {
    $perfGraphStartTime = strtotime($startDate);
    $perfGraphEndTime = strtotime($endDate . ' 23:59:59');
} elseif ($startDate !== '') {
    $perfGraphStartTime = strtotime($startDate);
    if ($perfMaxDataTime > 0 && $perfMaxDataTime >= $perfGraphStartTime) {
        $perfGraphEndTime = $perfMaxDataTime;
    } else {
        $perfGraphEndTime = strtotime(date('Y-12-31 23:59:59', $perfGraphStartTime));
    }
} elseif ($endDate !== '') {
    $perfGraphEndTime = strtotime($endDate . ' 23:59:59');
    if ($perfMinDataTime < PHP_INT_MAX && $perfMinDataTime <= $perfGraphEndTime) {
        $perfGraphStartTime = $perfMinDataTime;
    } else {
        $perfGraphStartTime = strtotime(date('Y-01-01', $perfGraphEndTime));
    }
}

// 2. If no explicit dates, infer from actual data
if ($perfGraphStartTime === null && $perfGraphEndTime === null) {
    if ($perfMinDataTime < PHP_INT_MAX && $perfMaxDataTime > 0) {
        $perfGraphStartTime = $perfMinDataTime;
        $perfGraphEndTime = $perfMaxDataTime;
    }
}

// 3. Default to current year if no dates are determined yet
if ($perfGraphStartTime === null || $perfGraphEndTime === null) {
    $currentYear = date('Y');
    $perfGraphStartTime = strtotime($currentYear . '-01-01');
    $perfGraphEndTime = strtotime($currentYear . '-12-31 23:59:59');
}

// Ensure $perfGraphEndTime is not before $perfGraphStartTime
if ($perfGraphEndTime < $perfGraphStartTime) {
    $perfGraphEndTime = $perfGraphStartTime;
}

// Adjust graph start/end to the first day of the month for DatePeriod
$startPerfGraph = new DateTime(date('Y-m-01', $perfGraphStartTime));
$endPerfGraph = new DateTime(date('Y-m-01', $perfGraphEndTime));
$endPerfGraph->modify('+1 month'); // Ensure the last month is included in the period

$interval = new DateInterval('P1M');
$period = new DatePeriod($startPerfGraph, $interval, $endPerfGraph);

foreach ($period as $dt) {
    $year = $dt->format('Y');
    $month = (int)$dt->format('n');
    $key = $year . '-' . $month;
    $performanceTrend[] = [
        'month' => $monthNames[$month],
        'year' => (int)$year,
        'enrolementCount' => isset($allTrendData[$key]['enrolementCount']) ? $allTrendData[$key]['enrolementCount'] : 0,
        'completionCount' => isset($allTrendData[$key]['completionCount']) ? $allTrendData[$key]['completionCount'] : 0,
        'faillerCount' => isset($allTrendData[$key]['faillerCount']) ? $allTrendData[$key]['faillerCount'] : 0
    ];
}

// ---- Age distribution for completion/failure/inProgress ----
$ageDistributionLA = [];
foreach ($ageGroups as $group => $ageRange) {
    $ageDistributionLA[$group] = [
        'ageGroup' => $group,
        'completeCount' => 0,
        'inProgressCount' => 0,
        'failedCount' => 0
    ];
}

$userAgeGroups = [];
foreach ($userDetails as $ud) {
    if (!in_array($ud->userid, $filteredUserids) || empty($ud->dateofbirth)) {
        continue;
    }
    $birthSeconds = (strlen($ud->dateofbirth) > 10) ? intval($ud->dateofbirth / 1000) : intval($ud->dateofbirth);
    $age = floor(($now - $birthSeconds) / (365.25 * 24 * 3600));
    foreach ($ageGroups as $group => [$min, $max]) {
        if (($age >= $min && $age <= $max) || ($group === '45+' && $age >= $min)) {
            if (!isset($userAgeGroups[$group])) {
                $userAgeGroups[$group] = [];
            }
            $userAgeGroups[$group][] = $ud->userid;
            break;
        }
    }
}

foreach ($userAgeGroups as $group => $userIdsInGroup) {
    $userIdsInGroupStr = implode(',', $userIdsInGroup);

    // Enrollments
    $enrollmentCount = $DB->count_records_sql("
        SELECT COUNT(*) FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        WHERE ue.userid IN ($userIdsInGroupStr) $courseFilterSQL $dateFilter", $allParams);

    // Completions
    $completionCount = $DB->count_records_sql("
        SELECT COUNT(*) FROM (
            SELECT cc.userid, cc.course FROM {course_completions} cc JOIN {enrol} e ON cc.course = e.courseid WHERE cc.userid IN ($userIdsInGroupStr) AND cc.timecompleted IS NOT NULL $courseFilterSQL
            UNION
            SELECT ci.userid, c.course FROM {customcert_issues} ci JOIN {customcert} c ON ci.customcertid = c.id JOIN {enrol} e ON c.course = e.courseid WHERE ci.userid IN ($userIdsInGroupStr) $courseFilterSQLForCustomCert
        ) AS completions", $courseParams);

    // Failures
    $failureCount = $DB->count_records_sql("
        SELECT COUNT(DISTINCT " . $DB->sql_concat('cc.userid', "'-'", 'cc.course') . ") FROM {course_completions} cc JOIN {enrol} e ON cc.course = e.courseid
        WHERE cc.userid IN ($userIdsInGroupStr) AND cc.timecompleted IS NULL $courseFilterSQL", $courseParams);

    $ageDistributionLA[$group]['completeCount'] = $completionCount;
    $ageDistributionLA[$group]['failedCount'] = $failureCount;
    $ageDistributionLA[$group]['inProgressCount'] = $enrollmentCount - ($completionCount + $failureCount);
    if ($ageDistributionLA[$group]['inProgressCount'] < 0) {
        $ageDistributionLA[$group]['inProgressCount'] = 0;
    }
}

$ageDistributionLAArray = array_values($ageDistributionLA);

// Get total courses (either selected courses or all available)
$totalCourses = 0;
if (!empty($coursesToAnalyze)) { 
    $totalCourses = count($coursesToAnalyze);
} 

$completedCoursesLA = $totalCompleted;
$failedCoursesLA = $totalFailed;
$completionRate = $totalEnrollments > 0 ? round(($completedCoursesLA / $totalEnrollments) * 100, 2) : 0;

// Average grade calculation
$avgScore = 0;
$gradeParams = [];
$gradeSql = "SELECT AVG(finalgrade) AS avgscore FROM {grade_grades} WHERE userid IN (" . implode(',', $filteredUserids) . ")";

if (!empty($courseIds)) {
    list($courseGradeSql, $courseGradeParams) = $DB->get_in_or_equal($courseIds, SQL_PARAMS_NAMED, 'gradecourse');
    $gradeSql .= " AND itemid IN (
        SELECT id FROM {grade_items} 
        WHERE courseid $courseGradeSql
    )";
    $gradeParams = $courseGradeParams;
}

$gradeRecords = $DB->get_records_sql($gradeSql, $gradeParams);
foreach ($gradeRecords as $rec) {
    $avgScore = $rec->avgscore ? round($rec->avgscore, 2) : 0;
}

$inProgressCourses = $totalEnrollments - ($completedCoursesLA + $failedCoursesLA);
if ($inProgressCourses < 0) $inProgressCourses = 0;


$notStartedCourses = $notStarted;

$maleCompletion = $DB->count_records_sql("
    SELECT COUNT(*) FROM (
        SELECT cc.userid, cc.course FROM {course_completions} cc
        JOIN {enrol} e ON cc.course = e.courseid
        WHERE LOWER((SELECT gender FROM {additional_user_details} ad WHERE ad.userid = cc.userid LIMIT 1)) IN ('m', 'male') AND cc.userid IN (" . implode(',', $filteredUserids) . ") AND cc.timecompleted IS NOT NULL
        $courseFilterSQL
        UNION
        SELECT ci.userid, c.course FROM {customcert_issues} ci
        JOIN {customcert} c ON ci.customcertid = c.id
        JOIN {enrol} e ON c.course = e.courseid
        WHERE LOWER((SELECT gender FROM {additional_user_details} ad WHERE ad.userid = ci.userid LIMIT 1)) IN ('m', 'male') AND ci.userid IN (" . implode(',', $filteredUserids) . ")
        $courseFilterSQLForCustomCert
    ) AS completions
", $courseParams);

$femaleCompletion = $DB->count_records_sql("
    SELECT COUNT(*) FROM (
        SELECT cc.userid, cc.course FROM {course_completions} cc
        JOIN {enrol} e ON cc.course = e.courseid
        WHERE LOWER((SELECT gender FROM {additional_user_details} ad WHERE ad.userid = cc.userid LIMIT 1)) IN ('f', 'female') AND cc.userid IN (" . implode(',', $filteredUserids) . ") AND cc.timecompleted IS NOT NULL
        $courseFilterSQL
        UNION
        SELECT ci.userid, c.course FROM {customcert_issues} ci
        JOIN {customcert} c ON ci.customcertid = c.id
        JOIN {enrol} e ON c.course = e.courseid
        WHERE LOWER((SELECT gender FROM {additional_user_details} ad WHERE ad.userid = ci.userid LIMIT 1)) IN ('f', 'female') AND ci.userid IN (" . implode(',', $filteredUserids) . ")
        $courseFilterSQLForCustomCert
    ) AS completions
", $courseParams);


$learningAnalytics = [
    'performanceLocationTrend' => $performanceLocationTrend,
    'performanceTrend' => $performanceTrend,
    'positionAnalytics' => $positionData,
    'coursesAnalytics' => $coursesAnalytics,
    'categoriesAnalytics' => $categoriesAnalytics,
    'metrics' => [
        'totalCourses' => $totalCourses,
        'completedCourses' => $completedCoursesLA,
        'failedCourses' => $failedCoursesLA,
        'averageCompletionRate' => $completionRate,
        'averageScore' => $avgScore,
        'inProgressCourses' => $inProgressCourses,
        'notStartedCourses' => $notStartedCourses
    ],
    'sexCompletionMetrics' => [
        'male' => $maleCompletion,
        'female' => $femaleCompletion
    ],
    'sexFaillingMetrics' => [
        'male' => $maleFaillerCount,
        'female' => $femaleFaillerCount
    ],
    'ageDistribution' => $ageDistributionLAArray
];

echo json_encode([
    'totalAnalytics' => $totalAnalytics,
    'enrollmentAnalytics' => $enrollmentAnalytics,
    'learningAnalytics' => $learningAnalytics,
    'studentsData' => $studentsData
]);
exit;