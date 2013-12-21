<?php
namespace CRAFTEngine;
include_once(dirname(__FILE__)."/../system/include.php");
if(!isset($core_confs))
$core_confs = array
(
	'root'=>dirname(__FILE__).'/../system/',
);

$core = new core($core_confs);

//Include system-scripts with this