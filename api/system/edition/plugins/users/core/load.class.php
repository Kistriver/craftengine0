<?php
namespace CRAFTEngine\plugins\users;
class load
{
	public function __construct($core)
	{
		$this->core = $core;

		//$u=$this->core->plugin->initPl('users','user');
		//var_dump($u->getAllProperties('2'));
		//var_dump($u->getProperties('2','3'));

		//$uc=$this->core->plugin->initPl('users','core');
		/*var_dump($uc->signup(
			array(
				'name'=>'Alexey',
				'surname'=>'Kachalov',
				'login'=>'Kachalov27',
				'email'=>'alex-kachalov@mail.ru',
				'password_salt'=>12345,
				'password'=>'qwerty',
			)
		));*/

		/*$this->core->mysql->connect("mcprimary");

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
		}*/
	}

	public function OnEnable()
	{
		$user = $this->core->plugin->initPl('users','user');

		foreach($user->getPropertiesList() as $p)
		{
			$user->$p->install();
		}
	}

	public function OnDisable()
	{

	}

	public function registerPluginEvent($id,$plugin,$addInfo)
	{
		switch($id.'_'.$plugin)
		{
			case 'upload_complete_core':
				if($addInfo['type']=='users_user_avatar')
				{

				}
				break;
		}

		return $addInfo;
	}
}
?>