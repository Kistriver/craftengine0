<?php //will be later(it's index.php)
namespace CRAFTEngine;

$start = microtime(true);
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json; charset=utf-8');

$version = empty($_GET['v'])?5:$_GET['v'];

$post = empty($_GET['post'])?$_SERVER['REQUEST_METHOD']:$_GET['post'];
$code = empty($_GET['status_code'])?null:$_GET['status_code'];

$plugin = empty($_GET['plugin'])?null:$_GET['plugin'];
$module = empty($_GET['module'])?null:$_GET['module'];
$method = empty($_GET['method'])?null:$_GET['method'];

include_once(dirname(__FILE__)."/system/include.php");
if(!isset($core_confs))
	$core_confs = array
	(
		'root'=>dirname(__FILE__).'/system/',
	);

$core_confs['api'] = array('plugin'=>$plugin,'module'=>$module,'method'=>$method,'type'=>$post,'code'=>$code);
$core_confs['start_time'] = $start;

$core = new core\core($core_confs);
$api = $core->api;