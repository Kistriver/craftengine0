<?php
namespace CRAFTEngine\client\core;
define('CE_HUB', true);

require_once(dirname(__FILE__).'/core/core.class.php');

$core_confs = array(
	'root'=>dirname(__FILE__).'/system/',
	'uri'=>$_GET['uri'],
);

session_start();
$core = new core($core_confs);

////========================REWRITE RULES ZONE========================////
$core->plugins->newRule(array(
	'preg'=>array('^index$','^$'),
	'preg_flags'=>'',
	'flags'=>'',
	'get'=>array(),
	'page'=>'index.php',
	'plugin'=>null,
));

$uri = &$core->uri;

/*$core->plugins->newRule(array('preg'=>'^admin$','page'=>'admin/index.php'));

$core->plugins->newRule(array('preg'=>'^admin/api/plugins$','page'=>'admin/api/plugins_list.php'));
$core->plugins->newRule(array('preg'=>'^admin/api/plugins/edit/(.*)$','page'=>'admin/api/plugins_config.php','get'=>array('plugin'=>'$1')));

$core->plugins->newRule(array('preg'=>'^admin/client/pages$','page'=>'admin/client/pages.php'));
$core->plugins->newRule(array('preg'=>'^admin/client/settings$','page'=>'admin/client/settings.php'));
$core->plugins->newRule(array('preg'=>'^admin/client/themes$','page'=>'admin/client/themes.php'));*/
////========================REWRITE RULES ZONE========================////



/*$uri = isset($_GET['uri'])?$_GET['uri']:null;
$hr = $core->conf->conf->core->tpl->root_http;
if(substr($uri, 0, strlen($hr))==$hr)$uri = substr($uri, strlen($hr));

if(preg_match("'\.\.'", $uri))$core->f->quit(403, 'Hack attempt');
elseif(preg_match("'^((system)/)'", $uri))
{
	$core->f->quit(403);
}*/

/*elseif(preg_match("'^((files|other|api)/)'", $uri))
{
	if(file_exists(dirname(__FILE__).'/'.$uri))
	{
		header('Content-type: '.$core->f->mime_content_type(dirname(__FILE__).'/'.$uri).';');

		echo file_get_contents(dirname(__FILE__).'/'.$uri);
	}
	else
	{
		$core->f->quit(404);
	}

	exit();
}*/
/*else*/if(preg_match("'^style/([^/].*?)/package.png$'", $uri))
{
	$cur_uri = preg_replace("'^style/([^/].*?)/package.png$'",'$1', $uri);
	$file = dirname(__FILE__).'/system/themes/'.$cur_uri.'/package.png';
	if(file_exists($file))
	{
		header('Content-type: image/png;');
		echo file_get_contents(dirname(__FILE__).'/system/themes/'.$cur_uri.'/package.png');
	}
	else
	{
		$core->f->quit(404);
	}

	exit();
}
elseif(preg_match("'^((style)/)'", $uri))
{
	$cur_uri = preg_replace("'^style/(.*?)'",'$1', $uri);
	$file = dirname(__FILE__).'/system/themes/'.$core->render['MAIN']['THEME'].'/styles/'.$cur_uri;
	if(file_exists($file) && !is_dir($file))
	{
		header('Content-type: '.$core->f->mime_content_type($file).';'/* charset=utf-8;'*/);

		echo file_get_contents($file);
	}
	else
	{
		$core->f->quit(404);
	}

	exit();
}

/*if(sizeof($core->plugins->getList())!=0)
	foreach($core->plugins->getList() as $pl => $pages)
	{
		if(file_exists(dirname(__FILE__).'/system/plugins/'.$pl.'/system/include.php'))
		include_once(dirname(__FILE__).'/system/plugins/'.$pl.'/system/include.php');
	}*/
if(sizeof($core->plugins->getList())!=0)
	foreach($core->plugins->getList() as $pl => $inf)
	{
		$cl = '\CRAFTEngine\client\plugins\\'.$pl.'\\'.$inf['loadClass'];
		$class = new $cl($core);
		if(method_exists($class,'construct'))
			$class->construct();
	}

$core->widgets->construct_man();

header('Content-type: text/html; charset=utf-8;');
//Берём правила реврайта
foreach($core->plugins->getRules() as $r)
{
	$rews = $r['preg'];
	$file = $r['page'];
	$get = isset($r['get'])?$r['get']:array();
	$preg_flags = isset($r['preg_flags'])?$r['preg_flags']:'';
	$flags = isset($r['flags'])?$r['flags']:'';
	$from_plugin = isset($r['plugin'])?$r['plugin']:null;

	if(!is_array($rews))
	{
		$rews1 = $rews;
		unset($rews);
		$rews[0] = $rews1;
		unset($rews1);
	}

	//Проходимся по всем правилам
	foreach($rews as $rew)
	{
		if(preg_match("'$rew'$preg_flags", $uri, $match) || $rew==$file)
		{
			//Создаём _GET переменные
			foreach($get as $key=>$val)
			{
				for($i=1; $i<sizeof($match);$i++)
				{
					$val = preg_replace("'([^\\\\]{0,1})\\$".$i."'", $match[$i], $val);
				}

				$_GET[$key] = $val;
			}

			$_SERVER['SCRIPT_NAME'] = $file;//TODO:И еще некоторые надо поправить

			//Если указан плагин
			if($from_plugin!=null)
			{
				if(!isset($core->plugins->getList()[$from_plugin]))
				{
					$core->f->quit(404);
				}

				include_once(dirname(__FILE__).'/system/plugins/'.$from_plugin.'/pages/'.$file);
				exit();
			}
			//Стандартные страницы
			elseif(file_exists(dirname(__FILE__).'/system/pages/'.$file))
			{
				//require_once(dirname(__FILE__).'/system/include.php');
				include_once(dirname(__FILE__).'/system/pages/'.$file);
				exit();
			}
			//Выдаём 404 ошибку
			else
			{
				$core->f->quit(404);
			}
		}
	}
}


$core->f->quit(404);
?>