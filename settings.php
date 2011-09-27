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
 * webctimport module admin settings and defaults
 *
 * @package    mod
 * @subpackage webctimport
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}, 2011 The University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");
	require_once("$CFG->dirroot/mod/webctimport/lib.php");

	//--- module settings -----------------------------------------------------------------------------------
	$link ='<a href="'.$CFG->wwwroot.'/mod/webctimport/users.php">'.get_string('configmodeditusers', 'mod_webctimport').'</a>';
	$settings->add(new admin_setting_heading('mod_webctimport/users', '', $link));
	
    $settings->add(new admin_setting_configdirectory('webctimport/rootfilepath',
        get_string('rootfilepath', 'webctimport'), get_string('configrootfilepath', 'webctimport'), "/webctimport/files"));

    $settings->add(new admin_setting_configdirectory('webctimport/rootfolderpath',
        get_string('rootfolderpath', 'webctimport'), get_string('configrootfolderpath', 'webctimport'), "/webctimport/folders"));

    $settings->add(new admin_setting_configselect('webctimport/importtype',
        get_string('importtype', 'webctimport'), get_string('configimporttype', 'webctimport'), 
        WEBCTIMPORT_FILE,
        array(WEBCTIMPORT_NONE => get_string('importtypenone','webctimport'),
        	WEBCTIMPORT_FILE => get_string('importtypefile','webctimport'),
        	WEBCTIMPORT_EQUELLA => get_string('importtypeequella','webctimport'))
        ));
        
    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_EMBED,
                                   RESOURCELIB_DISPLAY_OPEN,
                                   RESOURCELIB_DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('webctimportmodeditgeneral', get_string('modeditgeneral', 'webctimport'), get_string('configmodeditgeneral', 'webctimport')));
                                  
    $settings->add(new admin_setting_configtext('webctimport/framesize',
        get_string('framesize', 'webctimport'), get_string('configframesize', 'webctimport'), 130, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('webctimport/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configmultiselect('webctimport/displayoptions',
        get_string('displayoptions', 'webctimport'), get_string('configdisplayoptions', 'webctimport'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('webctimportmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox_with_advanced('webctimport/printheading',
        get_string('printheading', 'webctimport'), get_string('printheadingexplain', 'webctimport'),
        array('value'=>0, 'adv'=>false)));
    $settings->add(new admin_setting_configcheckbox_with_advanced('webctimport/printintro',
        get_string('printintro', 'webctimport'), get_string('printintroexplain', 'webctimport'),
        array('value'=>1, 'adv'=>false)));
    $settings->add(new admin_setting_configselect_with_advanced('webctimport/display',
        get_string('displayselect', 'webctimport'), get_string('displayselectexplain', 'webctimport'),
        array('value'=>RESOURCELIB_DISPLAY_AUTO, 'adv'=>false), $displayoptions));
    $settings->add(new admin_setting_configtext_with_advanced('webctimport/popupwidth',
        get_string('popupwidth', 'webctimport'), get_string('popupwidthexplain', 'webctimport'),
        array('value'=>620, 'adv'=>true), PARAM_INT, 7));
    $settings->add(new admin_setting_configtext_with_advanced('webctimport/popupheight',
        get_string('popupheight', 'webctimport'), get_string('popupheightexplain', 'webctimport'),
        array('value'=>450, 'adv'=>true), PARAM_INT, 7));

}
