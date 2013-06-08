<?php
//ini_set('display_errors', 0);
session_start();

include_once(dirname(__FILE__).'/core.class.php');
$core = new core();

if(!isset($_SESSION['loggedin']))$_SESSION['loggedin']=false;

$core->get('system.user',array('page'=>'1','sid'=>session_id().'php'));

$mod = array(
);

$core->tpl->assign('SESSION',$_SESSION);

$core->tpl->tpl('/html/main/login');
$mod['LOGIN'] = $core->tpl->render();

$core->tpl->tpl('/html/main/menu');
$mod['MENU'] = $core->tpl->render();

$core->tpl->tpl('/html/main/ads');
$mod['ADS'] = $core->tpl->render();

$core->tpl->tpl('/html/main/search');
$mod['SEARCH'] = $core->tpl->render();

$main['NAME'] = 'KachalovCRAFT NET';
$main = array(
	'NAME'=>$main['NAME'],
	'TITLE'=>$main['NAME'],
	'KEYWORDS'=>$main['NAME'],
	'DESC'=>$main['NAME'],
	'HEADER'=>$main['NAME'],
	'ERRORS'=>'',
);