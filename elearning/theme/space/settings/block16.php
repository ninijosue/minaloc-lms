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

$page = new admin_settingpage('theme_space_block16', get_string('settingsblock16', 'theme_space'));

$name = 'theme_space/displayblock16';
$title = get_string('turnon', 'theme_space');
$description = get_string('displayblock16_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title .
    '<span class="badge badge-sq badge-dark ml-2">Block #16</span>', $description, $default);
$page->add($setting);

$name = 'theme_space/displayhrblock16';
$title = get_string('displayblockhr', 'theme_space');
$description = get_string('displayblockhr_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block16class';
$title = get_string('additionalclass', 'theme_space');
$description = get_string('additionalclass_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block16introtitle';
$title = get_string('blockintrotitle', 'theme_space');
$description = get_string('blockintrotitle_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block16introcontent';
$title = get_string('blockintrocontent', 'theme_space');
$description = get_string('blockintrocontent_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block16htmlcontent';
$title = get_string('blockhtmlcontent', 'theme_space');
$description = get_string('blockhtmlcontent_desc', 'theme_space');
$default = '<div class="wrapper-md text-center">
<!-- start .badge -->
<div class="badge badge-info">Space 2 is here! Only for Moodle 4.x</div>
<h2 class="display-2 mt-4">Hi! Cześć! ¡Hola!<br>Space 2.2 is here!</h2>
<p class="rui-block-text--1 mt-3">Completely redesigned user interface.</p>
<div class="d-flex flex-wrap mt-4 justify-content-center w-100">
<a href="#" class="m-2 btn btn-lg btn-dark" target="_blank">Get this theme for $99*</a>
<a href="#" class="m-2 btn btn-lg btn-secondary" target="_blank">Documentation</a>
</div>
<p class="rui-block-text--light rui-block-text--3 mt-3">Trusted by hundreds of customers</p>
<div class="row justify-content-center align-items-center">
<div class="col-auto col-sm py-3 text-center">
<img src="https://assets.rosea.io/universe/demo/logo180x90.png" alt="Logo" width="106" height="60" class="img-fluid">
</div>
<!-- End Col -->
<div class="col-auto col-sm py-3 text-center">
<img src="https://assets.rosea.io/universe/demo/logo180x90.png" alt="Logo" width="206" height="80" class="img-fluid">
</div>
<!-- End Col -->
<div class="col-auto col-sm py-3 text-center">
<img src="https://assets.rosea.io/universe/demo/logo180x90.png" alt="Logo" width="69" height="80" class=">
</div>
<!-- End Col -->
<div class="col-auto col-sm py-3 text-center">
<img src="https://assets.rosea.io/universe/demo/logo180x90.png" alt="Logo" width="386" height="80" class="img-fluid">
</div>
<!-- End Col -->
<div class="col-auto col-sm py-3 text-center">
<img src="https://assets.rosea.io/universe/demo/logo180x90.png" alt="Logo" width="96" height="80" class="img-fluid">
</div>
<!-- End Col -->
</div>
<!-- End Row -->
</div><!-- end .wrapper-md -->';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block16footercontent';
$title = get_string('blockfootercontent', 'theme_space');
$description = get_string('blockfootercontent_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$fileid = 'block16bg';
$name = 'theme_space/block16bg';
$title = get_string('blockbg', 'theme_space');
$description = get_string('blockbg_desc', 'theme_space');
$opts = ['accepted_types' => ['.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'], 'maxfiles' => 1];
$setting = new admin_setting_configstoredfile($name, $title, $description, $fileid, 0, $opts);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

$name = 'theme_space/block16customcss';
$title = get_string('blockcustomcss', 'theme_space');
$description = get_string('blockcustomcss_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$settings->add($page);
