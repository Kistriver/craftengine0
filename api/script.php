<?php
namespace CRAFTEngine;

$start = microtime(true);

$script = isset($_GET['script'])?$_GET['script']:null;
$module = isset($_GET['module'])?$_GET['module']:null;

if(empty($script) || empty($module))
{
	echo json_encode(array('error'=>'not all parametrs'));
	exit;
}

require_once(dirname(__FILE__)."/system/edition/include.php");

$core_confs['start_time'] = $start;
$core_confs['utilities']['system']['script'] = null;

$core = new core\core($core_confs);

$status = $core->utilities->system->script->process($module,$script);

if(!$status)
{
	echo json_encode(array('error'=>'processing error'));
	exit;
}