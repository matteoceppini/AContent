<?php
/************************************************************************/
/* ACConntent                                                       */
/************************************************************************/
/* Copyright (c) 2009                                                   */
/* Adaptive Technology Resource Centre / University of Toronto          */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

define('TR_INCLUDE_PATH', 'include/');
error_reporting(E_ALL ^ E_NOTICE);

require('../include/constants.inc.php');

$new_version = VERSION;

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

require(TR_INCLUDE_PATH.'header.inc.php');
?>


<p>AContent does not appear to be installed. <a href="index.php">Continue on to the installation</a>.</p>


<?php require(TR_INCLUDE_PATH.'footer.inc.php'); ?>