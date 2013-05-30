<?php
class api_login extends api
{
	public function init()
	{
	   #$this->functions['function']='act';
		$this->functions['login']='login';
		$this->functions['logout']='logout';
		$this->functions['activate']='activate';
		$this->functions['restore']='restore';
	}
	
	protected function login()
	{
		$email = SanString($this->data['email']);
		$email = mb_convert_case($email, MB_CASE_LOWER, 'UTF-8');
		$password = SanString($this->data['password']);
		
		$user = new user();
		$u = $user->get_user($email, 'email');
		if(!$_SESSION['loggedin'])
		if($u)
		{
			if($email!='' AND $password!='')
			{
				if(preg_match($GLOBALS['_CONF']['server']['preg_match']['email'], $email) AND 
				strlen($password)<=$GLOBALS['_CONF']['server']['string_length']['password']['site']['max'] AND
				strlen($password)>=$GLOBALS['_CONF']['server']['string_length']['password']['site']['min'])
				{
					$res = queryMysql("SELECT id, email, login FROM signup WHERE email='$email'");
					if(mysql_num_rows($res)==0)
					{
						$time = $user->time_reg;
						$salt = $user->salt1;
						$password_md5 = $user->password_md5('site', 'no', $password, $time, $salt);
						if($password_md5 == $user->pass)
						{
							$rank = new rank();
							$rank->get_rank($user->id);
							
							if($rank->warnings<$GLOBALS['_CONF']['server']['max_warnings_per_service'])
							{
								if($rank->checked==3)
								{
									$time_now = time();
									queryMysql("UPDATE login SET time_login='$time_now' WHERE id='$res[id]'");
									
									$browser = new Browser();
									$bro = $browser->getBrowser() . " " . $browser->getVersion();
									$platform = $browser->getPlatform();
									$id = $user->id;
									queryMysql("INSERT INTO login_ok(id, browser, ip, platform, time) 
									VALUES('$id','$bro','$_SERVER[REMOTE_ADDR]','$platform','$time_now')");
									$user->set_user($user->id, 'id');

									$time_end = time() + 60*60*24*7;
									setcookie('cache_sessid', sha1($user->cache), $time_end, '/', $_SERVER['SERVER_REQUIRE'], false/*true*/);

									//return true;
								}
								else
								{
									$this->error('login','004');
								}
							}
							else
							{
								$this->error('login','003');
							}
						}
						else
						{
							$this->error('login','000');
							
							$browser = new Browser();
							$bro = $browser->getBrowser() . " " . $browser->getVersion();
							$platform = $browser->getPlatform();
							$id = $user->id;
							$time_now = time();
							queryMysql("INSERT INTO login_fail(id, browser, ip, platform, time) 
							VALUES('$id','$bro','$_SERVER[REMOTE_ADDR]','$platform','$time_now')");
						}
					}
					else
					{
						$res = mysql_fetch_array($res);
						$user->email($res['email'], 'activate', array($res['login'], $res['id']));
						$this->error('login','005');
					}
				}
				else
				{
					$this->error('login','002');
				}
			}
			else
			{
				$this->error('login','001');
			}
		}
		else
		{
			$this->error('login','000');
		}
		else
		$this->error('server','403');
		
		$returned = array();
		
		return $this->json($returned);
	}
	
	protected function logout()
	{
		if($_SESSION['loggedin'])
		logout();
		else
		$this->error('server','403');
		
		$returned = array();
		
		return $this->json($returned);
	}
	
	protected function activate()
	{
		$this->wip();
	}
	
	protected function restore()
	{
		$this->wip();
	}
	
	//launcher, client, server in other one file
}
?>