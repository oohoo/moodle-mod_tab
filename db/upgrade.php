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

/**
 * This function is run when the plugin have to be updated
 * @global stdClass $CFG
 * @global moodle_database $DB
 * @param int $oldversion The older version of the plugin installed on the moodle
 * @return boolean True if the update passed successfully
 */
function xmldb_tab_upgrade($oldversion = 0)
{

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2010120501)
    {
        //I changed the menu css. So let's upgrade the code
        $sql = "UPDATE {tab} SET menucss = ?";
        $params = array('
		#tab-menu-wrapper {
                    float: left;
                    width: 20%;

		}

                #tabcontent {
                    margin-left:  20%;
                    padding: 0 10px;

		}

		.menutable {
			border: 1px solid #808080;
		}
		.menutitle {
			background:#2647a0 url(../../lib/yui/2.8.1/build/assets/skins/sam/sprite.png) repeat-x left -1400px;
			color:#fff;
		}
		.row {
			background-color: #CFCFCF;
		}
		');

        $DB->execute($sql, $params);

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2010120501, 'tab');
    }

    if ($oldversion < 2010120900)
    {
        //This version empplies that the view.php file has been modified
        //No modificsations to the DB have been done
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2010120900, 'tab');
    }

    if ($oldversion < 2010120901)
    {

        // Define field css to be dropped from tab
        $table = new xmldb_table('tab');
        $field = new xmldb_field('css');
        $field2 = new xmldb_field('menucss');

        // Conditionally launch drop field css
        if ($dbman->field_exists($table, $field))
        {
            $dbman->drop_field($table, $field);
        }
        // Conditionally launch drop field css
        if ($dbman->field_exists($table, $field2))
        {
            $dbman->drop_field($table, $field2);
        }
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2010120901, 'tab');
    }

    if ($oldversion < 2011040200)
    {

        // Define field pdffile to be added to tab_content
        $table = new xmldb_table('tab_content');
        $field = new xmldb_field('pdffile', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'tabcontentorder');
        $field2 = new xmldb_field('urlembed', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'pdffile');

        // Conditionally launch add field pdffile
        if (!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table, $field2))
        {
            $dbman->add_field($table, $field2);
        }

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011040200, 'tab');
    }

    if ($oldversion < 2011040201)
    {

        // Rename field externalurl on table tab_content to NEWNAMEGOESHERE
        $table = new xmldb_table('tab_content');
        $field = new xmldb_field('urlembed', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'pdffile');

        // Launch rename field externalurl
        $dbman->rename_field($table, $field, 'externalurl');

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011040201, 'tab');
    }

    if ($oldversion < 2011041300)
    {

        // Rename field externalurl on table tab_content to NEWNAMEGOESHERE
        //Changes where done in the view.php file
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011041300, 'tab');
    }

    if ($oldversion < 2011071100)
    {

        // Define field id to be dropped from tab_content
        $table = new xmldb_table('tab_content');
        $field = new xmldb_field('pdffile');

        // Conditionally launch drop field id
        if ($dbman->field_exists($table, $field))
        {
            $dbman->drop_field($table, $field);
        }

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011071100, 'tab');
    }
    if ($oldversion < 2011071101)
    {

        // Changing nullability of field tabcontentorder on table tab_content to null
        $table = new xmldb_table('tab_content');
        $field = new xmldb_field('tabcontentorder', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, '1', 'tabcontent');

        // Launch change of nullability for field tabcontentorder
        $dbman->change_field_notnull($table, $field);

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011071101, 'tab');
    }
    if ($oldversion < 2011080800)
    {

        // Fixed two undefined variables
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011080800, 'tab');
    }
    if ($oldversion < 2011081000)
    {

        // Added PDF embedding
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011081000, 'tab');
    }
    if ($oldversion < 2011082300)
    {

        // Define field intro to be added to tab
        $table = new xmldb_table('tab');
        $field = new xmldb_field('intro', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'course');
        $field2 = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'timemodified');

        // Conditionally launch add field intro
        if (!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table, $field2))
        {
            $dbman->add_field($table, $field2);
        }

        // tab savepoint reached
        upgrade_mod_savepoint(true, 2011082300, 'tab');
    }

    if ($oldversion < 2012090600)
    {
        // Add acess addinstance for moodle 2.3 compatibility
        upgrade_mod_savepoint(true, 2012090600, 'tab');
    }

    if ($oldversion < 2012101700)
    {
        // Correction for the recent files
        upgrade_mod_savepoint(true, 2012101700, 'tab');
    }

    if ($oldversion < 2012120400)
    {
        // Correction for flash files that did not appear in the tabs
        upgrade_mod_savepoint(true, 2012120400, 'tab');
    }

    if ($oldversion < 2012121000)
    {
        // Correction for flash files that did not appear in the tabs
        upgrade_mod_savepoint(true, 2012121000, 'tab');
    }

    if ($oldversion < 2012121200)
    {
        // + Add description available
        // + Add PDF embed in a tab
        
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2012121200, 'tab');
    }
    
    if ($oldversion < 2013010200)
    {
        // + Correction in the JS for an old YAHOO Code
        
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2013010200, 'tab');
    }
    
    if ($oldversion < 2013021200)
    {
        // + Add the RTL support. Thanks to nadavkav
        
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2013021200, 'tab');
    }
    
    if ($oldversion < 2013021201)
    {
        // + Correction on the tab content to allow to put custom HTML like object tag, etc. => change the settype from TYPE_CLEAN to TYPE_RAW
        
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2013021201, 'tab');
    }
    
    if ($oldversion < 2013032800)
    {
        // + Changes on the logs
        
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2013032800, 'tab');
    }
    
    if ($oldversion < 2013062500)
    {
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2013062500, 'tab');
    }
    
    if ($oldversion < 2013072200)
    {
        //+Patch on backups
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2013072200, 'tab');
    }
    
    if ($oldversion < 2013072400)
    {
        //+Patch on filters
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2013072400, 'tab');
    }
    
    if ($oldversion < 2014040200)
    {
        //+ Moodle 2.6 Update
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2014040200, 'tab');
    }
    if ($oldversion < 2016053102)
    {
        //+ Moodle 3.0 Update
        // tab savepoint reached
        upgrade_mod_savepoint(true, 2016053102, 'tab');
    }
}