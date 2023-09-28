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
 * Define all the restore steps that will be used by the restore_choice_activity_task
 */

/**
 * Structure step to restore one choice activity
 */
class restore_tab_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = [];
        $paths[] = new restore_path_element('tab', '/activity/tab');
        $paths[] = new restore_path_element('tab_content', '/activity/tab/tab_contents/tab_content');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_tab($data): void {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the tab record.
        $newitemid = $DB->insert_record('tab', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_tab_content($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->tabid = $this->get_new_parentid('tab');
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('tab_content', $data);
        $this->set_mapping('tab_content', $oldid, $newitemid, true); // Has related files.
    }

    protected function after_execute(): void {
        // Add tab related files where itemname = tab_content (taken from $this->set_mapping).
        $this->add_related_files('mod_tab', 'intro', null);
        $this->add_related_files('mod_tab', 'content', 'tab_content');
    }
}
