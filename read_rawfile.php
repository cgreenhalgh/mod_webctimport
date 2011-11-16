<?php
// return a file from the webctimport cache.

require_once("../../config.php");
require_once("../../lib/filelib.php");

require_once($CFG->dirroot.'/mod/webctimport/locallib.php');

require_login();

$path = required_param('path', PARAM_PATH); // directory path
$filename = required_param('filename', PARAM_FILE); // filename
$mimetype = optional_param('mimetype', '', PARAM_TEXT); // mimetype

try {
	// will check path etc.
	$path = webctimport_get_file_content_path($path);
} catch (Exception $e) {
	print_error($e->getMessage());
	return;
}
//$ix = strrpos($path, '/');
//if ($ix!==false)
//	$filename = substr($path, $ix+1);
//else
//	$filename = $path;

//debugging('get_file '.$path);
//send_file($path, $filename, $lifetime = 'default' , $filter=0, $pathisstring=false, $forcedownload=false, $mimetype='')
send_file($path, urldecode($filename), 'default', 0, false, false, $mimetype);
