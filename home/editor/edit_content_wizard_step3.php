<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
    define('TR_INCLUDE_PATH', '../../include/');
    define("PATH", "../../include/");
    require_once(TR_INCLUDE_PATH.'vitals.inc.php');
    require_once(TR_INCLUDE_PATH.'../home/editor/editor_tab_functions.inc.php');
    require_once(TR_INCLUDE_PATH.'../home/classes/ContentUtility.class.php');
    require_once(TR_INCLUDE_PATH.'../home/classes/StructureManager.class.php');
    require_once(PATH.'classes/DAO/ContentDAO.class.php');

    Utility::authenticate(TR_PRIV_ISAUTHOR);

    $onload = "document.form.title.focus();";

    global  $_content_id;
    global  $_course_id;
    global  $_pid;
    $contentDAO = new ContentDAO();

    require(TR_INCLUDE_PATH.'header.inc.php');
    $savant->display('home/editor/edit_content_wizard_step3.tmpl.php');
    require(TR_INCLUDE_PATH.'footer.inc.php');
?>
