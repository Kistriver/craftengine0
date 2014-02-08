<?php
function generateSessionId(){
	srand(time());
	$randNum = rand(1000000000, 2147483647).rand(1000000000, 2147483647).rand(0,9);
	return $randNum;
}

function launcher()
{
	$file = file('data/launcher-version.txt');
	$count = count($file);
	$string = '';
	$string = trim( $file[0] );
	return $string;
}

function hash_xauth($realPass, $postPass)
{
	$cryptPass = false;
	$saltPos = (strlen($postPass) >= strlen($realPass) ? strlen($realPass) : strlen($postPass));
	$salt = substr($realPass, $saltPos, 12);
	$hash = hash('whirlpool', $salt . $postPass);
	$cryptPass = substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);

	return $cryptPass;
}

function hash_md5($postPass)
{
	$cryptPass = false;
	$cryptPass = md5($postPass);

	return $cryptPass;
}

function hash_dle($postPass)
{
	$cryptPass = false;
	$cryptPass = md5(md5($postPass));

	return $cryptPass;
}

function hash_cauth($realPass, $postPass)
{
	$cryptPass = false;
	if (strlen($realPass) < 32)
	{
		$cryptPass = md5($postPass);
		$rp = str_replace('0', '', $realPass);
		$cp = str_replace('0', '', $cryptPass);
		(strcasecmp($rp,$cp) == 0 ? $cryptPass = $realPass : $cryptPass = false);
	}
	else
	{
		$cryptPass = md5($postPass);
	}

	return $cryptPass;
}

function hash_authme($realPass, $postPass)
{
	$cryptPass = false;
	$ar = preg_split("/\\$/",$realPass);
	$salt = $ar[2];
	$cryptPass = '$SHA$'.$salt.'$'.hash('sha256',hash('sha256',$postPass).$salt);

	return $cryptPass;
}

function hash_joomla($realPass, $postPass)
{
	$cryptPass = false;
	$parts = explode( ':', $realPass);
	$salt = $parts[1];
	$cryptPass = md5($postPass . $salt) . ":" . $salt;

	return $cryptPass;
}

function hash_ipb($postPass, $salt)
{
	$cryptPass = false;
	$cryptPass = md5(md5($salt).md5($postPass));

	return $cryptPass;
}

function hash_xenforo($postPass, $salt)
{
	$cryptPass = false;
	$cryptPass = hash('sha256', hash('sha256', $postPass) . $salt);

	return $cryptPass;
}

function hash_wordpress($realPass, $postPass)
{
	$cryptPass = false;
	$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$count_log2 = strpos($itoa64, $realPass[3]);
	$count = 1 << $count_log2;
	$salt = substr($realPass, 4, 8);
	$input = md5($salt . $postPass, TRUE);
	do
	{
		$input = md5($input . $postPass, TRUE);
	}
	while (--$count);

	$output = substr($realPass, 0, 12);

	$count = 16;
	$i = 0;
	do
	{
		$value = ord($input[$i++]);
		$cryptPass .= $itoa64[$value & 0x3f];
		if ($i < $count)
			$value |= ord($input[$i]) << 8;
		$cryptPass .= $itoa64[($value >> 6) & 0x3f];
		if ($i++ >= $count)
			break;
		if ($i < $count)
			$value |= ord($input[$i]) << 16;
		$cryptPass .= $itoa64[($value >> 12) & 0x3f];
		if ($i++ >= $count)
			break;
		$cryptPass .= $itoa64[($value >> 18) & 0x3f];
	}
	while ($i < $count);

	$cryptPass = $output . $cryptPass;

	return $cryptPass;
}

function hash_vbulletin($postPass, $salt)
{
	$cryptPass = false;
	$cryptPass = md5(md5($postPass) . $salt);

	return $cryptPass;
}

