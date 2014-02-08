<?php
function get_res($address,$port) {
	$socket = @fsockopen($address,$port);
	if ($socket == false) $res['online'] = '-1';
	else {
		@fwrite($socket, "\xFE");
		$data = "";
		$data = @fread($socket, 256);
		@fclose($socket);

		if ($data !== false && substr($data, 0, 1) == "\xFF") {
			$info= substr( $data, 3 );
			$info = iconv( 'UTF-16BE', 'UTF-8', $info );
			if( $info[ 1 ] === "\xA7" && $info[ 2 ] === "\x31" ) {
				$info = explode( "\x00", $info );
				$res['online'] = IntVal( $info[4] );
				$res['max'] = IntVal( $info[5] );
			} else {
				$info = Explode( "\xA7", $info );
				$res['online'] = IntVal( $info[1] );
				$res['max'] = IntVal( $info[2] );
			}
		} else $res['online'] = '-1';

	}
	return $res;
}

function scan_dir()
{
	$dirname = $_GET['dir'];
	$dirname = urldecode($dirname);
	$dirname = str_replace("\\", "", $dirname);
	$type = $_GET['type'];
	GLOBAL $count_files, $count_dirs;
	$dir = opendir("check/".$dirname);
	while (($file = readdir($dir)) !== false)
	{
		if($file != "." && $file != "..")
		{
			if(is_file("check/".$dirname."/".$file))
			{
				$count_files++;
			}
			if(is_dir("check/".$dirname."/".$file))
			{
				$count_dirs++;
//scan_dir($dirname."/".$file);
			}
		}
	}
	closedir($dir);
	if ($type == "files"){
		if (empty($count_files)){
			$count_files = 0;
		}
		echo $count_files;
	}
	if ($type == "dirs"){
		if (empty($count_dirs)){
			$count_dirs = 0;
		}
		echo $count_dirs;
	}
}

function launcher()
{
	$file = file('data/launcher-version.txt');
	$count = count($file);
	$string = '';
	$string = trim( $file[0] );
	echo $string;
}

function version()
{
	$file = file('data/client-version.txt');
	$count = count($file);
	$string = '';
	$string = trim( $file[0] );
	echo $string;
}

function online()
{
	set_time_limit(0);
	$string = '';
	$f = fopen("data/servers.txt", 'r');
	while(!feof($f))
	{
		$text = fgets($f,999);
		$inf=explode(":",$text);
		$port = $inf[1];
		$address = $inf[0];
		$res = get_res($address,$port);
		$string .= $res['online'].":";
	}
	fclose($f);
	$fw=fopen('data/temp_monitor.txt','w');
	fwrite($fw, $string);
	fclose($fw);
	unlink('data/monitor.txt');
	rename('data/temp_monitor.txt','data/monitor.txt');
}

function updateOnline()
{
	set_time_limit(0);
	while(true){
		if(file_exists('stop')) break;
		online();
		sleep(60);
	}
	die("online update stopped");
}

function updateOnlineStop()
{
	set_time_limit(0);
	$file="stop";
	if (!file_exists($file)) {
		$fp = fopen($file, "w");
		fwrite($fp, "��������, �� ��� ����� � �����");
		fclose ($fp);
		sleep(90);
		unlink('stop');
	}
	die("online update stopped");
}

$action = $_GET['action'];
if ($action=="launcher")
	die(launcher());
if ($action=="version")
	die(version());
if ($action=="online")
	die(online());
//if ($action=="updateonline")
//    die(updateOnline());
//if ($action=="updateonlinestop")
//    die(updateOnlineStop());
if ($action=="scan")
	die(scan_dir());
die("�������� ���������� ��������!");
?>