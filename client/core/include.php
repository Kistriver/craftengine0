<?php
//ini_set('display_errors', 0);
session_start();

$_SESSION['sid'] = session_id().'php';

include_once(dirname(__FILE__).'/core.class.php');
$core = new core();

$core->get('system.loggedin',array('sid'=>$_SESSION['sid']));
$loggedin = $core->answer_decode;
if(sizeof($loggedin['errors'])==0)
$_SESSION['loggedin'] = $loggedin['data'][0];
else $_SESSION['loggedin'] = false;

if($_SESSION['loggedin']==true)
{
	$_SESSION['nickname'] = $core->render['SYS']['NICKNAME'] = $loggedin['data']['nickname'];
	$_SESSION['email'] = $core->render['SYS']['EMAIL'] = $loggedin['data']['email'];
	$_SESSION['id'] = $core->render['SYS']['ID'] = $loggedin['data']['id'];
	$_SESSION['login'] = $core->render['SYS']['LOGIN'] = $loggedin['data']['login'];
	$_SESSION['rank'] = $core->render['SYS']['RANK'] = $loggedin['data']['rank'];
	$_SESSION['rank_main'] = $core->render['SYS']['RANK_MAIN'] = $loggedin['data']['rank_main'];
}

$menu = array();
$menu[] = array('Главная','');
$menu[] = array('Пользователи','users');
if(!$_SESSION['loggedin'])$menu[] = array('Регистрация','signup');
if($_SESSION['loggedin'])$menu[] = array('Настройки','profile');
if(!$_SESSION['loggedin'])$menu[] = array('Вход','login');
if($_SESSION['loggedin'])$menu[] = array('Выход','logout');
$core->render['MAIN']['MENU']['MAIN'] = $menu;

$ver = $core->render['MAIN']['V'];

include_once(dirname(__FILE__).'/libs/Twig/Autoloader.php');
Twig_Autoloader::register(true);
$loader = new Twig_Loader_Filesystem('tpl/'.$ver);
$twig = new Twig_Environment($loader,array(/*'cache'=>'/../../tmp/',*/'auto_reload'=>true,'autoescape'=>false));