<?php
include_once(dirname(__FILE__)."/system/core/core.class.php");
$core = new core();

print_r($core->error->error);
echo $core->runtime();
//require null;
?>