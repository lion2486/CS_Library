<?php
/* User class, in order to use user-account
 * system, we can continue...
 */
class User{
	public $id, $username, $email, $access_level, $department_id, $admin, $message;
	
	function __constructor(){
		$admin = null;
	}
	
	function pass_encrypt($pass){
		return $pass;
		//return md5($pass);
	}

	function login($name, $pass){
		global $db;
		$db->connect();
		$name = mysql_real_escape_string($name);
		$pass = mysql_real_escape_string($pass);
		$pass = $this->pass_encrypt($pass);
		$query = "	SELECT * FROM `{$db->table["users"]}`
					WHERE 	`username` = '$name' 
					AND 	`password` = '$pass'
					LIMIT 1 ;";
		$result = $db->query($query);
		$user = mysql_fetch_array($result);
		$db->close();
	    if($user){
	    	$this->id 					= $user['id'];
	    	$this->access_level 		= $user['access_lvl'];
	    	$this->username				= $user['username'];
	    	$this->email				= $user['email'];
	    	$this->department_id		= $user['dep_id'];
            
	    	//$_SESSION['user']           = serialize($this);
	    	$_SESSION['logged_in']		= 1;
			$_SESSION['cur_page'] 		= $_SERVER['SCRIPT_NAME'];
			$_SESSION['sessionid'] 		= session_id();
	    }
		return $user;
	}
	
	function createUser($user, $pass, $mail, $dep_id){
		global $db;
		$db->connect();
		$user = mysql_real_escape_string($user);
		$pass = mysql_real_escape_string($pass);
		$pass = $this->pass_encrypt($pass);
		$mail = mysql_real_escape_string($mail);
		$dep_id = mysql_real_escape_string($dep_id);
		
		$query = "INSERT INTO `{$db->table["users"]}` 
					(`dep_id`, 
					 `username`, 
					 `password`, 
					 `email`, 
					 `access_lvl`, 
					 `created_date`, 
					 `last_ip`) VALUES 
					('$dep_id', '$user', '$pass', '$mail', '-1', 'NOW()', '".$_SERVER['REMOTE_ADDR']."') ";
		$db->query($query);
		//TODO add a confirmation link to a table
		$db->close();
		return;
	}
	
	function activateAccount(){
		//TODO have to consider how and where will store the activation keys
		return;
	}
	
	//TODO replace tale/column names below here
	function show_history($mode = 0, $user_id = -1){
		/**
		 * $mode values:
		 * default: 0, normal user mode, see the history of 1 user
		 * 1, admin, show all the histories
		 * 2, admin, show pendings only....
		 */
	    global $db;
	    if($user_id == -1)
	    	$user_id = $this->id;
	    if($mode == 1) 
	    	$query = "	SELECT * FROM `{$db->table["history"]}`
	    				CROSS JOIN `{$db->table["users"]}` 
	    				ON {$db->table["users"]}.id = {$db->table["history"]}.user_id 
						ORDER BY `date`";
	    elseif($mode == 2)
	    	$query = "	SELECT * FROM `{$db->table["history"]}`
	    				CROSS JOIN `{$db->table["users"]}` 
	    				ON {$db->table["users"]}.id = {$db->table["history"]}.user_id 
						GROUP BY `book_id`, `action` 
	    				ORDER BY `date`";
	    else
			$query = "	SELECT * FROM `{$db->table["history"]}`
						WHERE `user_id` = '$user_id'
						ORDER BY `date`";	    
		$result = $db->query($query);
		echo "<table><tr><th>Book</th>";
		echo ($mode ) ? "<th>User</th>" : "";
		echo "<th>Action</th><th>Date</th></tr>";
		while($row = mysql_fetch_array($result)){
			echo "<tr><td>".$row['title']."</td>";
			echo ($mode) ? "<td>{$row['name']} ({$row['user_id']})</td>" : "";
			echo "<td>";
            switch($row['action']){
		    case 1:
		    	echo ($mode) && book_avail($row['book_id'])
		    	? "<a href=\"?show=admin&more=lend&lend={$row['book_id']}&user={$row['user_id']}\" class=\"request-book\">Request</a>"
				: "Request (<a href=\"?show=cp&more=remove_request&id={$row['id']}\" class=\"cansel-request\">Delete</a>)";
		        break;
		    case 2:
		    	//TODO Change the actions to know if lended is now lended and if were lended in the past
				echo "Lended";
		        break;
		    case 3:
		    	//TODO return or back is the correct action for an admin?
		    	echo ($mode ) 
		    	? "<a href=\"?show=admin&more=return&return={$row['book_id']}&user={$row['user_id']}\" class=\"return-book\">Have it now</a>"
				: "Have it now";
				break;
            }
            echo "</td>";
			echo  "<td>".$row['date']."</td></tr><tr></tr>";
		}
		echo "</table>";
		return;
	}

