<?php
require_once(dirname(__FILE__).'/core.class.php');

session_start();
$core = new core();

////========================REWRITE RULES ZONE========================////
$core->rules[] = array(array('^index$','^$'),'index.php');

$core->rules[] = array('^plugins$','plugins.php');
////========================REWRITE RULES ZONE========================////

//$core->render['MAIN']['INFO'][] = 'Инфа';
//$core->render['MAIN']['ERRORS'][] = 'Ошибка';
//$core->render['MAIN']['SUCCESS'][] = 'Успешно';
?>
