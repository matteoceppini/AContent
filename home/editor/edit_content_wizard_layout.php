<?php

/************************************************************************/
/* AContent                                                             */
/************************************************************************/
/* Copyright (c) 2010                                                   */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

define('TR_INCLUDE_PATH', '../../include/');
require_once(TR_INCLUDE_PATH.'vitals.inc.php');
require_once(TR_INCLUDE_PATH.'../home/editor/editor_tab_functions.inc.php');
require_once(TR_INCLUDE_PATH.'../home/classes/ContentUtility.class.php');
require_once(TR_INCLUDE_PATH.'../home/classes/StructureManager.class.php');

global  $_content_id,$contentManager, $_course_id;

Utility::authenticate(TR_PRIV_ISAUTHOR);

$cid=$_GET['_cid'];
if(isset($_POST['apply_layout_to_content'])){

    define("PATH", "../../include/");
    require_once(PATH.'classes/DAO/ContentDAO.class.php');
    $contentDAO = new ContentDAO();
    $layout=$_POST['radio_layout'];
    $sql = "UPDATE ".TABLE_PREFIX."content 
		           SET layout='$layout'
		         WHERE content_id = ".$_content_id;
    
    $result=$contentDAO->execute($sql);

    header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
    exit;
      
}
if(isset($_POST['layout_back'])){
    header('Location: '.TR_BASE_HREF.'home/editor/edit_content_wizard_step3.php?_course_id='.$_course_id.'&_content_id='.$cid);
    exit; 
}
  
  
$onload = "document.form.title.focus();";
require(TR_INCLUDE_PATH.'header.inc.php');
$savant->display('home/editor/edit_content_wizard_layout.tmpl.php');
require(TR_INCLUDE_PATH.'footer.inc.php');
?>