	function show_info($user_id = -1){
        global $db;
        if($user_id == -1)
        	$user_id = $this->id;
        if(isset($_POST['hidden'])){
            $query = "	SELECT * FROM `{$db->table["users"]}`
            					WHERE 	`id` = '$user_id' 
            					AND 	`password` = '".mysql_real_escape_string($_POST['password'])."'
            					LIMIT 1 ;";
            $result = $db->query($query);
            if(mysql_num_rows($result)){
                $q = "UPDATE `{$db->table["users"]}` SET 
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
                echo "<span class=\"success\">Οι αλλαγές σας αποθηκεύτηκαν.</span>";
            }
            else
                echo "<span class=\"error\">Δώσατε λάθος κωδικό.</span>";
        }
        else{
            $query = "SELECT tmp1.username, tmp1.name, tmp1.surname, tmp1.born, tmp1.phone, tmp1.email, tmp1.tmima, tmp2.name as incharge_n, tmp2.surname as incharge_s FROM
    					(SELECT users.username, users.name, users.surname, users.born, users.phone, users.email, departments.name as tmima, departments.incharge FROM users
    						CROSS JOIN departments
    							ON users.dep_id = departments.id
    					WHERE users.id = '$user_id' ) AS tmp1
    						CROSS JOIN users AS tmp2
    							ON tmp1.incharge = tmp2.id";
            $result = $db->query($query);
            $row = mysql_fetch_assoc($result); ?>
            <form action="" method="post" id="change-info">
            <label for="username">Username: </label><input type="text" id="username" name="username" disabled="disabled" value="<?php echo $row['username']; ?>" /><br />
            <label for="incharge">Incharge: </label><input type="text" id="incharge" name="incharge" disabled="disabled" value="<?php echo $row['incharge_n']." ".$row['incharge_s']; ?>" /><br />
            <label for="name">Name: </label><input type="text" id="name" name="name" value="<?php echo $row['name']; ?>" /><br />
            <label for="surname">Surname: </label><input type="text" id="surname" name="surname" value="<?php echo $row['surname']; ?>" /><br />
            <label for="email">E-mail: </label><input type="email" id="email" name="email" value="<?php echo $row['email']; ?>" /><br />
            <label for="born">Born: </label><input type="date" id="born" name="born" value="<?php echo $row['born']; ?>" /><br />
            <label for="phone">Phone: </label><input type="tel" id="phone" name="phone" value="<?php echo $row['phone']; ?>" /><br />
            <label for="n_pass">New Password: </label><input type="password" id="n_pass" name="n_pass" /><br />
            <label for="r_n_pass">Repeat New Password: </label><input type="password" id="r_n_pass" name="r_n_pass" /><br />
            <label for="password">Your Password: </label><input type="password" id="password" name="password" /><br />
    			<input type="hidden" name="hidden" value="1" />   
    			<?php if($user_id == $this->id) {?>     
            		<input type="submit" value="Update" />
            	<?php } ?>
            </form><?php
        }
	}
	
	function is_logged_in(){
		return isset($_SESSION['logged_in']) 
		&& ($_SESSION['logged_in'] == 1) 
		&& ($this->access_level >= 0);
	}
	
	function show_login_status(){
		global $CONFIG, $url;
		$code = "";
		$more = " | <a href=\"?show=feedback\">Feedback</a> | <a href=\"javascript: pop_up('$url?show=help')\">Βοήθεια</a>";
		if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 1){
			if($CONFIG['allow_login'])
				$code .= "<a href=\"?show=login\">Είσοδος</a>";
			if($CONFIG['allow_register'])
				$code .= "<a href=\"?show=login\">/Εγγραφή</a> ";
			$code.= $more;
		}
		elseif($_SESSION['logged_in'] == 1){
			$code .= "<a href=\"?show=cp\">". /*$this->*/$this->username . "</a> |  ";
			if($this->is_admin() /*Trying something with better looing $this instanceof Admin*/)
				$code .= "<a href=\"?show=admin\">Admin</a> | <a href=\"?show=msg\">Μηνύματα</a>";
		    $code.= $more;
		    $code .= " | <a href=\"?show=logout\">Έξοδος</a>";
		}
		return $code;
	}
	
	function session_check(){
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

	function is_admin(){
	    return ($this->access_level >= 100) ? true : false;
	}
	
	function cansel_request($id){
		global $db;	
		
		$query = "DELETE FROM `requests` WHERE `id` = '$id' AND `user_id` = '{$this->id}'; ";
		$db->query($query);
		?>
		<p class="success">Your request have been deleted!</p>
		<?php 		
	}	
	
	public static function get_name($id){
		global $db;
		$query = "SELECT name FROM {$db->table['users']} WHERE `id` = '".mysql_real_escape_string($id)."';";
		$result = $db->query($query);
		$ret = mysql_fetch_row($result);
		return $ret[0];
	}
};

?>