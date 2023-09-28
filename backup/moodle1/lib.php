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

/**
 * Tab Display conversion handler
 */
class moodle1_mod_tab_handler extends moodle1_mod_handler {

    /** @var ?moodle1_file_manager */
    protected ?moodle1_file_manager $fileman = null;

    /** @var ?int cmid */
    protected ?int $moduleid = null;

    /**
     * Declare the paths in moodle.xml we are able to convert
     *
     * The method returns list of {@link convert_path} instances.
     * For each path returned, the corresponding conversion method must be
     * defined.
     *
     * Note that the path /MOODLE_BACKUP/COURSE/MODULES/MOD/CHOICE does not
     * actually exist in the file. The last element with the module name was
     * appended by the moodle1_converter class.
     *
     * @return array of {@link convert_path} instances
     */
    public function get_paths(): array {
        return [
            new convert_path('tab', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TAB'),
            new convert_path('tab_contents', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TAB/TABCONTENTS'),
            new convert_path('tab_content', '/MOODLE_BACKUP/COURSE/MODULES/MOD/TAB/TABCONTENTS/TABCONTENT',
                [
                    'renamefields' => [
                        'format' => 'contentformat',
                    ],
                    'newfields' => [
                        'externalurl' => null,
                        'contentformat' => 1,
                    ],
                ]
            ),
        ];
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/TAB
     * data available
     */
    public function process_tab($data) {

        // Get the course module id and context id.
        $instanceid = $data['id'];
        $cminfo = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

        // Get a fresh new file manager for this instance.
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_tab');

        // Vonvert course files embedded into the intro
        // $this->fileman->filearea = 'tabcontent';
        // $this->fileman->itemid   = 0;
        // $data['tabcontent'] = moodle1_converter::migrate_referenced_files($data['tabcontent'], $this->fileman);
        // Start writing choice.xml.
        $this->open_xml_writer("activities/tab_$this->moduleid/tab.xml");
        $this->xmlwriter->begin_tag('activity', ['id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'tab', 'contextid' => $contextid, ]);
        $this->xmlwriter->begin_tag('tab', ['id' => $instanceid]);

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $data;
    }

    /**
     * This is executed when the parser reaches the <OPTIONS> opening element
     */
    public function on_tab_contents_start(): void {
        $this->xmlwriter->begin_tag('tab_contents');
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/CHOICE/OPTIONS/OPTION
     * data available
     */
    public function process_tab_content($data): void {
        $this->write_xml('tab_content', $data, ['/tab_content/id']);
    }

    /**
     * This is executed when the parser reaches the closing </OPTIONS> element
     */
    public function on_tab_contents_end(): void {
        $this->xmlwriter->end_tag('tab_contents');
    }

    /**
     * This is executed when we reach the closing </MOD> tag of our 'choice' path
     */
    public function on_tab_end(): void {
        // Finalize tab.xml.
        $this->xmlwriter->end_tag('tab');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();
    }

}
