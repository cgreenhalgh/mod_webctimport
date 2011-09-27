<?php 
// View/manage additional user access to modules exported from WebCT.
// Basis is a list of all users, with additional access shown (from webctgrant).

require_once('../../config.php');

require_login();

$sort         = optional_param('sort', 'username', PARAM_ALPHA);
$dir          = optional_param('dir', 'ASC', PARAM_ALPHA);
$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 30, PARAM_INT);        // how many per page
$filter       = optional_param('filter', '', PARAM_TEXT);        
$action       = optional_param('action', '', PARAM_TEXT);        // add, 

$path = '/mod/webctimport/users.php';

$urlparams = array('sort'=>$sort,'dir'=>$dir,'page'=>$page,'perpage'=>$perpage,'filter'=>$filter);
$baseurl = new moodle_url($path, $urlparams);
$PAGE->set_url($baseurl);

$context = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($context);

require_capability('mod/webctimport:manageusers', $context);

// standard page & heading, etc
$strmanage = get_string('manageusers', 'mod_webctimport');

$PAGE->set_pagelayout('standard');
$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$settingsurl = new moodle_url('/admin/settings.php?section=modsettingwebctimport');
$manageusers = new moodle_url($path); //, $urlparams);
$PAGE->navbar->add(get_string('activities'));
$PAGE->navbar->add(get_string('pluginname', 'mod_webctimport'), $settingsurl);
$PAGE->navbar->add(get_string('manageusers', 'mod_webctimport'), $manageusers);
echo $OUTPUT->header();

if ($action=='add') {
	$userid = required_param('userid', PARAM_INT);
	
	$user = $DB->get_record('user',array('id'=>$userid));
	echo $OUTPUT->heading(get_string('manageusersadd', 'mod_webctimport', $user));
	
	echo $OUTPUT->footer();
	return;
}


// Carry on with the user listing

$columns = array("firstname", "lastname", "username");
$string = array();
$heading = array();

foreach ($columns as $column) {
	$string[$column] = get_string("$column");
	if ($sort != $column) {
		$columnicon = "";
		$columndir = "ASC";
	} else {
		$columndir = $dir == "ASC" ? "DESC":"ASC";
		$columnicon = $dir == "ASC" ? "down":"up";
		$columnicon = " <img src=\"" . $OUTPUT->pix_url('t/' . $columnicon) . "\" alt=\"\" />";
	}
	$params = $urlparams;
	$params['sort'] = $column;
	$params['dir'] = $columndir;
	$url = new moodle_url($path, $params);
	$heading[$column] = '<a href="'.$url.'">'.$string[$column].'</a>'.$columnicon;
}

$extrasql = null;
$params = array(); //$ufiltering->get_sql_filter();
$users = get_users_listing($sort, $dir, $page*$perpage, $perpage, '', '', '', $extrasql, $params);
$usercount = get_users(false);
$usersearchcount = get_users(false, '', true, null, "", '', '', '', '', '*', $extrasql, $params);

if ($extrasql !== '') {
	echo $OUTPUT->heading("$usersearchcount / $usercount ".get_string('users'));
	$usercount = $usersearchcount;
} else {
	echo $OUTPUT->heading("$usercount ".get_string('users'));
}

$params = $urlparams;
unset($params['page']);
$pageurl = new moodle_url($path, $params);
echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);

// ....

flush();


if (!$users) {
	$match = array();
	echo $OUTPUT->heading(get_string('nousersfound'));

	$table = NULL;

} else {

	$table = new html_table();
	$table->head = array ($heading['firstname'],$heading['lastname'], $heading['username'], '');
	$table->align = array ("left", "left", "left", "center");
	$table->width = "95%";
	foreach ($users as $user) {
		if (isguestuser($user)) {
			continue; // do not display guest here
		}
		$params = $urlparams;
		$params['action'] = 'add';
		$params['userid'] = $user->id;
		$addurl = new moodle_url($path, $params);
		$addbutton = '<a href="'.$addurl.'"><img src="'.$OUTPUT->pix_url('t/add').'" alt="Add"/></a>';
		
		$table->data[] = array ($user->firstname,$user->lastname,$user->username,$addbutton);
	}
}

if (!empty($table)) {
	echo html_writer::table($table);
	echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
}

echo $OUTPUT->footer();
