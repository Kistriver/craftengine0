<?php
require_once(dirname(__FILE__).'/../core/core.class.php');

$core_confs = array(
	'root'=>dirname(__FILE__).'/',
);

session_start();
$core = new core($core_confs);

////========================REWRITE RULES ZONE========================////
$core->rules[] = array(
	'preg'=>array('^index$','^$'),
	'preg_flags'=>'',
	'flags'=>'',
	'get'=>array(),
	'page'=>'index.php',
	'plugin'=>null,
);

$core->rules[] = array('preg'=>'^admin$','page'=>'admin/index.php');
$core->rules[] = array('preg'=>'^admin/api/plugins$','page'=>'admin/api_plugins_list.php');
$core->rules[] = array('preg'=>'^admin/api/plugins/edit/(.*)$','page'=>'admin/api_plugins_config.php','get'=>array('plugin'=>'$1'));
$core->rules[] = array('preg'=>'^admin/client/settings$','page'=>'admin/client_settings.php');
////========================REWRITE RULES ZONE========================////
?>
