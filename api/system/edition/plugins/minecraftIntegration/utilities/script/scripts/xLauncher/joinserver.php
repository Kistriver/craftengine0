<?php
define('INCLUDE_CHECK',true);
include(dirname(__FILE__)."/connect.php");
$sessionid = mysql_real_escape_string($_GET['sessionId']);
$user = mysql_real_escape_string($_GET['user']);
$serverid = mysql_real_escape_string($_GET['serverId']);

if (!preg_match("/^[a-zA-Z0-9_-]+$/", $user) || !preg_match("/^[a-zA-Z0-9_-]+$/", $sessionid) || !preg_match("/^[a-zA-Z0-9_-]+$/", $serverid)){

	echo "Bad login";

	exit;
}

$result = mysql_query("Select $db_columnUser From $db_table Where $db_columnSesId='$sessionid' And $db_columnUser='$user' And $db_columnServer='$serverid'") or die ("������");

if(mysql_num_rows($result) == 1) echo "OK";
else
{
	$result = mysql_query("Update $db_table SET $db_columnServer='$serverid' Where $db_columnSesId='$sessionid' And $db_columnUser='$user'") or die ("������");
	if(mysql_affected_rows() == 1) echo "OK";
	else echo "Bad login";
}