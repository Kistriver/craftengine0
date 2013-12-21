<?php
namespace CRAFTEngine\core;
include_once(dirname(__FILE__)."/../system/include.php");
if(!isset($core_confs))
$core_confs = array
(
	'root'=>dirname(__FILE__).'/../system/',
);

$core = new core($core_confs);

$time = time();
$time_was = $core->sanString($_GET['time']);
$hash = $core->sanString($_GET['hash']);
$ip = $_SERVER['REMOTE_ADDR'];

if($time<$time_was-90)die(json_encode(array(false,'out of time')));

$r = $core->mysql->query("SELECT * FROM upload_sid WHERE hash='$hash' AND ip='$ip' AND time='$time_was' LIMIT 0,1");

if($core->mysql->rows($r)==1)
{
	$con = fopen('php://input','rb');
	if(empty($con))die(json_encode(array(false,'empty request')));
	
	$name = str_replace(array('0.',' '), array('',''), microtime());
	file_put_contents(dirname(__FILE__).'/../files/'.$name,$con);
	$core->mysql->query("DELETE FROM upload_sid WHERE hash='$hash' AND ip='$ip' AND time='$time_was'");
	die(json_encode(array(true,$name)));
}
else
{
	die(json_encode(array(false,'wrong parameters')));
}

?>