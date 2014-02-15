<?php
namespace CRAFTEngine\plugins\minecraftIntegration\scripts;
class xLauncher
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->config = $this->core->conf->plugins->minecraftIntegration->settings->launcher['xLauncher'];

		$this->users_core = $this->core->plugin->initPl('users','core');
		$this->users_core->user->addProperty(dirname(__FILE__)."/xLauncher/users/","minecraft_integration_session_id");
		$this->users_core->user->addProperty(dirname(__FILE__)."/xLauncher/users/","minecraft_integration_server_id");
		$this->users_core->user->addProperty(dirname(__FILE__)."/xLauncher/users/","minecraft_integration_hw_id");
		$this->users_core->user->addProperty(dirname(__FILE__)."/xLauncher/users/","minecraft_integration_banned");

		$file = isset($_GET['file'])?$_GET['file']:null;
		$rep = isset($_GET['rep'])?$_GET['rep']:null;
		if($rep!==null)
		{
			if(preg_match("'^\?([^=]*?)=(.*?)$'i",$rep,$rep_m))
			{
				$_GET[$rep_m[1]] = $rep_m[2];
			}
		}

		$act = isset($_GET['action'])?$_GET['action']:null;

		switch($file)
		{
			case 'joinserver.php':
				$this->joinserver();
				break;

			case 'checkserver.php':
				$this->checkserver();
				break;

			case 'monitor.txt':
				$this->show($this->getMon());
				break;
				
			case 'news.html':
				$this->news();
				break;

			case 'mainfile.php':
				switch($act)
				{
					case 'auth':
						$this->loginServer();
						break;

					case 'getsession':
						$this->getSession();
						break;

					case 'authcheck':
						$this->authCheck();
						break;

					default:
						$this->quit("Undefined act type");
						break;
				}
				break;

			case 'maininfo.php':
				switch($act)
				{
					case 'launcher':
						$this->show($this->launcher());
						break;

					case 'version':
						$this->show($this->version());
						break;

					//FIXME: Error handler is still working
					case 'online':
						$this->online();
						break;

					case 'scan':
						$this->scan();
						break;

					default:
						$this->quit("Undefined act type");
						break;
				}
			break;

			default:
				$this->quit("Undefined file type");
				break;
		}
	}

	public function news()
	{
		$art_core = $this->core->plugin->initPl('articles','core');
		if($art_core!==false)
		{
			$art = $art_core->get->posts(array('page'=>1));
			$news = '';
			foreach($art as $a)
			{
				$news .=
'<div class="newsall">
<div class="zagn">
'.(isset($a['title'])?$this->core->sanString($a['title'],'html'):'').'
</div>

<div class="txtn">
'.(isset($a['body'])?$this->core->sanString($a['body'],'html'):'').'
</div>
</div>
';
			}
		}
		else
		{
			$news =
'<div class="newsall">
<div class="zagn">
Новостей нет
</div>

<div class="txtn">
Нет ни одной новости
</div>
</div>
';
		}

		$tpl = <<<TPL
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Новости</title>

        <style type="text/css">
		.newsall{padding-bottom:10px;}
.zagn{color:#000000;font:bold 10px arial;}
		.txtn{color:#ffffff;font:normal 8px arial;}
			</style>
		</head>
		<body>
{{MAINCONTENT}}
</body>
</html>
TPL;

		$this->show(str_replace('{{MAINCONTENT}}',$news,$tpl));
	}

	public function joinserver()
	{
		$sessionid = $this->core->sanString(isset($_GET['sessionId'])?$_GET['sessionId']:null);
		$user = $this->core->sanString(isset($_GET['user'])?$_GET['user']:null);
		$serverid = $this->core->sanString(isset($_GET['severId'])?$_GET['serverId']:null);

		if (!preg_match("/^[a-zA-Z0-9_-]+$/", $user) || !preg_match("/^[a-zA-Z0-9_-]+$/", $sessionid) || !preg_match("/^[a-zA-Z0-9_-]+$/", $serverid))
			$this->quit("Bad login");

		$id = $this->users_core->user->login->getPropertyByValue($user);
		if($id===false)$this->quit("Bad login");

		if($this->users_core->user->minecraft_integration_session_id->getProperty($id)==$sessionid &&
			$this->users_core->user->minecraft_integration_server_id->getProperty($id)==$serverid)$this->quit("OK");
		else
		{
			if($this->users_core->user->minecraft_integration_session_id->getProperty($id)==$sessionid &&
				$this->users_core->user->login->getProperty($id)==$user)
			{
				$this->users_core->user->minecraft_integration_session_id->setProperty($id,$serverid);
				$this->quit("OK");
			}
			else
			{
				$this->quit("Bad login");
			}
		}
	}

	public function checkserver()
	{
		$user = $this->core->sanString(isset($_GET['user'])?$_GET['user']:null);
		$serverid = $this->core->sanString(isset($_GET['serverId'])?$_GET['serverId']:null);

		if (!preg_match("/^[a-zA-Z0-9_-]+$/", $user) || !preg_match("/^[a-zA-Z0-9_-]+$/", $serverid))$this->quit("NO");

		$id = $this->users_core->user->login->getPropertyByValue($user);
		if($id===false)$this->quit("NO");

		if($this->users_core->user->minecraft_integration_server_id->getProperty($id)!=$serverid)$this->quit("NO");

		if($this->users_core->user->login->getProperty($id)!==$user)$this->quit("NO");
	}

	public function generateSessionId(){
		srand(time());
		$randNum = rand(1000000000, 2147483647).rand(1000000000, 2147483647).rand(0,9);
		return $randNum;
	}

	public function launcher()
	{
		$file = $this->config['launcher-version'];
		$count = trim($file);
		return $count;
	}

	public function version()
	{
		$file = $this->config['client-version'];
		$count = trim($file);
		return $count;
	}

	public function online()
	{//$this->core->file->writeFromString('cache/minecraftIntegrationXlauncherMonitoring','42:-1:');die;
		$string = '';
		foreach($this->config['servers'] as $server)
		{
			$port = $server[1];
			$address = $server[0];
			$res = $this->getRes($address,$port);
			$string .= $res['online'].":";
		}
		$this->core->file->writeFromString('cache/minecraftIntegrationXlauncherMonitoring',$string);
	}

	public function getMon()
	{
		return $this->core->file->readAsString('cache/minecraftIntegrationXlauncherMonitoring');
	}

	public function getRes($address,$port)
	{
		set_time_limit(15);
		$socket=@fsockopen($address,$port);
		stream_set_timeout($socket, 0, 90 * 1000);
		if($socket===false)
		$res['online']='-1';
		else
		{
			@fwrite($socket,"\xFE");
			$data = "";
			$data = @fread($socket,256);
			@fclose($socket);

			if($data!==false && substr($data,0,1)=="\xFF")
			{
				$info= substr($data,3);
				$info = iconv('UTF-16BE','UTF-8',$info);

				if($info[1]==="\xA7" && $info[2]==="\x31")
				{
					$info = explode("\x00",$info);
					$res['online'] = IntVal($info[4]);
					$res['max'] = IntVal($info[5]);
				}
				else
				{
					$info = Explode("\xA7",$info);
					$res['online'] = IntVal($info[1]);
					$res['max'] = IntVal($info[2]);
				}
			}
			else
			$res['online'] = '-1';
		}

		return $res;
	}

	public function scan()
	{
		static $count_files=0;
		static $count_dirs=0;

		$dirname = isset($_GET['dir'])?str_replace(array('..','\\'),array('',''),$_GET['dir']):null;

		$type = isset($_GET['type'])?$_GET['type']:null;
		$root = dirname(__FILE__).'/xLauncher/data/check';

		if($dirname===null)return;

		$dir = opendir($root.$dirname);
		if($dir===null)return;
		
		while(($file=readdir($dir))!==false)
		{
			if($file != "." && $file != "..")
			{
				if(is_file($root.$dirname."/".$file))
				{
					$count_files++;
				}
				if(is_dir($root.$dirname."/".$file))
				{
					$count_dirs++;
				}
			}
		}
		closedir($dir);

		switch($type)
		{
			case 'files':
				if(empty($count_files))
				{
					$count_files = 0;
				}

				$this->show($count_files);
				break;

			case 'dirs':
				if(empty($count_dirs))
				{
					$count_dirs = 0;
				}

				$this->show($count_dirs);
				break;
		}
	}

	public function xorencode($str, $key)
	{
		while(strlen($key) < strlen($str))
		{
			$key .= $key;
		}
		return $str ^ $key;
	}

	public function strtoint($text)
	{
		$res = "";
		for ($i = 0; $i < strlen($text); $i++) $res .= ord($text{$i}) . "-";
		$res = substr($res, 0, -1);
		return $res;
	}

	public function inttostr($text)
	{
		$res = "";
		$split = explode("-", $text);
		for ($i = 0; $i < count($split); $i++) $res .= chr($split{$i});
		return $res;
	}

	public function authCheck()
	{
		if(isset($_GET['user']) && isset($_GET['s']))
		{
			$login = $this->core->sanString($_GET['user']);
			$hwid = $this->core->sanString($_GET['s']);

			$id = $this->users_core->user->minecraft_integration_hw_id->getPropertyByValue($hwid);
			if(!$id)
			{
				$id = $this->users_core->user->login->getPropertyByValue($login);

				if($id===false)$this->quit("Пользователь не найден");

				$this->users_core->user->minecraft_integration_hw_id->setProperty($id,$hwid);
			}
			$ban = $this->users_core->user->minecraft_integration_banned->getProperty($id);
			if($ban!=1)
			{
				$this->quit("1");
			}
			else
			{
				$this->quit("banned");
			}
		}
	}

	public function loginServer()
	{
		if(isset($_GET['user']) && isset($_GET['password']) && isset($_GET['version']))
		{
			$ver=$_GET['version'];
			if($this->launcher()!==$ver)$this->quit("oldLauncher");

			$postPass = $this->core->sanString($_GET['password']);
			$login = $this->core->sanString($_GET['user']);

			$id = $this->users_core->user->login->getPropertyByValue($login);
			if($id===false)$this->quit("abuse");

			if ($this->users_core->user->password->comparePass($id,$postPass))
			{
				$sessid = $this->generateSessionId();
				$this->users_core->user->minecraft_integration_session_id->setProperty($id,$sessid);
				$this->quit("0");
			}
			else
			{
				$this->quit("fail");
			}
		}
	}

	public function getClientSize()
	{
		$sizefromclient = $_REQUEST['size'];
		$dirname = dirname(__FILE__).'/xLauncher/data/check/bin';
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

		if(md5($size)==$sizefromclient)
		{
			$this->show("2");
		}
		else
		{
			$this->show("abuseSize");
		}
	}

	function getSession()
	{
		$login = $this->core->sanString(isset($_GET['user'])?$_GET['user']:null);

		$id = $this->users_core->user->login->getPropertyByValue($login);
		if($id===false)$this->quit("false");

		$sessionid = $this->users_core->user->minecraft_integration_session_id->getProperty($id);

		$sessid = $this->strtoint($this->xorencode($sessionid,$this->config['session-key']));
		$this->show("4:".$sessid);
	}

	public function quit($msg=null)
	{
		if($msg!==null)echo $msg;
		exit;
	}

	public function show($msg=null)
	{
		if($msg!==null)echo $msg;
	}
}