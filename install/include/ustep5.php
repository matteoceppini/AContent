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

if(isset($_POST['submit'])) {
	unset($_POST['submit']);
	unset($action);
	store_steps($step);
	$step++;
	return;
}

$file = '../include/config.inc.php';

unset($errors);
unset($progress);

if ( file_exists($file) ) {
	@chmod($file, 0666);
	if (!is_writeable($file)) {
		$errors[] = '<strong>' . $file . '</strong> is not writeable. Use <kbd>chmod a+rw '.$file.'</kbd> to change permissions.';
	}else{
		$progress[] = '<strong>' . $file . '</strong> is writeable.';
	}
} else {
	$errors[] = '<strong>' . $file . '</strong> does not exist.';
}

print_progress($step);

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="form">';

if (isset($errors)) {
	if (isset($progress)) {
		print_feedback($progress);
	}
	print_errors($errors);

	echo'<input type="hidden" name="step" value="'.$step.'" />';

	unset($_POST['step']);
	unset($_POST['action']);
	unset($errors);
	print_hidden($step);

	echo '<p><strong>Note:</strong> To change permissions on Unix use <kbd>chmod a+rw</kbd> then the file name.</p>';

	echo '<p align="center"><input type="submit" class="button" value=" Try Again " name="retry" />';

} else {

	if (!copy('../../'.$_POST['step1']['old_path'] . '/include/config.inc.php', '../include/config.inc.php')) {
		echo '<input type="hidden" name="step" value="'.$step.'" />';

		print_feedback($progress);

		$errors[] = 'include/config.inc.php cannot be written! Please verify that the file exists and is writeable. On Unix issue the command <kbd>chmod a+rw include/config.inc.php</kbd> to make the file writeable. On Windows edit the file\'s properties ensuring that the <kbd>Read-only</kbd> attribute is <em>not</em> checked and that <kbd>Everyone</kbd> access permissions are given to that file.';
		print_errors($errors);

		echo '<p><strong>Note:</strong> To change permissions on Unix use <kbd>chmod a+rw</kbd> then the file name.</p>';

		echo '<p align="center"><input type="submit" class="button" value=" Try Again " name="retry" />';

	} else {
		echo '<input type="hidden" name="step" value="'.$step.'" />';
		print_hidden($step);

		$progress[] =  'Data has been saved successfully.';

		@chmod('../include/config.inc.php', 0444);

		print_feedback($progress);

		echo '<p align="center"><input type="submit" class="button" value=" Next &raquo; " name="submit" /></p>';
		
	}
}

?>

</form>