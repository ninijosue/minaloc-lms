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
 * An embedded layout for the space theme.
 *
 * @package   theme_space
 * @copyright 2022 onwards, Marcin Czaja (https://rosea.io)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$fakeblockshtml = $OUTPUT->blocks('side-pre', [], 'aside', true);
$hasfakeblocks = strpos($fakeblockshtml, 'data-block="_fake"') !== false;
$renderer = $PAGE->get_renderer('core');

$siteurl = $CFG->wwwroot;
$templatecontext = [
    'output' => $OUTPUT,
    'headercontent' => $PAGE->activityheader->export_for_template($renderer),
    'hasfakeblocks' => $hasfakeblocks,
    'fakeblocks' => $fakeblockshtml,
    'siteurl' => $siteurl,
];

// Load theme settings.
$themesettings = new \theme_space\util\theme_settings();
$templatecontext = array_merge($templatecontext, $themesettings->global_settings());
$templatecontext = array_merge($templatecontext, $themesettings->footer_settings());
echo $OUTPUT->render_from_template('theme_space/tmpl-embedded', $templatecontext);
