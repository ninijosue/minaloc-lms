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

// Login settings.
$page = new admin_settingpage('theme_space_settingslogin', get_string('settingslogin', 'theme_space'));

$name = 'theme_space/hlogin';
$heading = get_string('hlogin', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('hlogin_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/setloginlayout';
$title = get_string('setloginlayout', 'theme_space');
$description = get_string('setloginlayout_desc', 'theme_space');
$options = [];
$options[1] = get_string('loginlayout1', 'theme_space');
$options[2] = get_string('loginlayout2', 'theme_space');
$options[3] = get_string('loginlayout3', 'theme_space');
$options[4] = get_string('loginlayout4', 'theme_space');
$options[5] = get_string('loginlayout5', 'theme_space');
$setting = new admin_setting_configselect($name, $title, $description, 1, $options);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/loginidprovtop';
$title = get_string('loginidprovtop', 'theme_space');
$description = get_string('loginidprovtop_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/customloginlogo';
$title = get_string('customloginlogo', 'theme_space');
$description = get_string('customloginlogo_desc', 'theme_space');
$opts = ['accepted_types' => ['.png', '.jpg', '.gif', '.svg']];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'customloginlogo', 0, $opts);
$page->add($setting);

$name = 'theme_space/customlogindmlogo';
$title = get_string('customlogindmlogo', 'theme_space');
$description = get_string('customlogindmlogo_desc', 'theme_space');
$opts = ['accepted_types' => ['.png', '.jpg', '.gif', '.svg']];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'customlogindmlogo', 0, $opts);
$page->add($setting);

$name = 'theme_space/loginlogooutside';
$title = get_string('loginlogooutside', 'theme_space');
$description = get_string('loginlogooutside_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/customsignupoutside';
$title = get_string('customsignupoutside', 'theme_space');
$description = get_string('customsignupoutside_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/loginbg';
$title = get_string('loginbg', 'theme_space');
$description = get_string('loginbg_desc', 'theme_space');
$opts = ['accepted_types' => ['.png', '.jpg', '.gif', '.webp', '.tiff', '.svg']];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'loginbg', 0, $opts);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/loginbgcolor';
$title = get_string('loginbgcolor', 'theme_space');
$description = get_string('loginbgcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/hideforgotpassword';
$title = get_string('hideforgotpassword', 'theme_space');
$description = get_string('hideforgotpassword_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/logininfobox';
$title = get_string('logininfobox', 'theme_space');
$description = get_string('logininfobox_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/loginintrotext';
$title = get_string('loginintrotext', 'theme_space');
$description = get_string('loginintrotext_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/loginhtmlcontent1';
$title = get_string('loginhtmlcontent1', 'theme_space');
$description = get_string('loginhtmlcontent1_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/loginhtmlcontent2';
$title = get_string('loginhtmlcontent2', 'theme_space');
$description = get_string('loginhtmlcontent2_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/loginhtmlblockbottom';
$title = get_string('loginhtmlblockbottom', 'theme_space');
$description = get_string('loginhtmlblockbottom_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/loginhtmlcontent3';
$title = get_string('loginhtmlcontent3', 'theme_space');
$description = get_string('loginhtmlcontent3_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/loginfootercontent';
$title = get_string('loginfootercontent', 'theme_space');
$description = get_string('loginfootercontent_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/logincustomfooterhtml';
$title = get_string('logincustomfooterhtml', 'theme_space');
$description = get_string('logincustomfooterhtml_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/stringca';
$title = get_string('stringca', 'theme_space');
$description = get_string('stringca_desc', 'theme_space');
$default = 'Don\'t have an account?';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/stringbacktologin';
$title = get_string('stringbacktologin', 'theme_space');
$description = get_string('stringbacktologin_desc', 'theme_space');
$default = 'Already have an account?';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/hsignup';
$heading = get_string('hsignup', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('hsignup_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/signupintrotext';
$title = get_string('signupintrotext', 'theme_space');
$description = get_string('signupintrotext_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/signuptext';
$title = get_string('signuptext', 'theme_space');
$description = get_string('signuptext_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$settings->add($page);
