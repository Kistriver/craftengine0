<?php
$core_confs = array
(
	'root'=>dirname(__FILE__).'/',
	'core'=>dirname(__FILE__).'/../core/',
/*	'confs'=>array('root'=>dirname(__FILE__).'/confs/'),
	'cache'=>array('root'=>dirname(__FILE__).'/core/cache/'),
	'tpl'=>array('root'=>dirname(__FILE__).'/confs/tpl/'),
	'plugins'=>array('root'=>dirname(__FILE__).'/plugins/'),*/
);
require_once($core_confs['core'].'core.class.php');