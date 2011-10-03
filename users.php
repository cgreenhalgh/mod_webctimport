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
$confirm      = optional_param('confirm', 0, PARAM_INT);        // add,

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

if ($action=='add') {
	$userid = required_param('userid', PARAM_INT);
	
	$user = $DB->get_record('user',array('id'=>$userid));
	if (!$user) 
		print_error('unknownuser','mod_webctimport');
	
	if (!$confirm) {
		// form
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('manageusersadd', 'mod_webctimport', $user));

		$PAGE->requires->js_init_call('M.mod_webctimport.init_grant_treeview', array());
		
		?>
		<p>Select WebCT contexts to grant access to:</p>
		<form action="users.php" method="POST">
		<?php
		echo '<input type="hidden" name="sort" value="'.$sort.'">';
		echo '<input type="hidden" name="dir" value="'.$dir.'">';
		echo '<input type="hidden" name="page" value="'.$page.'">';
		echo '<input type="hidden" name="perpage" value="'.$perpage.'">';
		echo '<input type="hidden" name="filter" value="'.$filter.'">';
		echo '<input type="hidden" name="userid" value="'.$userid.'">';
		echo '<input type="hidden" name="action" value="add">';
		echo '<input type="hidden" name="confirm" value="1">';
		// sessKey? modulename? instance?
		?>
		<ul id="treeview_root">
			<li>...</li>
		</ul>
		<input type="submit" value="Grant access to selected items">
		</form>
		<?php 
		$params = $urlparams;
		unset($params['action']);
		echo $OUTPUT->single_button(new moodle_url($path,$urlparams), get_string('cancel'));				
		
		echo $OUTPUT->footer();
	}
	else {
		// submit -see treeviewsubmit
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
				$grant = new stdClass();
				$grant->title = $item['title'];
				if (isset($item['description']))
					$grant->description = $item['description'];
				else
					$grant->description = '';
				$grant->path = $item['path'];
				$grant->granted = time();
				$grant->grantedby = $USER->id;
				$grant->size = 0;
				$grant->webcttype = 'GrantContext';
				$grant->userid = $user->id;				
				$grant->id = $DB->insert_record('webctgrant', $grant);
				if (!$grant->id) 
					print_error('addinggrant','mod_webctimport');
				
				debugging('Granted '.$user->username.' access to '.$grant->path);	
			}
		}
		
		redirect($baseurl);
	}
	return;
}
else if ($action=='delete') {
	
	$grantid = required_param('grantid', PARAM_INT);
	
	$grant = $DB->get_record('webctgrant',array('id'=>$grantid));
	if (!$grant)
	print_error('unknowngrant','mod_webctimport');
	
	if ($confirm && confirm_sesskey()) {
		$DB->delete_records('webctgrant', array('id'=>$grant->id));
		redirect($baseurl);
	} else {
		echo $OUTPUT->header();
		$params = $urlparams;
		$params['action'] = 'delete';
		$params['grantid'] = $grantid;
		$params['confirm'] = 1;
		$params['sesskey'] = sesskey();
		$confirmurl = new moodle_url($path, $params);
		echo $OUTPUT->confirm(get_string('confirmdeletegrant', 'mod_webctimport', $grant), $confirmurl, $baseurl);
		echo $OUTPUT->footer();
		return;
	}
}

echo $OUTPUT->header();

// filter...
echo '<div>';
echo '<form action="users.php" method="POST">';
echo '<input type="hidden" name="sort" value="'.$sort.'">';
echo '<input type="hidden" name="dir" value="'.$dir.'">';
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '<input type="hidden" name="perpage" value="'.$perpage.'">';
echo '<input type="hidden" name="userid" value="'.$userid.'">';
echo get_string('filterprompt','mod_webctimport').'<input type="text" name="filter" value="'.$filter.'">';
echo '<input type="submit" value="'.get_string('filter','mod_webctimport').'">';
echo '</form>';
echo '</div>';

$params = $urlparams;
unset($params['filter']);
echo $OUTPUT->single_button(new moodle_url($path,$params), get_string('clearfilter','mod_webctimport'));

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

$search = null;
if (!empty($filter)) {
	$search = $filter;
}
// Note: $page*$perpage is correct, in spite of appearances
// need to exclude guest user(s) explicitly only from get_users_listing (moodle 2.1)
$extrasql = "id <> :guestid2";
$extraparams = array('guestid2'=>$CFG->siteguest);
$users = get_users_listing($sort, $dir, $page*$perpage, $perpage, $search, '', '', $extrasql, $extraparams);
$usercount = get_users(false);
$usersearchcount = get_users(false, $search, true, null, "", '', '', '', '', '*', '', array());

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
	$table->head = array ($heading['firstname'],$heading['lastname'], $heading['username'], '', get_string('extraaccess','mod_webctimport'));
	$table->align = array ("left", "left", "left", "center", 'left');
	$table->width = "95%";
	foreach ($users as $user) {
		if (isguestuser($user)) {
			debugging('ignoring guest user '.$user->username.', id='.$user-id.' (siteguest='.$CFG->siteguest.')');
			continue; // do not display guest here
		}
		$params = $urlparams;
		$params['action'] = 'add';
		$params['userid'] = $user->id;
		$addurl = new moodle_url($path, $params);
		$addbutton = '<a href="'.$addurl.'"><img src="'.$OUTPUT->pix_url('t/add').'" alt="Add"/></a>';
		// grants...
		$grants = $DB->get_records('webctgrant',array('userid'=>$user->id),'title ASC');
		$delbuttons = '';
		foreach ($grants as $grant) {
			if (!empty($delbuttons))
				$delbuttons .= '<br>';
			$delbuttons .= '<span>'.$grant->title.'</span>';
			$params = $urlparams;
			$params['action'] = 'delete';
			$params['grantid'] = $grant->id;
			$delurl = new moodle_url($path, $params);
			$delbuttons .= '<a href="'.$delurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" alt="Delete"/></a>';
		}
		debugging('add user $user->username');
		$table->data[] = array ($user->firstname,$user->lastname,$user->username,$addbutton,$delbuttons);
	}
	debugging("Showing page $page with $perpage per page gave ".count($users)." users -> ".count($table->data)." rows");
}

if (!empty($table)) {
	echo html_writer::table($table);
	echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);
}

echo $OUTPUT->footer();
