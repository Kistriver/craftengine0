<?php
class plugin_user_load
{
	public function __construct()
	{
		include_once(dirname(__FILE__).'/user.class.php');
		include_once(dirname(__FILE__).'/rank.class.php');
	}
}
?>