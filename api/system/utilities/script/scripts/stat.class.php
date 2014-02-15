<?php
namespace CRAFTEngine\core\scripts;

header('Content-type: application/json; charset=utf-8');

set_time_limit(0);
ignore_user_abort(1);

class stat
{
	public function __construct($core)
	{
		$stat['value'] = file_get_contents($core->getParams()['root'].'cache/Stat');

		$stat['server'] = array(
			'ip'=>!$core->conf->system->core->{'non-anonymous-stat'}?'anonymous':$_SERVER['SERVER_ADDR'],
			'host'=>!$core->conf->system->core->{'non-anonymous-stat'}?'anonymous':$_SERVER['SERVER_NAME'],
			'port'=>!$core->conf->system->core->{'non-anonymous-stat'}?'anonymous':$_SERVER['SERVER_PORT'],
			'version'=>$core::CORE_VER,
			'admin_mail'=>!$core->conf->system->core->{'non-anonymous-stat'}?'anonymous':$core->conf->system->core->admin_mail,
		);

		$stat = $core->json_encode_ru($stat);

		$url = 'http://stat.kcraft.su/api/v5/stat/stat/set/craftengine/';

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

		file_put_contents($core->getParams()['root'].'cache/StatLock',time());
	}
}