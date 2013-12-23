<?php
namespace CRAFTEngine\client\plugins\core;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__).'/include.php');

$list = array('api'=>array(),'client'=>array(),'other'=>array());

$list['api'][] = array('icon'=>'list','value'=>'Плагины','href'=>'plugins');
$list['api'][] = array('icon'=>'wrench','value'=>'Настройки ядра','href'=>'plugins');
$list['api'][] = array('icon'=>'tasks','value'=>'Статистика запросов');

$list['client'][] = array('icon'=>'list','value'=>'Плагины');
$list['client'][] = array('icon'=>'adjust','value'=>'Оформление','href'=>'themes');
$list['client'][] = array('icon'=>'th-large','value'=>'Виджеты');
$list['client'][] = array('icon'=>'wrench','value'=>'Настройки ядра','href'=>'settings');
$list['client'][] = array('icon'=>'screenshot','value'=>'Просмотры');

$core->render['admin_menu'] = $core->plugins->makeEvent('admin_menu_render','admin',$list);
$core->f->show('admin/main');