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
 * Private webctimport module utility functions
 *
 * @package    mod
 * @subpackage webctimport
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/webctimport/lib.php");

require_once("$CFG->dirroot/mod/url/locallib.php");


/**
 * Print url header.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return void
 */
function webctimport_print_header($url, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$url->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($url);
    echo $OUTPUT->header();
}

/**
 * Print url heading.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function webctimport_print_heading($url, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);

    if ($ignoresettings or !empty($options['printheading'])) {
        echo $OUTPUT->heading(format_string($url->name), 2, 'main', 'urlheading');
    }
}

/**
 * Print url introduction.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function webctimport_print_intro($url, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($url->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'urlintro');
            echo format_module_intro('url', $url, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

function webctimport_get_preview_url($url) {
    $previewurl = "read_file.php?path=".urlencode($url->localfilepath);
	return $previewurl;
}
/**
 * Print url info and link.
 * @param object $url
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function webctimport_print_workaround($url, $cm, $course) {
    global $OUTPUT;

    webctimport_print_header($url, $cm, $course);
    webctimport_print_heading($url, $cm, $course, true);
    webctimport_print_intro($url, $cm, $course, true);

    $previewurl = webctimport_get_preview_url($url);
    echo '<div class="urlworkaround">';
    print_string('clicktopreview', 'webctimport', "<a href=\"$previewurl\" target=\"_blank\">Preview</a>");
    echo '</div>';

    if ($url->webctfileid!==null) {
	    require_once("form/webctimport.php");
    	$options = new stdClass();
	    $options->value = $url->webctfileid;
	    $options->webctimportId = $url->id;
    	echo form_webctimport_render($options);
    }
    
    echo $OUTPUT->footer();
    die;
}

/**
 * Optimised mimetype detection from general URL
 * @param $fullurl
 * @return string mimetype
 */
function webctimport_guess_icon($fullurl) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if (substr_count($fullurl, '/') < 3 or substr($fullurl, -1) === '/') {
        // most probably default directory - index.php, index.html, etc.
        return 'f/web';
    }

    $icon = mimeinfo('icon', $fullurl);
    $icon = 'f/'.str_replace(array('.gif', '.png'), '', $icon);

    if ($icon === 'f/html' or $icon === 'f/unknown') {
        $icon = 'f/web';
    }

    return $icon;
}

/**
 *  add a webctfile record for this webctimport record
 * @param object $data webctimport record dataobject
 * @return int id of new record */
function webctimport_add_webctfile($data) {
	global $USER, $DB;
	$file = new stdClass();
    $file->localfilepath = $data->localfilepath;
    try {
    	$fileinfo = webctimport_get_file_info($file->localfilepath);
    	if (isset($fileinfo->webctpath))
	    	$file->webctpath = $fileinfo->webctpath;
	    else 
			debugging('file.json did not have webctpath for '.$data->localfilepath);    	
	    
    }
    catch (Exception $e) {
		debugging('Could not get file.json for '.$data->localfilepath.' ('.$e->getMessage().')');    	
    }
    $file->status = WEBCTIMPORT_STATUS_NEW;
    if (isset($data->owneruserid))
	    $file->owneruserid = $data->owneruserid;
	else
		$file->owneruserid = $USER->id;
   	
	$file->id = $DB->insert_record('webctfile', $file);	

   	return $file->id;	
}


/** get file.json info from given cache path */
function webctimport_get_file_info($path) {
	$config = get_config('webctimport');
	$rootfolderpath = $config->rootfolderpath;

	if (substr($rootfolderpath, -1)=='/') {
		$rootfolderpath = substr($rootfolderpath, 0, strlen($rootfolderpath)-1);
	}

	if (strpos($path, '../')===0 || strpos($path, '/../')!==false) {
		throw new Exception('cannot return path including ../: '.$path);
	}

	$path = $rootfolderpath.$path.'/file.json';

	debugging('get_file '.$path);

	$json = file_get_contents($path);
	if ($json===FALSE) {
		throw new Exception('cannot find requested file information: '.$path);
	}
	$info = json_decode($json);
	
	return $info;
}
function webctimport_get_file_content_path($path) {
	$config = get_config('webctimport');
	$rootfilepath = $config->rootfilepath;

	if (substr($rootfilepath, -1)=='/') {
	$rootfilepath = substr($rootfilepath, 0, strlen($rootfilepath)-1);
	}

	if (strpos($path, '../')===0 || strpos($path, '/../')!==false) {
		throw new Exception('cannot return path including ../: '.$path);
	}

	$path = $rootfilepath.$path;
	return $path;
}
