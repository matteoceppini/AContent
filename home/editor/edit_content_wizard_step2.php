<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
define('TR_INCLUDE_PATH', '../../include/');
define("PATH", "../../include/");
require_once(TR_INCLUDE_PATH.'vitals.inc.php');
require_once(TR_INCLUDE_PATH.'classes/Utility.class.php');
require_once(TR_INCLUDE_PATH.'classes/DAO/CoursesDAO.class.php');
require_once(TR_INCLUDE_PATH.'classes/DAO/ContentDAO.class.php');
require(TR_INCLUDE_PATH.'../home/classes/StructureManager.class.php');
require(TR_INCLUDE_PATH.'../home/course/course_start_tabs.php');
require_once(TR_INCLUDE_PATH.'../home/classes/ContentUtility.class.php');

Utility::authenticate(TR_PRIV_ISAUTHOR);

global $enable_goals,$enable_acc,$enable_comp;
global $msg, $_course_id, $contentManager;
global $_course_id,$_content_id,$_pid;
$contentDAO = new ContentDAO();

$onload = "document.form.title.focus();";




if(isset($_POST['back_step1'])){
        header('Location: '.TR_BASE_HREF.'home/course/course_start.php?_course_id='.$_course_id);
        exit;
}

if(isset($_POST['select'])){
    if(isset($_POST['back_step2'])){
            if(isset($_POST['back_true'])){
                $sql="SELECT content_id FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id;
                $result=$contentDAO->execute($sql);
                if(is_array($result))
                {
                    foreach ($result as $support) {
                       $cid=$support['content_id']; //ok
                       break;
                    }  
                }
                header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
                exit;
            }
            else{
                header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step1.php?_course_id='.$_course_id);
                exit;
            }
    }else{
        if($_POST['title']==""){
            require(TR_INCLUDE_PATH.'header.inc.php');
            echo '<div class="input-form" style="width:95%; margin-left:1.5em;">';
            echo '<link rel="stylesheet" type="text/css" href="'.TR_BASE_HREF.'/themes/default/forms.css">';
            echo '<div style="line-height: 150%;" id="error">';
            echo '<h4>'._AT('the_follow_errors_occurred').'</h4><ul>';
            echo '<li>'._AT('error_wizard_fields').'</li></ul></div>';
            echo '<div class="row buttons">';
            echo '<form action="'.TR_BASE_HREF.'home/editor/edit_content_wizard_step1.php?_course_id='.$_course_id.'" method="post" name="form"> ';
            echo '<input type="submit" name="try_again" value="Try Again" title="Try Again alt-t"  accesskey="t" /></form>';
            echo '<form action="'.TR_BASE_HREF.'home/index.php" method="post" name="form"> ';
            echo '<input type="submit" name="give_up" value="Give Up" title="Give Up alt-g"  accesskey="g" /></form>';
            echo '</div>';
            echo '</div>';
            require(TR_INCLUDE_PATH.'footer.inc.php');
        }else{
            if(isset($_POST['active_goal'])){
                $struct="none";
                // KNOWLEDGE 
                $sup=0;
                $xml=  simplexml_load_file(TR_BASE_HREF.'templates/goals/knowledge.xml');
                foreach($xml->children() as $child){
                   $child=str_replace(" ","",$child);
                   if( (str_replace(" ", "",$_POST['goal_knowledge_'.$child.''] )) == (str_replace(" ", "", $child)) ){
                       if($sup!=1){
                           $struct="knowledge";
                           $sup=1;   
                       }
                       if($sup==1){
                           $struct .= ",";
                           $struct .= $child;
                       }
                   }

                }
                // META-COMPETENCY 
                $xml=  simplexml_load_file(TR_BASE_HREF.'templates/goals/meta-competency.xml');
                foreach($xml->children() as $child){
                   $child=str_replace(" ","",$child);
                   if( (str_replace(" ", "", $_POST['goal_meta_'.$child.''])) == (str_replace(" ", "", $child)) ){
                       if($sup==0){
                           $struct = "meta-competency";
                           $sup=2;
                       }
                       if($sup==1){
                           $struct .= ";";
                           $struct .= "meta-competency";
                           $sup=2;
                       }
                       if($sup==2){
                           $struct .= ",";
                          // $struct .= str_replace(" ", "", $child); old
                          $struct .= $child;
                       }

                   }
                }
                // CREATIVE 
                $xml=  simplexml_load_file(TR_BASE_HREF.'templates/goals/creative.xml');
                foreach($xml->children() as $child){
                   $child=str_replace(" ","",$child);
                   if( $_POST['goal_creative_'.$child.''] == str_replace(" ", "", $child) ){
                       if($sup==2 || $sup==1){
                           $struct .= ";";
                           $struct .= "creative";
                           $sup=3;
                       }
                       if($sup==0){
                           $struct = "creative";
                           $sup=3;  
                       }
                       if($sup==3){
                           $struct .= ",";
                           $struct .= $child;
                       }
                   }
                }
                if($struct=="none" && $_POST['create']!=folder ){
                    require(TR_INCLUDE_PATH.'header.inc.php');
                    echo '<div class="input-form" style="width:95%; margin-left:1.5em;">';
                    echo '<link rel="stylesheet" type="text/css" href="'.TR_BASE_HREF.'/themes/default/forms.css">';
                    echo '<div style="line-height: 150%;" id="error">';
                    echo '<h4>'._AT('the_follow_errors_occurred').'</h4><ul>';
                    echo '<li>'._AT('error_wizard_fields').'</li></ul></div>';
                    echo '<div class="row buttons">';
                    echo '<form action="'.TR_BASE_HREF.'home/editor/edit_content_wizard_step1.php?_course_id='.$_course_id.'" method="post" name="form"> ';
                    echo '<input type="submit" name="try_again" value="Try Again" title="Try Again alt-t"  accesskey="t" /></form>';
                    echo '<form action="'.TR_BASE_HREF.'home/index.php" method="post" name="form"> ';
                    echo '<input type="submit" name="give_up" value="Give Up" title="Give Up alt-g"  accesskey="g" /></form>';
                    echo '</div>';
                    echo '</div>';
                    require(TR_INCLUDE_PATH.'footer.inc.php');
                }else{
                    if(($_POST['create'])==folder){
                        $_POST['struct']	= $_POST['title'];
                        $ordering = 1;
                        $pid = 0;
                        $cid = $contentManager->addContent($_SESSION['course_id'],$pid,$ordering,$_POST['title'],'','','',0,'',0,'',1,CONTENT_TYPE_FOLDER);    
                        if(isset($_POST['accessibility'])){
                           if($_POST['accessibility']==TR_STATUS_ENABLED){
                                $enable_acc=TR_STATUS_ENABLED;
                                $sql = "UPDATE ".TABLE_PREFIX."content 
                                                   SET accessibility='$enable_acc'
                                                 WHERE content_id=".$cid;
                                $result=$contentDAO->execute($sql);
                            }
                        }
                        if(isset($_POST['copyright'])){
                            if($_POST['copyright']==TR_STATUS_ENABLED){
                                $enable_comp=TR_STATUS_ENABLED;
                                $sql = "UPDATE ".TABLE_PREFIX."content 
                                                   SET copyright='$enable_comp'
                                                 WHERE content_id=".$cid;
                                $result=$contentDAO->execute($sql);
                            }
                        }
                        $enable_comp=TR_STATUS_ENABLED;
                        $sql = "UPDATE ".TABLE_PREFIX."content 
                                           SET structure='$struct'
                                         WHERE content_id=".$cid;
                        $result=$contentDAO->execute($sql);
                        
                        $sql="SELECT COUNT(*) AS tot FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." GROUP BY course_id";
                        $result=$contentDAO->execute($sql);
                        if(is_array($result))
                        {
                            foreach ($result as $support) {
                               $tot=$support['tot']; //ok
                               break;
                            }  
                        }
                        if($tot>1){
                            //die("$tot");
                             $sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $stru=$support['structure']; //ok
                                    break;
                                 }  
                             }
                             $sql="SELECT accessibility FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $acc=$support['accessibility']; //ok
                                    break;
                                 }  
                             }
                             $sql="SELECT copyright FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $cop=$support['copyright']; //ok
                                    break;
                                 }  
                             }
                        //die("$stru");
                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET structure='$stru' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET copyright='$cop' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET accessibility='$acc' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                         }
                        
                  //  header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$cid);
                         header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
                        exit;
                    }else{
                        $_POST['struct']	= $_POST['title'];
                        $ordering = 1;
                        $pid = 0;
                        $cid = $contentManager->addContentWizard($_SESSION['course_id'],$pid,$ordering,$_POST['title'],$struct,'','',0,'',0,'',1,CONTENT_TYPE_CONTENT);
                        if(isset($_POST['accessibility'])){
                            if($_POST['accessibility']==TR_STATUS_ENABLED){
                                $enable_acc=TR_STATUS_ENABLED;
                                $sql = "UPDATE ".TABLE_PREFIX."content 
                                                   SET accessibility='$enable_acc'
                                                 WHERE content_id=".$cid;
                                $result=$contentDAO->execute($sql);
                            }
                        }
                        if(isset($_POST['copyright'])){
                            if($_POST['copyright']==TR_STATUS_ENABLED){
                                $enable_comp=TR_STATUS_ENABLED;
                                $sql = "UPDATE ".TABLE_PREFIX."content 
                                                   SET copyright='$enable_comp'
                                                 WHERE content_id=".$cid;

                                $result=$contentDAO->execute($sql);
                            }
                        }
                        
                        $sql="SELECT COUNT(*) AS tot FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." GROUP BY course_id";
                        $result=$contentDAO->execute($sql);
                        if(is_array($result))
                        {
                            foreach ($result as $support) {
                               $tot=$support['tot']; //ok
                               break;
                            }  
                        }
                        if($tot>1){
                            //die("$tot");
                             $sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $stru=$support['structure']; //ok
                                    break;
                                 }  
                             }
                             $sql="SELECT accessibility FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $acc=$support['accessibility']; //ok
                                    break;
                                 }  
                             }
                             $sql="SELECT copyright FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $cop=$support['copyright']; //ok
                                    break;
                                 }  
                             }
                        //die("$stru");
                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET structure='$stru' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET copyright='$cop' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET accessibility='$acc' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                         }
                        
                        header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
                     //   header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$cid);
                        exit;
                    }
                }
            }else{
                if(($_POST['create'])==folder){
                    $_POST['struct']	= $_POST['title'];
                    $ordering = 1;
                    $pid = 0;
                    $cid = $contentManager->addContent($_SESSION['course_id'],$pid,$ordering,$_POST['struct'],'','','',0,'',0,'',1,CONTENT_TYPE_FOLDER);
                    if(isset($_POST['accessibility'])){
                           if($_POST['accessibility']==TR_STATUS_ENABLED){
                                $enable_acc=TR_STATUS_ENABLED;
                                $sql = "UPDATE ".TABLE_PREFIX."content 
                                                   SET accessibility='$enable_acc'
                                                 WHERE content_id=".$cid;
                                $result=$contentDAO->execute($sql);
                            }
                        }
                        if(isset($_POST['copyright'])){
                            if($_POST['copyright']==TR_STATUS_ENABLED){
                                $enable_comp=TR_STATUS_ENABLED;
                                $sql = "UPDATE ".TABLE_PREFIX."content 
                                                   SET copyright='$enable_comp'
                                                 WHERE content_id=".$cid;
                                $result=$contentDAO->execute($sql);
                            }
                        }
                        $enable_comp=TR_STATUS_ENABLED;
                        $sql = "UPDATE ".TABLE_PREFIX."content 
                                           SET structure='$struct'
                                         WHERE content_id=".$cid;
                        $result=$contentDAO->execute($sql);
                        
                        $sql="SELECT COUNT(*) AS tot FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." GROUP BY course_id";
                        $result=$contentDAO->execute($sql);
                        if(is_array($result))
                        {
                            foreach ($result as $support) {
                               $tot=$support['tot']; //ok
                               break;
                            }  
                        }
                        if($tot>1){
                            //die("$tot");
                             $sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $stru=$support['structure']; //ok
                                    break;
                                 }  
                             }
                             $sql="SELECT accessibility FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $acc=$support['accessibility']; //ok
                                    break;
                                 }  
                             }
                             $sql="SELECT copyright FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
                             $result=$contentDAO->execute($sql);
                             if(is_array($result))
                             {
                                 foreach ($result as $support) {
                                    $cop=$support['copyright']; //ok
                                    break;
                                 }  
                             }
                        //die("$stru");
                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET structure='$stru' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET copyright='$cop' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                             $sql = "UPDATE ".TABLE_PREFIX."content 
                                            SET accessibility='$acc' 
                                            WHERE content_id=".$cid;
                             $contentDAO->execute($sql);

                         }


                     header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid); 
                     //header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$cid);
                        exit;
                }else{               
                    $ordering = 1;
                    $pid = 0;
                    $cid = $contentManager->addContentWizard($_SESSION['course_id'],$pid,$ordering,$_POST['title'],'','','',0,'',0,'',1,CONTENT_TYPE_CONTENT);	         
                    if(isset($_POST['accessibility'])){
                           if($_POST['accessibility']==TR_STATUS_ENABLED){
                               $enable_acc=TR_STATUS_ENABLED;
                               $sql = "UPDATE ".TABLE_PREFIX."content 
                                                  SET accessibility='$enable_acc'
                                                WHERE content_id=".$cid;
                               $result=$contentDAO->execute($sql);
                           }
                       }
                       if(isset($_POST['copyright'])){
                           if($_POST['copyright']==TR_STATUS_ENABLED){
                               $enable_comp=TR_STATUS_ENABLED;
                               $sql = "UPDATE ".TABLE_PREFIX."content 
                                                  SET copyright='$enable_comp'
                                                WHERE content_id=".$cid;

                               $result=$contentDAO->execute($sql);
                           }
                       }
                     
$sql="SELECT COUNT(*) AS tot FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." GROUP BY course_id";
$result=$contentDAO->execute($sql);
if(is_array($result))
{
    foreach ($result as $support) {
       $tot=$support['tot']; //ok
       break;
    }  
}
if($tot>1){
    //die("$tot");
     $sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
     $result=$contentDAO->execute($sql);
     if(is_array($result))
     {
         foreach ($result as $support) {
            $stru=$support['structure']; //ok
            break;
         }  
     }
     $sql="SELECT accessibility FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
     $result=$contentDAO->execute($sql);
     if(is_array($result))
     {
         foreach ($result as $support) {
            $acc=$support['accessibility']; //ok
            break;
         }  
     }
     $sql="SELECT copyright FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND ordering='$tot'";
     $result=$contentDAO->execute($sql);
     if(is_array($result))
     {
         foreach ($result as $support) {
            $cop=$support['copyright']; //ok
            break;
         }  
     }
//die("$stru");
     $sql = "UPDATE ".TABLE_PREFIX."content 
                    SET structure='$stru' 
                    WHERE content_id=".$cid;
     $contentDAO->execute($sql);

     $sql = "UPDATE ".TABLE_PREFIX."content 
                    SET copyright='$cop' 
                    WHERE content_id=".$cid;
     $contentDAO->execute($sql);

     $sql = "UPDATE ".TABLE_PREFIX."content 
                    SET accessibility='$acc' 
                    WHERE content_id=".$cid;
     $contentDAO->execute($sql);

 }
                        
                 //      header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$cid);
 header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
                       exit;
                }
            }

        }
    }     
}else{
    require(TR_INCLUDE_PATH.'header.inc.php');
    $savant->display('home/editor/edit_content_wizard_step2.tmpl.php');
    require(TR_INCLUDE_PATH.'footer.inc.php');
}
?>