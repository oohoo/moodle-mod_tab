<?php

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
require("../../config.php");
require_once("lib.php");
require_once("locallib.php");
require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');
require_once($CFG->dirroot . '/mod/tab/classes/event/course_module_viewed.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT); // tab ID

if ($id) {
    if (!$cm = get_coursemodule_from_id("tab", $id)) {
        error("Course Module ID was incorrect");
    }

    if (!$tab = $DB->get_record("tab", array("id" => $cm->instance))) {
        error("Course module is incorrect");
    }
} else {
    if (!$tab = $DB->get_record("tab", array("id" => $a))) {
        error("Course module is incorrect");
    }

    if (!$cm = get_coursemodule_from_instance("tab", $tab->id, $course->id)) {
        error("Course Module ID was incorrect");
    }
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
//Replace get_context_instance by the class for moodle 2.6+
if (class_exists('context_module')) {
    $context = context_module::instance($cm->id);
    $coursecontext = context_course::instance($course->id);
} else {
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
}

require_capability('mod/tab:view', $context);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header

$PAGE->set_url('/mod/tab/view.php', array('id' => $cm->id));
$PAGE->set_title($tab->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_activity_record($tab);

//Gather css
$PAGE->requires->css('/mod/tab/styles.css');

//log the view information
$event = \mod_tab\event\course_module_viewed::create(array(
            'objectid' => $PAGE->cm->instance,
            'context' => $PAGE->context,
        ));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $tab);
$event->trigger();

echo $OUTPUT->header();

$output = $PAGE->get_renderer('mod_tab');
$view = new \mod_tab\output\view($tab,$course->id, $cm);
echo $output->render_view($view);

echo $OUTPUT->footer();

