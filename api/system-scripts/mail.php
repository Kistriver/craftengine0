<?php
require_once(dirname(__FILE__)."/../system/core/core.class.php");

set_time_limit(0);
ignore_user_abort(1);

include_once(dirname(__FILE__)."/../system/include.php");
if(!isset($core_confs))
$core_confs = array
(
	'confs'=>array('root'=>''),
	'cache'=>array('root'=>''),
	'tpl'=>array('root'=>''),
	'plugins'=>array('root'=>''),
);

$core = new core($core_confs);

$core->mail->get_waiting_list(50);
?>