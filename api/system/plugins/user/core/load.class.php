<?php
namespace CRAFTEngine\plugins\user;
class load
{
	public function __construct($core)
	{
		$this->core = $core;

		$this->core->mysql->connect("mcprimary");

		//ACCESS TO SYSTEM API MODULE
		if(!empty($_SESSION['rank_main']))
		if($_SESSION['rank_main']==1)
		$this->core->conf->system->core->admin_ip[] = $_SERVER['REMOTE_ADDR'];

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

	public function registerPluginEvent($id,$plugin,$addInfo)
	{
		return $addInfo;
	}
}
?>