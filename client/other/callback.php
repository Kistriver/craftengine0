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
$APP_CODE = $_GET['code'];
$redirect_uri = "http://test.kcraft.su/client/callback.php";

$url = "http://test.kcraft.su/api/token.php";

$data = http_build_query
(
	array
	(
		'client_id'		=>	$APP_ID,
		'client_secret'		=>	$APP_SECRET,
		'grant_type'		=>	"authorization_code",
		'code'			=>	$APP_CODE,
		'redirect_uri'		=>	urlencode($redirect_uri)
	)
);

$context = stream_context_create
(
	array
	(
		'http' => array
		(
			'method' => 'POST',
			'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL .
				'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL,
			'content' => $data,
		)
	)
);

$answer = file_get_contents($url,false,$context);

$arr = json_decode($answer, true);
echo $_SESSION['token'] = $token	= $arr['access_token'];