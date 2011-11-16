<?php

// handle treeview form submission - should always redirect?!

require_once("../../config.php");
require_once("../../course/lib.php");
require_once("../../lib/resourcelib.php");
//require_once("lib.php");
require_once($CFG->dirroot.'/mod/webctimport/locallib.php');

global $CFG;

//global $DB;
//debugging('treeviewsubmit.php...');

$courseid = required_param('course', PARAM_INT);
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

require_course_login($course, true);

//debugging('treeviewsubmit.php (2)...');

//$moduleid = required_param('module', PARAM_INT);
//$mod->coursemodule = required_param('coursemodule', PARAM_INT);
$sectionnumber = required_param('section', PARAM_INT);
//$mod->modulename = "webctimport";
$config = get_config('webctimport');

$webctimportmoduleid = $DB->get_record('modules', array('name'=>'webctimport'), '*', MUST_EXIST)->id;
$urlmoduleid = $DB->get_record('modules', array('name'=>'url'), '*', MUST_EXIST)->id;
$labelmoduleid = $DB->get_record('modules', array('name'=>'label'), '*', MUST_EXIST)->id;
$pagemoduleid = $DB->get_record('modules', array('name'=>'page'), '*', MUST_EXIST)->id;


//debugging('treeviewsubmit.php (3)...');

require_once("../label/lib.php");
require_once("../url/lib.php");

$indexes = array();
$items = array();
$minindent = 100;

foreach ($_POST as $key => $value) {
	//debugging('param '.$key.' = '.$value);
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
			$indent = count(explode('_',$index));
			if ($indent < $minindent)
				$minindent = $indent;
		}
	}
}
sort($indexes);

$labelindexes = array();

foreach ($indexes as $index) {
	$item = $items[$index];
	//debugging('add '.$item['type'].' '.$item['title']);
	$type = $item['type'];
    $mod = new stdClass();
    $mod->course = $courseid;
    $mod->section = $sectionnumber;
    $mod->indent = count(explode('_',$index))-$minindent;
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
		global $USER;
	    $mod->module = $webctimportmoduleid;
		$mod->name = $item['title'];
		if (isset($item['htmlwarnlevel'])) {
			if ($item['htmlwarnlevel']==WEBCTIMPORT_HTML_WARN_HIGH)
				$mod->name .= ' (NB: Broken Links)';
			else if ($item['htmlwarnlevel']==WEBCTIMPORT_HTML_WARN_MED)
				$mod->name .= ' (NB: Check Links)';
		}
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
		
		$mod->targettype = $config->importtype;
		$mod->owneruserid = $USER->id;
		
		$return = webctimport_add_instance($mod, null);
	}
	else if ($type=='h') {
		// HTML -> Page - fix me
		global $USER;
	    $mod->module = $pagemoduleid;
		$mod->name = $item['title'];
		if (isset($item['htmlwarnlevel'])) {
			if ($item['htmlwarnlevel']==WEBCTIMPORT_HTML_WARN_HIGH)
				$mod->name .= ' (NB: Broken Links)';
			else if ($item['htmlwarnlevel']==WEBCTIMPORT_HTML_WARN_MED)
				$mod->name .= ' (NB: Check Links)';
		}
		if (isset($item['description'])) {
			$mod->intro .= '<br>'.$item['description'];
		} else
			$mod->intro = null;
		$mod->modulename = 'webctimport';
		
		// defaults
		$mod->display = $config->display;
		// display options
		//$mod->popupwidth = $config->popupwidth;
		//$mod->popupheight = $config->popupwidth;
		//$mod->printheading = $config->printheading;
		//$mod->printintro = $config->printintro;

		$displayoptions = array();
		if ($mod->display == RESOURCELIB_DISPLAY_POPUP) {
			$displayoptions['popupwidth']  = $config->popupwidth;
			$displayoptions['popupheight'] = $config->popupheight;
		}
		$displayoptions['printheading'] = $config->printheading;
		$displayoptions['printintro']   = $config->printintro;
		$mod->displayoptions = serialize($displayoptions);
		
		//    $data->content       = $data->page['text'];
		$fileinfo = webctimport_get_file_info($item['path']);
		if (!$fileinfo || !isset($fileinfo->path)) {
			print_error('errorfindinghtmlcontent', 'mod_webctimport');
		}
		$path = webctimport_get_file_content_path($fileinfo->path);		
		try {
			$mod->content = file_get_contents($path);
		}
		catch (Exception $e) {
			debugging('reading '.$path.': '.$e);
			print_error('errorreadinghtmlcontent', 'mod_webctimport');
		}
    	//    $data->contentformat = $data->page['format'];
    	$mod->contentformat = FORMAT_HTML;
		// we don't want to use page_add_instance because it is not consistent with url_add_instance etc.
		// e.g. needs coursemodule in advance
		//$return = page_add_instance($mod, null);
    	$mod->timemodified = time();

    	$return = $mod->id = $DB->insert_record('page', $mod);
	}
	else {
 		debugging('unknown treeview type: '.$type.' - ignored!');
	}
	if ($return!=null) {
		$mod->instance = $return;
		// course_modules and course_sections each contain a reference
		// to each other, so we have to update one of them twice.
		if (! $mod->coursemodule = add_course_module($mod) ) {
			print_error('erroraddingcoursemodule', 'mod_webctimport');
		}

		if (! $sectionid = add_mod_to_section($mod) ) {
			print_error('erroraddingtosection', 'mod_webctimport');
		}

		if (! $DB->set_field("course_modules", "section", $sectionid, array("id" => $mod->coursemodule))) {
			print_error('errorupdatingcoursemodule', 'mod_webctimport');
		}
		set_coursemodule_visible($mod->coursemodule, true);
	}
}

rebuild_course_cache($mod->course);

?><html>
<head>
<title>Added items...</title>
<script type="text/javascript">
<?php echo webctimport_change_parent_url_call("$CFG->wwwroot/mod/webctimport/index.php?id=$mod->course"); ?>
</script>
</head>
<body>
</body>
</html>
