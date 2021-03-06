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

/**
 * This script handles the ajax post submit from "content editor" =? "adpated content"
 * to save the selected alternative into database
 * @see file_manager/filemanager_display.inc.php
 * @var $_POST values: 
 *      pid: primary resource id
 *      a_type: alternative type, must be one of the values in resource_types.type_id
 *      alternative: the location and name of the selected alternative
 */

define('TR_INCLUDE_PATH', '../../include/');
require_once(TR_INCLUDE_PATH.'vitals.inc.php');

$pid = intval($_POST['pid']);
$type_id = intval($_POST['a_type']);
$secondary_resource = trim($_POST['alternative']);

// check post vars
if ($pid == 0 || $type_id == 0 || $secondary_resource == '') exit;

require_once(TR_INCLUDE_PATH.'classes/DAO/DAO.class.php');
$dao = new DAO();

// delete the existing alternative for this (pid, a_type)
$sql = "SELECT sr.secondary_resource_id 
          FROM ".TABLE_PREFIX."secondary_resources sr, ".TABLE_PREFIX."secondary_resources_types srt
         WHERE sr.secondary_resource_id = srt.secondary_resource_id
           AND sr.primary_resource_id = ".$pid."
           AND sr.language_code = '".$_SESSION['lang']."'
           AND srt.type_id=".$type_id;
//$existing_secondary_result = mysql_query($sql, $db);
$existing_secondary_rows = $dao->execute($sql);

if (is_array($existing_secondary_rows)) {
	foreach ($existing_secondary_rows as $existing_secondary)
	{
		$sql = "DELETE FROM ".TABLE_PREFIX."secondary_resources 
		         WHERE secondary_resource_id = ".$existing_secondary['secondary_resource_id'];
		$dao->execute($sql);
	
		$sql = "DELETE FROM ".TABLE_PREFIX."secondary_resources_types 
		         WHERE secondary_resource_id = ".$existing_secondary['secondary_resource_id']."
		           AND type_id=".$type_id;
		$dao->execute($sql);
	}
}

// insert new alternative
$sql = "INSERT INTO ".TABLE_PREFIX."secondary_resources (primary_resource_id, secondary_resource, language_code)
        VALUES (".$pid.", '".mysql_real_escape_string($secondary_resource)."', '".$_SESSION['lang']."')";
$dao->execute($sql);
$secondary_resource_id = mysql_insert_id();

$sql = "INSERT INTO ".TABLE_PREFIX."secondary_resources_types (secondary_resource_id, type_id)
        VALUES (".$secondary_resource_id.", ".$type_id.")";
$dao->execute($sql);

exit;

?>