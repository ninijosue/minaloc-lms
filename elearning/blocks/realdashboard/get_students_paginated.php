<?php
require_once('../../config.php');
require_login();

header('Content-Type: application/json');

// Pagination params
$page = optional_param('page', 1, PARAM_INT);
$limit = optional_param('limit', 50, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);

// Get all filter params (same as get_data.php)
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

// Validate pagination params
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 50;

// Parse courses
$courseIds = [];
if (!empty($selectedCourses)) {
    $courseIds = array_filter(array_map('intval', explode(',', $selectedCourses)));
}

// Load all user details from additional_user_details table
$userDetails = $DB->get_records('additional_user_details');

// Apply all filters to userDetails
$filteredUsers = [];
foreach ($userDetails as $ud) {
    // Date of birth filter
    if ($dateofbirth !== '') {
        $birthTimestamp = !empty($ud->dateofbirth) ?
            (strlen($ud->dateofbirth) > 10 ? intval($ud->dateofbirth / 1000) : intval($ud->dateofbirth)) : 0;
        $filterTimestamp = strtotime($dateofbirth);
        if ($birthTimestamp !== $filterTimestamp) continue;
    }

    // Gender filter
    if ($gender !== '') {
        $userGender = strtolower(trim($ud->gender ?? ''));
        $filterGender = strtolower(trim($gender));
        if ($userGender !== $filterGender) continue;
    }

    // Service point category filter
    if ($servicepointcategory !== '') {
        if (strcasecmp($ud->servicepointcategory ?? '', $servicepointcategory) !== 0) continue;
    }

    // District filter
    if ($district !== '') {
        if (strcasecmp($ud->district ?? '', $district) !== 0) continue;
    }

    // Sector filter
    if ($sector !== '') {
        if (strcasecmp($ud->sector ?? '', $sector) !== 0) continue;
    }

    // Cell filter
    if ($cell !== '') {
        if (strcasecmp($ud->cell ?? '', $cell) !== 0) continue;
    }

    // Village filter
    if ($village !== '') {
        if (strcasecmp($ud->village ?? '', $village) !== 0) continue;
    }

    // Health facility filter
    if ($healthfacility !== '') {
        if (strcasecmp($ud->healthfacility ?? '', $healthfacility) !== 0) continue;
    }

    // Service country filter
    if ($servicecountry !== '') {
        if (strcasecmp($ud->servicecountry ?? '', $servicecountry) !== 0) continue;
    }

    // Position filter
    if ($position !== '') {
        if (strcasecmp($ud->position ?? '', $position) !== 0) continue;
    }

    // Phone number filter
    if ($phonenumber !== '') {
        if (stripos($ud->phonenumber ?? '', $phonenumber) === false) continue;
    }

    // Email filter
    if ($email !== '') {
        if (stripos($ud->email ?? '', $email) === false) continue;
    }

    // Other specified filter
    if ($otherspecified !== '') {
        if (strcasecmp($ud->otherspecified ?? '', $otherspecified) !== 0) continue;
    }

    $filteredUsers[] = $ud;
}

// Apply search filter across multiple fields
if ($search !== '') {
    $searchLower = strtolower($search);
    $searchFiltered = [];
    foreach ($filteredUsers as $ud) {
        $matchFound = false;

        // Search in names
        if (stripos($ud->names ?? '', $search) !== false) $matchFound = true;

        // Search in national ID
        if (stripos($ud->nationalid ?? '', $search) !== false) $matchFound = true;

        // Search in email
        if (stripos($ud->email ?? '', $search) !== false) $matchFound = true;

        // Search in phone number
        if (stripos($ud->phonenumber ?? '', $search) !== false) $matchFound = true;

        // Search in district
        if (stripos($ud->district ?? '', $search) !== false) $matchFound = true;

        // Search in sector
        if (stripos($ud->sector ?? '', $search) !== false) $matchFound = true;

        // Search in cell
        if (stripos($ud->cell ?? '', $search) !== false) $matchFound = true;

        // Search in village
        if (stripos($ud->village ?? '', $search) !== false) $matchFound = true;

        if ($matchFound) {
            $searchFiltered[] = $ud;
        }
    }
    $filteredUsers = $searchFiltered;
}

// Calculate pagination
$total = count($filteredUsers);
$totalPages = $total > 0 ? ceil($total / $limit) : 1;
$offset = ($page - 1) * $limit;

// Ensure page is within bounds
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Get page slice
$pageUsers = array_slice($filteredUsers, $offset, $limit);

// Prepare course filters
$courseFilterSQL = '';
$courseParams = [];
if (!empty($courseIds)) {
    list($courseSql, $courseParams) = $DB->get_in_or_equal($courseIds, SQL_PARAMS_NAMED, 'course');
    $courseFilterSQL = " AND e.courseid $courseSql";
}

// Date filter for enrollments
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

// Get Moodle emails for page users
$moodleEmails = [];
if (!empty($pageUsers)) {
    $pageUserIds = array_map(function($ud) { return $ud->userid; }, $pageUsers);
    if (!empty($pageUserIds)) {
        list($in_sql, $in_params) = $DB->get_in_or_equal($pageUserIds, SQL_PARAMS_NAMED, 'stu_');
        $emailRecords = $DB->get_records_sql("SELECT id, email FROM {user} WHERE id $in_sql", $in_params);
        foreach ($emailRecords as $record) {
            $moodleEmails[$record->id] = $record->email;
        }
    }
}

// Build students data for current page
$studentsData = [];
foreach ($pageUsers as $ud) {
    $studentAllParams = array_merge($courseParams, $dateParams, ['userid' => $ud->userid]);

    // Enrollments count
    $enrollments = $DB->count_records_sql("
        SELECT COUNT(*) FROM {user_enrolments} ue
        JOIN {enrol} e ON ue.enrolid = e.id
        WHERE ue.userid = :userid
        $courseFilterSQL
        $dateFilter
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
            $dateFilter
        )
        AND (cc.timecompleted IS NOT NULL OR cert_issue.id IS NOT NULL)
        $courseFilterSQL
    ", array_merge($courseParams, $dateParams, ['userid1' => $ud->userid, 'userid2' => $ud->userid, 'userid3' => $ud->userid]));

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

// Return JSON response
echo json_encode([
    'students' => $studentsData,
    'pagination' => [
        'page' => (int)$page,
        'limit' => (int)$limit,
        'total' => (int)$total,
        'totalPages' => (int)$totalPages
    ]
]);
