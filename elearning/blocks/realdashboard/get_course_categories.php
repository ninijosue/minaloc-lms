<?php
/**
 * Get course categories for filtering purposes.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * This file provides course categories data for filtering courses.
 */

require_once(__DIR__ . '/../../config.php');

global $DB, $USER, $SESSION;

// Fetch all course categories
$categories = $DB->get_records('course_categories', null, 'name ASC', 'id, name');

// Prepare array for output
$availableCategories = [];
foreach ($categories as $category) {
    $availableCategories[] = [
        'id' => $category->id,
        'name' => $category->name,
    ];
}

echo json_encode([
    'availableCategories' => $availableCategories,
]);
exit;