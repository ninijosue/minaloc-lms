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

$page = new admin_settingpage('theme_space_general', get_string('generalsettings', 'theme_space'));

$name = 'theme_space/hintro';
$heading = get_string('hintro', 'theme_space', $a);
$setting = new space_setting_specialsettingheading($name, $heading,
    format_text(get_string('hintro_desc', 'theme_space', $a), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/accessiblelinks';
$title = get_string('accessiblelinks', 'theme_space');
$description = get_string('accessiblelinks_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/accessiblebtnfocus';
$title = get_string('accessiblebtnfocus', 'theme_space');
$description = get_string('accessiblebtnfocus_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/darkmodetheme';
$title = get_string('darkmodetheme', 'theme_space');
$description = get_string('darkmodetheme_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/darkmodefirst';
$title = get_string('darkmodefirst', 'theme_space');
$description = get_string('darkmodefirst_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/sdarkmode';
$title = get_string('sdarkmode', 'theme_space');
$description = get_string('sdarkmode_desc', 'theme_space');
$default = 'Dark Mode';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/slightmode';
$title = get_string('slightmode', 'theme_space');
$description = get_string('slightmode_desc', 'theme_space');
$default = 'Light Mode';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/themeauthor';
$title = get_string('themeauthor', 'theme_space');
$description = get_string('themeauthor_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/backtotop';
$title = get_string('backtotop', 'theme_space');
$description = get_string('backtotop_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

// Video Size.
$name = 'theme_space/forcefwvideo';
$title = get_string('forcefwvideo', 'theme_space');
$description = get_string('forcefwvideo_desc', 'theme_space', $a);
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Show hint for switched role.
$name = 'theme_space/showswitchedroleincourse';
$title = get_string('showswitchedroleincoursesetting', 'theme_space');
$description = get_string('showswitchedroleincoursesetting_desc', 'theme_space', $a);
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Show hint in hidden courses.
$name = 'theme_space/showhintcoursehidden';
$title = get_string('showhintcoursehiddensetting', 'theme_space');
$description = get_string('showhintcoursehiddensetting_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

// Show hint guest for access.
$name = 'theme_space/showhintcourseguestaccess';
$title = get_string('showhintcoursguestaccesssetting', 'theme_space');
$description = get_string('showhintcourseguestaccesssetting_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

// Show hint for self enrolment without enrolment key.
$name = 'theme_space/showhintcourseselfenrol';
$title = get_string('showhintcourseselfenrolsetting', 'theme_space');
$description = get_string('showhintcourseselfenrolsetting_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

// Unaddable blocks.
// Blocks to be excluded when this theme is enabled in the "Add a block" list: Administration, Navigation, Courses and
// Section links.
$default = 'navigation,settings,course_list,section_links';
$setting = new admin_setting_configtext(
    'theme_space/unaddableblocks',
    get_string('unaddableblocks', 'theme_space'),
    get_string('unaddableblocks_desc', 'theme_space'),
    $default,
    PARAM_TEXT
);
$page->add($setting);

// Google analytics block.
$name = 'theme_space/googleanalytics';
$title = get_string('googleanalytics', 'theme_space');
$description = get_string('googleanalyticsdesc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$settings->add($page);
