<?php
require_once(dirname(__FILE__).'/system/include.php');
define('CE_HUB', true);

$uri = isset($_GET['uri'])?$_GET['uri']:null;
$hr = $core->conf->conf->core->tpl->root_http;
if(substr($uri, 0, strlen($hr))==$hr)$uri = substr($uri, strlen($hr));

if(preg_match("'\.\.'", $uri))$core->f->quit(403, 'Hack attempt');

elseif(preg_match("'^((system)/)'", $uri))
{
	$core->f->quit(403);
}
elseif(preg_match("'^((files|other|style|api)/)'", $uri))
{
	if(file_exists(dirname(__FILE__).'/'.$uri))
	{
		if(preg_match("'(.css)$'", $uri))
		header('Content-type: text/css; charset=utf-8;');
		else
		header('Content-type: '.mime_content_type(dirname(__FILE__).'/'.$uri).'; charset=utf-8;');
		
		echo file_get_contents(dirname(__FILE__).'/'.$uri);
	}
	else
	{
		$core->f->quit(404);
	}
	
	exit();
}

if(sizeof($core->plugins->list)!=0)
foreach($core->plugins->list as $pl => $pages)
{
	include_once(dirname(__FILE__).'/system/plugins/'.$pl.'/system/include.php');
}

header('Content-type: text/html; charset=utf-8;');
//Берём правила реврайта
foreach($core->rules as $r)
{
	$rews = $r[0];
	$file = $r[1];
	$get = isset($r[2])?$r[2]:array();
	
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
		if(preg_match("'$rew'", $uri, $match) || $rew==$file)
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
			
			//Меню
			//TODO: Remake it!
			$s = explode('/',$_SERVER['SCRIPT_NAME']);
			if(sizeof($core->render['NAVMENU'])!=0)
			foreach($core->render['NAVMENU'] as &$m)
			{
				if($m[1].'.php'==$s[sizeof($s)-1] or ($m[1]=='' AND $s[sizeof($s)-1]=='index.php'))
				{
					$m[2] = true;
				}
			}
			
			//Стандартные страницы
			if(file_exists(dirname(__FILE__).'/system/pages/'.$file))
			{
				//require_once(dirname(__FILE__).'/system/include.php');
				include_once(dirname(__FILE__).'/system/pages/'.$file);
				exit();
			}
			//Страницы плагинов
			elseif(sizeof($core->plugins->list)!=0)
			{
				//Проходимся по списку плагинов
				foreach ($core->plugins->list as $pl => $pags)
				{
					//И по их страницам
					foreach($pags['pages'] as $pag)
					{
						if($pag==$file)
						{
							//include_once(dirname(__FILE__).'/system/plugins/'.$pl.'/system/include.php');
							include_once(dirname(__FILE__).'/system/plugins/'.$pl.'/pages/'.$file);
							exit();
						}
					}
				}
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