<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block realdashboard is defined here.
 *
 * @package     block_realdashboard
 * @copyright   2025 Josue <ninijosue123@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_realdashboard extends block_base
{

    /**
     * Initializes class member variables.
     */
    public function init()
    {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_realdashboard');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content()
    {
        global $PAGE;
        global $DB;
        global $USER;
        if ($this->content !== null) {
            return $this->content;
        }


        // Add Chart.js
        $PAGE->requires->js(new moodle_url('https://cdn.jsdelivr.net/npm/chart.js'), true);

        // Add jQuery (Moodle already includes jQuery, but if you need a specific version:)
        $PAGE->requires->js(new moodle_url('https://code.jquery.com/jquery-3.6.0.min.js'), true);
        $PAGE->requires->js('/blocks/realdashboard/js/index.js');
        $PAGE->requires->js_amd_inline("import('/blocks/realdashboard/frontend/dist/index_002.js');");
        $PAGE->requires->css('/blocks/realdashboard/frontend/dist/styles.css');

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }
        // total number of students
        // 1. Total number of students (users with 'student' role)
        $sql_students = "
            SELECT COUNT(DISTINCT u.id)
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {role} r ON ra.roleid = r.id
            WHERE r.shortname = 'student'
        ";

        $total_students = number_format($DB->count_records_sql($sql_students));
        // 2. Students enrolled in at least one course
        $sql_enrolled = "
            SELECT COUNT(DISTINCT ue.userid)
            FROM {user_enrolments} ue
            JOIN {enrol} e ON ue.enrolid = e.id
            JOIN {role_assignments} ra ON ra.userid = ue.userid
            JOIN {role} r ON ra.roleid = r.id
            WHERE r.shortname = 'student' AND e.courseid > 1
        ";
        $students_enrolled = number_format($DB->get_field_sql($sql_enrolled));

        // 3. Students who have excelled (final course grade >= 60%)
        $sql_excelling = "
            SELECT COUNT(DISTINCT g.userid)
            FROM {grade_grades} g
            JOIN {grade_items} gi ON g.itemid = gi.id
            JOIN {role_assignments} ra ON ra.userid = g.userid
            JOIN {role} r ON ra.roleid = r.id
            WHERE r.shortname = 'student'
              AND gi.itemtype = 'course'
              AND g.finalgrade IS NOT NULL
              AND g.finalgrade >= (gi.grademax * 0.6)
        ";
        $students_excelling = number_format($DB->get_field_sql($sql_excelling));

        // Example: Prepare monthly joining data for 2024
        $monthly_data = [];
        $months = [
            1 => "January",
            2 => "February",
            3 => "March",
            4 => "April",
            5 => "May",
            6 => "June",
            7 => "July",
            8 => "August",
            9 => "September",
            10 => "October",
            11 => "November",
            12 => "December"
        ];

        // Initialize with 0 students per month
        foreach ($months as $num => $name) {
            $monthly_data[$num] = [
                'month' => $name,
                'students' => 0
            ];
        }

        // Query new students (with student role) per month in 2024
        $sql = "
            SELECT
            EXTRACT(MONTH FROM FROM_UNIXTIME(u.timecreated)) AS month,
            COUNT(DISTINCT u.id) AS students
            FROM
                {user} u
            JOIN
                {role_assignments} ra ON ra.userid = u.id
            JOIN
                {role} r ON ra.roleid = r.id
            WHERE
                r.shortname = 'student'
                AND u.timecreated >= :startdate
                AND u.timecreated < :enddate
            GROUP BY
                month
            ORDER BY
            month
        ";
        $params = [
            'startdate' => strtotime('2025-01-01 00:00:00'),
            'enddate' => strtotime('2026-01-01 00:00:00')
        ];
        $results = $DB->get_records_sql($sql, $params);

        foreach ($results as $row) {
            $monthly_data[intval($row->month)]['students'] = intval($row->students);
        }
        $chart_data = array_values($monthly_data);
        // $PAGE->requires->js_init_call('window.initMyChart', [json_encode($chart_data)], true);
        echo '<div style="opacity:0;height:0" id="myChartData" data-chart=\'' . json_encode($chart_data) . '\'></div>';


        $this->content = new stdClass();
        $this->content->items = [];
        $this->content->icons = [];
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            
            $context = context_system::instance();

            // Get all roles of the user in system context
            $userroles = get_user_roles($context, $USER->id, true);

            $can_view_dashboard = false;
            foreach ($userroles as $role) {
                if ($role->roleid == 9) { // Default manager roleid is 1
                    $can_view_dashboard = true;
                    break;
                }
            }
            if (is_siteadmin()) {
                $text = "
                <dashboard-main></dashboard-main>
                ";
                $this->content->text = $text;
            }
            else if ($can_view_dashboard) {
                 $text = "
                <dashboard-main></dashboard-main>
                ";
                $this->content->text = $text;
            }
            else {
                $text = "<learner-dashboard></learner-dashboard>";
                $this->content->text = $text;
            }
           
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization()
    {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_realdashboard');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config()
    {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats()
    {
        return [
            'all' => true,
        ];
    }

    function _self_test()
    {
        return true;
    }
}
