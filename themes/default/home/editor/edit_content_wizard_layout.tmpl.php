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

global $_course_id,$_cid;

define("PATH", "../../include/");
require_once(PATH.'classes/DAO/ContentDAO.class.php');

$contentDAO = new ContentDAO();

$_course_id		= $course_id = (isset($_REQUEST['course_id']) ? intval($_REQUEST['course_id']) : $_course_id);
$_content_id	= $cid = (isset($_REQUEST['cid']) ? intval($_REQUEST['cid']) : $_content_id); /* content id of an optional chapter */

// paths settings

$mod_path					= array();
$mod_path['templates']		= realpath(TR_BASE_HREF			. 'templates').'/';
$mod_path['templates_int']	= realpath(TR_INCLUDE_PATH		. '../templates').'/';
$mod_path['templates_sys']	= $mod_path['templates_int']	. 'system/';
$mod_path['layout_dir']		= $mod_path['templates']		. 'layout/';
$mod_path['layout_dir_int']	= $mod_path['templates_int']	. 'layout/';

// include the file "applicaTema" so that he can inherit variables and constants defined by the system
include_once($mod_path['templates_sys'].'Layout.class.php');

// instantiate the class layout (which calls the constructor)
$layout		= new Layout($mod_path);

// take the list of available valid layout
$layout_list	= $layout->getLayoutList();

$sql="SELECT content_id FROM ".TABLE_PREFIX."content WHERE course_id=".$_course_id."";
$result=$contentDAO->execute($sql);
if(is_array($result))
{
    foreach ($result as $support) {
       $cid_content=$support['content_id'];
       break;
    }  
}
$sql = "SELECT layout FROM ".TABLE_PREFIX."content WHERE content_id =".$cid_content."";
$result=$contentDAO->execute($sql);
if(is_array($result))
{
    foreach ($result as $support) {
       $layout_associated=$support['layout']; 
       break;
    }  
}

?>
<script type="text/javascript" src="<?php echo TR_BASE_HREF; ?>home/editor/js/wizard.js"></script>

<div class="input-form" style="width:95%; margin-left:1.5em;">
<?php if($layout_associated==''){ ?>    
<div style=" weight: 10%; margin: 10px 0px 0px 10px;">In this moment there isn't an associated layout.</div>
<?php }else{ ?>
<div style=" weight: 10%; margin: 10px 0px 0px 10px;">In this moment the layout associated is:<b> <?php echo $layout_associated; ?></b></div>
<?php } ?>
<div style=" weight: 10%; margin: 10px 0px 0px 10px;">Select your favorite layout:</div>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="templates" method="post">
    <input type="hidden" name="cid" value="<?php echo $_cid; ?>" />
    <div style=" weight: 10%; margin: 10px 0px 0px 10px;">
        <div style="margin: 5px;">
            <input type="submit" style="width:250px;" value="<?php echo _AT('layout_content_apply'); ?>" id="apply_layout_to_content" name="apply_layout_to_content" />
            <input type="submit" style="width:250px;" value="Back" id="layout_back" name="layout_back" />      
        </div>
    </div>
    <div style="margin: 10px;">
		
    <table class="data" rules="cols" summary="">
    <thead>
            <tr>
            <th scope="col">&nbsp;</th>
            <th scope="col">Name</th>
            <th scope="col">Description</th>
            <th scope="col">Icone</th>
            </tr>
            </thead>

    <tbody>
    <tr onclick="preview('nothing');" onmousedown="document.form['radio-nothing'].checked=!document.form['radio-nothing'].checked; togglerowhighlight(this,'radio-nothing')">
    <td id="radio_nothing" name="<?php echo $cid_content; ?>" title="nothing"><input id="radio-nothing" mouseseup="this.checked=!this.checked" type="radio" name="radio_layout" value=""></td>
    <td>Nothing</td>
    <td>Without layout</td>
    <td><div><img class="layout_img_small"  src="<?php echo TR_BASE_HREF; ?>templates/system/nolayout.png" style="height:48px;" src=""  desc="Nothing Screenshot" title="Nothing Screenshot" id="layoutscreenshot"  /></td></div>     
    </tr> 
    </tr>
                        
    <?php 
    foreach($layout_list as $tname => $tval){ 
        ?>
        <tr onclick="preview('<?php echo $tname; ?>');" onmousedown="document.form['radio-'<?php echo $tname; ?>].checked=!document.form['radio-'<?php echo $tname; ?>].checked;togglerowhighlight(this,'radio-'<?php echo $tname; ?>);">
            <td id="radio_<?php echo $tname; ?>" name="<?php echo $cid_content; ?>" title="<?php echo $tname; ?>">
                <input  id="radio-<?php echo $tname; ?>" mouseseup="this.checked=!this.checked" type="radio" name="radio_layout" value="<?php echo $tname; ?>">
            </td>

       
            <td><?php echo $tval['name'] ?></td>
            <td><?php echo $tval['description']; ?></td>
            <?php
            if($tname!='seti' && $tname!='windows'&& $tname!='unibo') {
                ?>
                <td><div><img class="layout_img_big" src="<?php echo TR_BASE_HREF; ?>templates/layout/<?php echo $tname; ?>/screenshot-<?php echo $tname; ?>.png" alt="Error Screenshot <?php echo $tname; ?>" desc="Screenshot <?php echo $tname; ?>" title="Screenshot <?php echo $tname; ?>" id="layoutscreenshot"  /></td></div>      
                </tr>
                <?php
            }elseif($tname != unibo){ ?>
                <td><div><img  class="layout_img_small" src="<?php echo TR_BASE_HREF; ?>templates/layout/<?php echo $tname; ?>/screenshot-<?php echo $tname; ?>.png" alt="Error Screenshot <?php echo $tname; ?>" desc="Screenshot <?php echo $tname; ?>" title="Screenshot <?php echo $tname; ?>" id="layoutscreenshot"  /></td></div>      
                </tr> 
            <?php }else{ ?>
                <td><div><img  src="<?php echo TR_BASE_HREF; ?>templates/layout/<?php echo $tname; ?>/screenshot-<?php echo $tname; ?>.png" alt="Error Screenshot <?php echo $tname; ?>" desc="Screenshot <?php echo $tname; ?>" title="Screenshot <?php echo $tname; ?>" id="layoutscreenshot"  /></td></div>       
                </tr>
            <?php }

        }
        ?>
    </tbody>                
    </table>                     
</div>
</form>

<div id="content">

</div> 

</div>              
<script>
$('.unsaved').css('display','none');
</script>

