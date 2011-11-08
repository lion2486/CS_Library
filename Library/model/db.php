<?php
class Lbdb{
	private $connection, $hostname, $username, $password, $dbname;
	//TODO try to handle the table names and the collumn names in variables/arrays 
	public $booklist;
	function __construct()
	{
		global $db_hostname, $db_username, $db_password, $db_name;
		$this->hostname = $db_hostname;
		$this->username = $db_username;
		$this->password = $db_password;
		$this->dbname = $db_name;
		$this->connect();
	}
	
	function __destruct(){
		$this->close();
	}
	
	function connect(){
		$this->connection = mysql_connect($this->hostname, $this->username, $this->password) 
			or die("Could not connect: ".mysql_error());
		mysql_select_db($this->dbname, $this->connection)
			or die("Error selecting database: ".mysql_error());
		mysql_query("SET NAMES 'utf8'", $this->connection)
			or die(mysql_error());
	}
	
	function close(){
		@mysql_close($this->connection);
	}
	
	/*
	 * Need some fixing, in order to get
	 * protected from harmful queries. 
	 */
	function query($query){
		$results = mysql_query($query, $this->connection) or die("Query error: ".mysql_error());
		return $results;
	}
	
	/*
	 * Returns an array both numeric and associative
	 * with $items books in it, with $limit_offset.
	 * Also first element book[0] represents how
	 * many books are indexed in our db.
	 * 
	 * Returns 
	 */
	function get_books($limit_offset, $items, $query = "SELECT * FROM booklist ORDER BY booklist.id ASC LIMIT "){
		$res = $this->query($query.$limit_offset.",". ($items+1) .";");
		for($i = 1; $books[$i] = mysql_fetch_array($res); $i++);
		if($books['1'] == FALSE)	
			return $books;
		array_pop($books);
		$a = mysql_fetch_array($this->query("SELECT COUNT(*) FROM booklist;"));
		$books['0'] = $a['0'];
		return $books;
	}
	
	/*
	 * Searches for a specific book or books througth the library
	 * Mode 1 searches only in title
	 * Mode 2 searches only in writer_organization
	 * Mode 3 searches both title and writer_organization
	 */
	function search($string, $mode, $limit_offset, $items){
		$s = mysql_real_escape_string(trim(mysql_real_escape_string($string)));
		$query = "SELECT * FROM `booklist` WHERE ";
		if($mode == '1')
			$query .= " booklist.title LIKE \"%$s%\"";
		else if($mode == '2')
			$query .= " booklist.writer_or LIKE \"%$s%\" ";
		else
			$query .= " booklist.title LIKE \"%$s%\" OR booklist.writer_or LIKE \"%$s%\" ";
		$query .= "ORDER BY booklist.id ASC LIMIT ";
		
		$books = $this->get_books($limit_offset, $items, $query);
		return $books;
	}
}
global $db;
$db = new Lbdb();
?>