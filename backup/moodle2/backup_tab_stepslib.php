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
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */
class backup_tab_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure(): backup_nested_element {

        // Define each element separated.
        $tab = new backup_nested_element('tab', ['id'], [
            'name', 'intro', 'css', 'menucss', 'displaymenu', 'menuname', 'taborder',
            'legacyfiles', 'legacyfileslast', 'timemodified', 'introformat',
            ]
        );

        $tabcontents = new backup_nested_element('tab_contents');

        $tabcontent = new backup_nested_element('tab_content', ['id'], ['tabname',
            'tabcontent', 'tabcontentorder', 'externalurl', 'contentformat', 'timemodified', ]);

        // Build the tree.
        $tab->add_child($tabcontents);
        $tabcontents->add_child($tabcontent);
        // Define sources.
        $tab->set_source_table('tab', ['id' => backup::VAR_ACTIVITYID]);

        $tabcontent->set_source_sql(
            'SELECT * FROM {tab_content}
                        WHERE tabid = ?', [backup::VAR_PARENTID]);

        // Define id annotations
        // $tab_content->annotate_ids('tabid', 'tabid');
        // Define file annotations.
        $tab->annotate_files('mod_tab', 'intro', null);
        $tabcontent->annotate_files('mod_tab', 'content', 'id');

        // Return the root element (tab), wrapped into standard activity structure.
        return $this->prepare_activity_structure($tab);
    }

}
