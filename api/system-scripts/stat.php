<?php
namespace CRAFTEngine\core;
set_time_limit(0);
ignore_user_abort(1);

include_once(dirname(__FILE__)."/../system/include.php");
if(!isset($core_confs))
$core_confs = array
(
	'root'=>dirname(__FILE__).'/../system/',
);

$core = new core($core_confs);

$stat['stat_ver'] = 'v1.1';
$sid = 'CRAFTENGINE';//= 'CRAFTEngine-'.str_replace('.','-',$_SERVER['SERVER_ADDR']);
$stat['value'] = file_get_contents(dirname(__FILE__).'/../system/cache/Stat');

$stat['server'] = array('ip'=>$_SERVER['SERVER_ADDR'],
	'host'=>$_SERVER['SERVER_NAME'],
	'port'=>$_SERVER['SERVER_PORT'],
	'version'=>$core::CORE_VER,
	'admin_mail'=>$core->conf->system->core->admin_mail
);

$stat = $core->json_encode_ru($stat);

$url = 'http://stat.kcraft.su:80/index.php?method=stat.set&sid='.$sid;

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
			'header' => 'Content-Type: application/x-www-form-urlencoded'.PHP_EOL.
						'User-agent: CraftEngine('.$core->conf->system->core->version.')',
			'content' => $data,
		)
	)
);

echo $answer = @file_get_contents($url,false,$context);

$answer_decode = json_decode($answer, true);
if(!$answer_decode)
{

}

if(!empty($answer_decode['data'][0]))
{
	if($answer_decode['data'][0]===true)
	{
		$core->statCache('clear',time(),true);
	}
}

file_put_contents(dirname(__FILE__).'/../system/cache/StatLock',time());
//if(file_exists(dirname(__FILE__).'/../system/cache/StatLock'))unlink(dirname(__FILE__).'/../system/cache/StatLock');
?>