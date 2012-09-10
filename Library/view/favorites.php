<?php 
	if(!defined('VIEW_NAV'))
		die("Invalid request!");
	define('VIEW_SHOW', true);
	
	global $db, $user; ?>
	<div id="direction"><a href="index.php">Αρχική</a>&nbsp;&gt;&gt;&nbsp;Αγαπημένα Βιβλία</div>
	<?php
	if(!$user->is_logged_in()){ 
		echo "<div class=\"content\"><div class=\"error\">Πρέπει να συνδεθείτε πρώτα.<br />";
		redirect("index.php?show=login");
	}else{
		if(isset($_GET['action']) && $_GET['action'] == "add"){
			if(!isset($_GET['id']))
				echo '<div class="error">Invalid request.</div>';
			else
				$user->favorites->add_favorite($db->db_escape_string($_GET['id']));
		}elseif(isset($_GET['action']) && $_GET['action'] == "remove"){
			if(!isset($_GET['id']))
				echo '<div class="error">Invalid request.</div>';
			else
				$user->favorites->delete_favorite($db->db_escape_string($_GET['id']));
		}
		$books = $db->get_books($user->favorites->get_favorites(!empty($_GET['id']) ? $db->db_escape_string($_GET['id']) : -1), 
								"SELECT * FROM `{$db->table['favorites']}` WHERE `user_id` = '{$user->id}';");
		echo "<div class=\"content\">";
			list_books($books);
		echo "</div>";
	}
?>