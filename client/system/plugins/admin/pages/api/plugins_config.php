<?php
namespace CRAFTEngine\client\plugins\admin;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__) . '/../../system/includeAdmin.php');

if(empty($_GET['plugin']))$core->f->quit(404);

if(sizeof($_POST)!=0)
{
	if(!empty($_POST['config']))
	{
		$config = $_POST['config'];

		$ans = $core->api->get('system.setEditConfs',array('plugin'=>$_GET['plugin'],'config'=>$config));
		//TODO: FIX IT
		if(sizeof($ans['data'])==0)$core->error->error('Конфигурация не изменена');
		//elseif(sizeof($ans['data'])==1 || $ans['data'][0]===false)$core->error->error('Конфигурация не изменена');
		else $core->render['MAIN']['INFO'][] = 'Конфигурация изменена';
	}
}

$conf = $core->api->get('system.getEditConfs',array('plugin'=>$_GET['plugin']));

if(sizeof($conf['data'])==0 || (isset($conf['data'][0]) && $conf['data'][0]===false))$core->f->quit(404);

$core->render['conf'] = $conf['data'];
if(sizeof($conf['data'])==1){if(isset($conf['data'][0]) && $conf['data'][0]==null)$core->render['conf'] = false;}

$core->f->show('api/plugins_config/conf','admin');