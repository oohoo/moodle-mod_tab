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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/resourcelib.php');
require_once($CFG->dirroot . '/mod/tab/lib.php');

/**
 * File browsing support class
 */
class tab_content_file_info extends file_info_stored {

    /**
     * Returns parent file_info instance
     * @return file_info|null file_info instance or null for root
     */
    public function get_parent(): ?file_info {
        if ($this->lf->get_filepath() === '/' && $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }

    /**
     * Returns localised visible name.
     * @return string
     */
    public function get_visible_name(): string {
        if ($this->lf->get_filepath() === '/' && $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }

}

/**
 * Return an array of options for the editor tinyMCE
 *
 * @param object $context The context ID
 * @return array The array of options for the editor
 * @global stdClass $CFG
 */
function tab_get_editor_options($context): array {
    global $CFG;
    return [
        'subdirs' => 1,
        'maxbytes' => $CFG->maxbytes,
        'maxfiles' => -1,
        'changeformat' => 1,
        'context' => $context,
        'noclean' => 1,
        'trusttext' => 0,
    ];
}

/**
 * Prepare an URL. Trim, delete useless tags, etc.
 *
 * @param string $string The URL to prepare
 * @return string
 */
function process_urls(string $string): string {
    preg_match_all("/<a href=.*?<\/a>/", $string, $matches);
    foreach ($matches[0] as $mtch) {
        $mtchbits = explode('"', $mtch);
        $string = str_replace($mtch, "{$mtchbits[1]}", $string);
    }
    $path = str_replace('<div class="text_to_html">', '', $string);
    $path = str_replace('</div>', '', $path);
    $path = str_replace('<p>', '', $path);
    $path = str_replace('</p>', '', $path);

    return $path;
}

/**
 * Returns general link or file embedding html.
 *
 * @param string $fullurl
 * @param string $clicktoopen
 * @return string html
 * @global moodle_page $PAGE
 */
function tab_embed_general(string $fullurl, string $clicktoopen): string {
    global $PAGE;

    $idsuffix = md5($fullurl);

    $code = <<<EOT
<div class="resourcecontent resourcegeneral">
  <iframe id="resourceobject_$idsuffix" src="$fullurl" class="mod-tab-embedded">
    $clicktoopen
  </iframe>
</div>
EOT;

    $PAGE->requires->js_call_amd('mod_tab/module', 'init', ["resourceobject_$idsuffix"]);

    return $code;
}
