<?php 
	if(!defined('VIEW_NAV'))
		die("Invalid request!");
	define('VIEW_SHOW', true);
	session_unset(); 
	session_destroy();
?>
<div id="direction"><a href="index.php">Αρχική</a>&nbsp;&gt;&gt;&nbsp;Έξοδος</div>
<div class="content">
	<?php echo "<div class=\"success\">Επιτυχής αποσύνδεση...<br />";
	 redirect($CONFIG['url']); ?>
</div>