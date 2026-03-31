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
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_additionaluserdetails
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// function local_additionaluserdetails_before_standard_html_head(){
//     \core\notification::add(
//         "This is a custom message from the Additional User Details plugin.",
//         \core\output\notification::NOTIFY_INFO
//     );
// }


function local_additionaluserdetails_get_config(){
    $pluginenvs = require_once(__DIR__ . '/env.php');

    $production = $pluginenvs['production'] ?? false;
    $username = $pluginenvs['dev_username'] ?? '';
    $password = $pluginenvs['dev_password'] ?? '';
    $keyphrase = $pluginenvs['dev_keyphrase'] ?? '';
    $loginurl = $pluginenvs['dev_loginurl'] ?? '';
    $citizenurl = $pluginenvs['dev_citizenurl'] ?? '';

    if($production) {
        // Use production environment settings if in production mode.
        $username = $pluginenvs['prod_username'] ?? $username;
        $password = $pluginenvs['prod_password'] ?? $password;
        $keyphrase = $pluginenvs['prod_keyphrase'] ?? $keyphrase;
        $loginurl = $pluginenvs['prod_loginurl'] ?? $loginurl;
        $citizenurl = $pluginenvs['prod_citizenurl'] ?? $citizenurl;
    }
    return [
        'username' => $username,
        'password' => $password,
        'keyphrase' => $keyphrase,
        'loginurl' => $loginurl,
        'citizenurl' => $citizenurl,
    ];
}


// the the redirection when the user is not logged in or has not filled the form
// This function checks if the user is logged in and has filled the additional user details form.
// If not, it redirects them to the registration page.
// It is called before the HTTP headers are sent to ensure the user is redirected appropriately.
// This function is typically called in the config.php or a similar initialization file.
// It checks if the user is logged in and not a guest user.
// If the user does not have a record in the 'additional_user_details' table, they
// are redirected to the registration page.
// This is useful for ensuring that all users complete their profile information before accessing other parts of the application.
//. and is not an admin user
function local_additionaluserdetails_before_http_headers(){
    global $USER, $DB, $CFG;
  if (isloggedin() && !isguestuser() && !is_siteadmin()) {
        $exists = $DB->record_exists('additional_user_details', ['userid' => $USER->id]);
        $checks = !$exists && $_SERVER['SCRIPT_NAME'] != '/local/additionaluserdetails/register.php' 
        && $_SERVER['SCRIPT_NAME'] != '/local/additionaluserdetails/getcitizeninfo.php' 
        && $_SERVER['SCRIPT_NAME'] != '/local/additionaluserdetails/saveuserinfo.php'
        && $_SERVER['SCRIPT_NAME'] != '/login/logout.php';
        if ($checks) {
            redirect($CFG->wwwroot . '/local/additionaluserdetails/register.php');
            exit;
        }
    }
}

