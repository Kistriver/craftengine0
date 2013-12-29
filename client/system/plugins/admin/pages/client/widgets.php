<?php
namespace CRAFTEngine\client\plugins\admin;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__) . '/../../core/includeAdmin.php');

if(sizeof($_POST)!=0)
{
	$widget = $_POST['widget'];

	switch($_POST['type'])
	{
		case 'enable':
			$core->widgets->on($widget);
			break;

		case 'disable':
			$core->widgets->off($widget);
			break;

		case 'up':
			$core->widgets->up($widget);
			break;

		case 'down':
			$core->widgets->down($widget);
			break;
	}
}

$core->render['widgets'] = $core->widgets->list;
$core->render['widgets_order'] = $core->widgets->order;

$core->f->show('client/widgets/main','admin');