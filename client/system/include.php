<?php
require_once(dirname(__FILE__).'/core.class.php');

session_start();
$core = new core();

$core->render['MAIN']['MENU'] = array('menu','vk','online','ads','news');
$core->render['NAVMENU'] = array(
array('Главная',''),
array('Новости','articles',false,'+42'),
array('Пользователи','users'),
array('Регистрация','signup'),
array('Вход','login'),
);

//TODO: Remake it!
$s = explode('/',$_SERVER['SCRIPT_NAME']);
foreach($core->render['NAVMENU'] as &$m)
{
	if($m[1].'.php'==$s[sizeof($s)-1] or ($m[1]=='' AND $s[sizeof($s)-1]=='index.php'))
	{
		$m[2] = true;
	}
}

$core->api->get('user.loggedin',array());
$loggedin = $core->api->answer_decode;
if(isset($loggedin['errors']))
{
if(sizeof($loggedin['errors'])==0)
{
$_SESSION['loggedin'] = $core->render['SYS']['LOGGEDIN'] = $loggedin['data'][0];
}
else $_SESSION['loggedin'] = $core->render['SYS']['LOGGEDIN'] = false;

if($_SESSION['loggedin']==true)
{
	$_SESSION['nickname'] = $core->render['SYS']['NICKNAME'] = $loggedin['data']['nickname'];
	$_SESSION['email'] = $core->render['SYS']['EMAIL'] = $loggedin['data']['email'];
	$_SESSION['id'] = $core->render['SYS']['ID'] = $loggedin['data']['id'];
	$_SESSION['login'] = $core->render['SYS']['LOGIN'] = $loggedin['data']['login'];
	$_SESSION['rank'] = $core->render['SYS']['RANK'] = $loggedin['data']['rank'];
	$_SESSION['rank_main'] = $core->render['SYS']['RANK_MAIN'] = $loggedin['data']['rank_main'];
	
	
	$core->render['SYS']['APPOINTMENT'] = 'Undefined';
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

//$core->render['MAIN']['INFO'][] = 'Инфа';
//$core->render['MAIN']['ERRORS'][] = 'Ошибка';
//$core->render['MAIN']['SUCCESS'][] = 'Успешно';

//$core->api->get('plugins.list',array('sid'=>'sdsd'));
//echo '<pre>';print_r($core);echo '</pre>';
?>