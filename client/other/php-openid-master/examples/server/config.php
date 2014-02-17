<?php
/**
 * The URL for the server.
 *
 * This is the location of server.php. For example:
 *
 * $server_url = 'http://example.com/~user/server.php';
 *
 * This must be a full URL.
 */
$server_url = "http://test.kcraft.su/client/other/php-openid-master/examples/server/server.php";

/**
 * Initialize an OpenID store
 *
 * @return object $store an instance of OpenID store (see the
 * documentation for how to create one)
 */
function getOpenIDStore()
{
	require_once "Auth/OpenID/FileStore.php";
	return new Auth_OpenID_FileStore("/home/kachalov/www/kcraft.su/test/client/other/php-openid-master/examples/server/storage");
}

?>