<?php
/* User class, in order to use user-account
 * system, we can continue...
 */

class User{
	public $id, $username, $email, $access_level, $message;
	public $admin;
	public $favorites;
	
	function __constructor(){
	    $a = new Admin;
	    $admin = null;
	}
	
	private static function pass_encrypt($pass){
		return $pass;
		//return md5($pass);
	}

	public function login($name, $pass){
		global $db;
		
		$name = mysql_real_escape_string($name);
		$pass = mysql_real_escape_string($pass);
		$pass = $this->pass_encrypt($pass);
		$query = "	SELECT * FROM `{$db->table['users']}`
					WHERE 	`username` = '$name' 
					AND 	`password` = '$pass'
					LIMIT 1 ;";
		$result = $db->query($query);
		//TODO use the full capabilities of fetch_object for user
		$user = mysql_fetch_object($result);
	    if($user){
	    	$this->id 					= $user->id;
	    	$this->access_level 		= $user->access_lvl;
	    	$this->username				= $user->username;
	    	$this->email				= $user->email;

	    	$_SESSION['logged_in']		= 1;
			$_SESSION['cur_page'] 		= $_SERVER['SCRIPT_NAME'];
			$_SESSION['sessionid'] 		= session_id();
	    }
		return $user;
	}
	
	public static function createUser($user, $pass, $mail){
		global $db, $CONFIG;
		$user = mysql_real_escape_string($user);
		$pass = mysql_real_escape_string($pass);
		$pass = user::pass_encrypt($pass);
		$mail = mysql_real_escape_string($mail);
		$query2 = "(";
		if(!empty($_POST['name'])){
		    $x = mysql_real_escape_string($_POST['name']);
		    $query2 .= "'".$x."', ";
		}
		else
		    $query2 .= "NULL, ";
		if(!empty($_POST['surname'])){
		    $x = mysql_real_escape_string($_POST['surname']);
		    $query2 .= "'".$x."', ";
		}
		else
		    $query2 .= "NULL, ";
		$query2 .= "'".$user."', ";
		if(!empty($_POST['user_type'])){
		    $x = mysql_real_escape_string($_POST['user_type']);
		    $query2 .= "'".$x."', ";
		}
		else
		    $query2 .= "'', ";
		$query2 .= " '".$pass."', ";
		if(!empty($_POST['born'])){
		    $x = mysql_real_escape_string($_POST['born']);
		    $query2 .= "'".$x."', ";
		}
		else
		    $query2 .= "NULL, ";
		if(!empty($_POST['phone'])){
		    $x = mysql_real_escape_string($_POST['phone']);
		    $query2 .= "'".$x."', ";
		}
		else
		    $query2 .= "NULL, ";
		$query2 .= " '".$mail."', '0', NOW(), '".$_SERVER['REMOTE_ADDR']."') ";
		$query = "INSERT INTO `{$db->table['users']}` 
					(`name`, `surname`, `username`, `usertype`, 
					 `password`, `born`, `phone`, `email`, `access_lvl`, 
					 `created_date`, `last_ip`) VALUES";
		$query .= $query2;
        $db->query($query);
		//TODO send an e-mail to user
		return;
	}
	
	public static function delete_user($id){
		global $db, $CONFIG; 
		$query = "	DELETE FROM `{$db->table['users']}`
					WHERE `{$db->table['users']}`.`id` = '$id'
					LIMIT 1";
		echo "<div class=\"success\">Ο χρήστης ".user::get_name($id)." διαγράφηκε.<br />";
		//TODO we should print the username instead of the name
		$db->query($query);
		redirect($CONFIG['url']."?show=admin&more=users", 2500);
	}

	public function show_history($user_id = -1){
	    global $db;
	    if($user_id == -1)
	    	$user_id = $this->id;
		$query = "	SELECT * FROM `{$db->table['log_lend']}`
					CROSS JOIN `{$db->table['booklist']}`
					ON `{$db->table['booklist']}`.id = `{$db->table['log_lend']}`.book_id
					WHERE `{$db->table['log_lend']}`.`user_id` = '$user_id'
					ORDER BY `{$db->table['log_lend']}`.`returned`";
		$result = $db->query($query);
		echo "<table id=\"history\"><tr><th>Βιβλίο</th><th>Το Πήρες</th><th>Το Έφερες</th></tr>";
		$flag = 0;
		while($row = mysql_fetch_array($result)){
			if($flag++ % 2 == 0)
				echo "\t\t\t\t<tr class=\"alt\">";
			else
				echo "\t\t\t\t<tr>";
			echo "<td class=\"table-book-title\"><a href=\"index.php?show=book&id={$row['book_id']}\">{$row['title']}</a></td>";
			echo "<td class=\"date\">".date('d-m-Y', strtotime($row['taken']))."</td>\n";
			echo "<td class=\"date\">".date('d-m-Y', strtotime($row['returned']))."</td></tr>\n";
		}
		echo "</table><br />"; 
		return;
	}

	public function show_info($user_id = -1){
        global $db, $user_info;
		if($user_id == -1)
			$user_id = $this->id;
		$query = "SELECT * FROM `{$db->table['users']}` WHERE `id` = '$user_id'";
		$result = $db->query($query);
		$user_info = mysql_fetch_object($result);
	}
	
