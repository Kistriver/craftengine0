<?php
namespace CRAFTEngine\client\plugins\admin;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__) . '/../../core/includeAdmin.php');

$core->api->get('system/system/pluginList');
$data = $core->api->answer_decode;

if(!empty($_POST['state']) AND !empty($_POST['name']))
{
	$state = $_POST['state']=='ON'?'On':'Off';
	$core->api->get('system/system/plugin'.$state,array('name'=>$_POST['name'],'sid'=>$_SESSION['sid']));

	$core->api->get('system/system/pluginList',array('sid'=>$_SESSION['sid']));
}

$data = $core->api->answer_decode;

if(sizeof($data['errors'])==0)
{
	foreach($data['data']['included'] as $f=>$c)
		foreach($data['data']['all'] as $fa=>&$ca)
			if($f==$fa AND $c['name']==$ca['name'])
				$ca['_included_'] = true;

	$core->render['plugins_num'] = sizeof($data['data']['all']);
	$core->render['plugins'] = $data['data']['all'];
	//$core->render['pluginsWorked'] = $data['data']['included'];
}

$core->f->show('api/plugins_config/list','admin');
?>