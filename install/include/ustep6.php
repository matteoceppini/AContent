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

print_progress($step);

?>
<p><strong>Congratulations on your upgrade of AContent <?php echo $new_version; ?><i>!</i></strong></p>

<p>It is important that you login as the AContent administrator to review and set any new System Configuration options.</p>
<p>For security reasons,  after you have confirmed the installation was successful, it is also important that you delete the <kbd>install/</kbd> directory and reset the<kbd> /include/config.inc.php</kbd> file to read-only. On Linux/Unix systems, use <kbd>chmod a-w include/config.inc.php</kbd>.</p>
<p>See the <a href="http://www.atutor.ca/forum/18/1.html">Support Forums</a> on <a href="http://www.atutor.ca/acontent/">atutor.ca</a> for additional help &amp; support.</p>

<br />

<form method="get" action="../index.php">
	<div align="center">
		<input type="submit" name="submit" value="&raquo; Go To AContent!" class="button" />
	</div>
</form>