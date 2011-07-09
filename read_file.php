<?php
// return a file from the webctimport cache - look for /file.json and redirect to read_rawfile with path

require_once("../../config.php");
require_once("../../lib/filelib.php");
require_once("locallib.php");

require_login();

$path = required_param('path', PARAM_PATH); // directory path

try {
	// will check path etc.
	$info = webctimport_get_file_info($path);
} catch (Exception $e) {
	print_error($e->getMessage());
	return;
}
	
$rawpath = $info->path;
$filename = $info->filename;
if (empty($rawpath)) {
	print_error('cannot find path in file information: '.$path);
	return;
}
debugging('get_file redirects to '.$path.' -> '.$rawpath.' (filename '.$filename.')');
redirect($CFG->wwwroot.'/mod/webctimport/read_rawfile.php?path='.urlencode($rawpath).'&filename='.urlencode($filename));
