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
 * *************************************************************************
 * *                         OOHOO - Tab Display                          **
 * *************************************************************************
 * @package     mod                                                       **
 * @subpackage  tab                                                       **
 * @name        tab                                                       **
 * @copyright   oohoo.biz                                                 **
 * @link        http://oohoo.biz                                          **
 * @author      Patrick Thibaudeau                                        **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************ */

use mod_tab\event\course_module_viewed;
use mod_tab\output\view;

require("../../config.php");
require_once("lib.php");
require_once("locallib.php");
require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');
require_once($CFG->dirroot . '/mod/tab/classes/event/course_module_viewed.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or.
$a = optional_param('a', 0, PARAM_INT); // Tab ID.

if ($id) {
    if (!$cm = get_coursemodule_from_id("tab", $id)) {
        throw new moodle_exception("Course Module ID was incorrect");
    }

    if (!$tab = $DB->get_record("tab", ["id" => $cm->instance])) {
        throw new moodle_exception("Course module is incorrect");
    }
} else {
    if (!$tab = $DB->get_record("tab", ["id" => $a])) {
        throw new moodle_exception("Course module is incorrect");
    }

    if (!$cm = get_coursemodule_from_instance("tab", $tab->id, $course->id)) {
        throw new moodle_exception("Course Module ID was incorrect");
    }
}

$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

require_capability('mod/tab:view', $context);

// Update 'viewed' state if required by completion system.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.

$PAGE->set_url('/mod/tab/view.php', ['id' => $cm->id]);
$PAGE->set_title($tab->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_activity_record($tab);

// Gather css.
$PAGE->requires->css('/mod/tab/styles.css');

// Log the view information.
$event = course_module_viewed::create([
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
]);
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $tab);
$event->trigger();

echo $OUTPUT->header();

$output = $PAGE->get_renderer('mod_tab');
$view = new view($tab, $course->id, $cm);
echo $output->render_view($view);

echo $OUTPUT->footer();

