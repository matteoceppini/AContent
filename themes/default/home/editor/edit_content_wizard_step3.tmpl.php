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
require_once(TR_INCLUDE_PATH.'../home/classes/ContentManager.class.php');

global $_content_id;
global $_course_id;


$_content_id=$_GET['_content_id'];
$cid=$_content_id;

$contentDAO = new ContentDAO();
define("PATH", "../../include/");
require_once(PATH.'classes/DAO/ContentDAO.class.php');
?>
<script type="text/javascript" src="<?php echo TR_BASE_HREF;?>home/editor/js/wizard.js"></script>
<?php

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
    ?>
<div class="input-form" style="width:95%; margin-left:1.5em;">
    <div style=" weight: 10%; margin: 10px 0px 0px 10px;" id="title_search">
        <label class="label_search" id="label_title"><b>You have created more content click here to edit content other than the selected</b></label>
    </div>    
    <?php
}
?>
<div style="display:none;" id="search_content">
        <form action="" method="post" name="form">
<!--    <div style=" weight: 10%; margin: 10px 0px 0px 10px;"><b>Enter the name of the content to edit</b></div>
        <input style=" margin-left: 20px; width: 290px;" type="text" name="title" />
        <div class="row buttons">
            <input style="width: 120px; height: 30px;" type="submit" name="title_mult" value="Ok" title="ok alt-o"  accesskey="o" />
            <input style="width: 120px; height: 30px;" type="submit" name="back_mult" value="Back" title="back alt-b"  accesskey="b" />
        </div>
        </form>
-->
   <?php 
   $sql="SELECT title FROM ".TABLE_PREFIX."courses WHERE course_id=".$_course_id."";
    $result=$contentDAO->execute($sql);
    if(is_array($result))
    {
        foreach ($result as $support) {
           $title_lesson=$support['title']; //ok
        }  
    }
   require_once(TR_INCLUDE_PATH.'classes/DAO/CoursesDAO.class.php');

    global $_base_path;
    global $savant;
    global $contentManager, $_course_id;

    ob_start();

    echo '<div style=" weight: 10%; margin: 10px 0px 0px 10px;">';
    echo '<b>Structure of your lesson:</b><br/>';
    //echo '<a href="'.$_base_path.'home/course/outline.php?_course_id='.$_course_id.'">';
    echo '<label style="color:red;">'; echo $title_lesson; echo '</label><br/>';

    /* @See classes/ContentManager.class.php	*/
    $contentManager->printMainMenuWizard();
    echo '</div>';
       ?>
    <div class="row buttons">
         <!--   <input style="width: 120px; height: 30px;" type="submit" name="title_mult" value="Ok" title="ok alt-o"  accesskey="o" /> -->
         <input style="width: 120px; height: 30px;" type="submit" name="back_mult" value="Back" title="back alt-b"  accesskey="b" />
    </div>
    </form>
   
   
</div>
    <script>
    //    $('#title_search').css('display','none');
    </script>
<?php

if(isset($_POST['back_mult'])){
    header('Location: '.TR_BASE_HREF.'home/editor/edit_content_step3.php?_course_id='.$_course_id);
    //exit;
}


