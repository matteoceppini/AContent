<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('TR_INCLUDE_PATH')) { exit; } 
global $onload,$goal,$accessibility,$copyright;
$onload = 'document.form.title.focus();';
require_once(TR_INCLUDE_PATH.'classes/CoursesUtility.class.php');
require_once(TR_INCLUDE_PATH.'../home/classes/GoalsManager.class.php');

// recovery data entered in the three previous radio button
// remember, 1 for Enabled and 0 for Disabled
$goal=$_POST['use_goals'];
$accessibility=$_POST['use_accessibility'];
$copyright=$_POST['use_copyright'];

global $_course_id;
$contentDAO = new ContentDAO();

?>
<script>
    $('#subnavlistcontainer').css('display','none');
</script>

<script type="text/javascript" src="<?php echo TR_BASE_HREF;?>home/editor/js/wizard.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo TR_BASE_HREF; ?>themes/default/forms.css">

<form action="<?php echo TR_BASE_HREF;?>home/editor/edit_content_wizard_step2.php?_course_id=<?php echo $_course_id; ?>" method="post" name="form"> 
    <div class="input-form" style="width:95%; margin-left:1.5em;">
        <input type="hidden"  id="hidd_page" name="create" value="page"/>
        <input type="hidden"  id="hidd_folder" name="create" value="folder"/>
       
<?php
    if($accessibility==1){
        ?> <input type="hidden" name="accessibility" value="<?php echo TR_STATUS_ENABLED; ?>"/> <?php
    }
    if($copyright==1){
        ?> <input type="hidden" name="copyright" value="<?php echo TR_STATUS_ENABLED; ?>"/> <?php
    }
    
    if(isset($_GET['_back'])){
        ?>
        <input type="hidden" name="back_true" value="on" />
        <?php
    }
?>

    <div id="step2_intro" class="row">
        <div style=" weight: 10%; margin: 5px 0px 0px 6px;"><b><?php echo _AT('create_content'); ?></b> <?php echo _AT('you'); ?>
        <label id="label_create_page" class="label_link"><?php echo _AT('create_cont_page'); ?><img src="<?php echo TR_BASE_HREF; ?>images/add_sibling_page.gif" alt=""  title=""/></label><br></div>
        <div style="margin:10px 0px 0px 122px;"><?php echo _AT('Or_'); ?><label id="label_create_folder" class="label_link"><?php echo _AT('create_cont_folder'); ?><img src="<?php echo TR_BASE_HREF; ?>images/add_sibling_folder.gif" alt=""  title=""/></label>
        </div>
        <label style="font-size:12px;"><i><br><?php echo _AT('description'); echo _AT('description_create_pf'); ?></i></label>
        
    </div>
    

<div id="buttons_intro">
    <div class="row buttons">
        <!--<label class="label_button" id="first_continue">Continue</label>-->
        <input style="width: 120px; height: 30px;"  type="submit" name="back_step2" value="Back" title="Back alt-b"  accesskey="b" />
 <!--       <input style="width: 120px; height: 30px;"  type="submit" name="continue" value="Next" title="Next alt-n" accesskey="n" /> -->
    </div>
