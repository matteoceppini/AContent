<?php
/*
To change this template, choose Tools | Templates
and open the template in the editor.
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

?>
<script type="text/javascript" src="<?php echo TR_BASE_HREF;?>home/editor/js/wizard.js"></script>
<div class="row buttons">
<form action="<?php echo TR_BASE_HREF; ?>home/editor/edit_content_wizard_delete.php?_cid=<?php echo $cid; ?>" method="post" name="form">
    
            <input style="width: 120px; height: 30px;" type="submit" name="back_step3" value="Back" title="Back alt-b"  accesskey="b" />
            <input id="delete" style="display:none; width: 120px; height: 30px;" type="submit" name="delete" value="Delete" title="Delete alt-d" accesskey="d" />
    
</form>
<input style="width: 120px; height: 30px;" id="d_confirm" type="submit" name="d_confirm" value="Delete" title="Delete alt-d" accesskey="d" />
</div>   