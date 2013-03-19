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

if (!defined('TR_INCLUDE_PATH')) { exit; }
if (!defined('TR_BASE_HREF')) { exit; }
require_once(TR_INCLUDE_PATH.'vitals.inc.php');
require_once(TR_INCLUDE_PATH.'classes/DAO/ContentDAO.class.php');
require_once(TR_INCLUDE_PATH.'/../home/classes/StructureManager.class.php');

global $_course_id;
$contentDAO = new ContentDAO();

?>

<div style=" weight: 10%; margin: 10px;">
<?php    
    echo _AT('create_content_4', TR_BASE_HREF.'home/editor/edit_content_wizard_step1.php?_course_id='.$_course_id, "");
?>
</div>
