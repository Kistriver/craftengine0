<?php
require_once(dirname(__FILE__)."/../system/core/core.class.php");

set_time_limit(0);
ignore_user_abort(1);

$core = new core();

$stat['sid'] = 'CRAFTEngine-'.str_replace('.','-',$_SERVER['SERVER_ADDR']);
$stat['value'] = file_get_contents(dirname(__FILE__).'/../system/core/cache/Stat');

$stat['server'] = array('ip'=>$_SERVER['SERVER_ADDR'],
	'host'=>$_SERVER['SERVER_NAME'],
	'port'=>$_SERVER['SERVER_PORT'],
	'version'=>$core->conf->system->core->version,
	'admin_mail'=>$core->conf->system->core->admin_mail
);

$stat = $core->json_encode_ru($stat);

$url = 'http://178.140.61.70:8080/index.php?method=stat.set';

$data = http_build_query
(
	array
	(
		'data' => $stat
	)
);

$context = stream_context_create
(
	array
	(
		'http' => array
		(
			'method' => 'POST',
			'header' => 'Content-Type: application/x-www-form-urlencoded',
			'content' => $data,
		)
	)
);

$answer = @file_get_contents($url,false,$context);

$answer_decode = json_decode($answer, true);
if(!$answer_decode)
{
	exit;
}

if(!empty($answer_decode['data'][0]))
{
	if($answer_decode['data'][0]===true)
	{
		$core->statCache('clear',time(),true);
	}
}
?>