<?php
if(!defined('CE_HUB'))die('403');

switch($_GET['act'])
{
	case 'main':
		$core->f->show('main','documentation');
		break;

	case 'api':
		switch($_GET['page'])
		{
			case 'plugins':
				$core->f->show('api/plugins','documentation');
				break;

			default:
				$core->f->quit(404);
				break;
		}
		break;

	case 'client':
		switch($_GET['page'])
		{
			case 'plugins':
				$core->f->show('client/plugins','documentation');
				break;

			default:
				$core->f->quit(404);
				break;
		}
		break;

	default:
		$core->f->quit(404);
		break;
}