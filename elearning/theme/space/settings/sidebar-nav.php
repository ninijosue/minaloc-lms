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
$page = new admin_settingpage('theme_space_settingssidebar-nav', get_string('settingssidebar-nav', 'theme_space'));

$name = 'theme_space/isitemonsitehome';
$title = get_string('isitemonsitehome', 'theme_space');
$description = get_string('isitemonsitehome_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/possitehome';
$title = get_string('possitehome', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 1;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/isitemondashboard';
$title = get_string('isitemondashboard', 'theme_space');
$description = get_string('isitemondashboard_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/posdashboard';
$title = get_string('posdashboard', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 2;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/isitemoncalendar';
$title = get_string('isitemoncalendar', 'theme_space');
$description = get_string('isitemoncalendar_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/poscalendar';
$title = get_string('poscalendar', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 3;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/isitemonprivatefiles';
$title = get_string('isitemonprivatefiles', 'theme_space');
$description = get_string('isitemonprivatefiles_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/posprivatefiles';
$title = get_string('posprivatefiles', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 4;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/isitemoncontentbank';
$title = get_string('isitemoncontentbank', 'theme_space');
$description = get_string('isitemoncontentbank_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/poscontentbank';
$title = get_string('poscontentbank', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 5;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/isitemonmycourses';
$title = get_string('isitemonmycourses', 'theme_space');
$description = get_string('isitemonmycourses_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/posmycourses';
$title = get_string('posmycourses', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 6;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/customnavitems';
$title = get_string('customnavitems', 'theme_space');
$description = get_string('customnavitems_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

// Custom Item #1.
$name = 'theme_space/hcustomitem1';
$heading = get_string('hcustomitem1', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('empty_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/iscustomitem1on';
$title = get_string('iscustomitem1on', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/poscustomitem1';
$title = get_string('poscustomitem1', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 7;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/labelcustomitem1';
$title = get_string('labelcustomitem1', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/iconcustomitem1';
$title = get_string('iconcustomitem1', 'theme_space');
$description = get_string('iconcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/urlcustomitem1';
$title = get_string('urlcustomitem1', 'theme_space');
$description = get_string('urlcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/customitemrole1';
$title = get_string('secnavuserrole', 'theme_space');
$description = get_string('secnavuserrole_desc', 'theme_space');
$options = [];
$options[0] = get_string('none', 'theme_space');
$options[1] = get_string('secnavuserrole1', 'theme_space');
$setting = new admin_setting_configselect($name, '<span class="rui-admin-no">1</span>' .
    $title, $description, 0, $options);
$page->add($setting);

// Custom Item #2.
$name = 'theme_space/hcustomitem2';
$heading = get_string('hcustomitem2', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('empty_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/iscustomitem2on';
$title = get_string('iscustomitem2on', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/poscustomitem2';
$title = get_string('poscustomitem2', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 8;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/labelcustomitem2';
$title = get_string('labelcustomitem2', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/iconcustomitem2';
$title = get_string('iconcustomitem2', 'theme_space');
$description = get_string('iconcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/urlcustomitem2';
$title = get_string('urlcustomitem2', 'theme_space');
$description = get_string('urlcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/customitemrole2';
$title = get_string('secnavuserrole', 'theme_space');
$description = get_string('secnavuserrole_desc', 'theme_space');
$options = [];
$options[0] = get_string('none', 'theme_space');
$options[1] = get_string('secnavuserrole1', 'theme_space');
$setting = new admin_setting_configselect($name, '<span class="rui-admin-no">2</span>' .
    $title, $description, 0, $options);
$page->add($setting);

// Custom Item #3.
$name = 'theme_space/hcustomitem3';
$heading = get_string('hcustomitem3', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('empty_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/iscustomitem3on';
$title = get_string('iscustomitem3on', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/poscustomitem3';
$title = get_string('poscustomitem3', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 9;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/labelcustomitem3';
$title = get_string('labelcustomitem3', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/iconcustomitem3';
$title = get_string('iconcustomitem3', 'theme_space');
$description = get_string('iconcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/urlcustomitem3';
$title = get_string('urlcustomitem3', 'theme_space');
$description = get_string('iconcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/customitemrole3';
$title = get_string('secnavuserrole', 'theme_space');
$description = get_string('secnavuserrole_desc', 'theme_space');
$options = [];
$options[0] = get_string('none', 'theme_space');
$options[1] = get_string('secnavuserrole1', 'theme_space');
$setting = new admin_setting_configselect($name, '<span class="rui-admin-no">3</span>' .
    $title, $description, 0, $options);
$page->add($setting);

// Custom Item #4.
$name = 'theme_space/hcustomitem4';
$heading = get_string('hcustomitem4', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('empty_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/iscustomitem4on';
$title = get_string('iscustomitem4on', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/poscustomitem4';
$title = get_string('poscustomitem4', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 10;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/labelcustomitem4';
$title = get_string('labelcustomitem4', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/iconcustomitem4';
$title = get_string('iconcustomitem4', 'theme_space');
$description = get_string('iconcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/urlcustomitem4';
$title = get_string('urlcustomitem4', 'theme_space');
$description = get_string('urlcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/customitemrole4';
$title = get_string('secnavuserrole', 'theme_space');
$description = get_string('secnavuserrole_desc', 'theme_space');
$options = [];
$options[0] = get_string('none', 'theme_space');
$options[1] = get_string('secnavuserrole1', 'theme_space');
$setting = new admin_setting_configselect($name, '<span class="rui-admin-no">4</span>' .
    $title, $description, 0, $options);
$page->add($setting);

// Custom Item #5.
$name = 'theme_space/hcustomitem5';
$heading = get_string('hcustomitem5', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('empty_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/iscustomitem5on';
$title = get_string('iscustomitem5on', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/poscustomitem5';
$title = get_string('poscustomitem5', 'theme_space');
$description = get_string('navposition_desc', 'theme_space');
$default = 11;
$options = [];
for ($i = 1; $i <= 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$name = 'theme_space/labelcustomitem5';
$title = get_string('labelcustomitem5', 'theme_space');
$description = get_string('empty_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/iconcustomitem5';
$title = get_string('iconcustomitem5', 'theme_space');
$description = get_string('iconcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/urlcustomitem5';
$title = get_string('urlcustomitem5', 'theme_space');
$description = get_string('urlcustomitem_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/customitemrole5';
$title = get_string('secnavuserrole', 'theme_space');
$description = get_string('secnavuserrole_desc', 'theme_space');
$options = [];
$options[0] = get_string('none', 'theme_space');
$options[1] = get_string('secnavuserrole1', 'theme_space');
$setting = new admin_setting_configselect($name, '<span class="rui-admin-no">5</span>' .
    $title, $description, 0, $options);
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
