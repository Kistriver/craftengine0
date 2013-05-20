<?php
class mysql
{
	public	$core,
			$db			=		array(),
			$result;
	
	public function __construct($core)
	{
		$this->core = $core;
		
		
	}
	
	public function __destruct()
	{
		//mysql_close($link);
	}
	
	public function connect($name, $host, $dbname, $user, $pass)
	{
		$db = mysql_connect($host, $user, $pass) or 
		trigger_error(mysql_error($db), E_USER_ERROR);
		mysql_select_db($dbname, $db) or 
		trigger_error(mysql_error($db), E_USER_ERROR);
		mysql_set_charset("utf8", $db);
		
		$this->db[$name] = $db;
	}
	
	public function query($query, $name=null)
	{
		if(empty($name))$name = $this->core->conf->db[0][0];
		$this->result = mysql_query($query, $this->db[$name]) or trigger_error(mysql_error(), E_USER_ERROR);
		return $this->result;
	}
}
?>