<?php

// This file is part of moodle webctimport module 
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
 * URL configuration form
 *
 * @package    mod
 * @subpackage webctimport
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}, 2011 The University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/webctimport/locallib.php');

class mod_webctimport_mod_form extends moodleform_mod {
	var $form;
	var $previewelement;
	var $previewurl;
	
	function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        $config = get_config('webctimport');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $this->add_intro_editor($config->requiremodintro);

        //-------------------------------------------------------
        $mform->addElement('header', 'content', get_string('contentheader', 'webctimport'));
        $this->previewelement = $mform->addElement('static', 'previewlink', get_string('clicktopreview', 'webctimport', "(Preview)"));
//        $mform->addElement('html', 
//'<div id="checkstatus">Requires Javascript</div><script type="text/javascript">YUI().use("node", function(Y) { alert("hi!"); Y.one("#checkstatus").replace(Y.Node.create("<div>Checking...</div>")); });</script>');
        // TODO - hmm, how do we preview here - the object isn't known at this point
//        $mform->addElement('html', '<div class="urlworkaround">');
//        $previewurl = webctimport_get_preview_url($webctimport);
//    	$mform->addElement('html', get_string('clicktopreview', 'webctimport', "<a href=\"$previewurl\" target=\"_blank\">Preview</a>"));
//		$mform->addElement('html', '</div>');
        //$mform->addElement('url', 'externalurl', get_string('externalurl', 'url'), array('size'=>'60'), array('usefilepicker'=>true));
        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('optionsheader', 'webctimport'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'webctimport'), $options);
            $mform->setDefault('display', $config->display);
            $mform->setAdvanced('display', $config->display_adv);
            $mform->addHelpButton('display', 'displayselect', 'webctimport');
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'webctimport'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', $config->popupwidth_adv);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'webctimport'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', $config->popupheight_adv);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_AUTO, $options) or
          array_key_exists(RESOURCELIB_DISPLAY_EMBED, $options) or
          array_key_exists(RESOURCELIB_DISPLAY_FRAME, $options)) {
            $mform->addElement('checkbox', 'printheading', get_string('printheading', 'webctimport'));
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printheading', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printheading', $config->printheading);
            $mform->setAdvanced('printheading', $config->printheading_adv);

            $mform->addElement('checkbox', 'printintro', get_string('printintro', 'webctimport'));
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->disabledIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printintro', $config->printintro);
            $mform->setAdvanced('printintro', $config->printintro_adv);
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }

    function set_data($data)
	{
		$this->form = $data;
		if (isset($data->localfilepath))
			$this->previewurl = "$CFG->wwwroot/mod/webctimport/read_file.php?path=".urlencode($data->localfilepath);
		parent::set_data($data);
	}
    /**
     * override if you need to setup the form depending on current
     * values. This method is called after definition(), data submission and set_data().
     * All form setup that is dependent on form values should go in here.
     */
    function definition_after_data() {
    	if ($this->previewurl)
	    	$this->previewelement->setLabel(get_string('clicktopreview', 'webctimport', 
		    	"<a href=\"$this->previewurl\" target=\"_blank\">Preview</a>"));
    }

	
    /** display special case - add doesn't really add a single item, it starts the tree browser! */
   	function display()
	{
		global $CFG,$USER;
		$form = $this->form;
		if (isset($form->add))
		{
			$url = $CFG->wwwroot.'/mod/webctimport/treeview.php'
				. '?sesskey='.urlencode($USER->sesskey)
				. '&course='.urlencode($form->course)
				. '&coursemodule='.urlencode($form->coursemodule)
				. '&section='.urlencode($form->section)
				. '&module='.urlencode($form->module)
				. '&modulename='.urlencode($form->modulename)
				. '&instance='.urlencode($form->instance);

			echo resourcelib_embed_general($url, null, 'WebCT import browser should open here!', 'text/html');
		}
		else
		{
			parent::display();
		}
	}
    
}
