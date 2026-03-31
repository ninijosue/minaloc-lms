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
 * A maintenance layout for the space theme.
 *
 * @package   theme_space
 * @copyright 2022 onwards, Marcin Czaja (https://rosea.io)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$siteurl = $CFG->wwwroot;
$templatecontext = [
    // We cannot pass the context to format_string, this layout can be used during
    // installation. At that stage database tables do not exist yet.
    'sitename' => format_string($SITE->shortname, true, ["escape" => false]),
    'output' => $OUTPUT,
    'siteurl' => $siteurl,
];

// Load theme settings.
$themesettings = new \theme_space\util\theme_settings();
$templatecontext = array_merge($templatecontext, $themesettings->global_settings());
$templatecontext = array_merge($templatecontext, $themesettings->footer_settings());

$PAGE->requires->js_call_amd('theme_space/rui', 'init');

echo $OUTPUT->render_from_template('theme_space/tmpl-maintenance', $templatecontext);
