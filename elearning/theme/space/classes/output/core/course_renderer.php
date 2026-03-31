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
 * Course renderer.
 *
 * @package   theme_space
 * @copyright 2022 onwards, Marcin Czaja (https://rosea.io)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_space\output\core;

use moodle_url;
use html_writer;
use core_course_category;
use coursecat_helper;
use stdClass;
use core_course_list_element;
use single_select;
use lang_string;
use cm_info;
use core_text;
use completion_info;
use course_handler;
use context_course;
use theme_space\util\course;

/**
 * @package    theme_space
 * @copyright  2022 onwards, Marcin Czaja - Rosea Themes - rosea.io
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The core course renderer
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('core','course');
 */
class course_renderer extends \core_course_renderer {

    /**
     * Renders html to display a course search form
     *
     * @param string $value default value to populate the search field
     * @return string
     */
    public function course_search_form($value = '') {

        $data = [
            'action' => \core_search\manager::get_course_search_url(),
            'btnclass' => 'btn-primary',
            'inputname' => 'q',
            'searchstring' => get_string('searchcourses'),
            'hiddenfields' => (object) ['name' => 'areaids', 'value' => 'core_course-course'],
            'query' => $value,
        ];

        return $this->render_from_template('core/search_input_fw', $data);
    }

    /**
     * Renders the list of courses
     *
     * This is internal function, please use core_course_renderer::courses_list() or another public
     * method from outside of the class
     *
     * If list of courses is specified in $courses; the argument $chelper is only used
     * to retrieve display options and attributes, only methods get_show_courses(),
     * get_courses_display_option() and get_and_erase_attributes() are called.
     *
     * @param coursecat_helper $chelper various display options
     * @param array $courses the list of courses to display
     * @param int|null $totalcount total number of courses (affects display mode if it is AUTO or pagination if applicable),
     *     defaulted to count($courses)
     * @return string
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null) {
        global $CFG;

        $theme = \theme_config::load('space');

        if ($totalcount === null) {
            $totalcount = count($courses);
        }

        if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        }

        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {
            // In 'auto' course display mode we analyse if number of courses is more or less than $CFG->courseswithsummarieslimit.
            if ($totalcount <= $CFG->courseswithsummarieslimit) {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
            } else {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
            }
        }

        // Prepare content of paging bar if it is needed.
        $paginationurl = $chelper->get_courses_display_option('paginationurl');
        $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
        if ($totalcount > count($courses)) {
            // There are more results that can fit on one page.
            if ($paginationurl) {
                // The option paginationurl was specified, display pagingbar.
                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, ['perpage' => $perpage])
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, ['perpage' => 'all']),
                        get_string('showall', '', $totalcount)
                    ), ['class' => 'paging paging-showall']);
                }
            } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                // The option for 'View more' link was specified, display more link.
                $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag(
                    'div',
                    html_writer::link($viewmoreurl, $viewmoretext, ['class' => 'btn btn-primary']),
                    ['class' => 'paging paging-morelink text-center mt-4']
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // There are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode.
            $pagingbar = html_writer::tag(
                'div',
                html_writer::link(
                    $paginationurl->out(
                        false,
                        ['perpage' => $CFG->coursesperpage]
                    ),
                    get_string('showperpage', '', $CFG->coursesperpage)
                ),
                ['class' => 'paging paging-showperpage']
            );
        }

        // Display list of courses.
        $attributes = $chelper->get_and_erase_attributes('courses');
        $content = html_writer::start_tag('div', $attributes);

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        $coursecount = 1;

        if ($theme->settings->courselistview == 1) {
            $content .= html_writer::start_tag('div', ['class' => 'rui-course--list mt-2']);
        } else {
            $content .= html_writer::start_tag('div', ['class' => 'rui-course-card-deck mt-2', 'role' => 'list']);
        }

        foreach ($courses as $course) {
            $content .= $this->coursecat_coursebox($chelper, $course);

            $coursecount++;
        }

        $content .= html_writer::end_tag('div');

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div'); // End courses.

        return $content;
    }

    /**
     * Renders the list of subcategories in a category
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat
     * @param int $depth depth of the category in the current tree
     * @return string
     */
    protected function coursecat_subcategories(coursecat_helper $chelper, $coursecat, $depth) {
        global $CFG;
        $subcategories = [];
        if (!$chelper->get_categories_display_option('nodisplay')) {
            $subcategories = $coursecat->get_children($chelper->get_categories_display_options());
        }
        $totalcount = $coursecat->get_children_count();
        if (!$totalcount) {
            // Note that we call core_course_category::get_children_count() AFTER core_course_category::get_children().
            // To avoid extra DB requests.
            // Categories count is cached during children categories retrieval.
            return '';
        }

        // Prepare content of paging bar or more link if it is needed.
        $paginationurl = $chelper->get_categories_display_option('paginationurl');
        $paginationallowall = $chelper->get_categories_display_option('paginationallowall');
        if ($totalcount > count($subcategories)) {
            if ($paginationurl) {
                // The option 'paginationurl was specified, display pagingbar.
                $perpage = $chelper->get_categories_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_categories_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar(
                    $totalcount,
                    $page,
                    $perpage,
                    $paginationurl->out(false, ['perpage' => $perpage])
                );
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link(
                        $paginationurl->out(false, ['perpage' => 'all']),
                        get_string('showall', '', $totalcount)
                    ), ['class' => 'paging paging-showall']);
                }
            } else if ($viewmoreurl = $chelper->get_categories_display_option('viewmoreurl')) {
                // The option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id).
                if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
                    $viewmoreurl->param('categoryid', $coursecat->id);
                }
                $viewmoretext = $chelper->get_categories_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag(
                    'div',
                    html_writer::link($viewmoreurl, $viewmoretext, ['class' => 'btn btn-secondary w-100']),
                    ['class' => 'paging paging-morelink wrapper-fw']
                );
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // There are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode.
            $pagingbar = html_writer::tag('div', html_writer::link(
                $paginationurl->out(false, ['perpage' => $CFG->coursesperpage]),
                get_string('showperpage', '', $CFG->coursesperpage)
            ), ['class' => 'paging paging-showperpage']);
        }

      

        // Display list of subcategories.
        $content = html_writer::start_tag('div', ['class' => 'subcategories']);

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        $content .= html_writer::start_tag('div', ['class' => 'cats_-grid']);
        foreach ($subcategories as $subcategory) {
            $content .= $this->coursecat_category($chelper, $subcategory, $depth);
        }
        $content .= html_writer::end_tag('div'); // End cats_-grid.

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div');
        return $content;
    }

    /**
     * Returns HTML to display course content (summary, course contacts and optionally category name)
     *
     * This method is called from coursecat_coursebox() and may be re-used in AJAX
     *
     * @param coursecat_helper $chelper various display options
     * @param stdClass|core_course_list_element $course
     *
     * @return string
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course) {
        global $DB, $COURSE;

        $theme = \theme_config::load('space');

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        $cccteachers = $theme->settings->cccteachers;
        $cccsummary = $theme->settings->cccsummary;
        $cccfooter = $theme->settings->cccfooter;
        $coursecarddesclimit = $theme->settings->coursecarddesclimit;

        if ($coursecarddesclimit == null) {
            $coursecarddesclimit = 100;
        }

        $courseprogressbar = $theme->settings->courseprogressbar;

        $courseutil = new course($course);

        $coursecontacts = $courseutil->get_course_contacts();

        $courseenrolmenticons = $courseutil->get_enrolment_icons();
        $courseenrolmenticons = !empty($courseenrolmenticons) ? $this->render_enrolment_icons($courseenrolmenticons) : false;
        $courseprogress = $courseutil->get_progress();
        $hasprogress = $courseprogress != null;

        if ($courseprogressbar == 0) {
            $hasprogress = false;
            $courseprogress = false;
            $cccfooter = true;
        }

        if ($courseprogressbar == 1 && $hasprogress == true) {
            $cccfooter = false;
        }

        $forcedlanguage = ($theme->settings->courselangbadge == 1) ? strval($course->lang) : null;
        $showcustomfields = ($theme->settings->showcustomfields == 1) ? true : false;

        $stringaccess = format_text(theme_space_get_setting('stringaccess'), FORMAT_HTML, ['noclean' => true]);

        $data = [
            'id' => $course->id,
            'fullname' => $chelper->get_course_formatted_name($course),
            'visible' => $course->visible,
            'image' => $courseutil->get_summary_image(),
            'summary' => $courseutil->get_summary($chelper),
            'category' => $courseutil->get_category(),
            'customfields' => $courseutil->get_custom_fields(),
            'showcustomfields' => $showcustomfields,
            'hasprogress' => $hasprogress,
            'progress' => (int) $courseprogress,
            'hasenrolmenticons' => $courseenrolmenticons != false,
            'enrolmenticons' => $courseenrolmenticons,
            'hascontacts' => !empty($coursecontacts),
            'contacts' => $coursecontacts,
            'stringaccess' => $stringaccess,
            'cccteachers' => $cccteachers,
            'cccfooter' => $cccfooter,
            'cccsummary' => $cccsummary,
            'coursecarddesclimit' => $coursecarddesclimit,
            'forcedlanguage' => $forcedlanguage,
        ];

        if ($theme->settings->courselistview == 1) {
            return $this->render_from_template('theme_space/custom_courselist', $data);
        } else {
            return $this->render_from_template('theme_space/custom_coursecard', $data);
        }
    }

    /**
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use core_course_renderer::course_info_box()
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_list_element|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }

        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        return $this->coursecat_coursebox_content($chelper, $course);
    }

    /**
     * Returns HTML to display a tree of subcategories and courses in the given category
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat top category (this category's name and description will NOT be added to the tree)
     * @return string
     */
    protected function coursecat_tree(coursecat_helper $chelper, $coursecat) {
        // Reset the category expanded flag for this course category tree first.
        $this->categoryexpandedonload = false;
        $categorycontent = $this->coursecat_category_content($chelper, $coursecat, 1);
        if (empty($categorycontent)) {
            return '';
        }

        // Start content generation.
        $content = '';
        $attributes = $chelper->get_and_erase_attributes('course_category_tree clearfix');
        $content .= html_writer::start_tag('div', $attributes);

        // if ($coursecat->get_children_count()) {
            
        //     $classes = [
        //         'collapseexpand',
        //         'aabtn',
        //     ];

        //     // Check if the category content contains subcategories with children's content loaded.
        //     if ($this->categoryexpandedonload) {
        //         $classes[] = 'collapse-all';
        //         $linkname = get_string('collapseall');
        //     } else {
        //         $linkname = get_string('expandall');
        //     }

        //     // Only show the collapse/expand if there are children to expand.
        //     $content .= html_writer::start_tag('div', ['class' => 'wrapper-fw collapsible-actions position-relative']);
        //     $content .= html_writer::link('#', $linkname, ['class' => implode(' ', $classes)]);
        //     $content .= html_writer::end_tag('div');
        //     $this->page->requires->strings_for_js(['collapseall', 'expandall'], 'moodle');
        // }

        $content .= html_writer::tag('div', $categorycontent, ['class' => 'content']);

        $content .= html_writer::end_tag('div'); // End course_category_tree.

        return $content;
    }

    /**
     * Returns HTML to display a course category as a part of a tree
     *
     * This is an internal function, to display a particular category and all its contents
     * use {@link core_course_renderer::course_category()}
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat
     * @param int $depth depth of this category in the current tree
     * @return string
     */
    protected function coursecat_category(coursecat_helper $chelper, $coursecat, $depth) {
        // Open category tag.
        
        $classes = [''];
        if (empty($coursecat->visible)) {
            $classes[] = 'dimmed_category';
        }
        if ($chelper->get_subcat_depth() > 0 && $depth >= $chelper->get_subcat_depth()) {
            // Do not load content.
            $categorycontent = '';
            $classes[] = 'notloaded';
            if (
                $coursecat->get_children_count() ||
                ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_COLLAPSED && $coursecat->get_courses_count())
            ) {
                $classes[] = 'with_children';
                $classes[] = 'collapsed';
            }
        } else {
            // Load category content.
            $categorycontent = $this->coursecat_category_content($chelper, $coursecat, $depth);
            $classes[] = 'loaded';
            if (!empty($categorycontent)) {
                $classes[] = 'with_children';
                // Category content loaded with children.
                $this->categoryexpandedonload = true;
            }
        }

        // Make sure JS file to expand category content is included.
        $this->coursecat_include_js();

        // $content = html_writer::start_tag('div', [
        //     'class' => join(' ', $classes),
        //     'data-categoryid' => $coursecat->id,
        //     'data-depth' => $depth,
        //     'data-showcourses' => $chelper->get_show_courses(),
        //     'data-type' => self::COURSECAT_TYPE_CATEGORY,
        // ]);
        
        // Category name.
        $categoryname = html_writer::link(
            new moodle_url(
                '/course/index.php',
                ['categoryid' => $coursecat->id]
            ),
            $coursecat->get_formatted_name(),
            ['class' => 'link text-decoration-none text-white  ']
        );

        $description = $chelper->get_category_formatted_description($coursecat);

        $icon = '<svg class="mr-1" width="14" height="14" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19.25 5.75C19.25 5.19772 18.8023 4.75 18.25 4.75H14C12.8954 4.75 12 5.64543
            12 6.75V19.25L12.8284 18.4216C13.5786 17.6714 14.596 17.25 15.6569 17.25H18.25C18.8023
            17.25 19.25 16.8023 19.25 16.25V5.75Z"></path>
        <path stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4.75 5.75C4.75 5.19772 5.19772 4.75 5.75 4.75H10C11.1046 4.75 12 5.64543
            12 6.75V19.25L11.1716 18.4216C10.4214 17.6714 9.40401 17.25 8.34315
            17.25H5.75C5.19772 17.25 4.75 16.8023 4.75 16.25V5.75Z"></path>
        </svg>';

        $a = $coursecat->get_courses_count();
        $b = $coursecat->get_children_count();
        $coursesum = $a + $b;

        $coursescount = $icon . $coursesum;

        // $content .= html_writer::start_tag('div', ['class' => 'wrapper-fw info']);
        // $content .= html_writer::start_tag('h5', ['class' => 'categoryname aabtn']);
        // $content .= $categoryname;
        $acontent = '';
        if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
            // The option for 'View more' link was specified, display more link.
            $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new \lang_string('viewmore'));
            // The option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id).
            if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
                $viewmoreurl->param('categoryid', $coursecat->id);
            }
            $acontent .= html_writer::tag('div', html_writer::link(
                $viewmoreurl,
                $viewmoretext,
                ['class' => 'btn btn-xs btn-info ml-2']
            ));
        }
        // $content .= '<div class="rui-number-of-courses d-inline-flex">' .
        //     html_writer::tag('span', $coursescount, ['class' => 'badge-sq badge-light']) . $acontent . '</div>';

        // $content .= html_writer::end_tag('h5');

        // $content .= html_writer::end_tag('div'); // End .info.

        // // Add category content to the output.
        // $content .= html_writer::tag('div', $categorycontent, ['class' => 'content']);

        // $content .= html_writer::end_tag('div'); // End .category.

        // <div class="category-card">
        //             <div class="category-image hiv-aids">
        //                 <div class="category-title">HIV, STI, Viral Hepatitis, Other viral diseases</div>
        //             </div>
        //         </div>
        $courses_page_url = new moodle_url('/course/index.php', ['categoryid' => $coursecat->id]);

        $content = html_writer::start_tag('div', ['class' => 'catone_-card', 'data-categoryid' => $coursecat->id,
            'data-depth' => $depth,
            'data-showcourses' => $chelper->get_show_courses(),
            'data-type' => self::COURSECAT_TYPE_CATEGORY,]);
        // $content .= html_writer::start_tag('div' , ['class' => 'catone_-image']);
        // $content .= $description;
        // $content .= html_writer::start_tag('div', ['class' => 'catone_-title']);
        // $content .= $categoryname;
        // $content .= html_writer::end_tag('div'); // End .catone_-title.
        // $content .= html_writer::end_tag('div'); // End .catone_-image.


        $content .= html_writer::link(
            $courses_page_url,
            '
            <div class="catone_-image ">
                ' . $description . '
                <div class="catone_-title">
                    ' . $categoryname . '
                </div>
            </div>
            ',
            ['escape' => false] // important: allows HTML in link text
        );

        $content .= html_writer::end_tag('div'); // End .catone_-card.

        // <div class="catone_-card">
        //         ' . $category_container . '
        //     </div>

        $content .= '
            
            <style>
                /* Course Categories */

                #frontpage-category-names, #frontpage-category-combo {
                    max-width: 1200px!important
                }

                .cats_-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 40px;
                    margin-top: 80px
                }

                .catone_-card {
                height: 300px;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s, box-shadow 0.3s;
                    cursor: pointer;
                }

                .catone_-card:hover {
                    transform: translateY(-10px);
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                }

                .catone_-image {
                    
                    position: relative;
                    display: block;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-weight: bold;
                    font-size: 1.1rem;
                    height: 100%;
                }
                .catone_-title {
                    background: #028ccf;
                    height: 80px;
                    position: absolute;
                    width: 100%;
                    bottom: 0;
                    display: flex;
                    align-items: center;
                    padding: 0 20px;
                }
            </style>
        ';

        // Return the course category tree HTML.

        return $content;
    }

    /**
     * Returns enrolment icons
     *
     * @param array $icons
     *
     * @return array
     */
    protected function render_enrolment_icons(array $icons): array {
        $data = [];

        foreach ($icons as $icon) {
            $data[] = $this->render($icon);
        }

        return $data;
    }

}
