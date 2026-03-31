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

$page = new admin_settingpage('theme_space_customization', get_string('settingscustomization', 'theme_space'));
$name = 'theme_space/hgooglefont';
$heading = get_string('hgooglefont', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('hgooglefont_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

// Google Font.
$name = 'theme_space/googlefonturl';
$title = get_string('googlefonturl', 'theme_space');
$description = get_string('googlefonturl_desc', 'theme_space');
$default = 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/fontheadings';
$title = get_string('fontheadings', 'theme_space');
$description = get_string('fontheadings_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/fontweightheadings';
$title = get_string('fontweightheadings', 'theme_space');
$description = get_string('fontweightheadings_desc', 'theme_space');
$default = '700';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/fontbody';
$title = get_string('fontbody', 'theme_space');
$description = get_string('fontbody_desc', 'theme_space');
$default = "'Poppins', sans-serif";
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/fontweightregular';
$title = get_string('fontweightregular', 'theme_space');
$description = get_string('fontweightregular_desc', 'theme_space');
$default = '400';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/fontweightmedium';
$title = get_string('fontweightmedium', 'theme_space');
$description = get_string('fontweightmedium_desc', 'theme_space');
$default = '500';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/fontweightbold';
$title = get_string('fontweightbold', 'theme_space');
$description = get_string('fontweightbold_desc', 'theme_space');
$default = '700';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/hgeneral';
$heading = get_string('hgeneral', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('hgeneral_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/colorbodybg';
$title = get_string('colorbodybg', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorborder';
$title = get_string('colorborder', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/btnborderradius';
$title = get_string('btnborderradius', 'theme_space');
$description = get_string('btnborderradius_desc', 'theme_space');
$setting = new admin_setting_configtext($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/hcolorstxt';
$heading = get_string('hcolorstxt', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('hcolorstxt_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/colorbody';
$title = get_string('colorbody', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorbodysecondary';
$title = get_string('colorbodysecondary', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorbodylight';
$title = get_string('colorbodylight', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorheadings';
$title = get_string('colorheadings', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorlink';
$title = get_string('colorlink', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorlinkhover';
$title = get_string('colorlinkhover', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/hcolorsprimary';
$heading = get_string('hcolorsprimary', 'theme_space');
$setting = new admin_setting_heading($name, $heading,
    format_text(get_string('hcolorsprimary_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/colorprimary600';
$title = get_string('colorprimary600', 'theme_space');
$description = get_string('colorprimary_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorprimary100';
$title = get_string('colorprimary100', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorprimary200';
$title = get_string('colorprimary200', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorprimary300';
$title = get_string('colorprimary300', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorprimary400';
$title = get_string('colorprimary400', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorprimary500';
$title = get_string('colorprimary500', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorprimary700';
$title = get_string('colorprimary700', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorprimary800';
$title = get_string('colorprimary800', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorprimary900';
$title = get_string('colorprimary900', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/hcolorsgrays';
$heading = get_string('hcolorsgrays', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('hcolorsgrays_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/colorgray100';
$title = get_string('colorgray100', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorgray200';
$title = get_string('colorgray200', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorgray300';
$title = get_string('colorgray300', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorgray400';
$title = get_string('colorgray400', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorgray500';
$title = get_string('colorgray500', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorgray600';
$title = get_string('colorgray600', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorgray700';
$title = get_string('colorgray700', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorgray800';
$title = get_string('colorgray800', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/colorgray900';
$title = get_string('colorgray900', 'theme_space');
$description = get_string('color_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/hdmgeneral';
$heading = get_string('hdmgeneral', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('hgeneral_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/dmcolorbodybg';
$title = get_string('dmcolorbodybg', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorborder';
$title = get_string('dmcolorborder', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/hdmcolorstxt';
$heading = get_string('hdmcolorstxt', 'theme_space');
$setting = new admin_setting_heading($name, $heading, format_text(get_string('hdmcolorstxt_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/dmcolorbody';
$title = get_string('dmcolorbody', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorbodysecondary';
$title = get_string('dmcolorbodysecondary', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorbodylight';
$title = get_string('dmcolorbodylight', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorheadings';
$title = get_string('dmcolorheadings', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorlink';
$title = get_string('dmcolorlink', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorlinkhover';
$title = get_string('dmcolorlinkhover', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/hdmcolorsgrays';
$heading = get_string('hdmcolorsgrays', 'theme_space');
$setting = new admin_setting_heading($name, $heading,
    format_text(get_string('hdmcolorsgrays_desc', 'theme_space'), FORMAT_MARKDOWN));
$page->add($setting);

$name = 'theme_space/dmcolorgray100';
$title = get_string('dmcolorgray100', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorgray200';
$title = get_string('dmcolorgray200', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorgray300';
$title = get_string('dmcolorgray300', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorgray400';
$title = get_string('dmcolorgray400', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorgray500';
$title = get_string('dmcolorgray500', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorgray600';
$title = get_string('dmcolorgray600', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorgray700';
$title = get_string('dmcolorgray700', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorgray800';
$title = get_string('dmcolorgray800', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/dmcolorgray900';
$title = get_string('dmcolorgray900', 'theme_space');
$description = get_string('dmcolor_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$settings->add($page);
