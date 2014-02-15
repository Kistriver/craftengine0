<?php
namespace CRAFTEngine\client\plugins\users;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function construct()
	{
		if($this->loggedin())
		{
			$this->core->render['SYS']['USERS']['ID'] = $_SESSION['users']['id'];
			$this->core->render['SYS']['USERS']['LOGIN'] = $_SESSION['users']['login'];
			$this->core->render['SYS']['USERS']['RANK'] = $_SESSION['users']['rank'];
		}
		else
		{
			$this->core->render['SYS']['USERS']['ID'] = $_SESSION['users']['id'] = 0;
			$this->core->render['SYS']['USERS']['LOGIN'] = $_SESSION['users']['login'] = null;
			$this->core->render['SYS']['USERS']['RANK'] = $_SESSION['users']['rank'] = array();
		}

		$this->core->render['SYS']['LOGGEDIN'] = $this->loggedin();

		/*$this->core->api->get('user/user/loggedin',array('auth'=>!empty($_COOKIE['authed'])?$_COOKIE['authed']:''));
		$loggedin = $this->core->api->answer_decode;
		if(isset($loggedin['errors']))
		{
			if(sizeof($loggedin['errors'])==0)
			{
				$_SESSION['loggedin'] = $this->core->render['SYS']['LOGGEDIN'] = $loggedin['data'][0];
				if($loggedin['data'][0]===true)
					if($_COOKIE['authed']!=$loggedin['data']['id'].':'.$loggedin['data']['auth'])
					{
						setcookie("authed", $loggedin['data']['id'].':'.$loggedin['data']['auth'], time()+36000, '/');
					}
			}
			else
			{

				$_SESSION['loggedin'] = $this->core->render['SYS']['LOGGEDIN'] = false;
			}

			if($_SESSION['loggedin']==true)
			{
				$_SESSION['nickname'] = $this->core->render['SYS']['NICKNAME'] = $loggedin['data']['nickname'];
				$_SESSION['email'] = $this->core->render['SYS']['EMAIL'] = $loggedin['data']['email'];
				$_SESSION['id'] = $this->core->render['SYS']['ID'] = $loggedin['data']['id'];
				$_SESSION['login'] = $this->core->render['SYS']['LOGIN'] = $loggedin['data']['login'];
				$_SESSION['rank'] = $this->core->render['SYS']['RANK'] = $loggedin['data']['rank'];
				$_SESSION['rank_main'] = $this->core->render['SYS']['RANK_MAIN'] = $loggedin['data']['rank_main'];
				$_SESSION['avatar_format'] = $this->core->render['SYS']['AVATAR_FORMAT'] = $loggedin['data']['avatar_format'];


				$this->core->render['SYS']['APPOINTMENT'] = $this->appointment($_SESSION['rank_main'])?$this->appointment($_SESSION['rank_main']):'Undefined';
			}
			else
			{
				$_SESSION['nickname'] = '';
				$_SESSION['email'] = '';
				$_SESSION['id'] = '';
				$_SESSION['login'] = '';
				$_SESSION['rank'] = '';
				$_SESSION['rank_main'] = '';
				$_SESSION['avatar_format'] = '';
			}
		}*/
	}

	public function rules()
	{
		$this->core->plugins->newRule(array('preg'=>'^signup$','page'=>'signup.php','plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^signup/activate/([0-9]*)-([0-9a-z]*)$','page'=>'signup.php','get'=>array('id'=>'$1','code'=>'$2','act'=>'activate'),'plugin'=>'users'));

		$this->core->plugins->newRule(array('preg'=>'^login$','page'=>'login.php','get'=>array('act'=>'login'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^logout$','page'=>'login.php','get'=>array('act'=>'logout'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^login/restore/([a-z0-9]*)$','page'=>'login.php','get'=>array('act'=>'restore','code'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^login/restore$','page'=>'login.php','get'=>array('act'=>'restore'),'plugin'=>'users'));

		$this->core->plugins->newRule(array('preg'=>'^users/page-([0-9]*)$','page'=>'users.php','get'=>array('act'=>'all','page'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/id([0-9]*)$','page'=>'users.php','get'=>array('act'=>'user','id'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/([A-Za-z0-9_]*)$','page'=>'users.php','get'=>array('act'=>'user','login'=>'$1'),'plugin'=>'users'));

		$this->core->plugins->newRule(array('preg'=>'^admin/other/users/import','page'=>'import.php','get'=>array(),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^profile/([A-Za-z_]*)$','page'=>'profile.php','get'=>array('type'=>'$1'),'plugin'=>'users'));

		/*
		$this->core->plugins->newRule(array('preg'=>'^users$','page'=>'users.php','get'=>array('act'=>'all','page'=>'1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/page-([0-9]*)$','page'=>'users.php','get'=>array('act'=>'all','page'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/id([0-9]*)$','page'=>'users.php','get'=>array('act'=>'user','page'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/confirm$','page'=>'users.php','get'=>array('act'=>'confirm','page'=>'1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>array('^users/confirm/page-([0-9]*)$','^admin/other/users/confirm/page-([0-9]*)$'),'page'=>'users.php','get'=>array('act'=>'confirm','page'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/([A-Za-z0-9_]*)$','page'=>'users.php','get'=>array('act'=>'user','login'=>'$1'),'plugin'=>'users'));

		$this->core->plugins->newRule(array('preg'=>'^profile(|\/)$','page'=>'profile.php','get'=>array('type'=>'main'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^profile/([A-Za-z_]*)$','page'=>'profile.php','get'=>array('type'=>'$1'),'plugin'=>'users'));*/
	}

	public function RegisterPluginEvent($id,$plugin,$info)
	{
		switch($id.'_'.$plugin)
		{
			case 'admin_menu_render_admin':
				foreach($_SESSION['users']['rank'] as $r)
				{
					if(in_array($r,array('main_admin','admin')))
						$info['other']['users_confirm'] = array('icon'=>'plus','value'=>'Подтверждение новых пользователей','href'=>'users/confirm/page-1');

					if(in_array($r,array('main_admin')))
						$info['other']['users_import'] = array('icon'=>'arrow-right','value'=>'Импортирование пользователей','href'=>'users/import');
				}
				break;

			case 'admin_access_admin':
				//if($info[0]===null)$info[0] = false;
				if(preg_match("'^other/users/import$'i",$info[1]))
				{
					foreach($_SESSION['users']['rank'] as $r)
					{
						if(in_array($r,array('main_admin')))
							$info[0] = true;
					}
				}
				if(preg_match("'^((client|api)/)|(other/(users/confirm)/)'i",$info[1]))
				{
					foreach($_SESSION['users']['rank'] as $r)
					{
						if(in_array($r,array('main_admin','admin','moderator')))
							$info[0] = true;
					}
				}
				if($info[1]=='')
				{
					foreach($_SESSION['users']['rank'] as $r)
					{
						if(in_array($r,array('main_admin','admin','moderator')))
							$info[0] = true;
					}
				}
				break;

			case 'render_widget_menu':
				if($this->loggedin())
				{
					$info[] = array('Пользователи','users/page-1');
					$info[] = array('Настройки','profile/main');
					$info[] = array('Выход','logout');
				}
				else
				{
					$info[] = array('Пользователи','users/page-1');
					$info[] = array('Регистрация','signup');
					$info[] = array('Вход','login');
				}
				break;
		}
		return $info;
	}

	public function appointment($rank)
	{
		$appointments = array(
			'main_admin'=>'Гл. Администратор',
			'admin'=>'Администратор',
			'moderator'=>'Модератор',
			'developer'=>'Разработчик',
			'user'=>'Пользователь',
			'guest'=>'Посетитель',
		);

		return isset($appointments[$rank]) ? $appointments[$rank]:$rank;
	}

	public function loggedin()
	{
		if($this->currentUser()===0)return false;
		else return true;
	}

	public function currentUser($id=null)
	{
		if($id!==null)$_SESSION['users']['id'] = $id;

		return isset($_SESSION['users']['id'])?$_SESSION['users']['id']:0;
	}
}