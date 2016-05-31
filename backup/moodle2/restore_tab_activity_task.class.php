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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/tab/backup/moodle2/restore_tab_stepslib.php'); // Because it exists (must)

/**
 * choice restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_tab_activity_task extends restore_activity_task
{

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings()
    {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps()
    {
        // Tab only has one structure step
        $this->add_step(new restore_tab_activity_structure_step('tab_structure', 'tab.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents()
    {
        $contents = array();

        $contents[] = new restore_decode_content('tab', array('intro'), 'tab');
        $contents[] = new restore_decode_content('tab_content', array('tabcontent'), 'tab_content');
        
        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('TABVIEWBYID', '/mod/tab/view.php?id=$1', 'course_module');

        return $rules;

    }

}
