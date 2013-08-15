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
/**
 * Define all the restore steps that will be used by the restore_choice_activity_task
 */

/**
 * Structure step to restore one choice activity
 */
class restore_tab_activity_structure_step extends restore_activity_structure_step
{

    protected function define_structure()
    {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('tab', '/activity/tab');
        $paths[] = new restore_path_element('tab_content', '/activity/tab/tab_contents/tab_content');


        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_tab($data)
    {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the tab record
        $newitemid = $DB->insert_record('tab', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);

	for ($i=1;$i<=9;$i++) { // assume we have maximum of 8 tabs in the old moodle 19 module
            if (empty($data->{"tab".$i})) continue;
            $tcdata = new StdClass;
            $tcdata->id = $oldid;
            $tcdata->tabid = $newitemid;
            $tcdata->tabname = $data->{"tab".$i};
            $tcdata->tabcontent = text_to_html($data->{"tab".$i."content"});
            $tcdata->contentformat = FORMAT_HTML;
            $tcdata->tabcontentorder = $i;
            $tcdata->timemodified = $data->timemodified;
            $this->process_tab_content($tcdata);

        }
    }

    protected function process_tab_content($data)
    {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->tabid = $this->get_new_parentid('tab');
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('tab_content', $data);
        $this->set_mapping('tab_content', $oldid, $newitemid, true); //has related files
    }

    protected function after_execute()
    {
        global $DB;
        // Add tab related files where itemname = tab_content (taken from $this->set_mapping)
        $this->add_related_files('mod_tab', 'intro', null);
        $this->add_related_files('mod_tab', 'content', 'tab_content');
    }

}
