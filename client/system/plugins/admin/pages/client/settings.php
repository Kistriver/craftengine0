<?php
namespace CRAFTEngine\client\plugins\admin;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__) . '/../../core/includeAdmin.php');

if(sizeof($_POST)!=0)
{
	$cc = $core->conf->get('core');

	$cc->api->url = $_POST['api']['adress'];
	$cc->api->files = $_POST['api']['adressFile'];
	$cc->core->errors->api = empty($_POST['api']['errors'])?false:($_POST['api']['errors']=='on'?true:false);
	$cc->core->detailed_req = empty($_POST['api']['debug'])?false:($_POST['api']['debug']=='on'?true:false);

	$cc->twig->cache = empty($_POST['tpl']['cache'])?false:($_POST['tpl']['cache']=='on'?true:false);
	$cc->twig->reload = empty($_POST['tpl']['cacheReload'])?false:($_POST['tpl']['cacheReload']=='on'?true:false);
	$cc->tpl->theme = $_POST['tpl']['theme'];
	$cc->tpl->client_name = $_POST['tpl']['clientName'];
	$cc->tpl->root = $cc->tpl->root_http = $_POST['tpl']['root'];
	$cc->tpl->client_keywords = $_POST['tpl']['keywords'];
	$cc->tpl->client_desc = $_POST['tpl']['desc'];

	$core->conf->set('core',$cc);
	header('Location: '.$core->render['MAIN']['ROOT'].'admin/client/settings');
}

$cc = $core->conf->get('core');
$pc = $core->conf->get('plugins');

$core->render['config'] = array('core'=>$cc,'plugins'=>$pc);

$core->f->show('client/settings/main','admin');