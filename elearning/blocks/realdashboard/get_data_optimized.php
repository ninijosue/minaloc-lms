<?php
/**
 * OPTIMIZED Analytics API - Uses data warehouse but keeps original calculation logic
 *
 * Strategy: Fetch ALL enrollment data from warehouse table ONCE, then use
 * the exact same PHP calculation logic as get_data.php
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/dml/moodle_database.php');

// Increase limits for large datasets
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '180');

global $DB, $USER, $SESSION;

// ========================================
// STEP 1: HANDLE FILTER PARAMETERS (SAME AS ORIGINAL)
// ========================================
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

// Build filters for additional_user_details
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

// Process selected courses (SAME AS ORIGINAL)
$courseIds = [];
if (!empty($selectedCourses)) {
    $decodedCourses = json_decode($selectedCourses);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedCourses)) {
        $courseIds = array_map('intval', $decodedCourses);
    } else if (is_numeric($selectedCourses)) {
        $courseIds = [intval($selectedCourses)];
    } else if (is_string($selectedCourses) && strpos($selectedCourses, ',') !== false) {
        $parts = explode(',', $selectedCourses);
        $courseIds = array_map('intval', array_filter($parts, 'is_numeric'));
    } else if (is_array($selectedCourses)) {
        $courseIds = array_map('intval', $selectedCourses);
    }
}

if (empty($courseIds) && !empty($courseCategory)) {
    $courseIds = $DB->get_fieldset_sql("
        SELECT id FROM {course}
        WHERE category = :category",
        ['category' => $courseCategory]
    );
}

// Get filtered users from additional_user_details (SAME AS ORIGINAL)
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

// Deduplicate userDetails by userid (SAME AS ORIGINAL)
$userMap = [];
foreach ($userDetails as $ud) {
    if (!isset($userMap[$ud->userid])) {
        $userMap[$ud->userid] = $ud;
    }
}
$userDetails = array_values($userMap);

// ========================================
// KEY OPTIMIZATION: LOAD ALL ENROLLMENT DATA FROM WAREHOUSE ONCE
// ========================================

// Get all userids
$allUserids = array_column($userDetails, 'userid');
if (empty($allUserids)) {
    // No users matched filters - return empty response
    echo json_encode([
        'totalAnalytics' => ['studentJoiningCourses' => [], 'ageDistribution' => [], 'studentByLocation' => [], 'metrics' => []],
        'enrollmentAnalytics' => ['studentJoiningCourses' => [], 'ageDistribution' => [], 'studentByLocation' => [], 'metrics' => []],
        'learningAnalytics' => ['performanceLocationTrend' => [], 'performanceTrend' => [], 'positionAnalytics' => [], 'coursesAnalytics' => [], 'categoriesAnalytics' => [], 'metrics' => [], 'sexCompletionMetrics' => [], 'sexFaillingMetrics' => [], 'ageDistribution' => []],
        'studentsData' => []
    ]);
    exit;
}

// Fetch ALL warehouse data for these users ONCE
// Use get_recordset_sql() because userid is not unique (one user = multiple enrollments)
$useridList = implode(',', $allUserids);
$warehouseRecordset = $DB->get_recordset_sql("
    SELECT
        lrs.userid,
        lrs.courseid,
        lrs.status,
        lrs.timeenrolled,
        lrs.timecompleted
    FROM {local_reporting_status} lrs
    WHERE lrs.userid IN ($useridList)
");

// Convert recordset to array for multiple iterations
$warehouseData = [];
foreach ($warehouseRecordset as $record) {
    $warehouseData[] = $record;
}
$warehouseRecordset->close(); // Important: close the recordset

// Build fast lookup structures to mimic what the original SQL queries would return
$enrollmentsByUser = []; // userid => [array of course enrollments]
$completionsByUser = []; // userid => [array of completed courses]
$failedByUser = []; // userid => [array of failed courses]

foreach ($warehouseData as $record) {
    $uid = $record->userid;
    $cid = $record->courseid;

    // Track all enrollments
    if (!isset($enrollmentsByUser[$uid])) {
        $enrollmentsByUser[$uid] = [];
    }
    $enrollmentsByUser[$uid][] = $record;

    // Track completions
    if ($record->status === 'completed') {
        if (!isset($completionsByUser[$uid])) {
            $completionsByUser[$uid] = [];
        }
        $completionsByUser[$uid][] = $record;
    }

    // Track failures
    if ($record->status === 'failed') {
        if (!isset($failedByUser[$uid])) {
            $failedByUser[$uid] = [];
        }
        $failedByUser[$uid][] = $record;
    }
}

// ========================================
// STEP 2: BUILD ENROLLMENT-BASED ANALYTICS
// All metrics count ENROLLMENTS not USERS!
// ========================================

// Setup age groups and common variables
$ageGroups = [
    '<25' => [0, 24],
    '25-34' => [25, 34],
    '35-44' => [35, 44],
    '45+' => [45, 200]
];
$now = time();
$locationField = 'district'; // Primary location field

// Convert date filters to timestamps
$startTimestamp = $startDate !== '' ? strtotime($startDate) : null;
$endTimestamp = $endDate !== '' ? strtotime($endDate . ' 23:59:59') : null;

// ========================================
// TOTAL ANALYTICS (NO FILTERS)
// ========================================

// Count total enrollments by status
$totalEnrollments = count($warehouseData);
$totalCompleted = 0;
$totalFailed = 0;
$totalInProgress = 0;

foreach ($warehouseData as $record) {
    if ($record->status === 'completed') {
        $totalCompleted++;
    } elseif ($record->status === 'failed') {
        $totalFailed++;
    } else {
        $totalInProgress++;
    }
}

// Sex metrics - COUNT ENROLLMENTS by gender
$maleEnrollments = 0;
$femaleEnrollments = 0;

// Create user gender lookup
$userGender = [];
foreach ($userDetails as $ud) {
    $userGender[$ud->userid] = strtolower(trim($ud->gender ?? ''));
}

foreach ($warehouseData as $record) {
    $gender = $userGender[$record->userid] ?? '';
    if ($gender === 'm' || $gender === 'male') {
        $maleEnrollments++;
    } elseif ($gender === 'f' || $gender === 'female') {
        $femaleEnrollments++;
    }
}

// Age distribution - COUNT ENROLLMENTS by age group
$ageEnrollments = array_fill_keys(array_keys($ageGroups), 0);

// Create user age lookup
$userAge = [];
foreach ($userDetails as $ud) {
    if (!empty($ud->dateofbirth)) {
        $birthSeconds = (strlen($ud->dateofbirth) > 10)
            ? intval($ud->dateofbirth / 1000)
            : intval($ud->dateofbirth);
        $age = floor(($now - $birthSeconds) / (365.25 * 24 * 3600));
        $userAge[$ud->userid] = $age;
    }
}

foreach ($warehouseData as $record) {
    if (isset($userAge[$record->userid])) {
        $age = $userAge[$record->userid];
        foreach ($ageGroups as $group => [$min, $max]) {
            if (($age >= $min && $age <= $max) || ($group === '45+' && $age >= $min)) {
                $ageEnrollments[$group]++;
                break;
            }
        }
    }
}

$ageDistribution_total = [];
foreach ($ageGroups as $group => $_) {
    $ageDistribution_total[] = [
        'ageGroup' => $group,
        'count' => $ageEnrollments[$group]
    ];
}

// Student by location - COUNT ENROLLMENTS by location
$userLocation = [];
foreach ($userDetails as $ud) {
    $userLocation[$ud->userid] = $ud->$locationField ?: 'Other';
}

$locationEnrollments = [];
foreach ($warehouseData as $record) {
    $loc = $userLocation[$record->userid] ?? 'Other';

    if (!isset($locationEnrollments[$loc])) {
        $locationEnrollments[$loc] = [
            'total' => 0,
            'males' => 0,
            'females' => 0,
            'ageDist' => array_fill_keys(array_keys($ageGroups), 0)
        ];
    }

    $locationEnrollments[$loc]['total']++;

    // Count by gender
    $gender = $userGender[$record->userid] ?? '';
    if ($gender === 'm' || $gender === 'male') {
        $locationEnrollments[$loc]['males']++;
    } elseif ($gender === 'f' || $gender === 'female') {
        $locationEnrollments[$loc]['females']++;
    }

    // Count by age
    if (isset($userAge[$record->userid])) {
        $age = $userAge[$record->userid];
        foreach ($ageGroups as $group => [$min, $max]) {
            if (($age >= $min && $age <= $max) || ($group === '45+' && $age >= $min)) {
                $locationEnrollments[$loc]['ageDist'][$group]++;
                break;
            }
        }
    }
}

$studentByLocation_total = [];
foreach ($locationEnrollments as $loc => $data) {
    $ageDistArr = [];
    foreach ($ageGroups as $group => $_) {
        $ageDistArr[] = [
            'ageGroup' => $group,
            'count' => $data['ageDist'][$group]
        ];
    }
    $studentByLocation_total[] = [
        'location' => $loc,
        'count' => $data['total'],
        'males' => $data['males'],
        'females' => $data['females'],
        'ageDistribution' => $ageDistArr
    ];
}

// Student joining courses by month
$monthlyEnrollments = [];
foreach ($warehouseData as $record) {
    $monthKey = date('Y-m', $record->timeenrolled);
    if (!isset($monthlyEnrollments[$monthKey])) {
        $monthlyEnrollments[$monthKey] = 0;
    }
    $monthlyEnrollments[$monthKey]++;
}

ksort($monthlyEnrollments);
$studentJoiningCourses_total = [];
foreach ($monthlyEnrollments as $monthKey => $count) {
    // Convert 'Y-m' format to month name and year
    $timestamp = strtotime($monthKey . '-01');
    $monthName = date('F', $timestamp); // Full month name (January, February, etc.)
    $year = (int)date('Y', $timestamp);

    $studentJoiningCourses_total[] = [
        'month' => $monthName,
        'year' => $year,
        'count' => $count
    ];
}

// Build totalAnalytics response
$totalAnalytics = [
    'studentJoiningCourses' => $studentJoiningCourses_total,
    'ageDistribution' => $ageDistribution_total,
    'studentByLocation' => $studentByLocation_total,
    'metrics' => [
        'totalStudents' => count($allUserids),
        'totalEnrollments' => $totalEnrollments,
        'enrolledStudents' => count($allUserids), // Users who have enrollments
        'excelledStudents' => 0, // Not implemented yet
        'completedCourses' => $totalCompleted,
        'failedCourses' => $totalFailed,
        'notStarted' => $totalInProgress,
        'sexMetrics' => [
            'males' => $maleEnrollments,
            'females' => $femaleEnrollments
        ]
    ]
];

// ========================================
// ENROLLMENT ANALYTICS (WITH FILTERS)
// ========================================

// Apply filters
$filteredEnrollments = [];
foreach ($warehouseData as $record) {
    // Course filter
    if (!empty($courseIds) && !in_array($record->courseid, $courseIds)) {
        continue;
    }

    // Date filter
    if ($startTimestamp !== null && $record->timeenrolled < $startTimestamp) {
        continue;
    }
    if ($endTimestamp !== null && $record->timeenrolled > $endTimestamp) {
        continue;
    }

    $filteredEnrollments[] = $record;
}

// Recalculate all metrics for filtered data
$filteredTotal = count($filteredEnrollments);
$filteredCompleted = 0;
$filteredFailed = 0;
$filteredInProgress = 0;

foreach ($filteredEnrollments as $record) {
    if ($record->status === 'completed') {
        $filteredCompleted++;
    } elseif ($record->status === 'failed') {
        $filteredFailed++;
    } else {
        $filteredInProgress++;
    }
}

// Sex metrics for filtered
$filteredMales = 0;
$filteredFemales = 0;
foreach ($filteredEnrollments as $record) {
    $gender = $userGender[$record->userid] ?? '';
    if ($gender === 'm' || $gender === 'male') {
        $filteredMales++;
    } elseif ($gender === 'f' || $gender === 'female') {
        $filteredFemales++;
    }
}

// Age distribution for filtered
$filteredAgeEnrollments = array_fill_keys(array_keys($ageGroups), 0);
foreach ($filteredEnrollments as $record) {
    if (isset($userAge[$record->userid])) {
        $age = $userAge[$record->userid];
        foreach ($ageGroups as $group => [$min, $max]) {
            if (($age >= $min && $age <= $max) || ($group === '45+' && $age >= $min)) {
                $filteredAgeEnrollments[$group]++;
                break;
            }
        }
    }
}

$ageDistribution_filtered = [];
foreach ($ageGroups as $group => $_) {
    $ageDistribution_filtered[] = [
        'ageGroup' => $group,
        'count' => $filteredAgeEnrollments[$group]
    ];
}

// Location for filtered
$filteredLocationEnrollments = [];
foreach ($filteredEnrollments as $record) {
    $loc = $userLocation[$record->userid] ?? 'Other';

    if (!isset($filteredLocationEnrollments[$loc])) {
        $filteredLocationEnrollments[$loc] = [
            'total' => 0,
            'males' => 0,
            'females' => 0,
            'ageDist' => array_fill_keys(array_keys($ageGroups), 0)
        ];
    }

    $filteredLocationEnrollments[$loc]['total']++;

    $gender = $userGender[$record->userid] ?? '';
    if ($gender === 'm' || $gender === 'male') {
        $filteredLocationEnrollments[$loc]['males']++;
    } elseif ($gender === 'f' || $gender === 'female') {
        $filteredLocationEnrollments[$loc]['females']++;
    }

    if (isset($userAge[$record->userid])) {
        $age = $userAge[$record->userid];
        foreach ($ageGroups as $group => [$min, $max]) {
            if (($age >= $min && $age <= $max) || ($group === '45+' && $age >= $min)) {
                $filteredLocationEnrollments[$loc]['ageDist'][$group]++;
                break;
            }
        }
    }
}

$studentByLocation_filtered = [];
foreach ($filteredLocationEnrollments as $loc => $data) {
    $ageDistArr = [];
    foreach ($ageGroups as $group => $_) {
        $ageDistArr[] = [
            'ageGroup' => $group,
            'count' => $data['ageDist'][$group]
        ];
    }
    $studentByLocation_filtered[] = [
        'location' => $loc,
        'count' => $data['total'],
        'males' => $data['males'],
        'females' => $data['females'],
        'ageDistribution' => $ageDistArr
    ];
}

// Monthly for filtered
$filteredMonthlyEnrollments = [];
foreach ($filteredEnrollments as $record) {
    $monthKey = date('Y-m', $record->timeenrolled);
    if (!isset($filteredMonthlyEnrollments[$monthKey])) {
        $filteredMonthlyEnrollments[$monthKey] = 0;
    }
    $filteredMonthlyEnrollments[$monthKey]++;
}

ksort($filteredMonthlyEnrollments);
$studentJoiningCourses_filtered = [];
foreach ($filteredMonthlyEnrollments as $monthKey => $count) {
    // Convert 'Y-m' format to month name and year
    $timestamp = strtotime($monthKey . '-01');
    $monthName = date('F', $timestamp); // Full month name (January, February, etc.)
    $year = (int)date('Y', $timestamp);

    $studentJoiningCourses_filtered[] = [
        'month' => $monthName,
        'year' => $year,
        'count' => $count
    ];
}

$enrollmentAnalytics = [
    'studentJoiningCourses' => $studentJoiningCourses_filtered,
    'ageDistribution' => $ageDistribution_filtered,
    'studentByLocation' => $studentByLocation_filtered,
    'metrics' => [
        'totalStudents' => count(array_unique(array_column($filteredEnrollments, 'userid'))),
        'totalEnrollments' => $filteredTotal,
        'enrolledStudents' => count(array_unique(array_column($filteredEnrollments, 'userid'))),
        'excelledStudents' => 0,
        'completedCourses' => $filteredCompleted,
        'failedCourses' => $filteredFailed,
        'notStarted' => $filteredInProgress,
        'sexMetrics' => [
            'males' => $filteredMales,
            'females' => $filteredFemales
        ]
    ]
];

// ========================================
// LEARNING ANALYTICS
// ========================================

// Completion/failure by sex - COUNT ENROLLMENTS
$maleCompleted = 0;
$maleFailed = 0;
$femaleCompleted = 0;
$femaleFailed = 0;

foreach ($filteredEnrollments as $record) {
    $gender = $userGender[$record->userid] ?? '';
    if ($record->status === 'completed') {
        if ($gender === 'm' || $gender === 'male') {
            $maleCompleted++;
        } elseif ($gender === 'f' || $gender === 'female') {
            $femaleCompleted++;
        }
    } elseif ($record->status === 'failed') {
        if ($gender === 'm' || $gender === 'male') {
            $maleFailed++;
        } elseif ($gender === 'f' || $gender === 'female') {
            $femaleFailed++;
        }
    }
}

// Performance by Location - using data already loaded
$performanceByLocation = [];
foreach ($filteredEnrollments as $record) {
    $loc = $userLocation[$record->userid] ?? 'Other';

    if (!isset($performanceByLocation[$loc])) {
        $performanceByLocation[$loc] = [
            'location' => $loc,
            'total' => 0,
            'completed' => 0,
            'failed' => 0,
            'inProgress' => 0
        ];
    }

    $performanceByLocation[$loc]['total']++;

    if ($record->status === 'completed') {
        $performanceByLocation[$loc]['completed']++;
    } elseif ($record->status === 'failed') {
        $performanceByLocation[$loc]['failed']++;
    } else {
        $performanceByLocation[$loc]['inProgress']++;
    }
}
$performanceLocationTrend = array_values($performanceByLocation);

// Performance Trend by Month - using data already loaded
$performanceByMonth = [];
foreach ($filteredEnrollments as $record) {
    $monthKey = date('Y-m', $record->timeenrolled);

    if (!isset($performanceByMonth[$monthKey])) {
        $performanceByMonth[$monthKey] = [
            'enrolled' => 0,
            'completed' => 0,
            'failed' => 0,
            'inProgress' => 0
        ];
    }

    $performanceByMonth[$monthKey]['enrolled']++;

    if ($record->status === 'completed') {
        $performanceByMonth[$monthKey]['completed']++;
    } elseif ($record->status === 'failed') {
        $performanceByMonth[$monthKey]['failed']++;
    } else {
        $performanceByMonth[$monthKey]['inProgress']++;
    }
}
ksort($performanceByMonth);
$performanceTrend = [];
foreach ($performanceByMonth as $monthKey => $data) {
    $timestamp = strtotime($monthKey . '-01');
    $performanceTrend[] = [
        'month' => date('F', $timestamp),
        'year' => (int)date('Y', $timestamp),
        'enrolled' => $data['enrolled'],
        'completed' => $data['completed'],
        'failed' => $data['failed'],
        'inProgress' => $data['inProgress']
    ];
}

// Position Analytics - using data already loaded
$userPosition = [];
foreach ($userDetails as $ud) {
    $userPosition[$ud->userid] = $ud->position ?? 'Unknown';
}

$performanceByPosition = [];
foreach ($filteredEnrollments as $record) {
    $pos = $userPosition[$record->userid] ?? 'Unknown';

    if (!isset($performanceByPosition[$pos])) {
        $performanceByPosition[$pos] = [
            'name' => $pos,
            'total' => 0,
            'completed' => 0,
            'failed' => 0,
            'inProgress' => 0
        ];
    }

    $performanceByPosition[$pos]['total']++;

    if ($record->status === 'completed') {
        $performanceByPosition[$pos]['completed']++;
    } elseif ($record->status === 'failed') {
        $performanceByPosition[$pos]['failed']++;
    } else {
        $performanceByPosition[$pos]['inProgress']++;
    }
}
$positionAnalytics = array_values($performanceByPosition);

// Courses Analytics - using data already loaded
$performanceByCourse = [];
$uniqueCourses = [];

foreach ($filteredEnrollments as $record) {
    $cid = $record->courseid;
    $uniqueCourses[$cid] = true;

    if (!isset($performanceByCourse[$cid])) {
        $performanceByCourse[$cid] = [
            'courseId' => $cid,
            'courseName' => 'Course ' . $cid,
            'enrolled' => 0,
            'completed' => 0,
            'failed' => 0,
            'inProgress' => 0
        ];
    }

    $performanceByCourse[$cid]['enrolled']++;

    if ($record->status === 'completed') {
        $performanceByCourse[$cid]['completed']++;
    } elseif ($record->status === 'failed') {
        $performanceByCourse[$cid]['failed']++;
    } else {
        $performanceByCourse[$cid]['inProgress']++;
    }
}
$coursesAnalytics = array_values($performanceByCourse);

// Categories Analytics - based on servicepointcategory from user details
$userServicePointCategory = [];
foreach ($userDetails as $ud) {
    $userServicePointCategory[$ud->userid] = $ud->servicepointcategory ?? 'Unknown';
}

$performanceByCategory = [];
foreach ($filteredEnrollments as $record) {
    $category = $userServicePointCategory[$record->userid] ?? 'Unknown';

    if (!isset($performanceByCategory[$category])) {
        $performanceByCategory[$category] = [
            'name' => $category,
            'enrollments' => 0,
            'completions' => 0,
            'failures' => 0
        ];
    }

    $performanceByCategory[$category]['enrollments']++;

    if ($record->status === 'completed') {
        $performanceByCategory[$category]['completions']++;
    } elseif ($record->status === 'failed') {
        $performanceByCategory[$category]['failures']++;
    }
}

$categoriesAnalytics = array_values($performanceByCategory);

// Age Distribution with status breakdown for learning analytics
$ageDistributionLA = [];
foreach ($ageGroups as $group => $_) {
    $ageDistributionLA[$group] = [
        'count' => 0,
        'completeCount' => 0,
        'inProgressCount' => 0,
        'failedCount' => 0
    ];
}

foreach ($filteredEnrollments as $record) {
    if (isset($userAge[$record->userid])) {
        $age = $userAge[$record->userid];
        foreach ($ageGroups as $group => [$min, $max]) {
            if (($age >= $min && $age <= $max) || ($group === '45+' && $age >= $min)) {
                $ageDistributionLA[$group]['count']++;

                if ($record->status === 'completed') {
                    $ageDistributionLA[$group]['completeCount']++;
                } elseif ($record->status === 'failed') {
                    $ageDistributionLA[$group]['failedCount']++;
                } else {
                    $ageDistributionLA[$group]['inProgressCount']++;
                }
                break;
            }
        }
    }
}

$ageDistributionLA_formatted = [];
foreach ($ageGroups as $group => $_) {
    $ageDistributionLA_formatted[] = [
        'ageGroup' => $group,
        'count' => $ageDistributionLA[$group]['count'],
        'completeCount' => $ageDistributionLA[$group]['completeCount'],
        'inProgressCount' => $ageDistributionLA[$group]['inProgressCount'],
        'failedCount' => $ageDistributionLA[$group]['failedCount']
    ];
}

$learningAnalytics = [
    'performanceLocationTrend' => $performanceLocationTrend,
    'performanceTrend' => $performanceTrend,
    'positionAnalytics' => $positionAnalytics,
    'coursesAnalytics' => $coursesAnalytics,
    'categoriesAnalytics' => $categoriesAnalytics,
    'metrics' => [
        'totalCourses' => count($uniqueCourses),
        'avgScore' => 0,
        'completionRate' => $filteredTotal > 0 ? round(($filteredCompleted / $filteredTotal) * 100, 2) : 0,
        'totalEnrollments' => $filteredTotal,
        'completedCourses' => $filteredCompleted,
        'failedCourses' => $filteredFailed,
        'inProgressCourses' => $filteredInProgress
    ],
    'sexCompletionMetrics' => [
        'males' => $maleCompleted,
        'females' => $femaleCompleted
    ],
    'sexFaillingMetrics' => [
        'males' => $maleFailed,
        'females' => $femaleFailed
    ],
    'ageDistribution' => $ageDistributionLA_formatted
];

// ========================================
// STUDENTS DATAd
// ========================================
$studentsData = [];

// Return final JSON2
echo json_encode([
    'totalAnalytics' => $totalAnalytics,
    'enrollmentAnalytics' => $enrollmentAnalytics,
    'learningAnalytics' => $learningAnalytics,
    'studentsData' => $studentsData
]);
