<?php
// return a JSON-encoded list for the specified path from the
// webctimport cache.

require_once("../../config.php");

require_login();

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

if ($path=='/' || strlen($path)==0) {
	global $USER;
	// user-specific root
	$username = $USER->username;
	if (strlen($username)<3) {
		print_error('cannot return root for short username '.$username);
		return;
	}
	$path = '/user/'.substr($username, 0, 2).'/'.substr($username, 0, 3).'/'.$username.'/';
	debugging('get_listing of root for '.$username.' -> '.$path);
} 

$jsontext = file_get_contents($rootfolderpath.$path.'get_listing.json');
if ($jsontext==false) {
	debugging('Not found: get_listing from '.$rootfolderpath.' '.$path);
	echo '{"error":"File not found"}';
}
else {
	debugging('get_listing from '.$rootfolderpath.$path.' -> '.$jsontext);
	echo $jsontext;
}
