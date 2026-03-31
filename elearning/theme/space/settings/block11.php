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

$page = new admin_settingpage('theme_space_block11', get_string('settingsblock11', 'theme_space'));

$name = 'theme_space/displayblock11';
$title = get_string('turnon', 'theme_space');
$description = get_string('displayblock11_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title .
    '<span class="badge badge-sq badge-dark ml-2">Block #11</span>', $description, $default);
$page->add($setting);

$name = 'theme_space/displayhrblock11';
$title = get_string('displayblockhr', 'theme_space');
$description = get_string('displayblockhr_desc', 'theme_space');
$default = 1;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block11class';
$title = get_string('additionalclass', 'theme_space');
$description = get_string('additionalclass_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block11introtitle';
$title = get_string('blockintrotitle', 'theme_space');
$description = get_string('blockintrotitle_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block11introcontent';
$title = get_string('blockintrocontent', 'theme_space');
$description = get_string('blockintrocontent_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block11htmlcontent';
$title = get_string('blockhtmlcontent', 'theme_space');
$description = get_string('blockhtmlcontent_desc', 'theme_space');
$default = '<div class="wrapper-xxl row no-gutters mt-5">
<!-- Col #1 -->
<div class="col-md pr-lg-3 mr-lg-3 align-self-center">
<div class="mb-4 rui-img--rounded-fluid">
<img src="https://assets.rosea.io/themes/img.jpg" alt="pic" width="800" height="533">
</div>
<div class="rui-card--blank">
<h4 class="lead-4 mt-3">Get real-time insights on your performance.</h4>
<p class="rui-card-text">Auto-generated reports: Get accurate insights on your...</p>
<ul class="rui-special-list rui-special-list--primary">
<li>Personal asset watchlists</li>
<li>Curated market data feed</li>
<li>20+ on-chain, social &amp; dev metrics</li>
<li>Low-latency market signals and alerts</li>
<li class="list-icon-x">Sansheets plugin with pre-made templates</li>
</ul>
<a href="https://rosea.io" class="rui-card-btn-link mt-2">Learn more about the Smart Keyboard</a>
</div>
</div>
<!-- End Col #1 -->
<!-- Col #2 -->
<div class="col-md pl-lg-3 ml-lg-3 align-self-center">
<div class="mb-4 rui-img--rounded-fluid">
<img src="https://assets.rosea.io/themes/img.jpg" alt="pic" width="800" height="533">
</div>
<div class="rui-card--blank">
<h4 class="lead-4 mt-3">Get real-time insights on your performance.</h4>
<p class="rui-card-text">Auto-generated reports: Get accurate insights on your...</p>
<ul class="rui-special-list rui-special-list--primary">
<li>Personal asset watchlists</li>
<li>Curated market data feed</li>
<li>20+ on-chain, social &amp; dev metrics</li>
<li>Low-latency market signals and alerts</li>
<li class="list-icon-x">Sansheets plugin with pre-made templates</li>
</ul>
<a href="https://rosea.io" class="rui-card-btn-link mt-2">Learn more about the Smart Keyboard</a>
</div>
</div>
<!-- End Col #2 -->
</div>
<!-- End item -->';
$setting = new space_setting_confightmleditor($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/block11footercontent';
$title = get_string('blockfootercontent', 'theme_space');
$description = get_string('blockfootercontent_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

$settings->add($page);
