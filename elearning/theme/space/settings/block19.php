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

$page = new admin_settingpage('theme_space_block19', get_string('settingsblock19', 'theme_space'));

$name = 'theme_space/displayblock19';
$title = get_string('turnon', 'theme_space');
$description = get_string('displayblock19_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title .
    '<span class="badge badge-sq badge-dark ml-2">Block #19</span>', $description, $default);
$page->add($setting);

$name = 'theme_space/block19class';
$title = get_string('additionalclass', 'theme_space');
$description = get_string('additionalclass_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block19textalign';
$title = get_string('block19textalign', 'theme_space');
$description = get_string('block19textalign_desc', 'theme_space');
$options = [];
$options['left'] = get_string('left', 'theme_space');
$options['center'] = get_string('center', 'theme_space');
$options['right'] = get_string('right', 'theme_space');
$setting = new admin_setting_configselect($name, $title, $description, 'center', $options);
$page->add($setting);

$name = 'theme_space/block19titlesize';
$title = get_string('blocktitlesize', 'theme_space');
$description = get_string('blocktitlesize_desc', 'theme_space');
$default = 1;
$choices = [0 => 'Normal', 1 => 'Large', 2 => 'Extra Large'];
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);

$name = 'theme_space/block19titlecolor';
$title = get_string('blocktitlecolor', 'theme_space');
$description = get_string('blocktitlecolor_desc', 'theme_space');
$default = 1;
$choices = [0 => 'White', 1 => 'Black', 2 => 'Gradient'];
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);

$name = 'theme_space/block19titleweight';
$title = get_string('blocktitleweight', 'theme_space');
$description = get_string('blocktitleweight_desc', 'theme_space');
$default = 1;
$choices = [0 => 'Normal', 1 => 'Medium', 2 => 'Bold'];
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);

$name = 'theme_space/displayhrblock19';
$title = get_string('displayblockhr', 'theme_space');
$description = get_string('displayblockhr_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block19introtitle';
$title = get_string('blockintrotitle', 'theme_space');
$description = get_string('blockintrotitle_desc', 'theme_space');
$default = 'One-time charge<br />Get this theme today!';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block19introcontent';
$title = get_string('blockintrocontent', 'theme_space');
$description = get_string('blockintrocontent_desc', 'theme_space');
$default = 'Our exclusive lifetime update theme & user-centered design<br />
will keep your moodle site running strong for many years to come!';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);


$name = 'theme_space/block19htmlcontent';
$title = get_string('blockhtmlcontent', 'theme_space');
$description = get_string('blockhtmlcontent_desc', 'theme_space');
$default = '<div class="mt-4">
<a href="https://rosea.io/space" class="btn btn-lg btn-dark">Get this theme for $99*</a></div>
<div class="mt-2"><a href="https://space.rosea.io/doc" class="btn btn-sm btn-secondary">Documentation</a></div>';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);


$name = 'theme_space/block19footercontent';
$title = get_string('blockfootercontent', 'theme_space');
$description = get_string('blockfootercontent_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$fileid = 'block19bg';
$name = 'theme_space/block19bg';
$title = get_string('block19bg', 'theme_space');
$description = get_string('block19bg_desc', 'theme_space');
$opts = ['accepted_types' => ['.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'], 'maxfiles' => 1];
$setting = new admin_setting_configstoredfile($name, $title, $description, $fileid, 0, $opts);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/block19customcss';
$title = get_string('block19customcss', 'theme_space');
$description = get_string('block19customcss_desc', 'theme_space');
$default = 'background-position: top; background-size: cover;';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$settings->add($page);
