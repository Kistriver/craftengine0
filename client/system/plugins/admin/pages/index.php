<?php
namespace CRAFTEngine\client\plugins\admin;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__) . '/../system/includeAdmin.php');

$list = array('api'=>array(),'client'=>array(),'other'=>array());

$list['api']['default_plugins'] = array('icon'=>'list','value'=>'Плагины','href'=>'plugins');
$list['api']['default_settings'] = array('icon'=>'wrench','value'=>'Настройки ядра','href'=>'plugins');
$list['api']['default_stat'] = array('icon'=>'tasks','value'=>'Статистика запросов');

$list['client']['default_pages'] = array('icon'=>'pencil','value'=>'Редактирование страниц'/*,'href'=>'pages'*/);
$list['client']['default_plugins'] = array('icon'=>'list','value'=>'Плагины');
$list['client']['default_view'] = array('icon'=>'adjust','value'=>'Оформление','href'=>'themes');
$list['client']['default_widgets'] = array('icon'=>'th-large','value'=>'Виджеты');
$list['client']['default_settings'] = array('icon'=>'wrench','value'=>'Настройки ядра','href'=>'settings');
$list['client']['default_stat'] = array('icon'=>'screenshot','value'=>'Просмотры');

$core->render['admin_menu'] = $core->plugins->makeEvent('admin_menu_render','admin',$list);
$core->f->show('main','admin');