function hash_drupal($realPass, $postPass)
{
	$cryptPass = false;
	$setting = substr($realPass, 0, 12);
	$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	$count_log2 = strpos($itoa64, $setting[3]);
	$salt = substr($setting, 4, 8);
	$count = 1 << $count_log2;
	$input = hash('sha512', $salt . $postPass, TRUE);
	do
	{
		$input = hash('sha512', $input . $postPass, TRUE);
	}
	while (--$count);

	$count = strlen($input);
	$i = 0;

	do
	{
		$value = ord($input[$i++]);
		$cryptPass .= $itoa64[$value & 0x3f];
		if ($i < $count)
			$value |= ord($input[$i]) << 8;
		$cryptPass .= $itoa64[($value >> 6) & 0x3f];
		if ($i++ >= $count)
			break;
		if ($i < $count)
			$value |= ord($input[$i]) << 16;
		$cryptPass .= $itoa64[($value >> 12) & 0x3f];
		if ($i++ >= $count)
			break;
		$cryptPass .= $itoa64[($value >> 18) & 0x3f];
	}
	while ($i < $count);
	$cryptPass =  $setting . $cryptPass;
	$cryptPass =  substr($cryptPass, 0, 55);

	return $cryptPass;
}

function xorencode($str, $key)
{
	while(strlen($key) < strlen($str))
	{
		$key .= $key;
	}
	return $str ^ $key;
}

function strtoint($text)
{
	$res = "";
	for ($i = 0; $i < strlen($text); $i++) $res .= ord($text{$i}) . "-";
	$res = substr($res, 0, -1);
	return $res;
}

function inttostr($text)
{
	$res = "";
	$split = explode("-", $text);
	for ($i = 0; $i < count($split); $i++) $res .= chr($split{$i});
	return $res;
}

function authCheck()
{
	include("connect.php");
	if (isset($_GET['user']) && isset($_GET['s'])) {
		$loginName = $_GET['user'];
		$hwid = $_GET['s'];
		$login=mysql_real_escape_string($loginName);

		$result1 = mysql_query("SELECT $db_scolumnhwid FROM $db_stable WHERE $db_scolumnhwid='{$hwid}'") or die ("������ � ���� ���������� �������.".mysql_error());
		if (mysql_num_rows($result1) < 1){
			mysql_query("INSERT INTO $db_stable ($db_scolumnhwid, $db_scolumnUser) VALUES ('$hwid', '$login')") or die ("������ � ���� ���������� �������.");
		}
		$result2 = mysql_query("SELECT $db_scolumnUser, $db_scolumnhwid, $db_scolumncheck FROM $db_stable WHERE $db_scolumnhwid ='{$hwid}'") or die ("������ � ���� ���������� �������.".mysql_error());
		$row = mysql_fetch_assoc($result2);
		$userrow = $row[$db_scolumnUser];
		$checkrow = $row[$db_scolumncheck];
		if ($userrow != $login){
			mysql_query("UPDATE $db_stable SET $db_scolumnUser='$login' WHERE $db_scolumnhwid = '$hwid'") or die ("������ � ���� ���������� �������.");
		}
		if ($checkrow == 0){
			die ("1");
		} else {
			die ("banned");
		}
	}
}

