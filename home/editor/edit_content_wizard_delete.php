<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('TR_INCLUDE_PATH', '../../include/');
require_once(TR_INCLUDE_PATH.'vitals.inc.php');
require_once(TR_INCLUDE_PATH.'../home/editor/editor_tab_functions.inc.php');
require_once(TR_INCLUDE_PATH.'../home/classes/ContentUtility.class.php');
require_once(TR_INCLUDE_PATH.'../home/classes/StructureManager.class.php');

global  $_content_id,$contentManager, $_course_id;
$cid = $_content_id;

$contentDAO = new ContentDAO();

Utility::authenticate(TR_PRIV_ISAUTHOR);

if(isset($_POST['back_step3'])){
    header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
    exit;
}

if(isset($_POST['delete'])){
    define("PATH", "../../include/");
    require_once(PATH.'classes/DAO/ContentDAO.class.php');
    
    $sql="SELECT COUNT(*) AS tot FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." GROUP BY course_id";
    $result=$contentDAO->execute($sql);
    if(is_array($result))
    {
        foreach ($result as $support) {
           $tot=$support['tot']; 
           break;
        }  
    }
    
    $result = $contentManager->deleteContent($cid);
    
    if($tot==1){
        header('Location: '.TR_BASE_HREF.'home/course/course_start.php?_course_id='.$_course_id);
        exit;
    }else{
        $sql="SELECT content_id FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id."";
        $result=$contentDAO->execute($sql);
        if(is_array($result))
        {
            foreach ($result as $support) {
               $cid=$support['content_id']; 
               break;
            }  
        }
        
        header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
        exit;
    }
}

$onload = "document.form.title.focus();";
require(TR_INCLUDE_PATH.'header.inc.php');



?>
<link rel="stylesheet" type="text/css" href="<?php echo TR_BASE_HREF; ?>themes/default/forms.css">

<div class="input-form" style="width:95%; margin-left:1.5em;">
    <div style="margin:5px;">
        <?php
            define("PATH", "../../include/");
            require_once(PATH.'classes/DAO/ContentDAO.class.php');
            $sql = "SELECT content_type FROM ".TABLE_PREFIX."content 
                                 WHERE content_id = ".$cid;
            $result=$contentDAO->execute($sql);
            if(is_array($result))
            {
                foreach ($result as $support) {
                   $content=$support['content_type'];
                   break;
                }  
            }

            $sql = "SELECT title FROM ".TABLE_PREFIX."content 
                                 WHERE content_id = ".$cid;
            $result=$contentDAO->execute($sql);
            if(is_array($result))
            {
                foreach ($result as $support) {
                   $title=$support['title'];
                   break;
                }  
            }

            if($content==0){
        ?>
            <label style="margin:5px;"><?php echo _AT('wizard_delete_title','page'); ?></label><div style="padding:10px;"></div>
            <label id="first_call" style="margin-left:15px;"><?php echo _AT('wizard_delete','page',$title); ?></label>
            <label id="second_call" style="margin-left:15px; display:none;"><?php echo _AT('wizard_delete_confirm','page',$title,'page'); ?></label>
        <?php }else{ ?>
            <label style="margin:5px;"><?php echo _AT('wizard_delete_title','folder'); ?></label><div style="padding:10px;"></div>
            <label id="first_call" style="margin-left:15px;"><?php echo _AT('wizard_delete','folder',$title); ?></label>
            <label id="second_call" style="margin-left:15px; display:none; "><?php echo _AT('wizard_delete_confirm','folder',$title,'folder'); ?></label>
        <?php } 
            $savant->display('home/editor/edit_content_wizard_delete.tmpl.php');
        ?> 
    </div>
</div>
<?php
require(TR_INCLUDE_PATH.'footer.inc.php');

?>
