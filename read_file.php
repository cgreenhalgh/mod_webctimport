<?php
// return a file from the webctimport cache.

require_once("../../config.php");
require_once("../../lib/filelib.php");

require_login();

$path = required_param('path', PARAM_PATH); // directory path

$config = get_config('webctimport');
$rootfilepath = $config->rootfilepath;

if (substr($rootfilepath, -1)=='/') {
	$rootfilepath = substr($rootfilepath, 0, strlen($rootfilepath)-1);
}

if (strpos($path, '../')===0 || strpos($path, '/../')!==false) {
	error('cannot return path including ../: '.$path);
	return;
}

$path = $rootfilepath.$path;
$ix = strrpos($path, '/');
if ($ix!==false)
	$filename = substr($path, $ix+1);
else
	$filename = $path;

debugging('get_file '.$path);

send_file($path, urldecode($filename));
