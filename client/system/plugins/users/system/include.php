<?php
function appointment($rank)
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

$core->api->get('user.loggedin',array('auth'=>!empty($_COOKIE['authed'])?$_COOKIE['authed']:''));
$loggedin = $core->api->answer_decode;
if(isset($loggedin['errors']))
{
	if(sizeof($loggedin['errors'])==0)
	{
		$_SESSION['loggedin'] = $core->render['SYS']['LOGGEDIN'] = $loggedin['data'][0];
		if($loggedin['data'][0]===true)
		if($_COOKIE['authed']!=$loggedin['data']['id'].':'.$loggedin['data']['auth'])
		{
			setcookie("authed", $loggedin['data']['id'].':'.$loggedin['data']['auth'], time()+36000, '/');
		}
	}
	else
	{
		
		$_SESSION['loggedin'] = $core->render['SYS']['LOGGEDIN'] = false;
	}
	
	if($_SESSION['loggedin']==true)
	{
		$_SESSION['nickname'] = $core->render['SYS']['NICKNAME'] = $loggedin['data']['nickname'];
		$_SESSION['email'] = $core->render['SYS']['EMAIL'] = $loggedin['data']['email'];
		$_SESSION['id'] = $core->render['SYS']['ID'] = $loggedin['data']['id'];
		$_SESSION['login'] = $core->render['SYS']['LOGIN'] = $loggedin['data']['login'];
		$_SESSION['rank'] = $core->render['SYS']['RANK'] = $loggedin['data']['rank'];
		$_SESSION['rank_main'] = $core->render['SYS']['RANK_MAIN'] = $loggedin['data']['rank_main'];
		
		
		$core->render['SYS']['APPOINTMENT'] = appointment($_SESSION['rank_main'])?appointment($_SESSION['rank_main']):'Undefined';
	}
	else
	{
		$_SESSION['nickname'] = '';
		$_SESSION['email'] = '';
		$_SESSION['id'] = '';
		$_SESSION['login'] = '';
		$_SESSION['rank'] = '';
		$_SESSION['rank_main'] = '';
	}
}

$core->render['MAIN']['MENU'] = $core->conf->conf->core->menu;

if($_SESSION['loggedin'])
{
	$core->render['NAVMENU'] = array(
	array('Главная',''),
	array('Новости','articles',false/*,'+42'*/),
	array('Пользователи','users'),
	array('Настройки','profile'),
	array('Выход','logout'),
	);
}
else
{
	$core->render['NAVMENU'] = array(
	array('Главная',''),
	array('Новости','articles'),
	array('Пользователи','users'),
	array('Регистрация','signup'),
	array('Вход','login'),
	);
}
?>