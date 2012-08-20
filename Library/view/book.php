<?php 
	if(!defined('VIEW_NAV'))
		die("Invalid request!");
	define('VIEW_SHOW', true);
	
	if(!isset($_GET['id']))
		die("Λάθος αίτημα");
		
	$lended  = FALSE;
	$have = FALSE;
	$requested = FALSE;
	$id = mysql_real_escape_string($_GET['id']);
	$results = $db->query("SELECT *, COUNT(category_id) AS numCategories 
							FROM {$db->table['booklist']} 
							CROSS JOIN  {$db->table['book_has_category']} 
							ON id = book_id 
							WHERE `id` = '$id';");
	if($logged = $user->is_logged_in()){
		$have = have_book($id, $user->id);
		$requested = have_book_rq($id, $user->id);
		$query = "SELECT * FROM `{$db->table['lend']}` WHERE `user_id` = '{$user->id}';";
		$res = $db->query($query);
		for($i = 0; $tmp = mysql_fetch_array($res); $i++){
		    $lend[$i][0] = $tmp['book_id'];
		    $lend[$i][1] = $tmp['taken'];
		}
	}
	if(mysql_num_rows($results) == 0)
		die("Λάθος αίτημα");
	$results = mysql_fetch_array($results);
	if(isset($_GET['lend']) && $logged && !$requested && !$have){
		lend_request($id);
		$lended = TRUE;
		$have = TRUE;
	}
	elseif(isset($_GET['lend']) && !$logged)
		$msg = "Θα πρέπει πρώτα να συνδεθείτε με το λογαριασμό σας!";
?>
<div id="direction">
	<a href="index.php">Αρχική</a>&nbsp;&gt;&gt;&nbsp;<a href="index.php?show=list">Κατάλογος βιβλίων</a>&nbsp;&gt;&gt;&nbsp;<?php echo $results['title']; ?>
</div>
<div class="content book-prev">
	<div class="book-title"><?php echo $results['title']; ?></div>
	<div class="book-info">
		<div class="book-left-info">
			<div class="book-image" id="book-image"><a href="<?php echo ($results['image_url'] == NULL) ? "view/images/noimage.jpg": $results['image_url']; ?>"><img src="<?php echo ($results['image_url'] == NULL) ? "view/images/noimage.jpg": $results['image_url']; ?>" alt="<?php echo str_replace('"', "'", $results['title']); ?>" title="<?php echo str_replace('"', "'", $results['title']); ?>" /></a></div>
			<div class="book-isbn">ISBN: <?php echo $results['isbn']; ?></div>
			<div class="book-writer">
				<span class="book-colored">Συγγραφέας:</span><br />
				<span class="book-prop"><?php echo strlen($results['writer'])>=2 ? $results['writer'] : "Άγνωστος"; ?></span>
			</div>
			<div class="book-publisher">
				<span class="book-colored">Εκδότης:</span><br />
				<span class="book-prop"><?php echo strlen($results['publisher'])>=2 ? $results['publisher'] : "Άγνωστος"; ?></span>
			</div>
			<div class="book-category">
				<span class="book-colored">Κατηγορία:</span><br />
				<span class="book-prop"><?php echo ($results['numCategories'] == 0) ? "Χωρίς Κατηγορία": get_category_name($results['id']);?></span>
			</div>
		</div>
		<div class="book-right-info">
			<div id="buttons" class="block">
				<div class="box book-button book-add-to-wish">
					<?php favorites::show_favorites_button($results['id']); ?>  
	    		</div>
	    		<?php if(!$have && !$requested && $results['availability']){ ?>
	    		<div class="box book-button book-lend-book" id="lend">
	    			<?php if(!$logged){ ?>
	    				<a onclick="return alert('Πρέπει να συνδεθείτε πρώτα');" href="?show=login">Δανείσου το</a>
	    			<?php }else{ ?>
	    				<a onclick="return confirm('Είσαι σίγουρος ότι θέλεις να το δανειστείς;');" href="?show=book&amp;id=<?php echo $_GET['id']; ?>&amp;lend=1">Δανείσου το</a>
	    			<?php }?>
	    		</div>
	    		<?php } elseif($logged && $have) { ?>
	    			<div class="book-button box"><a onclick="return alert('Μπορείτε να κρατήσετε το βιβλίο για άλλες 15 μέρες');" href="#">Ανανέωση</a></div>
	    		<?php } ?>
			</div><!--  #buttons end -->
			<div class="book-avail block">
				<span class="book-colored">Διαθεσιμότητα:</span>
				<?php if($logged && $have) {
						echo "<span class=\"avail\">Το έχεις εσύ</span>";
					} else {
						echo (!$have && $results['availability'] == 1) ? "<span class=\"avail\">Διαθέσιμο</span>" : "<span class=\"avail_no\">Μη Διαθέσιμο</span>"; 
					} ?>
			</div>
			<?php if($lended){ ?>
    			<p class="error">Το αίτημά σας κατοχυρώθηκε και θα εξεταστεί από το διαχειριστή.</p><?php ;
    		}
    		if($logged && $requested && !$have){ ?>
				<p class="error">Έχετε κάνει ήδη μια αίτησή για αυτό το βιβλίο, θα το πάρετε όταν είναι διαθέσιμο.</p>
			<?php }
			elseif($logged && $have){
				if($logged && (($taken = in_there_pos($lend, $id)) != -1)) { ?>
					<div class="error" >
					Έχεις πάρει αυτό το βιβλίο την <?php echo date('d-m-Y στις H:i', strtotime($taken)); ?> και <br />θα πρέπει να το επιστρέψεις μέχρι την 
					<?php echo date('d-m-Y', mktime(0, 0, 0, date("m", strtotime($taken)), date("d", strtotime($taken))+$CONFIG['lend_default_days'], date("Y", strtotime($taken))));?>
					</div><?php
				}
			} elseif(isset($msg)){ ?>
				<div class="error" ><?php echo $msg."<br />";
				redirect("index.php?show=login", 4);
			}
			/*
			 * <p class="error">Έχεις πάρει αυτό το βιβλίο την <?php echo date('d-m-Y στις H:i', strtotime($taken)); ?> και θα πρέπει να το επιστρέψει μέχρι την 
			 *	<?php echo date('d-m-Y', mktime(0, 0, 0, date("m", strtotime($taken)), date("d", strtotime($taken))+$CONFIG['lend_default_days'], date("Y", strtotime($taken))));?>
			 * </p>
			 */
			?>
			<div class="book-description">
				<span class="book-colored">Περιγραφή: </span><span style="font-size: 17px;">
					<?php if($results['description'] != NULL) { ?>
						<?php echo $results['description'];
					} else { ?> 
						Μη διαθέσιμη.
					<?php } ?>
				</span> 
			</div>
		</div>
	</div><!-- .book-right-info end -->
	<script type="text/javascript">
		$(function() {
			$('#book-image a').lightBox();
		});
	</script>
</div>