function loginServer()
{
	include("connect.php");
	$ver=$_GET['version'];
	if(isset($_GET['user']) && isset($_GET['password']) && isset($_GET['version']))
	{
		if(launcher() == $ver){
			$postPass = $_GET['password'];
			$loginName = $_GET['user'];
			$login=mysql_real_escape_string($loginName);

			$result1 = mysql_query("SELECT $db_columnUser, $db_columncheck FROM $db_table WHERE $db_columnUser ='{$login}'") or die ("������ � ���� ���������� �������.".mysql_error());
			$row1 = mysql_fetch_assoc($result1);
			$checkrow = $row1[$db_columncheck];
			if ($checkrow != 0){
				die ("abanned");
			}

			if ($crypt == 'hash_md5' || $crypt == 'hash_authme' || $crypt == 'hash_xauth' || $crypt == 'hash_cauth' || $crypt == 'hash_joomla' || $crypt == 'hash_wordpress' || $crypt == 'hash_dle' || $crypt == 'hash_drupal')
			{
				$query = "SELECT $db_columnUser, $db_columnPass FROM $db_table WHERE $db_columnUser='{$login}'";
				$result = mysql_query($query) or die(mysql_error());
				$row = mysql_fetch_assoc($result);
				$realPass = $row[$db_columnPass];
			}

			if ($crypt == 'hash_ipb' || $crypt == 'hash_vbulletin')
			{
				$query = "SELECT $db_columnUser,$db_columnPass,$db_columnSalt FROM $db_table WHERE $db_columnUser='{$login}'";
				$result = mysql_query($query)or die(mysql_error());
				$row = mysql_fetch_assoc($result);
				$realPass = $row[$db_columnPass];
				$salt = $row[$db_columnSalt];
			}

			if ($crypt == 'hash_xenforo')
			{
				$query = "SELECT $db_table.$db_columnId,$db_table.$db_columnUser,$db_tableOther.$db_columnId,$db_tableOther.$db_columnPass FROM $db_table, $db_tableOther WHERE $db_table.$db_columnId = $db_tableOther.$db_columnId AND $db_table.$db_columnUser='{$login}'";
				$result = mysql_query($query)or die(mysql_error());
				$row = mysql_fetch_assoc($result);
				$realPass = substr($row[$db_columnPass],22,64);
				$salt = substr($row[$db_columnPass],105,64);
			}

			if ($realPass)
			{
				if ($crypt == 'hash_md5' || $crypt == 'hash_dle')
					$checkPass = $crypt($postPass);
				if ($crypt == 'hash_authme' || $crypt == 'hash_xauth' || $crypt == 'hash_cauth' || $crypt == 'hash_joomla' || $crypt == 'hash_wordpress' || $crypt == 'hash_drupal')
					$checkPass = $crypt($realPass, $postPass);
				if ($crypt == 'hash_ipb' || $crypt == 'hash_vbulletin' || $crypt == 'hash_xenforo')
					$checkPass = $crypt($postPass, $salt);

				if(strcmp($realPass,$checkPass) == 0) {
					$sessid = generateSessionId();
					mysql_query("UPDATE $db_table SET $db_columnSesId='$sessid' WHERE $db_columnUser = '$login'") or die ("������ � ���� ���������� �������.");
					die("0");
				} else {
					die("abuse");
				}
			} else {
				die("fail");
			}
		} else {
			die("oldLauncher");
		}
	}
}

function getClientSize()
{
	$sizefromclient = $_REQUEST['size'];
	$dirname = "check/bin";
	$size = "";
	$dir = opendir($dirname);
	while (($file = readdir($dir)) !== false)
	{
		if($file != "." && $file != "..")
		{
			if(is_file($dirname."/".$file))
			{
				$size .= md5_file($dirname."/".$file);
			}
		}
	}

	if (md5($size) == $sizefromclient){
		echo "2";
	} else {
		echo "abuseSize";
	}
}

function checkMod()
{
	$sizefromclient = $_REQUEST['size'];
	$dirname = $_GET['dir'];
	$filename = $_GET['mod'];
	$file = urldecode($filename);
	$size = 0;
	if(file_exists($dirname."/".$file))
	{
		$size = md5_file($dirname."/".$file);
	} else {
		die("false");
	}

	if ($size == $sizefromclient){
		echo "3";
	} else {
		echo "false";
	}
}

function getSession()
{
	include("connect.php");
	$loginName = $_GET['user'];
	$login=mysql_real_escape_string($loginName);
	$query = "SELECT $db_columnUser, $db_columnSesId FROM $db_table WHERE $db_columnUser='{$login}'";
	$result = mysql_query($query) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	$sessionid = $row[$db_columnSesId];
	$sessid = strtoint(xorencode($sessionid, $sessidkey));
	die("4:".$sessid);
}

$action = $_GET['action'];
if ($action=="auth")
	die(loginServer());
if ($action=="getsession")
	die(getSession());
//if ($action=="clientssize")
//	die(getClientSize());
//if ($action=="checkmod")
//	die(checkMod());
if ($action=="authcheck")
	die(authCheck());
die("�������� ���������� ��������!");
?>