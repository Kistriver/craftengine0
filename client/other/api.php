<?php
namespace CRAFTEngine\client\core;
ini_set('display_errors',1);error_reporting(E_ALL);
define('CE_HUB', true);

require_once(dirname(__FILE__).'/core/core.class.php');

$core_confs = array(
	'root'=>dirname(__FILE__).'/system/',
	'uri'=>$_GET['uri'],
);

session_start();
$core = new core($core_confs);

$APP_ID = 'testclient';
$APP_SECRET = 'testpass';
$redirect_uri = urlencode("http://test.kcraft.su/client/callback.php");
$login_url = "http://test.kcraft.su/api/authorize.php?client_id={$APP_ID}&response_type=code&redirect_uri={$redirect_uri}&state=ff";
//echo file_get_contents($login_url);
echo '<a target="_blank" href="'.$login_url.'">Click</a>';