<?php
// return a JSON-encoded list for the specified path from the
// webctimport cache.

require_once("../../config.php");
require_once("$CFG->dirroot/mod/webctimport/locallib.php");

require_login();
$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);

$path = required_param('path', PARAM_PATH); // directory path

$config = get_config('webctimport');
$rootfolderpath = $config->rootfolderpath;

if (substr($rootfolderpath, -1)=='/') {
	$rootfolderpath = substr($rootfolderpath, 0, strlen($rootfolderpath)-1);
}
//debugging('get_listing from '.$rootfolderpath.' '.$path);
if (strpos($path, '../')===0 || strpos($path, '/../')!==false) {
	print_error('cannot return path including ../: '.$path);
	return;
}

$toplevel = 0;
if ($path=='/' || strlen($path)==0) {
	global $USER;
	// user-specific root
	$username = $USER->username;
	if (strlen($username)<3) {
		print_error('cannot return root for short username '.$username);
		return;
	}
	$path = '/user/'.substr($username, 0, 2).'/'.substr($username, 0, 3).'/'.$username.'/';
	//debugging('get_listing of root for '.$username.' -> '.$path);
	$toplevel = 1;
} 

$jsontext = file_get_contents($rootfolderpath.$path.'get_listing.json');
if ($jsontext==false) {
	debugging('Not found: get_listing from '.$rootfolderpath.' '.$path);
	if (!$toplevel) {
		echo '{"error":"File not found"}';
//		echo '{"error":"This user does not have any files from WebCT"}';
		return;
	}
}
try {
	if ($jsontext==false) {
		// synthentic get_listing...
		$json = new stdClass();
		$json->path = array();
		$pathel = new stdClass();
		$pathel->name = 'WebCT/grant ('.$USER->username.')';
		$pathel->path = '/';
		$json->path[] = $pathel;
		$json->nologin = true;
		$json->list = array();
	}
	else
		$json = json_decode($jsontext);
}
catch (Exception $e) {
	debugging('get_listing, decode: '.$e->getMessage());
	echo $jsontext;
}

if ($toplevel) {
	$grants = $DB->get_records('webctgrant',array('userid'=>$USER->id));
	try {
		foreach ($grants as $grant) {
			$item = new stdClass();
			$item->title = $grant->title.' (granted access)';
			$item->description = $grant->description;
			$item->webcttype = $grant->webcttype;
			$item->size = 0;
			$item->children = array();
			$item->path = $grant->path;
			$json->list[] = $item;
		}
	}
	catch (Exception $e) {
		debugging('get_listing, extra grants: '.$e->getMessage());
		echo $jsontext;
	}
}

foreach ($json->list as $item) {
	webctimport_get_item_extra_info($item);
}
// files area to end?
if (count($json->list)>1 && $json->list[0]->webcttype=='Template/Default') {
	$files = $json->list[0];
	unset($json->list[0]);
	$json->list[] = $files;	
}

echo json_encode($json);
