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
 * webctimport module upgrade code
 *
 * This file keeps track of upgrades to
 * the resource module
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package    mod
 * @subpackage url
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_webctimport_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2011080301) {

    	// webctfile table hasn't been used yet - just drop and re-create
        $table = new xmldb_table('webctfile');
    	
        // Conditionally launch drop table for webctfile
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Define table webctfile to be created
        $table = new xmldb_table('webctfile');

        // Adding fields to table webctfile
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('workerid', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('workertimestamp', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('localfilepath', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('webctpath', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('error', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('owneruserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);

        // Adding keys to table webctfile
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table webctfile
        $table->add_index('webctpathindex', XMLDB_INDEX_NOTUNIQUE, array('webctpath (255)'));

        // Conditionally launch create table for webctfile
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // webctimport table has been used...
        // Define table webctimport to be created
        $table = new xmldb_table('webctimport');

	    // drop and re-create unused column webctfileid
        $field = new xmldb_field('webctfileid');
        // Conditionally launch drop field webctfileid
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('webctfileid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'timemodified');
        // Conditionally launch add field webctfileid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        //$table->add_field('targettype', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $field = new xmldb_field('targettype', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'webctfileid');
        // Conditionally launch add field targettype
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // drop columns error, metadata & owners
        $field = new xmldb_field('error');
        // Conditionally launch drop field error
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('metadata');
        // Conditionally launch drop field metadata
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new xmldb_field('owners');
        // Conditionally launch drop field owners
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // webctimport savepoint reached
        upgrade_mod_savepoint(true, 2011080301, 'webctimport');
    }
    
    if ($oldversion < 2011080302) {

        // Define table webctimport to be created
        $table = new xmldb_table('webctimport');
    	
        $field = new xmldb_field('owneruserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        // Conditionally launch add field targettype
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // webctimport savepoint reached
        upgrade_mod_savepoint(true, 2011080302, 'webctimport');        
    }
    
    if ($oldversion < 2011101001) {
    
    	// Define table webctgrant to be created
    	$table = new xmldb_table('webctgrant');
    
    	// Adding fields to table webctgrant
    	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    	$table->add_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
    	$table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
    	$table->add_field('path', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('granted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
    	$table->add_field('grantedby', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
    	$table->add_field('webcttype', XMLDB_TYPE_CHAR, '100', null, null, null, null);
    	$table->add_field('filesize', XMLDB_TYPE_INTEGER, '12', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
    
    	// Adding keys to table webctgrant
    	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    
    	// Conditionally launch create table for webctgrant
    	if (!$dbman->table_exists($table)) {
    		$dbman->create_table($table);
    	}
    
    	// webctimport savepoint reached
    	upgrade_mod_savepoint(true, 2011101001, 'webctimport');
    }
    
    return true;
}
