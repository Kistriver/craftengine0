<?php
class plugin_user_load
{
	public function __construct($core)
	{
		$this->core = $core;
		
		$this->core->mysql->connect("mcprimary");
		//$core = new core();
		
		//require_once(dirname(__FILE__).'/user.class.php');
		//require_once(dirname(__FILE__).'/rank.class.php');
	}
}
?>