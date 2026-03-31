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
 * @copyright Copyright © 2022 onwards, Marcin Czaja (https://rosea.io)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/quiz/renderer.php");

/**
 * Quiz.
 *
 */
class theme_space_mod_quiz_renderer extends mod_quiz\output\renderer {
    /**
     * Outputs a box.
     *
     * @param string $contents The contents of the box
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public function space_box($contents, $classes = 'generalbox', $id = null, $attributes = []) {
        return $this->space_box_start($classes, $id, $attributes) . $contents . $this->space_box_end();
    }

    /**
     * Outputs the opening section of a box.
     *
     * @param string $classes A space-separated list of CSS classes
     * @param string $id An optional ID
     * @param array $attributes An array of other attributes to give the box.
     * @return string the HTML to output.
     */
    public function space_box_start($classes = 'generalbox', $id = null, $attributes = []) {
        $this->opencontainers->push('box', html_writer::end_tag('div'));
        $attributes['id'] = $id;
        $attributes['class'] = 'box ' . renderer_base::prepare_classes($classes);
        return html_writer::start_tag('div', $attributes);
    }

    /**
     * Outputs the closing section of a box.
     *
     * @return string the HTML to output.
     */
    public function space_box_end() {
        return $this->opencontainers->pop('box');
    }

    /**
     * Output the page information
     *
     * @param object $quiz the quiz settings.
     * @param object $cm the course_module object.
     * @param context $context the quiz context.
     * @param array $messages any access messages that should be described.
     * @param bool $quizhasquestions does quiz has questions added.
     * @return string HTML to output.
     */
    public function view_information($quiz, $cm, $context, $messages, bool $quizhasquestions = false) {
        $output = '';

        // Output any access messages.
        if ($messages) {
            $output .= $this->box($this->access_messages($messages), 'rui-quizinfo quizinfo mt-3');
        }

        // Show number of attempts summary to those who can view reports.
        if (has_capability('mod/quiz:viewreports', $context)) {
            if (
                $strattemptnum = $this->quiz_attempt_summary_link_to_reports(
                    $quiz,
                    $cm,
                    $context
                )
            ) {
                $output .= html_writer::tag(
                    'div',
                    $strattemptnum,
                    ['class' => 'rui-quizattemptcounts quizattemptcounts my-4']
                );
            }
        }

        if (has_any_capability(['mod/quiz:manageoverrides', 'mod/quiz:viewoverrides'], $context)) {
            if ($overrideinfo = $this->quiz_override_summary_links($quiz, $cm)) {
                $output .= html_writer::tag('div', $overrideinfo, ['class' => 'rui-quizattemptcounts quizattemptcounts my-4']);
            }
        }

        return $output;
    }

    /**
     * Generates the table of summarydata
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_table($attemptobj, $displayoptions) {
        // Prepare the summary table header.
        $table = new html_table();
        $table->attributes['class'] = 'generaltable quizsummaryofattempt';
        $table->head = [get_string('question', 'quiz'), get_string('status', 'quiz')];
        $table->align = ['left', 'left'];
        $table->size = ['', ''];
        $markscolumn = $displayoptions->marks >= question_display_options::MARK_AND_MAX;
        if ($markscolumn) {
            $table->head[] = get_string('marks', 'quiz');
            $table->align[] = 'left';
            $table->size[] = '';
        }
        $tablewidth = count($table->align);
        $table->data = [];

        // Get the summary info for each question.
        $slots = $attemptobj->get_slots();
        foreach ($slots as $slot) {
            // Add a section headings if we need one here.
            $heading = $attemptobj->get_heading_before_slot($slot);

            if ($heading !== null) {
                // There is a heading here.
                $rowclasses = 'quizsummaryheading';
                if ($heading) {
                    $heading = format_string($heading);
                } else if (count($attemptobj->get_quizobj()->get_sections()) > 1) {
                    // If this is the start of an unnamed section, and the quiz has more
                    // than one section, then add a default heading.
                    $heading = get_string('sectionnoname', 'quiz');
                    $rowclasses .= ' dimmed_text';
                }
                $cell = new html_table_cell(format_string($heading));
                $cell->header = true;
                $cell->colspan = $tablewidth;
                $table->data[] = [$cell];
                $table->rowclasses[] = $rowclasses;
            }

            // Don't display information items.
            if (!$attemptobj->is_real_question($slot)) {
                continue;
            }

            $flag = '';

            // Real question, show it.
            if ($attemptobj->is_question_flagged($slot)) {
                // Quiz has custom JS manipulating these image tags - so we can't use the pix_icon method here.
                $flag = '<svg class="ml-2"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                <path d="M4.75 5.75V19.25"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">
                </path>
                <path d="M4.75 15.25V5.75C4.75 5.75 6 4.75 9 4.75C12 4.75 13.5 6.25 16 6.25C18.5
                6.25 19.25 5.75 19.25 5.75L15.75 10.5L19.25 15.25C19.25 15.25 18.5 16.25 16
                16.25C13.5 16.25 11.5 14.25 9 14.25C6.5 14.25 4.75 15.25 4.75 15.25Z"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"></path>
                </svg>';
            }
            if ($attemptobj->can_navigate_to($slot)) {
                $row = [
                    html_writer::link(
                        $attemptobj->attempt_url($slot),
                        $attemptobj->get_question_number($slot) . $flag
                    ),
                    $attemptobj->get_question_status($slot, $displayoptions->correctness),
                ];
            } else {
                $row = [
                    $attemptobj->get_question_number($slot) . $flag,
                    $attemptobj->get_question_status($slot, $displayoptions->correctness),
                ];
            }
            if ($markscolumn) {
                $row[] = $attemptobj->get_question_mark($slot);
            }
            $table->data[] = $row;
            $table->rowclasses[] = 'quizsummary' . $slot . ' ' . $attemptobj->get_question_state_class(
                $slot,
                $displayoptions->correctness
            );
        }

        // Print the summary table.
        $output = html_writer::table($table);

        return $output;
    }
}

require_once($CFG->dirroot . "/question/engine/renderer.php");

/**
 * theme_space_core_question_renderer
 */
class theme_space_core_question_renderer extends core_question_renderer {
    /**
     * Generate the information bit of the question display that contains the
     * metadata like the question number, current state, and mark.
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param qtype_renderer $qtoutput the renderer to output the question type
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return HTML fragment.
     */
    protected function info(
        question_attempt $qa,
        qbehaviour_renderer $behaviouroutput,
        qtype_renderer $qtoutput,
        question_display_options $options,
        $number
    ) {
        $output = '';
        $output .= '<div class="d-flex align-items-center flex-wrap mb-sm-2 mb-md-0">' .
            $this->number($number) .
            '<div class="d-inline-flex align-items-center flex-wrap">' .
            $this->status($qa, $behaviouroutput, $options) .
            $this->mark_summary($qa, $behaviouroutput, $options) .
            '</div></div>';
        $output .= '<div>' .
            $this->question_flag($qa, $options->flags) .
            $this->edit_question_link($qa, $options) .
            '</div>';
        return $output;
    }

    /**
     * Generate the display of the question number.
     * @param string|null $number The question number to display. 'i' is a special
     *      value that gets displayed as Information. Null means no number is displayed.
     * @return HTML fragment.
     */
    protected function number($number) {
        if (trim($number) === '') {
            return '';
        }
        $numbertext = '';
        if (trim($number) === 'i') {
            $numbertext = get_string('information', 'question');
        } else {
            $numbertext = get_string(
                'questionx',
                'question',
                html_writer::tag('span', $number, ['class' => 'rui-qno'])
            );
        }
        return html_writer::tag('h4', $numbertext, ['class' => 'h3 w-100 mb-2']);
    }


    /**
     * Generate the display of the status line that gives the current state of
     * the question.
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return HTML fragment.
     */
    protected function status(
        question_attempt $qa,
        qbehaviour_renderer $behaviouroutput,
        question_display_options $options
    ) {
        return html_writer::tag(
            'div',
            $qa->get_state_string($options->correctness),
            ['class' => 'state mr-2 my-2']
        );
    }

    /**
     * Render the question flag, assuming $flagsoption allows it.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param int $flagsoption the option that says whether flags should be displayed.
     */
    protected function question_flag(question_attempt $qa, $flagsoption) {
        global $CFG;

        $divattributes = ['class' => 'questionflag mx-1 d-none'];

        switch ($flagsoption) {
            case question_display_options::VISIBLE:
                $flagcontent = $this->get_flag_html($qa->is_flagged());
                break;

            case question_display_options::EDITABLE:
                $id = $qa->get_flag_field_name();
                $checkboxattributes = [
                    'type' => 'checkbox',
                    'id' => $id . 'checkbox',
                    'name' => $id,
                    'value' => 1,
                ];
                if ($qa->is_flagged()) {
                    $checkboxattributes['checked'] = 'checked';
                }
                $postdata = question_flags::get_postdata($qa);

                $flagcontent = html_writer::empty_tag(
                    'input',
                    ['type' => 'hidden', 'name' => $id, 'value' => 0]
                ) .
                    html_writer::empty_tag('input', $checkboxattributes) .
                    html_writer::empty_tag(
                        'input',
                        ['type' => 'hidden', 'value' => $postdata, 'class' => 'questionflagpostdata']
                    ) .
                    html_writer::tag(
                        'label',
                        $this->get_flag_html($qa->is_flagged(), $id . 'img'),
                        ['id' => $id . 'label', 'for' => $id . 'checkbox']
                    ) . "\n";

                $divattributes = [
                    'class' => 'questionflag mb-sm-2 mb-md-0 mx-md-2 editable d-inline-flex',
                    'aria-atomic' => 'true',
                    'aria-relevant' => 'text',
                    'aria-live' => 'assertive',
                ];

                break;

            default:
                $flagcontent = '';
        }

        return html_writer::nonempty_tag('div', $flagcontent, $divattributes);
    }

    /**
     * Generate the display of the edit question link.
     *
     * @param question_attempt $qa The question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string
     */
    protected function edit_question_link(question_attempt $qa, question_display_options $options) {
        if (empty($options->editquestionparams)) {
            return '';
        }

        $params = $options->editquestionparams;
        if ($params['returnurl'] instanceof moodle_url) {
            $params['returnurl'] = $params['returnurl']->out_as_local_url(false);
        }
        $params['id'] = $qa->get_question_id();
        $editurl = new moodle_url('/question/bank/editquestion/question.php', $params);

        return html_writer::tag('div', html_writer::link(
            $editurl, $this->pix_icon('t/edit', get_string('edit'), '', ['class' => 'iconsmall']) .
            get_string('editquestion', 'question'),
                ['class' => 'btn btn-sm btn-secondary ml-2']),
            ['class' => 'editquestion']);
    }
}

