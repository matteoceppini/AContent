<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<?php
    define('TR_INCLUDE_PATH', '../../include/');
    require_once(TR_INCLUDE_PATH.'vitals.inc.php');
    require_once(TR_INCLUDE_PATH.'../home/editor/editor_tab_functions.inc.php');
    require_once(TR_INCLUDE_PATH.'../home/classes/ContentUtility.class.php');
    require_once(TR_INCLUDE_PATH.'../home/classes/StructureManager.class.php');
    
    global  $_content_id,$contentManager, $_course_id;
    $cid = $_content_id;
    $cid_sup = $cid;
    
//save and redirect to step3   
    if($_POST['next']){
        
        // controls to check whether you have selected a radio button
        if(!isset($_POST['title'])){
            require(TR_INCLUDE_PATH.'header.inc.php');
            echo '<div class="input-form" style="width:95%; margin-left:1.5em;">';
            echo '<link rel="stylesheet" type="text/css" href="'.TR_BASE_HREF.'/themes/default/forms.css">';
            echo '<div style="line-height: 150%;" id="error">';
            echo '<h4>'._AT('the_follow_errors_occurred').'</h4><ul>';
            echo '<li>'._AT('error_wizard_fields').'</li></ul></div>';
            echo '<div class="row buttons">';
            echo '<form action="'.TR_BASE_HREF.'home/editor/edit_content_wizard_control_struct.php?_cid='.$cid.'" method="post" name="form"> ';
            echo '<input type="submit" name="try_again" value="Try Again" title="Try Again alt-t"  accesskey="t" /></form>';
            echo '<form action="'.TR_BASE_HREF.'home/index.php" method="post" name="form"> ';
            echo '<input type="submit" name="give_up" value="Give Up" title="Give Up alt-g"  accesskey="g" /></form>';
            echo '</div>';
            echo '</div>';
            require(TR_INCLUDE_PATH.'footer.inc.php');
        }else{
        
            $pid=0;

            if (defined('TR_FORCE_GET_FILE') && TR_FORCE_GET_FILE) {
                $course_base_href = 'get.php/';
            } else {
                $course_base_href = 'content/' . $_course_id . '/';
            }



            if ($cid > 0 && isset($contentManager)) {
                    $content_row = $contentManager->getContentPage($cid);
            }
            // save changes

            if ($_POST['title'] == '') {
                    $msg->addError(array('EMPTY_FIELDS', _AT('title')));
            }

            if (!$msg->containsErrors()) 
            {
                    $_POST['title']	= $content_row['title'] = $_POST['title'];
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
                            $cid = $contentManager->addContent($_SESSION['course_id'],
                                                               $pid,
                                                               $ordering,
                                                               $_POST['title'],
                                                               '','','',0,'',0,'',1,
                                                               CONTENT_TYPE_FOLDER);

                        $struc_manag = new StructureManager($_POST['title']);	     
                        $page_temp = $struc_manag->get_page_temp();

                        $struc_manag->createStruct($page_temp, $cid, $_course_id);   


                    }

                    $sql="SELECT copyright FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND content_id IN (SELECT MIN(content_id) FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id.")";
                    $contentDAO = new ContentDAO();
                    $result=$contentDAO->execute($sql);
                    if(is_array($result))
                    {
                        foreach ($result as $support) {
                           $copyright=$support['copyright']; 
                           break;
                        }  
                    }
                    if($copyright==TR_STATUS_ENABLED){

                        $sql = "UPDATE ".TABLE_PREFIX."content 
                                                       SET copyright='.$copyright.'
                                                     WHERE course_id=".$_course_id;
                        $result=$contentDAO->execute($sql);
                    }
                    $sql="SELECT accessibility FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND content_id IN (SELECT MIN(content_id) FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id.")";
                    $contentDAO = new ContentDAO();
                    $result=$contentDAO->execute($sql);
                    if(is_array($result))
                    {
                        foreach ($result as $support) {
                           $accessibility=$support['accessibility']; 
                           break;
                        }  
                    }
                    if($accessibility==TR_STATUS_ENABLED){

                        $sql = "UPDATE ".TABLE_PREFIX."content 
                                                       SET accessibility='.$accessibility.'
                                                     WHERE course_id=".$_course_id;
                        $result=$contentDAO->execute($sql);
                    }

                    $msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
                    header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
                    exit;
            //}

        }
    }
  
    
    if($_POST['save_structure']){
        
         if(!isset($_POST['title'])){
            require(TR_INCLUDE_PATH.'header.inc.php');
            echo '<div class="input-form" style="width:95%; margin-left:1.5em;">';
            echo '<link rel="stylesheet" type="text/css" href="'.TR_BASE_HREF.'/themes/default/forms.css">';
            echo '<div style="line-height: 150%;" id="error">';
            echo '<h4>'._AT('the_follow_errors_occurred').'</h4><ul>';
            echo '<li>'._AT('error_wizard_fields').'</li></ul></div>';
            echo '<div class="row buttons">';
            echo '<form action="'.TR_BASE_HREF.'home/editor/edit_content_wizard_control_struct.php?_cid='.$cid.'" method="post" name="form"> ';
            echo '<input type="submit" name="try_again" value="Try Again" title="Try Again alt-t"  accesskey="t" /></form>';
            echo '<form action="'.TR_BASE_HREF.'home/index.php" method="post" name="form"> ';
            echo '<input type="submit" name="give_up" value="Give Up" title="Give Up alt-g"  accesskey="g" /></form>';
            echo '</div>';
            echo '</div>';
            require(TR_INCLUDE_PATH.'footer.inc.php');
        }else{
        
            $pid=0;

            if (defined('TR_FORCE_GET_FILE') && TR_FORCE_GET_FILE) {
                $course_base_href = 'get.php/';
            } else {
                $course_base_href = 'content/' . $_course_id . '/';
            }



            if ($cid > 0 && isset($contentManager)) {
                    $content_row = $contentManager->getContentPage($cid);
            }
            // save changes

            if ($_POST['title'] == '') {
                    $msg->addError(array('EMPTY_FIELDS', _AT('title')));
            }

            if (!$msg->containsErrors()) 
            {
                    $_POST['title']	= $content_row['title'] = $_POST['title'];

             // add new content

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

                        $struc_manag = new StructureManager($_POST['title']);	     
                        $page_temp = $struc_manag->get_page_temp();

                        $struc_manag->createStruct($page_temp, $cid, $_course_id);   


                    }

                    $sql="SELECT copyright FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND content_id IN (SELECT MIN(content_id) FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id.")";
                    $contentDAO = new ContentDAO();
                    $result=$contentDAO->execute($sql);
                    if(is_array($result))
                    {
                        foreach ($result as $support) {
                           $copyright=$support['copyright']; 
                           break;
                        }  
                    }
                    if($copyright==TR_STATUS_ENABLED){

                        $sql = "UPDATE ".TABLE_PREFIX."content 
                                                       SET copyright='.$copyright.'
                                                     WHERE course_id=".$_course_id;
                        $result=$contentDAO->execute($sql);
                    }
                    $sql="SELECT accessibility FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND content_id IN (SELECT MIN(content_id) FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id.")";
                    $contentDAO = new ContentDAO();
                    $result=$contentDAO->execute($sql);
                    if(is_array($result))
                    {
                        foreach ($result as $support) {
                           $accessibility=$support['accessibility']; 
                           break;
                        }  
                    }
                    if($accessibility==TR_STATUS_ENABLED){

                        $sql = "UPDATE ".TABLE_PREFIX."content 
                                                       SET accessibility='.$accessibility.'
                                                     WHERE course_id=".$_course_id;
                        $result=$contentDAO->execute($sql);
                    }

                    $msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
                    header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_control_struct.php?_cid='.$cid_sup);
                    exit;
            //}

        }
    }


    Utility::authenticate(TR_PRIV_ISAUTHOR);

    if(isset($_POST['back_step3'])){
        header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
        exit;
    }
    $contentDAO = new ContentDAO();
    define("PATH", "../../include/");
    require_once(PATH.'classes/DAO/ContentDAO.class.php');
    
    $sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE content_id=".$cid."";
    $result=$contentDAO->execute($sql);
    if(is_array($result))
    {
        foreach ($result as $support) {
           $structure=$support['structure']; //ok
           break;
        }  
    }

