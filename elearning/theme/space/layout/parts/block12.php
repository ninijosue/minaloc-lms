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
global $PAGE, $OUTPUT;

// Variables Theme Settings.
$block12introtitle = format_text(theme_space_get_setting('block12introtitle'), FORMAT_HTML, ['noclean' => true]);
$block12introcontent = format_text(theme_space_get_setting('block12introcontent'), FORMAT_HTML, ['noclean' => true]);
$block12html = format_text(theme_space_get_setting('block12htmlcontent'), FORMAT_HTML, ['noclean' => true]);
$block12footer = format_text(theme_space_get_setting('block12footercontent'), FORMAT_HTML, ['noclean' => true]);
$block12class = theme_space_get_setting('block12class');

echo '<!-- Start Block 12-->';
echo '<div id="fbblock12" class="wrapper-xl rui-fp-block--12 ' . $block12class . '">';
if (!empty($block12introtitle) || !empty($block12introcontent)) {
    echo '<div class="wrapper-md">';
}
if (!empty($block12introtitle)) {
    echo '<h3 class="rui-block-title">' . $block12introtitle . '</h3>';
}
if (!empty($block12introcontent)) {
    echo '<div class="rui-block-desc">' . $block12introcontent . '</div>';
}
if (!empty($block12introtitle) || !empty($block12introcontent)) {
    echo '</div>';
}
echo $block12html;
if (!empty($block12footer)) {
    echo '<div class="rui-block-footer wrapper-fw">' . $block12footer . '</div>';
}
echo '</div>';
if (theme_space_get_setting("displayhrblock12") == '1') {
    echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 12 -->';
