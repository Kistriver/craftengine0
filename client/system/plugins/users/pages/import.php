<?php
namespace CRAFTEngine\client\plugins\admin;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__) . '/../../admin/core/includeAdmin.php');


$supported = array(
	"craftengine 0.3.0"=>"CRAFTEngine v0.3.0",
	//"ameden 2.6"=>"AWE v2.6",
	//"webmcr 2.3"=>"WebMCR v2.3",
);

if(sizeof($_POST)!=0)
{
	if(isset($_POST['engine'],$_FILES['dump']))
	{
		if($_FILES['dump']['size']!=0)
		{
			if(in_array($_POST['engine'],array_keys($supported)))
			{
				preg_match("'^(.*?)\s([a-zA-Z0-9-_\.]*)$'i",$_POST['engine'],$matched);
				if(sizeof($matched)==3)
				{
					$engine = $matched[1];
					$version = $matched[2];
					$dump = file_get_contents($_FILES['dump']['tmp_name']);

					$params = array('http' => array(
						'method' => 'POST',
						'content' => $dump,
						'header' => "Content-type: application/x-www-form-urlencoded\r\n".
							"Content-Length: ".strlen($dump)."\r\n"
					));
					$context = stream_context_create($params);

					if($remote = @fopen($core->conf->conf->core->api->url."script.php?module=users&script=import&engine=$engine&version=$version", 'rb', false, $context)){
						$response = @stream_get_contents($remote);
					}
					//$a = json_decode($response,true);

					$core->api->get('users/import/do',array('engine'=>$engine,'version'=>$version));
					if(!empty($core->api->answer_decode['data'][0]))
						$ans = $core->api->answer_decode['data'][0];
					else
						$ans = false;

					if($ans!==false)
					{
						$core->f->msg('success','Импорт удался');
					}
					else
					{
						$core->f->msg('error','Импорт не удался');
					}
				}
				else
				{
					$core->f->msg('error','Неправильный движок');
				}
			}
			else
			{
				$core->f->msg('error','Неправильный движок');
			}
		}
		else
		{
			$core->f->msg('error','Пустой дамп');
		}
	}
}


$core->render['supported'] = $supported;
$core->f->show('import/main','users');