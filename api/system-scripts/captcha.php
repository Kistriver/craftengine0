<?php
include_once(dirname(__FILE__)."/../system/include.php");
if(!isset($core_confs))
	$core_confs = array
	(
		'root'=>dirname(__FILE__).'/../system/',
	);
$sid = $_GET['sid'];
$core_confs['sid'] = $sid;
$core = new core($core_confs);

//if(empty($_GET['sid']))die(json_encode(array(false,'sid not get')));
//if(empty($_GET['type']))die(json_encode(array(false,'type not get')));

//$sid = $_GET['sid'];
$type = $_GET['type'];

/*session_id($sid);
session_start();*/

$c = $core->plugin->initPl('captcha','captcha');

header('Content-type: image/png');
$c->pict($type);
?>