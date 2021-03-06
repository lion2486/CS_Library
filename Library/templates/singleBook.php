<?php
	if(!defined('VIEW_NAV'))
		die("Invalid request!");
	global $book, $lended, $logged, $requested, $have, $taken, $lend, $msg, $CONFIG, $user;
	
?>
<div class="book-info">
	<div class="book-left-info">
		<div class="book-image" id="book-image"><a href="<?php echo ($book->image_url == NULL) ? "view/images/noimage.jpg": $book->image_url; ?>"><img src="<?php echo ($book->image_url == NULL) ? "view/images/noimage.jpg": $book->image_url; ?>" alt="<?php echo str_replace('"', "'", $book->title); ?>" title="<?php echo str_replace('"', "'", $book->title); ?>" /></a></div>
		<div class="book-isbn">ISBN: <?php echo $book->isbn; ?></div>
		<div class="book-writer">
			<span class="book-colored">Συγγραφέας:</span><br />
			<span class="book-prop"><?php echo strlen($book->writer)>=2 ? $book->writer : "Άγνωστος"; ?></span>
		</div>
		<div class="book-publisher">
			<span class="book-colored">Εκδότης:</span><br />
			<span class="book-prop"><?php echo strlen($book->publisher)>=2 ? $book->publisher : "Άγνωστος"; ?></span>
		</div>
		<div class="book-category">
			<span class="book-colored">Κατηγορία:</span><br />
			<span class="book-prop"><?php echo ($book->numCategories == 0) ? "Χωρίς Κατηγορία" : get_category_name($book->id); ?></span>
		</div>
	</div>
	<div class="book-right-info">
		<div class="book-title"><?php echo $book->title; ?></div>
		<div class="book-avail block">
			<span class="book-colored">Διαθεσιμότητα:</span>
			<?php if($logged && $have){
					?><span class="avail">Το έχεις εσύ</span><?php
				}else{
					echo (!$have && !$requested && $book->availability == 1) ? "<span class=\"avail\">Διαθέσιμο</span>" : "<span class=\"avail_no\">Μη Διαθέσιμο</span>"; 
				} ?>
		</div>
		<div id="buttons" class="block">
			<?php favorites::show_favorites_button($book->id, "single");
    		if(!$have && !$requested && $book->availability == 1){ ?>
	    		<a class="<?php echo $logged ? "request-book" : "must-login"; ?>" href="<?php echo $logged ? "?show=book&amp;id={$_GET['id']}&amp;lend=1" : "?show=login"; ?> ">
	    			<button type="button" class="book-button link box center bold">Δανείσου το</button></a>
    		<?php } elseif($logged && $have) { ?>
    			<div class="book-button box"><a class="renewal" href="#">Ανανέωση</a></div>
    		<?php } ?>
		</div><!--  #buttons end -->
		<?php if($logged && $requested && !$have && !isset($_GET['lend'])){
			?> <div class="error">Έχεις κάνει ήδη μια αίτησή για αυτό το βιβλίο, θα το πάρεις όταν είναι διαθέσιμο.</div><?php ;
		}
		if($requested && isset($_GET['lend'])){
			?> <div class="success">Το αίτημά σας κατοχυρώθηκε και θα εξεταστεί από το διαχειριστή.</div><?php ;
		}elseif(!$requested && isset($_GET['lend'])){
			?> <div class="error">Έχεις ξεπεράσει το όριο δανεισμών/αιτημάτων.<br />
				Να υπενθυμίσουμε ότι ισχύει: <br />
				1) Όριο δανεισμών: <?php echo $CONFIG['lendings']; ?><br />
				2) Όριο αιτημάτων: <?php echo $CONFIG['requests']; ?><br />
				Πρέπει όμως
				3) Tο άθροισμα αιτημάτων και δανεισμένων σας βιβλίων να μην ξεπερνά το όριο δανεισμών
			</div><?php ;
    	}elseif($logged && $have){
			?><div class="success" >
				Έχεις πάρει αυτό το βιβλίο την <span class="bold"><?php echo date('d-m-Y στις H:i', strtotime($have->taken)); ?></span> και <br />
				θα πρέπει να το επιστρέψεις μέχρι την <span class="bold"><?php echo date('d-m-Y', strtotime($have->must_return)); ?></span>
			</div><?php
		}elseif ($book->availability == 0){
			?><div class="error" >
				Το βιβλίο προβλέπεται να είναι διαθέσιμο ξανά μετά την <span class="bold"><?php echo date('d-m-Y', strtotime($book->get_book_date())); ?></span>
			</div><?php
		}elseif(isset($msg)){ ?>
			<?php echo "<div class=\"error\" >".$msg."<br />";
			redirect("index.php?show=login", 2500);
		} ?>
		<div class="book-description">
			<span class="book-colored">Περιγραφή: </span><span style="font-size: 17px;">
				<?php if($book->description != NULL) {
					echo $book->description;
				} else {
					echo "Μη διαθέσιμη.";
				} ?>
			</span> 
		</div>
		<?php if($user->is_admin()) { ?>
			<a href="index.php?show=book&edit=edit&id=<?php echo $book->id; ?>" class="link-button">
				<button type="button" class="index-button link box center bold">Επεξεργασία</button>
			</a>
			<a href="index.php?show=book&edit=delete&id=<?php echo $book->id; ?>" class="link-button delete-book">
				<button type="button" class="index-button link box center bold">Διαγραφή</button>
			</a>
		<?php } ?>
	</div>
	<div id="<?php echo $book->id; ?>" class="rate_widget">
		<div class="star_1 ratings_stars"></div>
		<div class="star_2 ratings_stars"></div>
		<div class="star_3 ratings_stars"></div>
		<div class="star_4 ratings_stars"></div>
		<div class="star_5 ratings_stars"></div>
		<div class="total_votes">βαθμολογία χρηστών</div>
	</div>
	<?php 
		render_template('ratings.php');
	?>
</div><!-- .book-info end -->
<script type="text/javascript">
	$(function() {
		$('#book-image a').lightBox();
	});
</script>
<?php echo "</div>"; ?>