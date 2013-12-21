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
elseif(preg_match("'^((style)/)'", $uri))
{
	$cur_uri = preg_replace("'^style/(.*?)'",'$1', $uri);
	if(file_exists(dirname(__FILE__).'/system/themes/'.$core->render['MAIN']['THEME'].'/styles/'.$cur_uri))
	{
		header('Content-type: '.$core->f->mime_content_type(dirname(__FILE__).'/system/themes/'.$core->render['MAIN']['THEME'].'/styles/'.$cur_uri).';'/* charset=utf-8;'*/);

		echo file_get_contents(dirname(__FILE__).'/system/themes/'.$core->render['MAIN']['THEME'].'/styles/'.$cur_uri);
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

			//Если указан плагин
			if($from_plugin!=null)
			{
				if(!isset($core->plugins->list[$from_plugin]))
				{
					$core->f->quit(404);
				}

				foreach($core->plugins->list[$from_plugin]['pages'] as $pag)
				{
					if($pag==$file)
					{
						include_once(dirname(__FILE__).'/system/plugins/'.$from_plugin.'/pages/'.$file);
						exit();
					}
				}

				$core->f->quit(404);
			}
			//Стандартные страницы
			elseif(file_exists(dirname(__FILE__).'/system/pages/'.$file))
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