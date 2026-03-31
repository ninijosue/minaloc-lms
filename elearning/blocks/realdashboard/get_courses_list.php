<?php
/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * This file provides analytics for user enrollment and learning, supporting various filters.
 * Now supports filtering by course category.
 *
 * Reference image for database structure:
 * ![image1](image1)
 */

require_once(__DIR__ . '/../../config.php');

global $DB, $USER, $SESSION;

// Get category filter from request (GET or POST)
$categoryId = optional_param('category', 0, PARAM_INT);

// Build the SQL query based on category filter
if ($categoryId > 0) {
    // Get courses from specific category
    $sql = "SELECT c.id, c.fullname, c.category, cc.name as categoryname
            FROM {course} c
            JOIN {course_categories} cc ON c.category = cc.id
            WHERE c.id > 1 AND c.category = :categoryid
            ORDER BY c.fullname ASC";
    $params = ['categoryid' => $categoryId];
    $courses = $DB->get_records_sql($sql, $params);
} else {
    // Get all courses (excluding site course, id=1)
    $sql = "SELECT c.id, c.fullname, c.category, cc.name as categoryname
            FROM {course} c
            LEFT JOIN {course_categories} cc ON c.category = cc.id
            WHERE c.id > 1
            ORDER BY c.fullname ASC";
    $courses = $DB->get_records_sql($sql);
}

// Prepare array for output
$availableCourses = [];
foreach ($courses as $course) {
    $availableCourses[] = [
        'id' => $course->id,
        'name' => $course->fullname,
        'category' => [
            'id' => $course->category,
            'name' => $course->categoryname ?? 'No Category'
        ]
    ];
}

echo json_encode([
    'availableCourses' => $availableCourses,
    'filteredByCategory' => $categoryId > 0,
    'categoryId' => $categoryId
]);
exit;