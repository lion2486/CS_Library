<?php 
	if(!defined('VIEW_NAV'))
		die("Invalid request!");
	define('VIEW_SHOW', true);

	$logged = $user->is_logged_in();
?>
<div id="direction"><a href="index.php">Αρχική</a></div>
<div class="content">
	<div class="block" id="show-index">
		render_template("indexPanel.php");
	</div>
	<?php if(announcements::num() > 0) { ?>
		<div class="block" id="announcements">
			<div id="announcements-header">Ανακοινώσεις</div>
			<?php announcements::show(); ?>
			<div class="center">
				<?php if($user->is_admin()) { ?>
					<a href="index.php?show=admin&more=announcements&id=0"><button type="button" class="link">Νέα</button></a>
				<?php } ?>
				<a href="#"><button type="button" class="link">Παλιότερες</button></a>
			</div>
		</div>
	<?php } ?>
</div>
