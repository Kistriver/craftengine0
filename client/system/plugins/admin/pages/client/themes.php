<?php
namespace CRAFTEngine\client\plugins\admin;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__) . '/../../system/includeAdmin.php');

if(sizeof($_POST)!=0)
{
	$cc = $core->conf->get('core');

	$cc->tpl->theme = $_POST['tpl']['theme'];
	$core->conf->set('core',$cc);
}

$cc = $core->conf->get('core');

$thr = $core->f->getThemesList();
$themes = $thr===false?array():$thr;

$core->render['config']['core']['tpl']['theme'] = $cc->tpl->theme;
$core->render['themes'] = $themes;

$core->f->show('client/themes/main','admin');