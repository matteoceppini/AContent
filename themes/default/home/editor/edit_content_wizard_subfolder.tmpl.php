<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('TR_INCLUDE_PATH')) { exit; } 
global $onload;
global $_content_id, $_content_id, $contentManager, $_course_id;
$cid = $_content_id;
$onload = 'document.form.title.focus();';
require_once(TR_INCLUDE_PATH.'classes/CoursesUtility.class.php');
require_once(TR_INCLUDE_PATH.'../home/classes/GoalsManager.class.php');

$_content_id=$_GET['_content_id'];



$contentDAO = new ContentDAO();
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
            <label id="lab_back" name="<?php echo $_course_id; ?>"><label class="button_back2">Back</label></label> 
            <input type="submit" name="submit" value="<?php echo _AT('save'); ?>" title="<?php echo _AT('save_changes'); ?> alt-s" accesskey="s" />
	</div>
</div>
</form>
