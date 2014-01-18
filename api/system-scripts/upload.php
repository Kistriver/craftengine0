<?php
namespace CRAFTEngine\core;
include_once(dirname(__FILE__)."/../system/include.php");
if($_GET['sid'])$core_confs['sid'] = $_GET['sid'];
$core = new core($core_confs);

$errors = array(
	'0'=>'wrong parameters',
	'1'=>'empty request',
	'2'=>'out of time',
	'3'=>'unexpected format',
);

$time = time();
$time_was = $core->sanString(isset($_GET['time'])?$_GET['time']:'');
$type = $core->sanString(isset($_GET['type'])?$_GET['type']:'');
$hash = $core->sanString((isset($_GET['hash'])?$_GET['hash']:''));
$format = $core->sanString((isset($_GET['format'])?$_GET['format']:''));
$ip = $_SERVER['REMOTE_ADDR'];

if(empty($time_was) || empty($type) || empty($hash))
	die(json_encode(array(false,0,$errors[0])));

if($time<$time_was-90)
	die(json_encode(array(false,2,$errors[2])));

$r = $core->mysql->query("SELECT * FROM uploads WHERE hash='$hash' AND ip='$ip' AND time='$time_was' LIMIT 0,1");

if($core->mysql->rows($r)==1)
{
	$con = fopen('php://input','rb');
	if(empty($con))
		die(json_encode(array(false,1,$errors[1])));

	$result = $core->mysql->fetch($r);
	$params = json_decode(empty($result['params'])?'[]':$result['params'],true);

	if(!in_array($format,isset($params['formats'])?$params['formats']:array()))
		die(json_encode(array(false,3,$errors[3])));

	//FIXME: it may be used like an exploit(.. in name)
	$name = isset($params['name'])?$params['name']:str_replace(array('0.',' '), array('',''), microtime());
	if(file_put_contents(dirname(__FILE__).'/../files/'.$name.'.'.$format,$con))
	{
		$core->mysql->query("DELETE FROM uploads WHERE hash='$hash' AND ip='$ip' AND time='$time_was'");
		$core->plugin->makeEvent('upload_complete','core',array('type'=>$type,'name'=>$name,'format'=>$format,'params'=>$params));
		die(json_encode(array(true,$name.'.'.$format,$core->error->error)));
	}
}
else
{
	die(json_encode(array(false,0,$errors[0])));
}