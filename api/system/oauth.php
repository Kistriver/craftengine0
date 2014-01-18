<?php
namespace CRAFTEngine;
//exit('Haven\'t done yet!');
$start = microtime(true);
header('Access-Control-Allow-Origin: *');

include_once(dirname(__FILE__)."/include.php");
if(!isset($core_confs))
	$core_confs = array
	(
		'root'=>dirname(__FILE__).'/',
	);
$core_confs['start_time'] = $start;

$core = new core\core($core_confs);

$dsn      = 'mysql:dbname='.$core->conf->system->core->db['craftengine']['db'].';host='.preg_replace("'^p:(.*?)$'",'$1',$core->conf->system->core->db['craftengine']['host']);
$username = $core->conf->system->core->db['craftengine']['user'];
$password = $core->conf->system->core->db['craftengine']['pass'];

// error reporting (this is a demo, after all!)
ini_set('display_errors',1);error_reporting(E_ALL);

// Autoloading (composer is preferred, but for this example let's just do this)
$core->plugin->coreLib('OAuth2Server/src/OAuth2/Autoloader');
\OAuth2\Autoloader::register();

// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
$storage = new \OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));

// Pass a storage object or array of storage objects to the OAuth2 server class
$server = new \OAuth2\Server($storage);

// Add the "Client Credentials" grant type (it is the simplest of the grant types)
$server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));

// Add the "Authorization Code" grant type (this is where the oauth magic happens)
$server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storage));