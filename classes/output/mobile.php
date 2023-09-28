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

use context_module;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');
require_once($CFG->libdir . '/filelib.php');

require_once($CFG->dirroot . '/mod/tab/locallib.php');

class mobile {

    /* Returns the initial page when viewing the activity for the mobile app.
     *
     * @param  array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and other data
     */
    public static function mobile_view_activity($args): array {
        global $OUTPUT, $DB, $CFG;

        $cm = get_coursemodule_from_id("tab", $args['cmid']);
        $tab = $DB->get_record("tab", ["id" => $cm->instance]);
        $context = context_module::instance($cm->id);
        $editoroptions =
            ['subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1, 'context' => $context,
                'noclean' => 1, 'trusttext' => true, ];
        $options = $DB->get_records('tab_content', ['tabid' => $tab->id], 'tabcontentorder');

        $data = [];
        $tablist = [];
        foreach ($options as $option) {
            $externalurl = $option->externalurl;

            $tabcontent['title'] = $option->tabname;

            if (!empty($externalurl)) {
                // Todo check url.
                if (!preg_match('{https?:\/\/}', $externalurl)) {
                    $externalurl = 'http://' . $externalurl;
                }
                $tabcontent['url'] = process_urls($externalurl);
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
                $tabcontent['html']  = $content;
            }

            $tablist[] = $tabcontent;
        }

        $data['tabList'] = $tablist;
        $versionname = $args['appversioncode'] >= 3950 ? 'latest' : 'ionic3';

        return [
            'templates' => [
                [
                    'id' => 'tab_main',
                    'html' => $OUTPUT->render_from_template("mod_tab/mobile_view_page_$versionname", $data),
                ],
            ],
        ];
    }
}


