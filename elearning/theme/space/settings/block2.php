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
 * @copyright 2021 Marcin Czaja (https://rosea.io)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage(
    'theme_space_block2',
    get_string('settingsblock2', 'theme_space')
);

$name = 'theme_space/displayblock2';
$title = get_string('turnon', 'theme_space');
$description = get_string('displayblock2_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title .
    '<span class="badge badge-sq badge-dark ml-2">Block #2 (Hero Video)</span>', $description, $default);
$page->add($setting);

$name = 'theme_space/block2fw';
$title = get_string('blockfw', 'theme_space');
$description = get_string('blockfw_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block2class';
$title = get_string('additionalclass', 'theme_space');
$description = get_string('additionalclass_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block2wrapperalign';
$title = get_string('block2wrapperalign', 'theme_space');
$description = get_string('block2wrapperalign_desc', 'theme_space');
$default = 1;
$choices = [0 => 'Left', 1 => 'Middle', 2 => 'Right'];
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);

$name = 'theme_space/showblock2wrapper';
$title = get_string('showblock2wrapper', 'theme_space');
$description = get_string('showblock2wrapper_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block2wrapperbg';
$title = get_string('block2wrapperbg', 'theme_space');
$description = get_string('block2wrapperbg_desc', 'theme_space');
$setting = new admin_setting_configcolourpicker($name, $title, $description, '');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/block2introcontent';
$title = get_string('blockintrocontent', 'theme_space');
$description = get_string('blockintrocontent_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block2herotitle';
$title = get_string('block2herotitle', 'theme_space');
$description = get_string('block2herotitle_desc', 'theme_space');
$setting = new admin_setting_configtextarea($name, $title, $description, '', PARAM_TEXT);
$page->add($setting);

$name = 'theme_space/block2herosubheading';
$title = get_string('block2herosubheading', 'theme_space');
$description = get_string('block2herosubheading_desc', 'theme_space');
$setting = new admin_setting_configtextarea($name, $title, $description, '', PARAM_TEXT);
$page->add($setting);

$name = 'theme_space/block2herotitlesize';
$title = get_string('blocktitlesize', 'theme_space');
$description = get_string('blocktitlesize_desc', 'theme_space');
$default = 1;
$choices = [0 => 'Normal', 1 => 'Large', 2 => 'Extra Large'];
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);

$name = 'theme_space/block2herotitlecolor';
$title = get_string('blocktitlecolor', 'theme_space');
$description = get_string('blocktitlecolor_desc', 'theme_space');
$default = 1;
$choices = [0 => 'White', 1 => 'Black', 2 => 'Gradient'];
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);

$name = 'theme_space/block2herotitleweight';
$title = get_string('blocktitleweight', 'theme_space');
$description = get_string('blocktitleweight_desc', 'theme_space');
$default = 1;
$choices = [0 => 'Normal', 1 => 'Medium', 2 => 'Bold'];
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$page->add($setting);

$name = 'theme_space/block2herocaption';
$title = get_string('block2herocaption', 'theme_space');
$description = get_string('block2herocaption_desc', 'theme_space');
$default = '<div class="rui-hero-desc">
<p>The Space 2 is dedicated only for Moodle 4.0 and later. For Moodle 3.9 - 3.11 there is Space 1.14</p>
<p class="mt-3 small">Need help with theme customization?<br>Or you want to report a bug?</p>
</div>
<div class="rui-hero-btns mt-3">
<a href="https://1.envato.market/OR707N" target="_blank" class="btn btn-lg btn-light my-1">Get this theme!</a>
</div>';
$setting = new admin_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block2htmlcontent';
$title = get_string('blockhtmlcontent', 'theme_space');
$description = get_string('blockhtmlcontent_desc', 'theme_space');
$default = '';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block2footercontent';
$title = get_string('blockfootercontent', 'theme_space');
$description = get_string('blockfootercontent_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block2videoposter';
$title = get_string('block2videoposter', 'theme_space');
$description = get_string('block2videoposter_desc', 'theme_space');
$opts = ['accepted_types' => ['.jpg, .png, .gif'], 'maxfiles' => 1];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'block2videoposter', 0, $opts);
$page->add($setting);

$name = 'theme_space/block2videomp4';
$title = get_string('block2videomp4', 'theme_space');
$description = get_string('block2videomp4_desc', 'theme_space');
$opts = ['accepted_types' => ['.mp4'], 'maxfiles' => 1];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'block2videomp4', 0, $opts);
$page->add($setting);

$name = 'theme_space/block2videowebm';
$title = get_string('block2videowebm', 'theme_space');
$description = get_string('block2videowebm_desc', 'theme_space');
$opts = ['accepted_types' => ['.webm'], 'maxfiles' => 1];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'block2videowebm', 0, $opts);
$page->add($setting);

$settings->add($page);
