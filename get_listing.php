<?php
// return a JSON-encoded list for the specified path from the
// webctimport cache.

require_once("../../config.php");

require_login();

$path = required_param('path', PARAM_PATH); // directory path

$config = get_config('webctimport');
$rootfilepath = $config->rootfilepath;

if (substr($rootfilepath, -1)=='/') {
	$rootfilepath = substr($rootfilepath, 0, strlen($rootfilepath)-1);
}
//debugging('get_listing from '.$rootfilepath.' '.$path);

$jsontext = file_get_contents($rootfilepath.$path.'get_listing.json');
if ($jsontext==false) {
	debugging('Not found: get_listing from '.$rootfilepath.' '.$path);
	echo '{"error":"File not found"}';
}
else {
	debugging('get_listing from '.$rootfilepath.$path.' -> '.$jsontext);
	echo $jsontext;
}
