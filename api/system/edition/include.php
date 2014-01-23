<?php
$core_confs = array
(
	'root'=>dirname(__FILE__).'/',
	'core'=>dirname(__FILE__).'/../core/',
	'utilities'=>array(
		'system'=>array(
			'migrate'=>array(
				'modules'=>array('system','article','comments','users'),
			),
		),
	),
);
require_once($core_confs['core'].'core.class.php');