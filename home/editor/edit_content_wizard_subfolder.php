<?php
/************************************************************************/
/* AContent                                                             */
/************************************************************************/
/* Copyright (c) 2013                                                   */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

define('TR_INCLUDE_PATH', '../../include/');
require_once(TR_INCLUDE_PATH.'vitals.inc.php');
require_once(TR_INCLUDE_PATH.'../home/editor/editor_tab_functions.inc.php');
require_once(TR_INCLUDE_PATH.'../home/classes/ContentUtility.class.php');

global $_content_id, $_content_id, $contentManager, $_course_id;
$_content_id=$_GET['pid'];
$cid = $_content_id;

global  $_content_id,$contentManager, $_course_id;

Utility::authenticate(TR_PRIV_ISAUTHOR);

if(isset($_POST['back'])){
    header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$_content_id);
    exit;
}

if (isset($_GET['pid'])) $pid = intval($_GET['pid']);

if (defined('TR_FORCE_GET_FILE') && TR_FORCE_GET_FILE) {
	$course_base_href = 'get.php/';
} else {
	$course_base_href = 'content/' . $_course_id . '/';
}

if ($cid > 0 && isset($contentManager)) {
	$content_row = $contentManager->getContentPage($cid);
}

// save changes
if ($_POST['submit'])
{
        $cid=$_POST['cid'];
	if ($_POST['title'] == '') {
		$msg->addError(array('EMPTY_FIELDS', _AT('title')));
	}
		
	if (!$msg->containsErrors()) 
	{
		$_POST['title']	= $content_row['title'] = $_POST['title'];
	/*	if ($cid > 0)
		{ // edit existing content
                    die("cid >0");
			$err = $contentManager->editContent($cid, 
			                                    $_POST['title'], 
			                                    '', 
			                                    '', 
			                                    $content_row['formatting'], 
			                                    '', 
			                                    $content_row['use_customized_head'], 
			                                    '');
		}
		else
		{ 
         
         */
         // add new content
      //   die("$pid");
			// find out ordering and content_parent_id
			if ($pid)
			{ // insert sub content folder
				$ordering = count($contentManager->getContent($pid))+1;
			}
			else
			{ // insert a top content folder
				$ordering = count($contentManager->getContent(0)) + 1;
				$pid = 0;
			}
			
       //  $ordering = count($contentManager->getContent($pid))+1;
			$cid = $contentManager->addContent($_SESSION['course_id'],
			                                   $pid,
			                                   $ordering,
			                                   $_POST['title'],
			                                   '',
			                                   '',
			                                   '',
			                                   0,
			                                   '',
			                                   0,
			                                   '',
			                                   1,
			                                   CONTENT_TYPE_FOLDER);
	//	}
		
		$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
                exit;
	}
}

