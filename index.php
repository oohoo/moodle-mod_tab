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

/*
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

use mod_tab\event\course_module_instance_list_viewed;

require('../../config.php');

$id = required_param('id', PARAM_INT); // Course id.

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

require_course_login($course);
$PAGE->set_pagelayout('incourse');

$strpage = get_string('modulename', 'tab');
$strpages = get_string('modulenameplural', 'tab');
$strsectionname = get_string('sectionname', 'format_' . $course->format);
$strname = get_string('name');
$strintro = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$modinfo = get_fast_modinfo($course);

$PAGE->set_url('/mod/tab/index.php', ['id' => $course->id]);
$PAGE->set_title($course->shortname . ': ' . $strpages);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strpages);
echo $OUTPUT->header();

// Log the view information.
$event = course_module_instance_list_viewed::create(['context' => context_course::instance($course->id)]);
$event->add_record_snapshot('course', $course);
$event->trigger();


if (!$tabs = get_all_instances_in_course('tab', $course)) {
    notice(get_string('thereareno', 'moodle', $strpages), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

if ($usesections) {
    $sections = $modinfo->get_section_info_all();
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head = [$strsectionname, $strname, $strintro];
    $table->align = ['center', 'left', 'left'];
} else {
    $table->head = [$strlastmodified, $strname, $strintro];
    $table->align = ['left', 'left', 'left'];
}


$currentsection = '';
foreach ($tabs as $tab) {
    $cm = $modinfo->cms[$tab->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($tab->section !== $currentsection) {
            if ($tab->section) {
                $printsection = get_section_name($course, $sections[$tab->section]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $tab->section;
        }
    } else {
        $printsection = '<span class="smallinfo">' . userdate($tab->timemodified) . "</span>";
    }

    $class = $tab->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.

    $table->data[] = [
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">" . format_string($tab->name) . "</a>",
        format_module_intro('tab', $tab, $cm->id), ];
}

echo html_writer::table($table);

echo $OUTPUT->footer();
