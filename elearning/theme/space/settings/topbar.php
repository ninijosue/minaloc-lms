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


$page = new admin_settingpage('theme_space_settingstopbar', get_string('settingstopbar', 'theme_space'));

$name = 'theme_space/turnoffdashboardlink';
$title = get_string('turnoffdashboardlink', 'theme_space');
$description = get_string('turnoffdashboardlink_desc', 'theme_space', $a);
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/topbarheight';
$title = get_string('topbarheight', 'theme_space');
$description = get_string('topbarheight_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/topbareditmode';
$title = get_string('topbareditmode', 'theme_space');
$description = get_string('topbareditmode_desc', 'theme_space', $a);
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/topbarlogoareaon';
$title = get_string('topbarlogoareaon', 'theme_space');
$description = get_string('topbarlogoareaon_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/customlogo';
$title = get_string('customlogo', 'theme_space');
$description = get_string('customlogo_desc', 'theme_space');
$opts = ['accepted_types' => ['.png', '.jpg', '.svg', 'gif']];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'customlogo', 0, $opts);
$page->add($setting);

$name = 'theme_space/customdmlogo';
$title = get_string('customdmlogo', 'theme_space');
$description = get_string('customdmlogo_desc', 'theme_space');
$opts = ['accepted_types' => ['.png', '.jpg', '.svg', 'gif']];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'customdmlogo', 0, $opts);
$page->add($setting);

$name = 'theme_space/customlogoandname';
$title = get_string('customlogoandname', 'theme_space');
$description = get_string('customlogoandname_desc', 'theme_space', $a);
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/customlogotxt';
$title = get_string('customlogotxt', 'theme_space');
$description = get_string('customlogotxt_desc', 'theme_space');
$default = 'Space';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/topbarcustomhtml';
$title = get_string('topbarcustomhtml', 'theme_space');
$description = get_string('topbarcustomhtml_desc', 'theme_space', $a);
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

// Colors.
$name = 'theme_space/htopbarcolors';
$heading = get_string('htopbarcolors', 'theme_space');
$setting = new admin_setting_heading($name, $heading,
    format_text(get_string('htopbarcolors_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/colortopbarbg';
$title = get_string('colortopbarbg', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colortopbartext';
$title = get_string('colortopbartext', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colortopbarlink';
$title = get_string('colortopbarlink', 'theme_space');
$description = get_string('colortopbarlink_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colortopbarlinkhover';
$title = get_string('colortopbarlinkhover', 'theme_space');
$description = get_string('colortopbarlink_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colortopbarbtn';
$title = get_string('colortopbarbtn', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colortopbarbtntext';
$title = get_string('colortopbarbtntext', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colortopbarbtnhover';
$title = get_string('colortopbarbtnhover', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colortopbarbtnhovertext';
$title = get_string('colortopbarbtnhovertext', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
