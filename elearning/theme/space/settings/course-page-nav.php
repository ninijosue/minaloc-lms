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

$page = new admin_settingpage('theme_space_settingscoursesnav', get_string('settingscoursesnav', 'theme_space'));

$name = 'theme_space/secnavgroupitem';
$title = get_string('secnavgroupitem', 'theme_space');
$description = get_string('secnavgroupitem_desc', 'theme_space', $a);
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/secnavitems';
$title = get_string('secnavitems', 'theme_space');
$description = get_string('secnavitems_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

if (get_config('theme_space', 'secnavitems')) {
    $name = 'theme_space/secnavitemscount';
    $title = get_string('secnavitemscount', 'theme_space');
    $description = get_string('secnavitemscount_desc', 'theme_space');
    $default = 0;
    $options = [];
    for ($i = 1; $i <= 10; $i++) {
        $options[$i] = $i;
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);
    $navitems = get_config('theme_space', 'secnavitemscount');
    if (!$navitems) {
        $navitems = 1;
    }
    for ($secnavindex = 1; $secnavindex <= $navitems; $secnavindex++) {
        $name = 'theme_space/hsecnavitem' . $secnavindex;
        $heading = get_string('hsecnavitem', 'theme_space');
        $setting = new admin_setting_heading($name, '<span class="rui-admin-no">' .
            $secnavindex .
            '</span>' .
            $heading, format_text(get_string('hsecnavitem_desc', 'theme_space'), FORMAT_MARKDOWN));
        $page->add($setting);

        $name = 'theme_space/secnavcustomnavlabel' . $secnavindex;
        $title = get_string('secnavcustomnavlabel', 'theme_space');
        $description = get_string('secnavcustomnavlabel_desc', 'theme_space');
        $setting = new admin_setting_configtext($name, '<span class="rui-admin-no">' .
            $secnavindex .
            '</span>' .
            $title, $description, '', PARAM_TEXT);
        $page->add($setting);

        $name = 'theme_space/secnavcustomnavurl' . $secnavindex;
        $title = get_string('secnavcustomnavurl', 'theme_space');
        $description = get_string('secnavcustomnavurl_desc', 'theme_space');
        $setting = new admin_setting_configtext($name, '<span class="rui-admin-no">' .
            $secnavindex .
            '</span>' .
            $title, $description, '', PARAM_TEXT);
        $page->add($setting);

        $name = 'theme_space/secnavuserroles' . $secnavindex;
        $title = get_string('secnavuserroles', 'theme_space');
        $description = get_string('secnavuserroles_desc', 'theme_space');
        $options = [];
        $options[0] = get_string('all', 'moodle');
        $options[1] = get_string('rolemanager', 'theme_space');
        $options[2] = get_string('rolecoursecreator', 'theme_space');
        $options[3] = get_string('roleeditingteacher', 'theme_space');
        $options[4] = get_string('rolenoneditingteacher', 'theme_space');
        $options[5] = get_string('rolestudent', 'theme_space');
        $options[6] = get_string('roleguest', 'theme_space');
        $options[7] = get_string('roleauthenticateduser', 'theme_space');
        $setting = new admin_setting_configselect($name, '<span class="rui-admin-no">' .
        $secnavindex .
        '</span>' .
        $title, $description, '0', $options);
        $page->add($setting);

    }
}

// Must add the page after definiting all the settings!
$settings->add($page);