//save and redirect to step3   
    if(!isset($_POST['next']) && !isset($_POST['save_structure'])){
        
        // controls to check whether you have selected a radio button
        if(!isset($_POST['title'])){    
    
    
    $onload = "document.form.title.focus();";
    require(TR_INCLUDE_PATH.'header.inc.php'); 
?>    
<div class="input-form" style="width:95%; margin-left:1.5em;">
    <form action="" method="post" name="form"> 
        <div style=" weight: 10%; margin: 10px 0px 0px 10px;" >
            <?php           $sup=$structure;
                            $sup=str_replace('knowledge','', $sup);
                            $sup=str_replace('creative','', $sup);
                            $sup=str_replace('meta-competency','', $sup);
                            $sup=str_replace(',',' ', $sup);
                            $sup=str_replace(';',' ', $sup);
                            
                            
                            
                            //INSERIRE CONTROLLO PER VERIFICARE SE VI SONO OBBIETTIVI
                            $lung = strlen($sup);
                            if($lung==0){
                                echo '<link rel="stylesheet" type="text/css" href="'.TR_BASE_HREF.'/themes/default/forms.css">';
                                echo '<div style="line-height: 150%;" id="error">';
                                echo '<h4>'._AT('the_follow_errors_occurred').'</h4><ul>';
                                echo '<li>You are within a predefined structure and they do not have goals associated</li></ul></div>';
                                echo '<div class="row buttons">';
                                echo '<form action="" method="post" name="form"> ';
                                echo '<input type="submit" name="back_step3" value="Back" title="Back alt-b"  accesskey="b" /></form>';
                                echo '</div>';
                            }else{
            ?>
            <label>You have selected the following objectives</label><br>

            <label style="margin: 10px 0px 0px 10px;"><b>
            <?php              
            echo $sup;  
            ?>
            </b></label>
            <p>Below you can see the default structures associated with the goals that you have chosen:</p>
        </div>
        <?php
  //      $savant->display('home/editor/edit_content_struct.tmpl.php');
        
			
			$mod_path['templates']		= realpath(TR_BASE_HREF			. 'templates').'/';
			$mod_path['templates_int']	= realpath(TR_INCLUDE_PATH		. '../templates').'/';
			$mod_path['templates_sys']	= $mod_path['templates_int']	. 'system/';
			$mod_path['structs_dir']		= $mod_path['templates']		. 'structures/';
			$mod_path['structs_dir_int']	= $mod_path['templates_int']	. 'structures/';

			include_once($mod_path['templates_sys'].'Structures.class.php');
			
			$structs	= new Structures($mod_path);

			$structsList = $structs->getStructsList();
			if (!is_array($structsList)) {
					$num_of_structs = 0;
					$output = _AT('none_found');
			} else {
                            echo '<div style=" weight: 10%; margin: 10px;">';
                            foreach ($structsList as $struct) {
                            //	echo '<input type="checkbox" name="title" id="'.$struct['name'].'" class="formfield" value="'.$struct['short_name'].'"/>';
                                
                                $a=$struct['name'];
                               
                                 $a=str_replace(' based',' ',$a);
                                 $a=trim($a);
                               // die("$structure");
                                if(strstr($structure, $a)){
                                    
                                $supp=strtoupper($struct['name']);
                                echo '<label for="'.$struct['name'].'"><b>'.$supp.'</b></label>';
                                $value = "";
                                foreach ($structsList as $val) { 
                                    if(isset($_POST['struct']) && $_POST['struct'] == $val['short_name'])
                                            $check = true;
                                    else 
                                            $check = false;

                                    if($val['name'] == $struct['name']){
                                        echo '<input type="radio" name="title" id="'.$struct['name'].'" class="formfield" value="'.$struct['short_name'].'"/>';
                                        //echo '<input type="checkbox" name="title" id="'.$struct['name'].'" class="formfield" value="'.$struct['short_name'].'"/>';
                  ?>                      
                                        <div style=" margin-bottom: 10px; <?php if($check) echo 'border: 2px #cccccc dotted;';?> ">
                                <!--	<li id="<?php echo $val['short_name'];?>"> <?php echo $val['name'];?> </li> -->

                                        <p style="margin-left: 10px; font-size:90%;"><span style="font-style:italic;"><?php echo _AT('description'); ?>:</span>
                                        <?php //echo $val['description']; 
                                            if($val['short_name']=='creative'){
                                                echo 'centered on the motivations and "emotions" of the learner.';
                                            }
                                            if($val['short_name']=='meta-competency'){
                                                echo 'emphasizes the logic of a constructivist approach to the construction of knowledge.';
                                            }
                                            if($val['short_name']=='knowledge'){
                                                echo 'is to follow a path of self-learning to acquire the "base" of the subject matter.';
                                            }
                                        
                                        
                                        ?></p>
                                        <div style="font-size:95%; margin-left: 10px;">
                                                <a title="outline_collapsed" id="a_outline_<?php echo $val['short_name'];?>" onclick="javascript: trans.utility.toggleOutline('<?php echo $val['short_name'];?>', '<?php echo _AT('hide_outline'); ?>', '<?php echo _AT('show_outline'); ?>'); " href="javascript:void(0)"><?php echo _AT('show_outline'); ?></a>
                                                <div style="display: none;" id="div_outline_<?php echo $val['short_name'];?>">
                                                        <?php $struc_manag = new StructureManager($val['short_name']);
                                                        $struc_manag->printPreview(false, $val['short_name']); ?>
                                                </div>
                                        </div>
                                        </div>

                                        <?php 
                                       }
                                }
		echo '</br>';
                             }
                            }
                echo '</div>';          
			}
		?>
        <div style=" weight: 10%; margin: 10px 0px 0px 10px;" >
            <label>Now if you want you can associate a predefined structure to the lesson select it and click save</label>
        </div>
        <div id="buttons_intro">
 <!--<input style="width: 120px; height: 30px;"  type="submit" name="save_structure" value="Save" title="Save alt-s"  accesskey="s" />-->
            
            <div class="row buttons">
                <input style="width: 120px; height: 30px;"  type="submit" name="save_structure" value="Save" title="Save alt-s"  accesskey="s" />
                <input style="width: 120px; height: 30px;"  type="submit" name="back_step3" value="Back" title="Back alt-b"  accesskey="b" />
                <input style="width: 120px; height: 30px;"  type="submit" name="next" value="Next" title="Next alt-n" accesskey="n" />
            </div>
        </div>
   </form>
</div>





<?php
                            }
   require(TR_INCLUDE_PATH.'footer.inc.php');
        }
    }

?>