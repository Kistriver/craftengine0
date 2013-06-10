<?php
//ini_set('display_errors', 0);
session_start();

$_SESSION['sid'] = session_id().'php';

include_once(dirname(__FILE__).'/core.class.php');
$core = new core();

$main['NAME'] = 'KachalovCRAFT NET';
$main = array(
	'NAME'=>$main['NAME'],
	'TITLE'=>$main['NAME'],
	'KEYWORDS'=>$main['NAME'],
	'DESC'=>$main['NAME'],
	'HEADER'=>$main['NAME'],
	'ERRORS'=>'',
);

$core->get('system.loggedin',array('sid'=>$_SESSION['sid']));
$loggedin = $core->answer_decode;
if(sizeof($loggedin['errors'])==0)
$_SESSION['loggedin'] = $loggedin['data'][0];
else $_SESSION['loggedin'] = false;

if($_SESSION['loggedin']==true)
{
	$_SESSION['nickname'] = $loggedin['data']['nickname'];
	$_SESSION['email'] = $loggedin['data']['email'];
	$_SESSION['id'] = $loggedin['data']['id'];
	$_SESSION['login'] = $loggedin['data']['login'];
	$_SESSION['rank'] = $loggedin['data']['rank'];
	$_SESSION['rank_main'] = $loggedin['data']['rank_main'];
}

$mod = array(
);

$core->tpl->assign('SESSION',$_SESSION);

$core->tpl->tpl('/html/main/login');
$mod['LOGIN'] = $core->tpl->render();

$menu = array(
	'' => 'Главная',
	'users' => 'Пользователи',
	'login' => 'Вход',
	'signup' => 'Регистрация',
);
$con = '';
foreach ($menu as $key => $value)
{
	$core->tpl->tpl('/html/main/menus/menu');
	$core->tpl->assign('HREF',$key);
	$core->tpl->assign('TEXT',$value);
	$con .= $core->tpl->render();
}
$core->tpl->tpl('/html/main/menus/menus');
$core->tpl->assign('MENU',array('HEADER'=>'Меню','CONTENT'=>$con));
$mod['MENU'] = $core->tpl->render();

$core->tpl->tpl('/html/main/ads');
$mod['ADS'] = $core->tpl->render();

$core->tpl->tpl('/html/main/search');
$mod['SEARCH'] = $core->tpl->render();