if(isset($_POST['title_mult'])){ //tasto OK in vecchia versione selezione contenuto con text area

    $title=$_POST['title'];
    // Find content_id the unique content built
  //  $sql="SELECT content_id FROM ".TABLE_PREFIX."content WHERE title='$title' AND course_id=".$_course_id."";
    $sql="SELECT content_id FROM ".TABLE_PREFIX."content WHERE content_id='$cid' AND course_id=".$_course_id."";
    $result=$contentDAO->execute($sql);
    if(is_array($result))
    {
        foreach ($result as $support) {
           $cid=$support['content_id']; //ok
           break;
        }  
    }

    
    if(is_null($cid)){
        
        ?>
        <div>
          <!--  <div class="input-form" style="width:95%; margin-left:1.5em;"> -->
            <link rel="stylesheet" type="text/css" href="'.TR_BASE_HREF.'/themes/default/forms.css">
            <div style="line-height: 150%;" id="error">
                <h4><?php echo _AT('the_follow_errors_occurred'); ?></h4><ul>
                <li><?php echo _AT('error_wizard_fields'); ?></li></ul></div>
                <div class="row buttons">
                <form action="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_step3.php?_course_id=<?php echo $_course_id; ?>" method="post" name="form"> 
                <input type="submit" name="try_again" value="Try Again" title="Try Again alt-t"  accesskey="t" /></form>
                <form action="<?php echo TR_BASE_HREF; ?>home/index.php" method="post" name="form">
                <input type="submit" name="give_up" value="Give Up" title="Give Up alt-g"  accesskey="g" /></form>
                </div>
         <!--   </div> -->
        </div>
            <?php
    }else{
        // Check if the user builds a folder or page
        $sql="SELECT content_type FROM ".TABLE_PREFIX."content WHERE content_id=".$cid."";
        $result=$contentDAO->execute($sql);
        if(is_array($result))
        {
            foreach ($result as $support) {
               $content_type=$support['content_type']; //ok
               break;
            }  
        }
        // Find the title of the content
        $sql="SELECT title FROM ".TABLE_PREFIX."content WHERE content_id=".$cid."";
        $result=$contentDAO->execute($sql);
        if(is_array($result))
        {
            foreach ($result as $support) {
               $title=$support['title']; //ok
               break;
            }  
        }
        $sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id." AND title='$title'";
        $result=$contentDAO->execute($sql);
        if(is_array($result))
        {
            foreach ($result as $support) {
               $structure=$support['structure']; //ok
               break;
            }  
        }
    }
}else{   
// NEW 13/02/2013
// NON calcolo piu il cid lo passo nell'url
// 
/*
    // Find content_id the unique content built
        $sql="SELECT content_id FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id."";
        $result=$contentDAO->execute($sql);
        if(is_array($result))
        {
            foreach ($result as $support) {
               $cid=$support['content_id']; //ok
               break;
            }  
        }      
 */
        // Check if the user builds a folder or page
        $sql="SELECT content_type FROM ".TABLE_PREFIX."content WHERE content_id=".$cid."";
        $result=$contentDAO->execute($sql);
        if(is_array($result))
        {
            foreach ($result as $support) {
               $content_type=$support['content_type']; //ok
               break;
            }  
        }
        // Find the title of the content
        $sql="SELECT title FROM ".TABLE_PREFIX."content WHERE content_id=".$cid."";
        $result=$contentDAO->execute($sql);
        if(is_array($result))
        {
            foreach ($result as $support) {
               $title=$support['title']; //ok
               break;
            }  
        }  
        
        // Find structure
        $sql="SELECT structure FROM ".TABLE_PREFIX."content WHERE title='$title' AND content_id=".$cid."";
        $result=$contentDAO->execute($sql);
        if(is_array($result))
        {
            foreach ($result as $support) {
               $structure=$support['structure']; //ok
               break;
            }  
        }

}
?>



