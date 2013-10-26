<?php
class plugin_user_load
{
	public function __construct($core)
	{
		$this->core = $core;

		$this->core->mysql->connect("mcprimary");

		//Авторизирован ли пользователь
		if(isset($_SESSION['id']) AND isset($_SESSION['login']))
		{
			if($_SESSION['id']!='' AND $_SESSION['login']!='')
				$_SESSION['loggedin'] = true;
		}
		else
			$_SESSION['loggedin'] = false;

		if(!$_SESSION['loggedin'])
		{
			$_SESSION['id'] = '';
		}
	}

	public function OnEnable()
	{

	}

	public function OnDisable()
	{

	}
}
?>