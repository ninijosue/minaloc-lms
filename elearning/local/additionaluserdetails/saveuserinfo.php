<?php
/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/user/lib.php');

global $DB, $USER, $SESSION;

$firstname = required_param('firstname', PARAM_TEXT);
$lastname = required_param('lastname', PARAM_TEXT);
$names = required_param('names', PARAM_TEXT);
$nationalid = required_param('nationalid', PARAM_TEXT);
$dateofbirth = required_param('dateofbirth', PARAM_TEXT);
$gender = required_param('gender', PARAM_TEXT);
$servicepointcategory = required_param('servicepointcategory', PARAM_TEXT);
$district = optional_param('district','', PARAM_TEXT);
$sector = optional_param('sector','', PARAM_TEXT);
$cell = optional_param('cell','', PARAM_TEXT);
$healthfacility = optional_param('healthfacility','', PARAM_TEXT);
$servicecountry = optional_param('servicecountry','', PARAM_TEXT);
$otherspecified = optional_param('otherspecified','', PARAM_TEXT);
$phonenumber = optional_param('phonenumber','', PARAM_TEXT);
$email = optional_param('email','', PARAM_TEXT);
$position = optional_param('position','', PARAM_TEXT);
$village = optional_param('village','', PARAM_TEXT);

$record = new stdClass();
$record->userid = $USER->id;
$record->names = $names;
$record->nationalid = $nationalid;
$record->dateofbirth = $dateofbirth;
$record->gender = $gender;
$record->servicepointcategory = $servicepointcategory;
$record->district = $district;
$record->sector = $sector;
$record->cell = $cell;
$record->healthfacility = $healthfacility;
$record->servicecountry = $servicecountry;
$record->otherspecified = $otherspecified;
$record->phonenumber = $phonenumber;
$record->email = $email;
$record->position = $position;
$record->village = $village;

// Check if the record already exists for this user
$existing = $DB->get_record('additional_user_details', ['userid' => $USER->id]);
$existing_nationalid = $DB->get_record('additional_user_details', ['nationalid' => $nationalid]);

// check if record exists for this user or with the same national id
if ($existing_nationalid || $existing) {
    // throw error if record exists
    echo json_encode(['status' => 'error', 'message' => 'A user with the same information already exists. Please verify your details or contact support.']);
    exit;
    
} else {
    // Insert new record
    $DB->insert_record('additional_user_details', $record);
    $response_message = 'Record created successfully.';
}

$user = $DB->get_record('user', ['id' => $USER->id]);
$user->firstname = $firstname;
$user->lastname = $lastname;
user_update_user($user, false, true);
$USER = $user;
unset($SESSION->profileforced);

// Return a success response
echo json_encode(['status' => 'success', 'message' => $response_message]);
exit;