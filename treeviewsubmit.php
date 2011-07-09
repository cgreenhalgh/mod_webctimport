<?php

// handle treeview form submission - should always redirect?!

require_once("../../config.php");
require_once("../../course/lib.php");
require_once("../../lib/resourcelib.php");
require_once("lib.php");

global $CFG;

//global $DB;
debugging('treeviewsubmit.php...');

$courseid = required_param('course', PARAM_INT);
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

require_course_login($course, true);

debugging('treeviewsubmit.php (2)...');

//$moduleid = required_param('module', PARAM_INT);
//$mod->coursemodule = required_param('coursemodule', PARAM_INT);
$sectionnumber = required_param('section', PARAM_INT);
//$mod->modulename = "webctimport";
$config = get_config('webctimport');

$webctimportmoduleid = $DB->get_record('modules', array('name'=>'webctimport'), '*', MUST_EXIST)->id;
$urlmoduleid = $DB->get_record('modules', array('name'=>'url'), '*', MUST_EXIST)->id;
$labelmoduleid = $DB->get_record('modules', array('name'=>'label'), '*', MUST_EXIST)->id;


debugging('treeviewsubmit.php (3)...');

require_once("../label/lib.php");
require_once("../url/lib.php");

$indexes = array();
$items = array();

foreach ($_POST as $key => $value) {
	debugging('param '.$key.' = '.$value);
	if (strpos($key, 'index')===0) {
		//$key = urldecode($key);
		$atts = explode('&', $key);
		$item = array();
		foreach ($atts as $att) {
			if (($ix = strpos($att,'='))!==false) {
				$item[substr($att,0,$ix)] = urldecode(substr($att,$ix+1));					
			}
		}
		$index = $item['index'];
		if (isset($index)) {
			$indexes[] = $index;
			$items[$index] = $item;
			//debugging('Received '.$index.': '.$item);
		}
	}
}
sort($indexes);

$labelindexes = array();

foreach ($indexes as $index) {
	$item = $items[$index];
	debugging('add '.$item['type'].' '.$item['title']);
	$type = $item['type'];
    $mod = new stdClass();
    $mod->course = $courseid;
    $mod->section = $sectionnumber;
    $mod->indent = count(explode('_',$index));
	$return = null;
	if ($type=='l') {
		// label = folder
    	$mod->module = $labelmoduleid;
		$mod->name = null;
		$mod->intro = $item['title'];
		if (isset($item['description'])) {
			$mod->intro .= '<br>'.$item['description'];
		}
		$mod->modulename = 'label';

		$return = label_add_instance($mod);
	}
	else if ($type=='u') {
    	$mod->module = $urlmoduleid;
		$mod->name = $item['title'];
		if (isset($item['description'])) {
			$mod->intro .= '<br>'.$item['description'];
		} else
			$mod->intro = null;
		$mod->modulename = 'url';
		// shouldn't urldecode again
		$mod->externalurl = $item['url'];
		// defaults
		$mod->display = $config->display;
		// display options
		$mod->popupwidth = $config->popupwidth;
		$mod->popupheight = $config->popupwidth;
		$mod->printheading = $config->printheading;
		$mod->printintro = $config->printintro;
		
		$return = url_add_instance($mod, null);
	} 
	else if ($type=='f') {
	    $mod->module = $webctimportmoduleid;
		$mod->name = $item['title'];
		if (isset($item['description'])) {
			$mod->intro .= '<br>'.$item['description'];
		} else
			$mod->intro = null;
		$mod->modulename = 'webctimport';
		
		// urldecode? not again...
		$mod->localfilepath = $item['path'];
		$mod->error = null;
		$mod->owners = null;
		$mod->webctfileid = null;
		
		// defaults
		$mod->display = $config->display;
		// display options
		$mod->popupwidth = $config->popupwidth;
		$mod->popupheight = $config->popupwidth;
		$mod->printheading = $config->printheading;
		$mod->printintro = $config->printintro;
		
		$return = webctimport_add_instance($mod, null);
	}
	else {
 		debugging('unknown treeview type: '.$type.' - ignored!');
	}
	if ($return!=null) {
		$mod->instance = $return;
		// course_modules and course_sections each contain a reference
		// to each other, so we have to update one of them twice.
		if (! $mod->coursemodule = add_course_module($mod) ) {
			error("Could not add a new course module");
		}

		if (! $sectionid = add_mod_to_section($mod) ) {
			error("Could not add the new course module to that section");
		}

		if (! $DB->set_field("course_modules", "section", $sectionid, array("id" => $mod->coursemodule))) {
			error("Could not update the course module with the correct section");
		}
		set_coursemodule_visible($mod->coursemodule, true);
	}
}

rebuild_course_cache($mod->course);

// TODO ...
/*
foreach ($links as $link)
{
	$mod->name = $link["name"];
	$mod->intro = $link["description"];
	$mod->introformat = FORMAT_HTML;
	$mod->url = $link["url"];
	if (isset($link["activationUuid"]))
	{
		$mod->activation = $link["activationUuid"];
	}
	$return = equella_add_instance($mod);

	$mod->instance = $return;

	// course_modules and course_sections each contain a reference
	// to each other, so we have to update one of them twice.
	if (! $mod->coursemodule = add_course_module($mod) ) {
		error("Could not add a new course module");
	}

	if (! $sectionid = add_mod_to_section($mod) ) {
		error("Could not add the new course module to that section");
	}

	if (! $DB->set_field("course_modules", "section", $sectionid, array("id" => $mod->coursemodule))) {
		error("Could not update the course module with the correct section");
	}
	set_coursemodule_visible($mod->coursemodule, true);
}

rebuild_course_cache($mod->course);
*/

?><html>
<head>
<title>Added items...</title>
<script>
window.parent.document.location = '<?php print "$CFG->wwwroot/course/view.php?id=$mod->course" ?>';
</script>
</head>
<body>
</body>
</html>
