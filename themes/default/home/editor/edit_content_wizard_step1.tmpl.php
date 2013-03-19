<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('TR_INCLUDE_PATH')) { exit; } 
global $onload;
$onload = 'document.form.title.focus();';
require_once(TR_INCLUDE_PATH.'classes/CoursesUtility.class.php');
require_once(TR_INCLUDE_PATH.'../home/classes/GoalsManager.class.php');

global $_course_id;
$contentDAO = new ContentDAO();
?>
<!--<script type="text/javascript" src="<?php echo TR_BASE_HREF;?>home/editor/js/wizard.js"></script>-->
<link rel="stylesheet" type="text/css" href="'.TR_BASE_HREF.'/themes/default/forms.css">

<div class="input-form" style="width:95%; margin-left:1.5em;">
        <form action="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_step2.php?_course_id=<?php echo $_course_id; ?>" method="post" name="form">
          <!--  <div style=" weight: 10%; margin: 10px 0px 0px 10px;"><b><?php// echo _AT('title_wizard_step1'); ?></b></div> -->
                <div class="row">
                <table class="form-data">
                    <tr>
                        <td><?php echo _AT('check_goals'); ?>
                            <input type="radio" name="use_goals" id="statusE" value="<?php echo TR_STATUS_ENABLED; ?>" /><label for="statusE"><?php echo _AT('enabled'); ?></label>
                            <input checked="checked" type="radio" name="use_goals" id="statusD" value="<?php echo TR_STATUS_DISABLED; ?>" /><label label for="statusD"><?php echo _AT('disabled'); ?></label>
                            <label style="font-size:12px;"><br><i><?php echo _AT('description');  echo _AT('description_goal');?></i></label>
                            <label><i></i></label>
                            <i><small><br><?php echo _AT('default_disable'); ?></small></i>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td><?php echo _AT('check_accessibility'); ?>
                            <input type="radio" name="use_accessibility" id="statusE" value="<?php echo TR_STATUS_ENABLED; ?>" /><label for="statusE"><?php echo _AT('enabled'); ?></label>
                            <input checked="checked" type="radio" name="use_accessibility" id="statusD" value="<?php echo TR_STATUS_DISABLED; ?>" /><label for="statusD"><?php echo _AT('disabled'); ?></label> 
                            <label style="font-size:12px;"><br><i><?php echo _AT('description'); echo _AT('description_accessibility'); ?></i></label>
                            <i><small><br><?php echo _AT('default_disable'); ?></small></i>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td><?php echo _AT('check_copyright'); ?>
                            <input type="radio" name="use_copyright" id="statusE" value="<?php echo TR_STATUS_ENABLED; ?>" /><label for="statusE"><?php echo _AT('enabled'); ?></label>
                            <input checked="checked" style="margin-left: 10px;" type="radio" name="use_copyright" id="statusD" value="<?php echo TR_STATUS_DISABLED; ?>" /><label for="statusD"><?php echo _AT('disabled'); ?></label> 
                            <label style="font-size:12px;"><br><i><?php echo _AT('description'); echo _AT('description_copyright'); ?></i></label>
                            <i><small><br><?php echo _AT('default_disable'); ?></small></i>
                        </td>
                    </tr>
                </table>
                <div class="row buttons">
                   <!--<label class="label_button" id="first_continue">Continue</label>-->
                   <input style="width: 120px; height: 30px;" type="submit" name="back_step1" value="Back" title="Back alt-b"  accesskey="b" />
                   <input style="width: 120px; height: 30px;" type="submit" name="continue" value="Start wizard" title="Start alt-s" accesskey="s" />
                </div>    
                </div>
            <input type="hidden" name="cid" value="">
            </form>
</div>
