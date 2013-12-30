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
		$this->core->api->get('user/user/loggedin',array('auth'=>!empty($_COOKIE['authed'])?$_COOKIE['authed']:''));
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
		}
	}

	public function rules()
	{
		$this->core->plugins->newRule(array('preg'=>'^signup$','page'=>'signup.php','plugin'=>'users'));

		$this->core->plugins->newRule(array('preg'=>'^logout$','page'=>'login.php','get'=>array('act'=>'logout'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^login$','page'=>'login.php'/*,'get'=>array('act'=>'login')*/,'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^login/restore$','page'=>'login.php','get'=>array('act'=>'restore'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^login/restore/([a-z0-9]*)$','page'=>'login.php','get'=>array('act'=>'restore','code'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^login/confirm$','page'=>'login.php','get'=>array('act'=>'confirm'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^login/confirm/([a-z0-9]*)$','page'=>'login.php','get'=>array('act'=>'confirm','code'=>'$1'),'plugin'=>'users'));

		$this->core->plugins->newRule(array('preg'=>'^users$','page'=>'users.php','get'=>array('act'=>'all','page'=>'1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/page-([0-9]*)$','page'=>'users.php','get'=>array('act'=>'all','page'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/id([0-9]*)$','page'=>'users.php','get'=>array('act'=>'user','page'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/confirm$','page'=>'users.php','get'=>array('act'=>'confirm','page'=>'1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>array('^users/confirm/page-([0-9]*)$','^admin/other/users/confirm/page-([0-9]*)$'),'page'=>'users.php','get'=>array('act'=>'confirm','page'=>'$1'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^users/([A-Za-z0-9_]*)$','page'=>'users.php','get'=>array('act'=>'user','login'=>'$1'),'plugin'=>'users'));

		$this->core->plugins->newRule(array('preg'=>'^profile(|\/)$','page'=>'profile.php','get'=>array('type'=>'main'),'plugin'=>'users'));
		$this->core->plugins->newRule(array('preg'=>'^profile/([A-Za-z_]*)$','page'=>'profile.php','get'=>array('type'=>'$1'),'plugin'=>'users'));
	}

	public function RegisterPluginEvent($id,$plugin,$info)
	{
		switch($id.'_'.$plugin)
		{
			case 'admin_menu_render_admin':
				if(in_array($_SESSION['rank_main'],array(1,2,3)))$info['other']['users_confirm'] = array('icon'=>'plus','value'=>'Подтверждение новых пользователей','href'=>'users/confirm/page-1');
				if($_SESSION['rank_main']<1)unset($info['api'],$info['client']);
				break;

			case 'admin_access_admin':
				if(preg_match("'^((client|api)/)|(other/(users/confirm)/)'i",$info[1]) && $_SESSION['rank_main']<1)
				{
					$info[0] = false;
				}
				if($info[1]=='' && $_SESSION['rank_main']<1)
				{
					$info[0] = false;
				}
				break;

			case 'render_widget_menu':
				if($_SESSION['loggedin'])
				{
					$info[] = array('Пользователи','users');
					$info[] = array('Настройки','profile');
					$info[] = array('Выход','logout');
				}
				else
				{
					$info[] = array('Пользователи','users');
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
			'1'=>'Гл. Администратор',
			'2'=>'Администратор',
			'3'=>'Модератор',
			'4'=>'Инспектор',
			'5'=>'Дизайнер',
			'6'=>'Журналист',
			'7'=>'Пользователь',
			'8'=>'Посетитель',
		);

		return isset($appointments[$rank]) ? $appointments[$rank]:false;
	}
}