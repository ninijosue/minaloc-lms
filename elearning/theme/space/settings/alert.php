<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   theme_space
 * @copyright 2022 onwards, Marcin Czaja (https://rosea.io)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */


defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('theme_space_customalert', get_string('alertsettings', 'theme_space'));

    // Custom alert.
    $name = 'theme_space/displaycustomalert';
    $title = get_string('displaycustomalert', 'theme_space');
    $description = get_string('displaycustomalert_desc', 'theme_space');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $page->add($setting);

    $name = 'theme_space/closecustomalert';
    $title = get_string('closecustomalert', 'theme_space');
    $description = get_string('closecustomalert_desc', 'theme_space');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default);
    $page->add($setting);

    $name = 'theme_space/customalertid';
    $title = get_string('customalertid', 'theme_space');
    $description = get_string('customalertid_desc', 'theme_space');
    $default = '1';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $page->add($setting);

    $name = 'theme_space/customalerthtml';
    $title = get_string('customalerthtml', 'theme_space');
    $description = get_string('customalerthtml_desc', 'theme_space');
    $default = '';
    $setting = new space_setting_confightmleditor($name, $title, $description, $default);
    $page->add($setting);

$settings->add($page);
