<?php
namespace CRAFTEngine\core;
include_once(dirname(__FILE__)."/../system/include.php");
if(!isset($core_confs))
	$core_confs = array
	(
		'root'=>dirname(__FILE__).'/../system/',
	);
$sid = empty($_GET['sid'])?'':$_GET['sid'];
$core_confs['sid'] = $sid;
$core = new core($core_confs);

//if(empty($_GET['sid']))die(json_encode(array(false,'sid not get')));
//if(empty($_GET['type']))die(json_encode(array(false,'type not get')));

//$sid = $_GET['sid'];
$type = empty($_GET['type'])?'':$_GET['type'];

/*session_id($sid);
session_start();*/

if(!isset($_SESSION['captcha'][$type]))
{
	echo json_encode(array("error"=>"Unexpected type"));
	exit;
}

$c = $core->plugin->initPl('captcha','captcha');

header('Content-type: image/png');
$c->pict($type);
?>