	public function update($user_id = -1){
		global $db;
		if($user_id == -1)
			$user_id = $this->id;
		$query = "	SELECT * FROM `{$db->table['users']}`
					WHERE 	`id` = '$user_id'
					AND 	`password` = '".mysql_real_escape_string($_POST['password'])."'
					LIMIT 1 ;";
		$result = $db->query($query);
		if(mysql_num_rows($result)){
			$q = "UPDATE `{$db->table['users']}` SET
			`name` = '".mysql_real_escape_string($_POST['name'])."',
			`surname` = '".mysql_real_escape_string($_POST['surname'])."',
			`born` = '".mysql_real_escape_string($_POST['born'])."',
			`phone` = '".mysql_real_escape_string($_POST['phone'])."',
					`email` = '".mysql_real_escape_string($_POST['email'])."'";
			if(isset($_POST['n_pass']) && $_POST['n_pass'] != ""){
				if($_POST['n_pass'] == $_POST['r_n_pass'] /*&& check_password($_POST['n_pass'])*/)
					$q .= ", `password` = '".mysql_real_escape_string($_POST['n_pass'])."'";
			}
			$q .= " WHERE users.id = '$user_id' AND users.password = '".mysql_real_escape_string($this->pass_encrypt($_POST['password']))."';";
			$db->query($q);
			echo "<div class=\"success\">Οι αλλαγές σας αποθηκεύτηκαν.</div>";
		}
		else
			echo "<div class=\"error\">Δώσατε λάθος κωδικό.</div>";
	}
	
	public function is_logged_in(){
		return isset($_SESSION['logged_in']) 
		&& ($_SESSION['logged_in'] == 1) 
		&& ($this->access_level >= 0);
	}
	
	public function show_login_status(){
		global $CONFIG;
		$code = "<span>";
		$more = " | <a id=\"lnkFeedback\" href=\"?show=feedback\"><span class=\"icon\"></span><span class=\"tooltip\">Επικοινωνία</span></a> | <a id=\"lnkHelp\" href=\"javascript: pop_up('{$CONFIG['url']}?show=help')\"><span class=\"icon\"></span><span class=\"tooltip\">Βοήθεια</span></a>";
		if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 1){
			if($CONFIG['allow_login'])
				$code .= "<a id=\"lnkLogin\" href=\"?show=login\"><span class=\"icon\"></span><span class=\"tooltip\">Είσοδος";
			if($CONFIG['allow_register'])
				//TODO $code .= "/Εγγραφή ";
			$code.= "</span></a>".$more;
		}
		elseif($_SESSION['logged_in'] == 1){
			$code .= "	<a id=\"lnkAccount\" href=\"?show=cp\"><span class=\"icon\"></span><span class=\"tooltip\">Προφίλ</span></a>|
						<a id=\"lnkFavorites\" href=\"?show=favorites\"><span class=\"icon\"></span><span class=\"tooltip\">Αγαπημένα</span></a>";
			if($this->is_admin() /*Trying something with better looing $this instanceof Admin*/)
			    $code .= " | <a id=\"lnkAdmin\" href=\"?show=admin\"><span class=\"icon\"></span><span class=\"tooltip\">Admin</span></a>";
				//$code .= " | <a id=\"\" href=\"?show=admin\"><span class=\"icon\"></span><span class=\"tooltip\">Admin</span></a> | <a id=\"\" href=\"?show=msg\"><span class=\"icon\"></span><span class=\"tooltip\">Μηνύματα</span></a>";
		    $code.= $more;
		    $code .= " | <a id=\"lnkLogout\" href=\"?show=logout\"><span class=\"icon\"></span><span class=\"tooltip\">Έξοδος</span></a>";
		}
		return $code . "</span>";
	}
	
	public function session_check(){
		if(!isset($_SESSION['logged_in']))
			session_empty();
		if(!isset($_SESSION['last_active'])){
	    	$_SESSION['last_active'] = time() + MAX_IDLE_TIME;
		}else{
	    	if($_SESSION['last_active'] < time()){   
		    	session_unset(); 
		        session_destroy();
		    }else{
		        $_SESSION['last_active'] = time() + MAX_IDLE_TIME;
		    }
		}
		$_SESSION['cur_page'] 	= $_SERVER['SCRIPT_NAME'];
		$_SESSION['sessionid'] 	= session_id();
	}

	public function is_admin(){
	    return ($this->access_level >= 100) ? true : false;
	}
	
	public function cansel_request($id, $current_url){
		global $db;	

		$query = "DELETE FROM `requests` WHERE `id` = '$id' AND `user_id` = '{$this->id}'; ";
		$db->query($query);
		echo "<div class=\"success\">Η αίτηση δανεισμού σου ακυρώθηκε!<br />";
		redirect($current_url);
	}	
	
	public static function get_name($id){
		global $db;
		$query = "SELECT name FROM {$db->table['users']} WHERE `id` = '".mysql_real_escape_string($id)."';";
		$result = $db->query($query);
		$ret = mysql_fetch_row($result);
		return $ret[0];
	}
	
	public static function username_check($username){
		global $db;
		$query = "SELECT * FROM `{$db->table['users']}` WHERE `username` = '".mysql_real_escape_string($username)."' LIMIT 1;";
		$result = $db->query($query);
		$num = mysql_num_rows($result);
		return ($num == 1);	
	}

	function show_lended(){
		global $CONFIG, $db;
		$query = "SELECT * FROM `{$db->table['booklist']}` CROSS JOIN `{$db->table['lend']}` 
					ON {$db->table['booklist']}.id = {$db->table['lend']}.book_id 
				  WHERE {$db->table['lend']}.user_id = '{$this->id}' ";
		$books = $db->get_books($query);
		if(!$books){ ?>
			<div class="error">Δεν έχετε δανειστεί κανένα βιβλίο.</div>
		<?php }
		list_books($books); 
		return;
	}
};

?>