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
$block11introtitle = format_text(theme_space_get_setting('block11introtitle'), FORMAT_HTML, ['noclean' => true]);
$block11introcontent = format_text(theme_space_get_setting('block11introcontent'), FORMAT_HTML, ['noclean' => true]);
$block11html = format_text(theme_space_get_setting('block11htmlcontent'), FORMAT_HTML, ['noclean' => true]);
$block11footer = format_text(theme_space_get_setting('block11footercontent'), FORMAT_HTML, ['noclean' => true]);
$block11class = theme_space_get_setting('block11class');

echo '<!-- Start Block 11 -->';
echo '<div id="fbblock11" class="wrapper-xl rui-fp-block--11 ' . $block11class . '">';

if (!empty($block11introtitle) || !empty($block11introcontent)) {
    echo '<div class="wrapper-md">';
}
if (!empty($block11introtitle)) {
    echo '<h3 class="rui-block-title">' . $block11introtitle . '</h3>';
}
if (!empty($block11introcontent)) {
    echo '<div class="rui-block-desc">' . $block11introcontent . '</div>';
}
if (!empty($block11introtitle) || !empty($block11introcontent)) {
    echo '</div>';
}
echo $block11html;

if (!empty($block11footer)) {
    echo '<div class="rui-block-footer wrapper-fw">' . $block11footer . '</div>';
}

echo '</div>';
if (theme_space_get_setting("displayhrblock11") == '1') {
    echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 11 -->';
