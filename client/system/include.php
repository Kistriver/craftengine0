<?php
namespace CRAFTEngine\client\core;
require_once(dirname(__FILE__).'/../core/core.class.php');

$core_confs = array(
	'root'=>dirname(__FILE__).'/',
);

session_start();
$core = new core($core_confs);

////========================REWRITE RULES ZONE========================////
$core->plugins->newRule(array(
	'preg'=>array('^index$','^$'),
	'preg_flags'=>'',
	'flags'=>'',
	'get'=>array(),
	'page'=>'index.php',
	'plugin'=>null,
));

$core->plugins->newRule(array('preg'=>'^admin$','page'=>'admin/index.php'));

$core->plugins->newRule(array('preg'=>'^admin/api/plugins$','page'=>'admin/api_plugins_list.php'));
$core->plugins->newRule(array('preg'=>'^admin/api/plugins/edit/(.*)$','page'=>'admin/api_plugins_config.php','get'=>array('plugin'=>'$1')));

$core->plugins->newRule(array('preg'=>'^admin/client/settings$','page'=>'admin/client_settings.php'));
$core->plugins->newRule(array('preg'=>'^admin/client/themes$','page'=>'admin/client_themes.php'));
////========================REWRITE RULES ZONE========================////
?>
