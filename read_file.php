<?php
// return a file from the webctimport cache - look for /file.json and redirect to read_rawfile with path

require_once("../../config.php");
require_once("../../lib/filelib.php");

require_login();

$path = required_param('path', PARAM_PATH); // directory path

$config = get_config('webctimport');
$rootfolderpath = $config->rootfolderpath;

if (substr($rootfolderpath, -1)=='/') {
	$rootfolderpath = substr($rootfolderpath, 0, strlen($rootfolderpath)-1);
}

if (strpos($path, '../')===0 || strpos($path, '/../')!==false) {
	print_error('cannot return path including ../: '.$path);
	return;
}

$path = $rootfolderpath.$path.'/file.json';

debugging('get_file '.$path);

$json = file_get_contents($path);
if ($json===FALSE) {
	print_error('cannot find requested file information: '.$path);
	return;
}
$info = json_decode($json);
$rawpath = $info->path;
$filename = $info->filename;
if (empty($rawpath)) {
	print_error('cannot find path in file information: '.$path);
	return;
}
debugging('get_file redirects to '.$path.' -> '.$rawpath.' (filename '.$filename.')');
redirect($CFG->wwwroot.'/mod/webctimport/read_rawfile.php?path='.urlencode($rawpath).'&filename='.urlencode($filename));
