<?php
/************************************************************************/
/* AContent                                                             */
/************************************************************************/
/* Copyright (c) 2013                                                   */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

if (!defined('TR_INCLUDE_PATH')) { exit; }

print_progress($step);

?>
<p><strong>Congratulations on your installation of AContent <?php echo $new_version; ?><i>!</i></strong></p>

<p>For security reasons once you have confirmed that AContent has installed correctly, you should delete the <kbd>install/</kbd> directory,
and reset the permissions on the config.inc.php file to read only. Use the administrator or author account created in the earlier step to login.</p>

<br />

<form method="get" action="../index.php">
	<div align="center">
		<input type="submit" name="submit" value="&raquo; Go To AContent!" class="button" />
	</div>
</form>