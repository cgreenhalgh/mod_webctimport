<?php
// do or wait for actual import process

require_once("../../config.php");
require_once("lib.php");
require_once("locallib.php");

$webctfileid = required_param('webctfileid', PARAM_INT); 
$id = required_param('id', PARAM_INT); 

$webctimport = $DB->get_record('webctimport', array('id'=>$id), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('webctimport', $webctimport->id, $webctimport->course, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/webctimport:view', $context);

require_course_login($course, true, $cm);

$PAGE->set_pagelayout('popup');

//$PAGE->requires->js_init_call('M.mod_webctimport.XX', array());
echo $OUTPUT->header();

global $DB, $CFG;

function print_status($webctfileid, $id) {
	global $CFG;
?><script>
window.document.location = '<?php print "$CFG->wwwroot/mod/webctimport/status.php?webctfileid=$webctfileid&id=$id" ?>';
</script><?php 
}

function show_resource($resid) {
	global $CFG;
?><script>
window.parent.document.location = '<?php print "$CFG->wwwroot/mod/resource/view.php?r=$resid" ?>';
</script><?php 
}

?><div><?php 
function set_status($id, $status, $error) {
	global $DB;
	$file= new stdClass();
	$file->id = $id;
	$file->status = $status;
	$file->error = $error;
	$DB->update_record('webctfile', $file);
}

$maxWait = 30;
while (true) {
	$file = $DB->get_record('webctfile', array('id'=>$webctfileid));
	if (!$file) {
		print_error('Unknown import');
		return;
	}

	if ($file->status==WEBCTIMPORT_STATUS_WORKING) {
		// time out?
		if ($maxWait>0) {				
			$maxWait = $maxWait-5;
			sleep(5);
			continue;
		}
		print_error('Timed out waiting for import on another thread');
		return;
	}
	if ($file->status==WEBCTIMPORT_STATUS_NEW || $file->status==WEBCTIMPORT_STATUS_TRANSIENT_ERROR) {
		// we try...
		//$DB->
		$workerid = uniqid(gethostname(), $true);
		$file->workerid = $workerid;
		$file->workertimestamp = time();
		$file->status = WEBCTIMPORT_STATUS_WORKING;
		$DB->update_record('webctfile', $file);
		try {
			$file = $DB->get_record('webctfile', array('id'=>$file->id));
			if ($file->workerid!=$workerid) {
				debugging('Someone else beat us to the import: '.$workerid);
				continue;
			}
			debugging('Try import on '.$file->id);

			$fileinfo = webctimport_get_file_info($file->localfilepath);
			if (!$fileinfo || !isset($fileinfo->path)) {
				set_status($webctfileid, WEBCTIMPORT_STATUS_PERMANENT_ERROR, "Could not find file info in cache (".$file->localfilepath.")");
				break;
			}

			require_once("$CFG->libdir/filelib.php");
			global $CFG, $USER;

			$draftitemid = file_get_unused_draft_itemid();

			$usercontext = get_context_instance(CONTEXT_USER, $USER->id);
			$fs = get_file_storage();
			$file_record = array('contextid'=>$usercontext->id,
							'component'=>'user',
							'filearea'=>'draft',
							'itemid'=>$draftitemid,
							'filename'=>$fileinfo->filename,
							'mimetype'=>$fileinfo->mimetype,
							'filepath'=>'/',
							'timemodified'=>($fileinfo->lastmodifiedts/1000),
							'author'=>'webct:'.$USER->username,
			);
			$fs->create_file_from_pathname($file_record, webctimport_get_file_content_path($fileinfo->path));

			require_once("$CFG->dirroot/mod/resource/locallib.php");
			// to create a mod_resource, must set $data->files to draftitemid
			$res = (array)$webctimport;
			$res = (object)$res;
			unset($res->id);
			$res->files = $draftitemid;
			// based on resource_add_instance
			$res->timemodified = time();
			$res->coursemodule = $cm->id;
			
			$res->id = $DB->insert_record('resource', $res);

			$cmid = $res->coursemodule;
			// we need to use context now, so we need to make sure all needed info is already in db
			$module = $DB->get_record('modules', array('name'=>'resource'));
			$DB->set_field('course_modules', 'module', $module->id, array('id'=>$cmid));
			$DB->set_field('course_modules', 'instance', $res->id, array('id'=>$cmid));
			// this should import the files
			resource_set_mainfile($res);
			
			set_status($webctfileid, WEBCTIMPORT_STATUS_DONE, null);
			debugging('done import of '.$webctfileid);
			webctimport_delete_instance($webctimport->id);
			
			show_resource($res->id);
		}
		catch (Exception $e) {
			try {
				set_status($webctfileid, WEBCTIMPORT_STATUS_PERMANENT_ERROR, $e->getMessage());				
			}
			catch (Exception $e) {
			}
			print_error("Problem importing file: ".$e->getMessage());
			throw $e;			
		}
    	// rebuild cache
		rebuild_course_cache($res->course);
		echo 'Done';    	
	}
	else if ($file->status==WEBCTIMPORT_STATUS_DONE) {
		echo 'Done';
		// replace...?!
		//	print_status($webctfileid, $id);
	}
	else if ($file->status==WEBCTIMPORT_STATUS_PERMANENT_ERROR) {
		print_status($webctfileid, $id);
	}
	break;		
}
?></div><?php
echo $OUTPUT->footer();
