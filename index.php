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
 * List of webctimports in course
 *
 * @package    mod
 * @subpackage webctimport
 * @copyright  2009 onwards Martin Dougiamas (http://dougiamas.com), 2011 The University of Nottingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/webctimport/locallib.php");

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'webctimport', 'view all', "index.php?id=$course->id", '');

$strurl       = get_string('modulename', 'mod_webctimport');
$strurls      = get_string('modulenameplural', 'mod_webctimport');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');
$strstatus = get_string('status', 'mod_webctimport');

$PAGE->set_url('/mod/webctimport/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strurls);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strurls);
echo $OUTPUT->header();

if (!$urls = get_all_instances_in_course('webctimport', $course)) {
    notice(get_string('thereareno', 'mod_webctimport'), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro, $strstatus);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro, $strstatus);
    $table->align = array ('left', 'left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';

$webctimporttoimport = null;

foreach ($urls as $url) {
	if ($webctimporttoimport==null && webctimport_can_import($url))
		$webctimporttoimport = $url;
    $cm = $modinfo->cms[$url->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($url->section !== $currentsection) {
            if ($url->section) {
                $printsection = get_section_name($course, $sections[$url->section]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $url->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($url->timemodified)."</span>";
    }

    $extra = empty($cm->extra) ? '' : $cm->extra;
    $icon = '';
    if (!empty($cm->icon)) {
        // each url has an icon in 2.0
        $icon = '<img src="'.$OUTPUT->pix_url($cm->icon).'" class="activityicon" alt="'.get_string('modulename', $cm->modname).'" /> ';
    }

    $class = $url->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed
    $table->data[] = array (
        $printsection,
        "<a $class $extra href=\"view.php?id=$cm->id\">".$icon.format_string($url->name)."</a>",
        format_module_intro('url', $url, $cm->id),
    	webctimport_get_status($url));
}

echo html_writer::table($table);

if ($webctimporttoimport!=null) {
	echo '<div>';
	echo '<p>Check/force import of '.$webctimporttoimport->name.'...</p>';

	// see also webctimport.php form_webctimport_render
	$url = new moodle_url('/mod/webctimport/status.php', array(
	        'ctx_id'=>$PAGE->context->id,
	        'course'=>$PAGE->course->id,
	        'sesskey'=>sesskey(),
	    	'webctfileid'=>$webctimporttoimport->webctfileid,
	    	'id'=>$webctimporttoimport->id,
	    	'courseindexid'=>$id,
	));
	
	echo webctimport_change_parent_url_helper();
	echo "<div><iframe src='$url' height='48' width='400' style='border:1px solid #000'>Sorry, cannot show status</iframe></div>";
	
	echo '</div>';
}

echo $OUTPUT->footer();