if ($cid > 0)
{ // edit existing content folder
	if (!$content_row || !isset($contentManager)) {
		$_pages['home/editor/edit_content_folder.php']['title_var'] = 'missing_content';
		$_pages['home/editor/edit_content_folder.php']['parent']    = 'index.php';
		$_pages['home/editor/edit_content_folder.php']['ignore']	= true;

		require(TR_INCLUDE_PATH.'header.inc.php');
	
		$msg->addError('MISSING_CONTENT');
		$msg->printAll();
	
		require (TR_INCLUDE_PATH.'footer.inc.php');
		exit;
	} /* else: */

	/* the "heading navigation": */
	$path	= $contentManager->getContentPath($cid);
	
	if ($content_row['content_path']) {
		$content_base_href = $content_row['content_path'].'/';
	}

	$parent_headings = '';
	$num_in_path = count($path);
	
	/* the page title: */
	$page_title = '';
	$page_title .= $content_row['title'];
	
	for ($i=0; $i<$num_in_path; $i++) {
		$content_info = $path[$i];
		if ($_SESSION['prefs']['PREF_NUMBERING']) {
			if ($contentManager->_menu_info[$content_info['content_id']]['content_parent_id'] == 0) {
				$top_num = $contentManager->_menu_info[$content_info['content_id']]['ordering'];
				$parent_headings .= $top_num;
			} else {
				$top_num = $top_num.'.'.$contentManager->_menu_info[$content_info['content_id']]['ordering'];
				$parent_headings .= $top_num;
			}
			if ($_SESSION['prefs']['PREF_NUMBERING']) {
				$path[$i]['content_number'] = $top_num . ' ';
			}
			$parent_headings .= ' ';
		}
	}
	
	if ($_SESSION['prefs']['PREF_NUMBERING']) {
		if ($top_num != '') {
			$top_num = $top_num.'.'.$content_row['ordering'];
			$page_title .= $top_num.' ';
		} else {
			$top_num = $content_row['ordering'];
			$page_title .= $top_num.' ';
		}
	}
	
	$parent = 0;
//	foreach ($path as $i=>$page) {
//		if (!$parent) {
//			$_pages['editor/edit_content_folder.php?cid='.$page['content_id']]['title']    = $page['content_number'] . $page['title'];
//			$_pages['editor/edit_content_folder.php?cid='.$page['content_id']]['parent']   = 'index.php';
//		} else {
//			$_pages['editor/edit_content_folder.php?cid='.$page['content_id']]['title']    = $page['content_number'] . $page['title'];
//			$_pages['editor/edit_content_folder.php?cid='.$page['content_id']]['parent']   = 'editor/edit_content_folder.php?cid='.$parent;
//		}
//	
//		$_pages['editor/edit_content_folder.php?cid='.$page['content_id']]['ignore'] = true;
//		$parent = $page['content_id'];
//	}
//	$last_page = array_pop($_pages);
//	$_pages['editor/edit_content_folder.php'] = $last_page;
	
	reset($path);
	$first_page = current($path);
	
	ContentUtility::saveLastCid($cid);
	
	if (isset($top_num) && $top_num != (int) $top_num) {
		$top_num = substr($top_num, 0, strpos($top_num, '.'));
	}
	$_tool_shortcuts = ContentUtility::getToolShortcuts($content_row);  // used by header.tmpl.php
	
	// display pre-tests
//	$sql = 'SELECT * FROM '.TABLE_PREFIX."content_prerequisites WHERE content_id=$_REQUEST[cid] AND type='".CONTENT_PRE_TEST."'";
//	$result = mysql_query($sql, $db);
//	while ($row = mysql_fetch_assoc($result)) {
//		$_POST['pre_tid'][] = $row['item_id'];
//	}

	$savant->assign('ftitle', $content_row['title']);
//	$savant->assign('shortcuts', $shortcuts);
	$savant->assign('cid', $cid);
}

if ($pid > 0 || !isset($pid)) {
	$savant->assign('pid', $pid);
	$savant->assign('course_id', $_course_id);
}

$onload = "document.form.title.focus();";
require(TR_INCLUDE_PATH.'header.inc.php');

//$savant->display('home/editor/edit_content_wizard_subfolder.tmpl.php');

?>
<link type="text/css" href="<?php echo TR_BASE_HREF;?>themes/default/forms.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo TR_BASE_HREF;?>home/editor/js/wizard.js"></script>
<form action="<?php echo TR_BASE_HREF;?>home/editor/edit_content_wizard_subfolder.php?_course_id=<?php echo $_course_id; ?>&pid=<?php echo $_content_id;?>" method="post" name="form"> 
<div class="input-form" style="width:95%;margin-left:1.5em;">
<input type="hidden" value="<?php echo $_content_id; ?>" name="cid">
    <div style=" weight: 10%; margin: 10px 0px 0px 10px;">Enter the name of the subfolder.</div>
    
	<div class="row">
		<div style="font-weight:bold;"><span class="required" title="<?php echo _AT('required_field'); ?>">*</span><label for="ftitle"><?php echo _AT('content_folder_title');  ?></label></div>
		<input type="text" name="title" id="ftitle" size="70" class="formfield" value="" />
	</div>
	
	<div class="row buttons">
           <!-- <label id="lab_back" name="<?php echo $_course_id; ?>"><label class="button_back2">Back</label></label>  -->
            <input type="submit" name="back" value="Back" title="back alt-s" accesskey="s" />
            <input type="submit" name="submit" value="<?php echo _AT('save'); ?>" title="<?php echo _AT('save_changes'); ?> alt-s" accesskey="s" />
	</div>
</div>
</form>
<?php

require(TR_INCLUDE_PATH.'footer.inc.php');

//save last visit page.
$_SESSION['last_visited_page'] = $server_protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>


