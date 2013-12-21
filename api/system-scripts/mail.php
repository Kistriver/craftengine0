<?php
namespace CRAFTEngine\core;
set_time_limit(0);
ignore_user_abort(1);

include_once(dirname(__FILE__)."/../system/include.php");
if(!isset($core_confs))
$core_confs = array
(
	'root'=>dirname(__FILE__).'/../system/',
);

$core = new core($core_confs);

$core->mail->getWaitingList(50);
?>