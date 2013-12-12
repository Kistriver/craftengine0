<?php
$core->rules[] = array('^docs$','doc.php', array('act'=>'main'),array('plugin'=>'documentation'));
$core->rules[] = array('^docs/api/(.*?)$','doc.php', array('act'=>'api','page'=>'$1'),array('plugin'=>'documentation'));
$core->rules[] = array('^docs/client/(.*?)$','doc.php', array('act'=>'client','page'=>'$1'),array('plugin'=>'documentation'));