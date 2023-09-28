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

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Class for the form of the tab
 */
class mod_tab_mod_form extends moodleform_mod {

    /**
     * The tab form
     * @global stdClass $CFG
     * @global moodle_database $DB
     */
    public function definition(): void {
        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name', 'tab'), ['size' => '45']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // Add Intro.
        $this->standard_intro_elements(false);

        $mform->setDefault('printintro', 0);
        $mform->setAdvanced('printintro', false);

        // Have to use this option for postgresqgl to work.
        $instance = $this->current->instance;
        if (empty($instance)) {
            $instance = 0;
        }

        // Following code used to create tabcontent order numbers.
        $optionid = optional_param_array('optionid', [], PARAM_INT);
        if (isset($optionid)) {
            $repeatnum = count($optionid);
        } else {
            $repeatnum = 0;
        }
        if ($repeatnum == 0) {
            $repeatnum = $DB->count_records('tab_content', ['tabid' => $instance]);
        }
        $taborder = 1; // Initialize to prevent warnings.
        for ($i = 1; $i <= $repeatnum + 1; $i++) {
            if ($i == 1) {
                $taborder = 1;
            } else {
                $taborder = $taborder . ',' . $i;
            }
        }
        $context = $this->context;

        $editoroptions = [
            'subdirs' => 1,
            'maxbytes' => $CFG->maxbytes,
            'maxfiles' => -1,
            'changeformat' => 1,
            'context' => $context,
            'noclean' => 1,
            'trusttext' => 1,
        ];

        $taborderarray = explode(',', $taborder);

        // ...*********************For adding tabs******************************
        $repeatarray = [];

        $repeatarray[] = $mform->createElement('header', 'tabs', get_string('tab', 'tab') . ' {no}');
        $repeatarray[] = $mform->createElement('text', 'tabname', get_string('tabname', 'tab'), ['size' => '65']);
        $repeatarray[] = $mform->createElement('editor', 'content', get_string('tabcontent', 'tab'), null, $editoroptions);
        $repeatarray[] = $mform->createElement('url', 'externalurl', get_string('externalurl', 'tab'),
            ['size' => '60'], ['usefilepicker' => true]);
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

        if ($this->_instance) {
            $repeatno = $DB->count_records('tab_content', ['tabid' => $instance]);
            $repeatno += 1;
        } else {
            $repeatno = 1;
        }

        $repeateloptions['tabcontentorder']['default'] = $i - 2;

        $repeateloptions['content']['helpbutton'] = ['content', 'tab'];

        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            'option_repeats',
            'option_add_fields',
            1,
            get_string('addtab', 'tab'),
        );
        // ...*********************************************************************************
        // ...*********************Display menu checkbox and name******************************
        // ...*********************************************************************************
        $mform->addElement('header', 'menu', get_string('displaymenu', 'tab'));
        $mform->addElement('advcheckbox', 'displaymenu', get_string('displaymenuagree', 'tab'), null, ['group' => 1], ['0', '1']);
        $mform->setType('displaymenu', PARAM_INT);
        $mform->addElement('text', 'taborder', get_string('taborder', 'tab'), ['size' => '15']);
        $mform->addElement('text', 'menuname', get_string('menuname', 'tab'), ['size' => '45']);

        $mform->setType('taborder', PARAM_INT);
        $mform->setType('menuname', PARAM_TEXT);

        // ...*********************************************************************************
        // ...*********************************************************************************

        $mform->setAdvanced('printintro');

        $this->standard_coursemodule_elements();

        // -------------------------------------------------------------------------------
        // Buttons.
        $this->add_action_buttons();
    }

    /**
     * The preprocessing data from the form
     * @param array $defaultvalues
     * @global moodle_database $DB
     * @global stdClass $CFG
     */
    public function data_preprocessing(&$defaultvalues): void {
        global $CFG, $DB;
        if ($this->current->instance) {
            $options = $DB->get_records('tab_content', ['tabid' => $this->current->instance], 'tabcontentorder');
            $tabids = array_keys($options);
            $options = array_values($options);
            $context = $this->context;
            $editoroptions = [
                'subdirs' => 1,
                'maxbytes' => $CFG->maxbytes,
                'maxfiles' => -1,
                'changeformat' => 1,
                'context' => $context,
                'noclean' => 1,
                'trusttext' => 1,
            ];
            foreach (array_keys($options) as $key) {
                $defaultvalues['tabname[' . $key . ']'] = $options[$key]->tabname;

                $draftitemid = file_get_submitted_draft_itemid('content[' . $key . ']');
                $defaultvalues['content[' . $key . ']']['format'] = $options[$key]->contentformat;
                $defaultvalues['content[' . $key . ']']['text'] = file_prepare_draft_area($draftitemid, $this->context->id,
                    'mod_tab', 'content', $options[$key]->id, $editoroptions, $options[$key]->tabcontent);
                $defaultvalues['content[' . $key . ']']['itemid'] = $draftitemid;

                $defaultvalues['externalurl[' . $key . ']'] = $options[$key]->externalurl;
                $defaultvalues['tabcontentorder[' . $key . ']'] = $options[$key]->tabcontentorder;
                $defaultvalues['optionid[' . $key . ']'] = $tabids[$key];
            }
        }
    }

}
