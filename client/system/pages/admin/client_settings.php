<?php
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__).'/include.php');

if(sizeof($_POST)!=0)
{
	$cc = $core->conf->get('core');

	$cc->api->url = $_POST['api']['adress'];
	$cc->api->files = $_POST['api']['adressFile'];
	$cc->core->errors->api = $_POST['api']['errors']=='true'?true:false;
	$cc->core->detailed_req = $_POST['api']['debug']=='true'?true:false;

	$cc->twig->cache = $_POST['tpl']['cache']=='true'?true:false;
	$cc->twig->reload = $_POST['tpl']['cacheReload']=='true'?true:false;
	$cc->tpl->theme = $_POST['tpl']['theme'];
	$cc->tpl->client_name = $_POST['tpl']['clientName'];
	$cc->tpl->root = $cc->tpl->root_http = $_POST['tpl']['root'];
	$cc->tpl->client_keywords = $_POST['tpl']['keywords'];
	$cc->tpl->client_desc = $_POST['tpl']['desc'];

	$core->conf->set('core',$cc);
}

$cc = $core->conf->get('core');
$pc = $core->conf->get('plugins');

$core->render['config'] = array('core'=>$cc,'plugins'=>$pc);

$core->f->show('admin/client_settings/main');