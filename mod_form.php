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
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Class for the form of the tab
 */
class mod_tab_mod_form extends moodleform_mod
{

    /**
     * The tab form
     * @global stdClass $CFG
     * @global moodle_database $DB 
     */
    function definition()
    {
        global $CFG, $DB;

        $mform = $this->_form;

        $config = get_config('tab');

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name', 'tab'), array('size' => '45'));
        if (!empty($CFG->formatstringstriptags))
        {
            $mform->setType('name', PARAM_TEXT);
        }
        else
        {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        //Add Intro
        $this->add_intro_editor(false);

        $mform->setDefault('printintro', 0);
        $mform->setAdvanced('printintro', false);

        //Have to use this option for postgresqgl to work
        $instance = $this->current->instance;
        if (empty($instance))
        {
            $instance = 0;
        }

        //following code used to create tabcontent order numbers
        $optionid = optional_param_array('optionid', array(), PARAM_INT);
        if (isset($optionid))
        {
            $repeatnum = count($optionid);
        }
        else
        {
            $repeatnum = 0;
        }
        if ($repeatnum == 0)
        {
            $repeatnum = $DB->count_records('tab_content', array('tabid' => $instance));
        }
        $taborder = 1; //initialize to prevent warnings
        for ($i = 1; $i <= $repeatnum + 1; $i++)
        {
            if ($i == 1)
            {
                $taborder = 1;
            }
            else
            {
                $taborder = $taborder . ',' . $i;
            }
        }
        $context = $this->context;

        $editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => 1);
        $taborderarray = explode(',', $taborder);
        //-----------------------------for adding tabs---------------------------------------------------------------
        $repeatarray = array();

        $repeatarray[] = $mform->createElement('header', 'tabs', get_string('tab', 'tab') . ' {no}');
        $repeatarray[] = $mform->createElement('text', 'tabname', get_string('tabname', 'tab'), array('size' => '65'));
        $repeatarray[] = $mform->createElement('editor', 'content', get_string('tabcontent', 'tab'), null, $editoroptions);
        $repeatarray[] = $mform->createElement('url', 'externalurl', get_string('externalurl', 'tab'), array('size' => '60'), array('usefilepicker' => true));
        $repeatarray[] = $mform->createElement('hidden', 'revision', 1);
        $repeatarray[] = $mform->createElement('select', 'tabcontentorder', get_string('order', 'tab'), $taborderarray);
        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);
        
        $mform->setType('tabname', PARAM_TEXT);
        $mform->setType('content', PARAM_RAW);
        $mform->setType('externalurl', PARAM_URL);
        $mform->setType('revision', PARAM_INT);
        $mform->setType('tabcontentorder', PARAM_INT);
        $mform->setType('optionid', PARAM_INT);
        $mform->setType('content', PARAM_RAW);
                
        if ($this->_instance)
        {
            $repeatno = $DB->count_records('tab_content', array('tabid' => $instance));
            $repeatno += 1;
        }
        else
        {
            $repeatno = 1;
        }

        $repeateloptions = array();
        if (!isset($repeateloptions['tabcontentorder']))
        {
            $repeateloptions['tabcontentorder']['default'] = $i - 2;
        }

        $repeateloptions['content']['helpbutton'] = array('content', 'tab');


        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'option_add_fields', 1, get_string('addtab', 'tab'));
        //-----------------------------------------------------------------------------------------------------------------------------------------------
        //*********************************************************************************
        //*********************Display menu checkbox and name******************************
        //*********************************************************************************
        $mform->addElement('header', 'menu', get_string('displaymenu', 'tab'));
        $mform->addElement('advcheckbox', 'displaymenu', get_string('displaymenuagree', 'tab'), null, array('group' => 1), array('0', '1'));
        $mform->setType('displaymenu', PARAM_INT);
        $mform->addElement('text', 'taborder', get_string('taborder', 'tab'), array('size' => '15'));
        $mform->addElement('text', 'menuname', get_string('menuname', 'tab'), array('size' => '45'));

        $mform->setType('taborder', PARAM_INT);
        $mform->setType('menuname', PARAM_TEXT);
        
        //*********************************************************************************
        //*********************************************************************************

        $mform->setAdvanced('printintro', true);

        $features = array('groups' => false, 'groupings' => false, 'groupmembersonly' => true,
            'outcomes' => false, 'gradecat' => false, 'idnumber' => false);
        $this->standard_coursemodule_elements($features);

        //-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }

    /**
     * The preprocessing data from the form
     * @global stdClass $CFG
     * @global moodle_database $DB
     * @param type $default_values 
     */
    function data_preprocessing(&$default_values)
    {
        global $CFG, $DB;
        if ($this->current->instance)
        {
            $options = $DB->get_records('tab_content', array('tabid' => $this->current->instance), 'tabcontentorder');
            // print_object($options)
            $tabids = array_keys($options);
            $options = array_values($options);
            $context = $this->context;
            $editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => 1);
            foreach (array_keys($options) as $key)
            {
                $default_values['tabname[' . $key . ']'] = $options[$key]->tabname;

                $draftitemid = file_get_submitted_draft_itemid('content[' . $key . ']');
                $default_values['content[' . $key . ']']['format'] = $options[$key]->contentformat;
                $default_values['content[' . $key . ']']['text'] = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_tab', 'content', $options[$key]->id, $editoroptions, $options[$key]->tabcontent);
                $default_values['content[' . $key . ']']['itemid'] = $draftitemid;

                //$default_values['format['.$key.']'] = $options[$key]->format;
                $default_values['externalurl[' . $key . ']'] = $options[$key]->externalurl;
                $default_values['tabcontentorder[' . $key . ']'] = $options[$key]->tabcontentorder;
                $default_values['optionid[' . $key . ']'] = $tabids[$key];
            }
        }
    }

}
