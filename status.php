<?php
// status report from import process, e.g. in edit form

require_once("../../config.php");
require_once("lib.php");
require_once("locallib.php");

$webctfileid = required_param('webctfileid', PARAM_TEXT); 

?><div><?php 
if (empty($webctfileid)) {
	//<div>Status...??<?php echo 'webctfileid='.$webctfileid; 
	echo 'Not specified as import as File';
} else {
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

	function print_import($webctfileid, $id) {
		global $CFG;
		?><script>
window.document.location = '<?php print "$CFG->wwwroot/mod/webctimport/import.php?webctfileid=$webctfileid&id=$id" ?>';
</script><?php 
	}

	$file = $DB->get_record('webctfile', array('id'=>$webctfileid));
	if ($file) {
		if ($file->status==WEBCTIMPORT_STATUS_NEW) {
			echo 'New...';
			print_import($webctfileid, $id);			
		}
		else if ($file->status==WEBCTIMPORT_STATUS_WORKING) {
			echo 'Importing...';	
			print_import($webctfileid, $id);
		}
		else if ($file->status==WEBCTIMPORT_STATUS_DONE) {
			echo 'Done (should be replaced!)';
			// replace this instance ?!
			debugging('View imported instance: '.$id);
		}
		else if ($file->status==WEBCTIMPORT_STATUS_TRANSIENT_ERROR) {
			echo $file->error.' - retrying...';	
			print_import($webctfileid, $id);
		}
		else if ($file->status==WEBCTIMPORT_STATUS_PERMANENT_ERROR)
			echo $file->error;		
	}
	?></div><?php 
	echo $OUTPUT->footer();
}