</div>
        
        
        <div id="step2_body" style="display:none;" class="row">
            <div style="font-weight:bold; weight: 10%; margin: -8px 0px 0px 12px;">
                <span class="required" title="<?php echo _AT('required_field'); ?>"></span>
                <label id ="ftitle_page" for="ftitle_page" style="display:none;" ><?php echo _AT('title_wizard_step2_page'); ?></label>
                <label id="ftitle_folder" for="ftitle_folder" style="display:none;"><?php echo _AT('title_wizard_step2_folder'); ?></label>
            </div>
            <table style="weight: 10%; margin: 10px 0px 0px 15px;">
         <!--       <tr style="padding-top: -5px;">
                    <td>
                        <?php //echo _AT('folder_or_content'); ?>
                        <input style="margin-left: 10px;" id="radio-contentpage" type="radio" name="create" value="content_page" /><label><?php echo _AT('content_page') ?></label>
                        <input type="radio" id="radio-folder" name="create" value="folder" /><label> <?php echo _AT('folder'); ?></label>
                        <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i><?php echo _AT('description_foldcontent');?></label>
                    </td>
                </tr>
         -->
                <td></td>
                <tr>
                    <td><?php echo _AT('enter'); ?>
                    <input style=" margin-left: 20px; width: 290px;" type="text" name="title" />
                    <label style="font-size:12px;"><br><i><?php echo _AT('description'); ?> : </i><?php echo _AT('description_title');?></label></td>
                </tr>
                
                <?php 
                if($goal==1){                      
                    $xml=  simplexml_load_file(TR_BASE_HREF.'templates/goals/knowledge.xml');
                    ?>
                    <td></td>
                    <tr><td><div id="goals_title"><?php echo _AT('choose_goal'); ?></div></td></tr>
                    <input type="hidden" name="active_goal" value="on" />
                    <tr>
                       <td>
                           <div id="goals_knowledge">
                            <fieldset><legend><?php echo _AT('knowledge'); ?></legend>
                                <table>
                                    <tr>
                                <?php
                                    $support=1;
                                    foreach($xml->children() as $child) {
                                        if($support>3){
                                        ?>
                                        </tr>
                                        <?php $support=1;
                                        
                                        ?> <tr> <?php } ?>
                                            <td>
                                        <input id="<?php echo $child;?>" type="checkbox" name="goal_knowledge_<?php echo str_replace(" ", "",$child);?>" value="<?php echo $child;?>"/>                                    
                                            </td><td> <label style=" margin-right: 15px;"><?php echo $child; ?></label>
                                            </td>
                                    <?php
                                    $support++;
                                    }
                                ?>
                                </tr>
                                </table>
                            </fieldset>
                            </div>
                      </td>
                 </tr>
                 <tr>
                     <?php  $xml=  simplexml_load_file(TR_BASE_HREF.'templates/goals/meta-competency.xml'); ?>
                      <td>
                          <div id="goals_metacompetency">
                          <fieldset><legend><?php echo _AT('meta-competency'); ?></legend>
                              <table>
                                    <tr>
                          <?php
                            $support=1;
                            foreach($xml->children() as $child){
                                if($support>3){
                                    ?>
                                    </tr>
                                    <?php $support=1;
                                    
                                    ?><tr> 
                                    <?php } ?>
                                        <td>
                                <input id="<?php echo $child;?>" type="checkbox" name="goal_meta_<?php echo str_replace(" ", "",$child);?>" value="<?php echo $child;?>"/>                                    
                                        </td><td><label style="margin-right: 15px;"><?php echo $child; ?></label>
                                </td>
                            <?php
                            $support++;
                            }
              
                         ?>
                          
                              </tr>
                              </table>
                          </fieldset>
                          </div>
                      </td>
                 </tr>
               
                 <tr>
                      <td>
                          <?php  $xml=  simplexml_load_file(TR_BASE_HREF.'templates/goals/creative.xml'); ?>
                          <div id="goals_creative">
                          <fieldset><legend><?php echo _AT('creative'); ?></legend>
                              <table>
                                    <tr>
                          <?php
                           $support=1;
                           foreach($xml->children() as $child) {
                                if($support>3){ ?>
                                </tr>
                                <?php $support=1; ?>
                                <tr>
                                <?php } ?>
                            <td>
                                <input id="<?php echo $child;?>" type="checkbox" name="goal_creative_<?php echo str_replace(" ", "",$child);?>" value="<?php echo $child;?>"/>                                    
                            </td><td><label style="margin-right:10px;"><?php echo $child; ?></label>
                            </td>
                                <?php
                                $support++;
                            }                                
                        }
    
                        ?>
                              </tr>
                            </table>
                          </fieldset>
                        </div>
                      </td>
                </tr>
                
                <tr>
                    <td>
                        <div id="no_golas_with_folder" style="display:none;">
                        <label style="font-size:12px;"><br><i>Information : </i>If you create a folder, you can not bind a structure, the latter can only be used on occasions you create a content page.
                        </div>    
                    </td>
                </tr>

            </table>
            <input type="hidden" name="select" value="on" />
            <div id="buttons_body" style="display: none;">
                <div class="row buttons">
                    <!--<label class="label_button" id="first_continue">Continue</label>-->
                    <input style="width: 120px; height: 30px;" type="submit" name="back_step2" value="Back" title="Back alt-b"  accesskey="b" />
                    <input style="width: 120px; height: 30px;" type="submit" name="continue" value="Next" title="Next alt-n" accesskey="n" />
                </div>
            </div>
          </div>  
    </div>  
</form>


