<?php
namespace CRAFTEngine\client\plugins\admin;
if(!defined('CE_HUB'))die('403');
require_once(dirname(__FILE__) . '/../../admin/core/includeAdmin.php');


$supported = array(
	"craftengine 0.3.0"=>"CRAFTEngine v0.3.0",
	"ameden 2.6"=>"AWE v2.6",
	"webmcr 2.3"=>"WebMCR v2.3",
);


$core->render['supported'] = $supported;
$core->f->show('import/main','users');