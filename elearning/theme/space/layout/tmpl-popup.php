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
 */

defined('MOODLE_INTERNAL') || die();
$extraclasses = [];

// Dark mode.
if (theme_space_get_setting('darkmodetheme') == '1') {
    $darkmodeon = get_user_preferences('darkmode-on');
    if ($darkmodeon) {
        $extraclasses[] = 'theme-dark';
    }
    $darkmodetheme = true;
} else {
    $darkmodeon = false;
}

if (theme_space_get_setting('darkmodefirst') == '1') {
    $extraclasses[] = 'theme-dark';
    $darkmodetheme = false;
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$siteurl = $CFG->wwwroot;
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'darkmodeon' => !empty($darkmodeon),
    'darkmodetheme' => !empty($darkmodetheme),
    'siteurl' => $siteurl,
];

if (empty($PAGE->layout_options['noactivityheader'])) {
    $header = $PAGE->activityheader;
    $renderer = $PAGE->get_renderer('core');
    $templatecontext['headercontent'] = $header->export_for_template($renderer);
}

// Load theme settings.
$PAGE->requires->js_call_amd('theme_space/rui', 'init');
$themesettings = new \theme_space\util\theme_settings();
$templatecontext = array_merge($templatecontext, $themesettings->global_settings());
$templatecontext = array_merge($templatecontext, $themesettings->footer_settings());

echo $OUTPUT->render_from_template('theme_space/tmpl-popup', $templatecontext);
