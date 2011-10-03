<?php

// Tree view - presented embedded in add webctimport page

require_once("../../config.php");
require_once("../../course/lib.php");
require_once("lib.php");

//global $DB;

require_login();
$mod->course = required_param('course', PARAM_INT);
$mod->module = required_param('module', PARAM_INT);
$mod->coursemodule = required_param('coursemodule', PARAM_INT);
$mod->section = required_param('section', PARAM_INT);
$mod->modulename = "webctimport";

$urlparams = (array)$mod;
$baseurl = new moodle_url('/mod/webctimport/importorgdata.php', $urlparams);
$PAGE->set_url($baseurl);

$course = $DB->get_record('course', array('id'=>$mod->course), '*', MUST_EXIST);
require_course_login($course, true);
//$context = get_context_instance(CONTEXT_COURSE, $mod->course);
$PAGE->set_course($course);
//$PAGE->set_context(get_course_context($course));

$PAGE->set_pagelayout('popup');

//$PAGE->set_url('/mod/webctimport/index.php', array('id' => $course->id));
//$PAGE->set_title($course->shortname.': '.$strurls);
//$PAGE->set_heading($course->fullname);
//$PAGE->navbar->add($strurls);
global $USER;
$PAGE->requires->js_init_call('M.mod_webctimport.init_treeview', array($USER->username, '/'));
echo $OUTPUT->header();

?>
<h3>Select item(s) to import from WebCT</h3>
<form action="treeviewsubmit.php" method="POST">
<?php 
	echo '<input type="hidden" name="course" value="'.$mod->course.'">';
	echo '<input type="hidden" name="module" value="'.$mod->module.'">';
	echo '<input type="hidden" name="coursemodule" value="'.$mod->coursemodule.'">';
	echo '<input type="hidden" name="section" value="'.$mod->section.'">';
	// sessKey? modulename? instance?
?>
<ul class="mod_webctimport_list" id="treeview_root">
	<li>...</li>
</ul>
<input type="submit" value="Add selected items">
</form>
<?php 
echo $OUTPUT->footer();
?>