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

Utility::authenticate(TR_PRIV_ISAUTHOR);



$onload = "document.form.title.focus();";
require(TR_INCLUDE_PATH.'header.inc.php');
$savant->display('home/editor/edit_content_wizard_step1.tmpl.php');
require(TR_INCLUDE_PATH.'footer.inc.php');

?>
