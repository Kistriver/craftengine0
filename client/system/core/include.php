<?php
//ini_set('display_errors', 0);
session_start();

$_SESSION['sid'] = session_id().'php';

function display($core, $twig, $num)
{
	$allow = array(403,404,500);
	$key = array_search($num, $allow);
	if(!isset($allow[$key]))die;
	$template = $twig->loadTemplate('errors/'.$allow[$key]);
	header('HTTP/1.1 '.$allow[$key]);
	echo $template->render($core->render());
	die;
}

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

include_once(dirname(__FILE__).'/core.class.php');
$core = new core();

$core->get('user.loggedin',array('sid'=>$_SESSION['sid']));
$loggedin = $core->answer_decode;
if(sizeof($loggedin['errors'])==0)
$_SESSION['loggedin'] = $core->render['SYS']['LOGGEDIN'] = $loggedin['data'][0];
else $_SESSION['loggedin'] = $core->render['SYS']['LOGGEDIN'] = false;

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

$core->render['SYS']['MENU']['LEFT'][] = 'menu';
$core->render['SYS']['MENU']['LEFT'][] = 'monitoring';
$core->render['SYS']['MENU']['LEFT'][] = 'online';

$core->render['SYS']['MENU']['RIGHT'][] = 'vk';
$core->render['SYS']['MENU']['RIGHT'][] = 'ads';

$menu = array();
$menu[] = array('Главная','');
$menu[] = array('Новости','articles');
$menu[] = array('Пользователи','users');
if($_SESSION['rank_main']==1)$menu[] = array('Плагины','plugins');
if(!$_SESSION['loggedin'])$menu[] = array('Регистрация','signup');
if($_SESSION['loggedin'])$menu[] = array('Настройки','profile');
if(!$_SESSION['loggedin'])$menu[] = array('Вход','login');
if($_SESSION['loggedin'])$menu[] = array('Выход','logout');
$core->render['MAIN']['MENU']['MAIN'] = $menu;

$ver = $core->render['MAIN']['V'];

$core->render['MAIN']['VERSION'] = 'v1.2 closed alpha';

include_once(dirname(__FILE__).'/libs/Twig/Autoloader.php');
Twig_Autoloader::register(true);
$loader = new Twig_Loader_Filesystem(dirname(__FILE__).'/../../php/tpl/'.$ver);
$twig = new Twig_Environment($loader,array(/*'cache'=>dirname(__FILE__).'/../../system/tmp',*/'auto_reload'=>true,'autoescape'=>false));