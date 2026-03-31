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
$block9introtitle = format_text(theme_space_get_setting('block9introtitle'), FORMAT_HTML, ['noclean' => true]);
$block9introcontent = format_text(theme_space_get_setting('block9introcontent'), FORMAT_HTML, ['noclean' => true]);
$block9html = format_text(theme_space_get_setting('block9htmlcontent'), FORMAT_HTML, ['noclean' => true]);
$block9footer = format_text(theme_space_get_setting('block9footercontent'), FORMAT_HTML, ['noclean' => true]);
$block9class = theme_space_get_setting('block9class');

echo '<!-- Start Block 9 -->';
echo '<div id="fbblock9" class="wrapper-xl rui-fp-block--9 ' . $block9class . '">';
if (!empty($block9introtitle) || !empty($block9introcontent)) {
    echo '<div class="wrapper-md">';
}
if (!empty($block9introtitle)) {
    echo '<h3 class="rui-block-title">' . $block9introtitle . '</h3>';
}
if (!empty($block9introcontent)) {
    echo '<div class="rui-block-desc">' . $block9introcontent . '</div>';
}
if (!empty($block9introtitle) || !empty($block9introcontent)) {
    echo '</div>';
}
echo $block9html;
if (!empty($block9footer)) {
    echo '<div class="rui-block-footer wrapper-fw">' . $block9footer . '</div>';
}
echo '</div>';
if (theme_space_get_setting("displayhrblock9") == '1') {
    echo '<hr class="rui-block-hr" />';
}
echo '<!-- End Block 9 -->';
