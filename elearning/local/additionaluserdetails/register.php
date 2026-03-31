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

require_once(__DIR__ . '/../../config.php');

$PAGE->set_url(new moodle_url('/local/additionaluserdetails/register.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('standard');

// $PAGE->set_title("Registration");

// $PAGE->requires->js('/local/additionaluserdetails/js/index.js', true);
// echo $OUTPUT->header();
// echo "<h1>Additional User Details Registration</h1>";
// echo $OUTPUT->footer();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <!-- Manually include your JS file -->
    <script defer type="module" src="<?php echo $CFG->wwwroot; ?>/local/additionaluserdetails/frontend/dist/index_002.js"></script>
    <link rel="shortcut icon" href="/pluginfile.php/1/core_admin/favicon/64x64/1754397409/LG_LOGO_eng-removebg-preview.png">
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/local/additionaluserdetails/frontend/dist/styles.css">
     <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f0f0f0;
        margin: 0;
        padding: 20px;
    }

    h1 {
        color: #333;
        text-align: center;
    }

</style>

</head>
<body>
    <!-- Your HTML/PHP page content -->
     <main-page root="<?php echo $CFG->wwwroot;?>"></main-page>
</body>
</html>