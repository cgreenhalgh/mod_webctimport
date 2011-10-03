<?php
// return a JSON-encoded list for the specified path from the
// webctimport cache - top-level contexts only, for adding permissions, falling back to 
// directory structure

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
	print_error('getcontextpath','mod_webctimport', $path);
	return;
}

$jsontext = file_get_contents($rootfolderpath.$path.'get_listing.json');
if ($jsontext==false) {
	debugging('Not found: get_listing from '.$rootfolderpath.' '.$path);
	// fall back to directory listing...
	echo '{"error":"File not found - the WebCT cache may be incomplete"}';
}
else {
	try {
		// filter...
		$json = json_decode($jsontext);
		$list = array();
		foreach ($json->list as $item) {
			if ($item->webcttype=='Institution' || $item->webcttype=='Course' || $item->webcttype=='Section')
				$list[] = $item;
		}
		$json->list = $list;
		//debugging('get_listing from '.$rootfolderpath.$path.' -> '.$jsontext);
		echo json_encode($json);
	}
	catch  (Exception $e) {
		print_error('getcontextjson','mod_webctimport', $e->getMessage());
	}
}
