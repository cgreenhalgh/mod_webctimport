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
 * Webct Import form element
 */

global $CFG;

require_once('HTML/QuickForm/element.php');

class MoodleQuickForm_webctimport extends HTML_QuickForm_element {
    public $_helpbutton = '';
	private $_webctimportId;
	
    function MoodleQuickForm_webctimport($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        parent::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
    }

    function setName($name) {
        $this->updateAttributes(array('name'=>$name));
    }

    function getName() {
        return $this->getAttribute('name');
    }

    function setValue($value) {
        $this->updateAttributes(array('value'=>$value));
    }

    function getValue() {
        return $this->getAttribute('value');
    }
    
    function setWebctimportId($value) {
    	$this->_webctimportId = $value;
    }

    function setHelpButton($helpbuttonargs, $function='helpbutton'){
        debugging('component setHelpButton() is not used any more, please use $mform->setHelpButton() instead');
    }

    function getHelpButton() {
        return $this->_helpbutton;
    }

    function getElementTemplateType() {
        if ($this->_flagFrozen){
            return 'nodisplay';
        } else {
            return 'default';
        }
    }

    function toHtml() {

        // security - never ever allow guest/not logged in user to upload anything or use this element!
        if (isguestuser() or !isloggedin()) {
            print_error('noguest');
        }

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }

        $id          = $this->_attributes['id'];
        $elname      = $this->_attributes['name'];
		$value       = $this->getValue();
		//debugging('webctimport element value='.$value);
				
        //$client_id = uniqid();
		$options = new stdClass();
		//$options->client_id = $client_id;
		$options->value = $value;
		$options->webctimportId = $this->_webctimportId;
		
        // filemanager options
        $html = $this->_getTabs();
        $html .= form_webctimport_render($options);

        $html .= '<input value="'.$value.'" name="'.$elname.'" type="hidden" />';

        return $html;
    }
}

function form_webctimport_render($options) {
	global $PAGE;
	
    $url = new moodle_url('/mod/webctimport/status.php', array(
        'ctx_id'=>$PAGE->context->id,
        'course'=>$PAGE->course->id,
        'sesskey'=>sesskey(),
    	'webctfileid'=>$options->value,
    	'id'=>$options->webctimportId,
        ));
	
    $html = "<div><object type='text/html' data='$url' height='48' width='400' style='border:1px solid #000'></object></div>";

    return $html;
}
