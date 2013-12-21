<?php
require_once dirname(__FILE__).'/system/oauth.php';

// Handle a request for an OAuth2.0 Access Token and send the response to the client
if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
$server->getResponse()->send();
die;
}
echo json_encode(array('success' => true, 'message' => 'You accessed my APIs!'));