<?php 
if(!is_null($cid)){
    if($content_type==CONTENT_TYPE_CONTENT){ 
        if($tot==1){ ?>
    <div class="input-form" style="width:95%; margin-left:1.5em;">
        <?php } ?>
        <div id="function">
    <!--    <form action="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_options.php?_course_id=<?php echo $_course_id; ?>" method="post" name="form">-->
            <div style=" weight: 10%; margin: 10px 0px 0px 10px;"><b>Choose how to create your own content</b></div>
                <div class="row">
                <table class="form-data">
                    <tr>
                        <td>
                            <div id="title">
                                <label style=" weight: 10%; margin: 10px 0px 0px 10px;">Title of the saved page:<b> <?php echo $title; ?></b></label>
                            </div>
                        </td>  
                    </tr>
                    <tr></tr>
                    <tr>   
                        <td>
                             <div id="Back to home">
                                <a href="<?php echo TR_BASE_HREF; ?>home/index.php">Back to the home for other work 
                                    <img class="shortcut_icon" border="0" src="<?php echo TR_BASE_HREF; ?>themes/default/images/exit.png" alt="alt+h"  title="home"/>
                                </a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i> if you are finished creating, you can return to the home.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>   
                        <td>
                            <div id="free_mode">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_classic_edit.php?_cid=<?php echo $cid;?>">Insert content through the classic editor 
                                    <img class="shortcut_icon" border="0" src="<?php echo TR_BASE_HREF; ?>images/medit.gif" alt="alt+e"  title="classic editor"/></a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i>create a content in free mode.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>
                            <div id="editopr">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_editor.php?_cid=<?php echo $cid;?>">Enter through the content editor</a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i>use the text editor to add content to the page.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>
                            <div id="wizard_template">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_template.php?_cid=<?php echo $cid;?>">Assign a template to content</a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i>allows you to add different template to content.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>
                            <div id="wizard_layout">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_layout.php?_cid=<?php echo $cid;?>">Assign a layout to content</a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i>lets you add a layout to content</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>
                            <div id="Delete page">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_delete.php?_cid=<?php echo $cid;?>">Delete this content page 
                                    <img class="shortcut_icon" border="0" src="<?php echo TR_BASE_HREF; ?>themes/default/images/delete.gif" alt="alt+d"  title="delete"/>
                                </a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i> you can delete the content page that you just created.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <?php
                    if($structure!=null){
                    ?>
                    <tr>
                        <td>
                            <div id="Control struct">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_control_struct.php?_cid=<?php echo $cid;?>">You have selected the objectives, go to the screen to add a predefined structure
                                </a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i> it's possible to insert a predefined structure in the lesson according to the objectives selected.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <?php
                    }
                    ?>
                    </table>
                 </div>
            </div>
<?php 
}else{
    if($tot==1){ ?>
        <div class="input-form" id="function" style="width:95%; margin-left:1.5em;">
<?php } ?>
        <div id="function">  
     <form action="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_step3.php?_course_id=<?php echo $_course_id; ?>" method="post" name="form">
            <div style=" weight: 10%; margin: 10px 0px 0px 10px;"><b>Choose how to create your own content</b></div>
                <div class="row">
                <table class="form-data">
                    <tr>
                        <td>
                            <div id="title">
                                <label style=" weight: 10%; margin: 10px 0px 0px 10px;">Title of the saved folder:<b> <?php echo $title; ?></b></label>
                            </div>
                        </td>  
                    </tr>
                    <tr></tr>
                    <tr>   
                        <td>
                            <div id="Back to home">
                                <a href="<?php echo TR_BASE_HREF; ?>home/index.php">Back to the home for other work 
                                    <img class="shortcut_icon" border="0" src="<?php echo TR_BASE_HREF; ?>themes/default/images/exit.png" alt="alt+h"  title="home"/>
                                </a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i> if you are finished creating, you can return to the home.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>
                            <div id="Add sub page">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_subpage.php?_course_id=<?php echo $_course_id; ?>&pid=<?php echo $cid;?>">Add a page into the folder
                                    <img class="shortcut_icon" border="0" src="<?php echo TR_BASE_HREF; ?>images/add_sub_page.gif" alt="alt+p"  title="sub page"/>
                                </a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i> you can add a page to the folder you just created.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>
                            <div id="Add sub folder">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_subfolder.php?_course_id=<?php echo $_course_id; ?>&pid=<?php echo $cid;?>">Add a folder into the folder
                                    <img class="shortcut_icon" border="0" src="<?php echo TR_BASE_HREF; ?>images/add_sub_folder.gif" alt="alt+f"  title="sub folder"/>
                                </a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i> you can add a subfolder to the folder you just created.</label>
                            </div>
                        </td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td>
                            <div id="Delete folder">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_delete.php?_cid=<?php echo $cid;?>">Delete this folder
                                     <img class="shortcut_icon" border="0" src="<?php echo TR_BASE_HREF; ?>themes/default/images/delete.gif" alt="alt+d"  title="delete"/>
                                </a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i> you can delete the folder that you just created.</label>
                            </div>
                        </td>
                    </tr>
                    <?php
                    if($structure!=null){
                    ?>
                    <tr></tr>
                    <tr>
                        <td>
                            <div id="Control struct">
                                <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_control_struct.php?_cid=<?php echo $cid;?>">You have selected the objectives, go to the screen to add a predefined structure
                                </a>
                                <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i> it's possible to insert a predefined structure in the lesson according to the objectives selected.</label>
                            </div>
                        </td>
                    </tr>            
                    <tr></tr>
                    <?php
                    }
                    ?>  
                    </table>
                 </div>
        </form>
        </div>
<?php
    }

?>

<!--
    Inserisco il link per aprire la nuova finestra con l'esempio delle strutture
-->
<?php 
    if($structure!=null){
?>
    <div id="info_struct">
        <div style="width:95%; margin-left:1.2em;">
            <label>You have selected the goals that you can view the predefined AContent associated with these goals.</label><br>
            <a href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_preview.php?_course_id=<?php echo $_course_id; ?>" onclick="open_new_win(this,900,700); return false">Information on the structures that AContent provides by default.</a>
        </div>
    </div>
<?php } ?>

    <div  id="button-next" class="row buttons">
        <a id="button_back" style="text-decoration: none; color:#000;" href="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_step2.php?_cid=<?php echo $cid;?>&_back=<?php echo 1; ?>"><label style="margin:0px;" class="button_back">Next</label></a>
    </div>
        
</div>  
            
<?php } ?>

        <script type="text/javascript">
<!--
function open_new_win (obj, w, h)
{
window.open (obj.href, null, "width=" + w + ",height=" + h + ",status=yes,toolbar=yes,titlebar=yes,menubar=yes,location=yes,resizable=yes,scrollbars=yes");
}
//-->
</script>