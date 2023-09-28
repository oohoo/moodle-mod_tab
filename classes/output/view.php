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

namespace mod_tab\output;

use context_course;
use context_module;
use core\context\course;
use moodle_database;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 *
 * @param renderer_base $output
 * @return array
 * @global stdClass $USER
 */
class view implements renderable, templatable {

    private stdClass $tab;
    private int $courseid;
    private course|false $coursecontext;
    private stdClass $cm;

    /**
     *
     * @param stdClass $tab
     * @param $courseid
     * @param stdClass $cm
     */
    public function __construct(stdClass $tab, $courseid, stdClass $cm) {
        $this->tab = $tab;
        $this->courseid = $courseid;
        $this->coursecontext = context_course::instance($courseid);
        $this->cm = $cm;
    }

    /**
     *
     * @param renderer_base $output
     * @return array
     * @global stdClass $USER
     * @global moodle_database $DB
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG;

        $tab = $this->tab;
        $cm = $this->cm;
        $intro = '';
        if (trim(strip_tags($tab->intro))) {
            $intro = format_module_intro('tab', $tab, $cm->id);
        }

        return [
            'wwwroot' => $CFG->wwwroot,
            'intro' => $intro,
            'showMenu' => $tab->displaymenu,
            'menu' => $this->getTabMenuContent(),
            'tabs' => $this->getTabContent(),
            'ismoodle40andgreater' => $CFG->version >= 2022041900, // Description is only rendert in < 4.0.
        ];
    }

    private function gettabmenucontent(): array {
        global $DB;

        $contentsql = <<<'EOF'
            SELECT {course_modules}.id as id,
            {course_modules}.visible as visible,
            {tab}.name as name,
            {tab}.taborder as taborder,
            {tab}.menuname as menuname 
            FROM ({modules} INNER JOIN {course_modules} ON {modules}.id = {course_modules}.module) 
            INNER JOIN {tab} ON {course_modules}.instance = {tab}.id 
            WHERE ((({modules}.name)='tab') AND (({course_modules}.course)=?)) 
            ORDER BY taborder;
            EOF;

        $results = $DB->get_records_sql($contentsql, [$this->courseid]);

        $items = [];
        $i = 0;
        foreach ($results as $result) { // Foreach.
            // Only print the tabs that have the same menu name.
            if ($result->menuname == $this->tab->menuname) {
                // Only print visible tabs within the menu.

                if ($result->visible == 1 || has_capability('moodle/course:update', $this->coursecontext)) {
                    $items[$i]['id'] = $result->id;
                    $items[$i]['name'] = $result->name;
                }
            }
            $i++;
        }

        return [
            'name' => $this->tab->menuname,
            'items' => $items,
        ];
    }

    private function gettabcontent(): array {
        global $CFG, $DB;

        $context = context_module::instance($this->cm->id);
        $editoroptions = [
            'subdirs' => 1,
            'maxbytes' => $CFG->maxbytes,
            'maxfiles' => -1,
            'changeformat' => 1,
            'context' => $context,
            'noclean' => 1,
            'trusttext' => true,
        ];
        $options = $DB->get_records('tab_content', ['tabid' => $this->tab->id], 'tabcontentorder');
        $contents = [];
        $i = 0;
        foreach ($options as $option) {
            $externalurl = $option->externalurl;

            if (!empty($externalurl)) {
                // Todo check url.
                if (!preg_match('{https?:\/\/}', $externalurl)) {
                    $externalurl = 'http://' . $externalurl;
                }
                $contents[$i]['content'] .= tab_embed_general(
                    process_urls($externalurl),
                    get_string('embed_fail_msg', 'tab')
                    . "<a href='$externalurl' target='_blank' >" . get_string('embed_fail_link_text', 'tab') . '</a>');
            } else {
                if (empty($option->format)) {
                    $option->format = 1;
                }
                $content = file_rewrite_pluginfile_urls(
                    $option->tabcontent,
                    'pluginfile.php',
                    $context->id,
                    'mod_tab',
                    'content',
                    $option->id
                );
                $content = format_text($content, $option->contentformat, $editoroptions, $context);
                // PDF.
                $pattern = '/<a\s+[^>]*href="([^"]*\.pdf)"[^>]*>(.*?)<\/a>/i';
                preg_match_all($pattern, $content, $matches);
                if (count($matches[1]) >= 1) {
                    foreach ($matches[1] as $link) {
                        // Enter into proper div.
                        $contents[$i]['content'] .= tab_embed_general(
                            process_urls($link),
                            get_string('embed_fail_msg', 'tab')
                            . "<a href='$link' target='_blank' >" . get_string('embed_fail_link_text', 'tab') . '</a>');
                    }
                } else {
                    $contents[$i]['content'] = $content;
                }
            }

            $contents[$i]['name'] = $option->tabname;
            $contents[$i]['id'] = $option->id;
            if ($i == 0) {
                $contents[$i]['active'] = true;
            } else {
                $contents[$i]['active'] = false;
            }
            $i++;
        }

        return $contents;
    }
}
