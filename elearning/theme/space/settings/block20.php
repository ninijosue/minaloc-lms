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

$page = new admin_settingpage('theme_space_block20', get_string('settingsblock20', 'theme_space'));

$name = 'theme_space/displayblock20';
$title = get_string('turnon', 'theme_space');
$description = get_string('displayblock20_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title .
    '<span class="badge badge-sq badge-dark ml-2">Block #20</span>', $description, $default);
$page->add($setting);

$name = 'theme_space/block20class';
$title = get_string('additionalclass', 'theme_space');
$description = get_string('additionalclass_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/herologo';
$title = get_string('herologo', 'theme_space');
$description = get_string('herologo_desc', 'theme_space');
$opts = ['accepted_types' => ['.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'], 'maxfiles' => 1];
$setting = new admin_setting_configstoredfile($name, $title, $description, 'herologo', 0, $opts);
$page->add($setting);

$name = 'theme_space/herologotxt';
$title = get_string('herologotxt', 'theme_space');
$description = get_string('herologotxt_desc', 'theme_space');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/imgslidesonly';
$title = get_string('imgslidesonly', 'theme_space');
$description = get_string('imgslidesonly_desc', 'theme_space');
$setting = new admin_setting_configcheckbox($name, $title, $description, 0);
$page->add($setting);

$name = 'theme_space/sliderloop';
$title = get_string('sliderloop', 'theme_space');
$description = get_string('sliderloop_desc', 'theme_space');
$setting = new admin_setting_configcheckbox($name, $title, $description, 1);
$page->add($setting);

$name = 'theme_space/sliderintervalenabled';
$title = get_string('sliderintervalenabled', 'theme_space');
$description = get_string('sliderintervalenabled_desc', 'theme_space');
$setting = new admin_setting_configcheckbox($name, $title, $description, 0);
$page->add($setting);

$name = 'theme_space/sliderinterval';
$title = get_string('sliderinterval', 'theme_space');
$description = get_string('sliderinterval_desc', 'theme_space');
$default = '6000';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);
$settings->hide_if(
    'theme_space/sliderinterval',
    'theme_space/sliderintervalenabled',
    'notchecked'
);

$name = 'theme_space/sliderclickable';
$title = get_string('sliderclickable', 'theme_space');
$description = get_string('sliderclickable_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/rtlslider';
$title = get_string('rtlslider', 'theme_space');
$description = get_string('rtlslider_desc', 'theme_space');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_space/slidercount';
$title = get_string('slidercount', 'theme_space');
$description = get_string('slidercount_desc', 'theme_space');
$default = 1;
$options = [];
for ($i = 1; $i < 11; $i++) {
    $options[$i] = $i;
}
$setting = new admin_setting_configselect($name, $title, $description, $default, $options);
$page->add($setting);

$slidercount = get_config('theme_space', 'slidercount');

if (!$slidercount) {
    $slidercount = 1;
}

for ($sliderindex = 1; $sliderindex <= $slidercount; $sliderindex++) {
    $name = 'theme_space/hblock1slide' . $sliderindex;
    $heading = get_string('hblock1slide', 'theme_space');
    $setting = new admin_setting_heading($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $heading, format_text(get_string('hblock1slide_desc', 'theme_space'), FORMAT_MARKDOWN));
    $page->add($setting);

    $fileid = 'sliderimage' . $sliderindex;
    $name = 'theme_space/sliderimage' . $sliderindex;
    $title = get_string('sliderimage', 'theme_space');
    $description = get_string('sliderimage_desc', 'theme_space');
    $opts = ['accepted_types' => ['.png', '.jpg', '.gif', '.webp', '.tiff', '.svg'], 'maxfiles' => 1];
    $setting = new admin_setting_configstoredfile($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $title, $description, $fileid, 0, $opts);
    $page->add($setting);

    $name = 'theme_space/sliderurl' . $sliderindex;
    $title = get_string('sliderurl', 'theme_space');
    $description = get_string('sliderurl_desc', 'theme_space');
    $default = '';
    $setting = new admin_setting_configtext($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $title, $description, $default);
    $page->add($setting);

    $name = 'theme_space/slidertitle' . $sliderindex;
    $title = get_string('slidertitle', 'theme_space');
    $description = get_string('slidertitle_desc', 'theme_space');
    $default = '';
    $setting = new admin_setting_configtextarea($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $title, $description, $default);
    $page->add($setting);

    $name = 'theme_space/slidersubtitle' . $sliderindex;
    $title = get_string('slidersubtitle', 'theme_space');
    $description = get_string('slidersubtitle_desc', 'theme_space');
    $default = '';
    $setting = new admin_setting_configtextarea($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $title, $description, $default);
    $page->add($setting);

    $name = 'theme_space/slidercap' . $sliderindex;
    $title = get_string('slidercaption', 'theme_space');
    $description = get_string('slidercaption_desc', 'theme_space');
    $default = '';
    $setting = new admin_setting_configtextarea($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $title, $description, $default);
    $page->add($setting);

    $name = 'theme_space/sliderhtml' . $sliderindex;
    $title = get_string('sliderhtml', 'theme_space');
    $description = get_string('sliderhtml_desc', 'theme_space');
    $setting = new admin_setting_configtextarea($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $title, $description, '', PARAM_TEXT);
    $page->add($setting);

    $name = 'theme_space/sliderheight' . $sliderindex;
    $title = get_string('sliderheight', 'theme_space');
    $description = get_string('sliderheight_desc', 'theme_space');
    $default = '';
    $setting = new admin_setting_configtext($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $title, $description, $default);
    $page->add($setting);

    $name = 'theme_space/slidebackdrop' . $sliderindex;
    $title = get_string('slidebackdrop', 'theme_space');
    $description = get_string('slidebackdrop_desc', 'theme_space');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, '<span class="rui-admin-no">' .
        $sliderindex .
        '</span>' .
        $title, $description, $default);
    $page->add($setting);
}

$settings->add($page);
