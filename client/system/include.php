<?php
require_once(dirname(__FILE__).'/../core/core.class.php');

$core_confs = array(
	'root'=>dirname(__FILE__).'/',
);

session_start();
$core = new core($core_confs);

////========================REWRITE RULES ZONE========================////
$core->rules[] = array(array('^index$','^$'),'index.php');

$core->rules[] = array('^admin$','admin/index.php');
$core->rules[] = array('^admin/api/plugins$','admin/api_plugins_list.php');
$core->rules[] = array('^admin/api/plugins/edit/(.*)$','admin/api_plugins_config.php',array('plugin'=>'$1'));
$core->rules[] = array('^admin/client/settings$','admin/client_settings.php');
////========================REWRITE RULES ZONE========================////

//$core->render['MAIN']['INFO'][] = 'Инфа';
//$core->render['MAIN']['ERRORS'][] = 'Ошибка';
//$core->render['MAIN']['SUCCESS'][] = 'Успешно';
?>
