<?php
require_once(dirname(__FILE__)."/../system/core/core.class.php");
$core = new core();

if(empty($_GET['sid']))die(json_encode(array(false,'sid not get')));
if(empty($_GET['type']))die(json_encode(array(false,'type not get')));

$sid = $_GET['sid'];
$type = $_GET['type'];

session_id($sid);
session_start();

$c = $core->plugin->initPl('captcha','captcha');

header('Content-type: image/png');
$c->pict($type);
?>