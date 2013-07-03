<?php
class api_login extends api
{
	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['login']='login';
		$this->functions['logout']='logout';
		$this->functions['activate']='activate';
		$this->functions['restore']='restore';
	}
	
	//Авторизация пользователя
	protected function login()
	{
		$this->input('password','email');
		
		$email = $this->core->SanString($this->data['email']);
		$email = mb_convert_case($email, MB_CASE_LOWER, 'UTF-8');
		$password = $this->core->SanString($this->data['password']);
		
		$this->core->plugin('user');
		$user = new user($this->core);
		$u = $user->get_user($email, 'email');
		if(!$_SESSION['loggedin'])
		if($u)
		{
			if($email!=null AND $password!=null)
			{
				if(preg_match($this->core->preg('mail'), $email) AND 
				strlen($password)<=$this->core->conf->length['password']['max'] AND
				strlen($password)>=$this->core->conf->length['password']['min'])
				{
					$time = $user->time_reg;
					$salt = $user->salt;
					$password_md5 = $user->password_md5($password, false, $time, $salt);
					if($password_md5 == $user->pass)
					{
						$this->core->plugin('rank');
						$rank = new rank($this->core);
						$rank->get_rank($user->id);
						
						if($rank->warnings!=100)
						{
							$time_now = time();
							//$this->core->mysql->query("UPDATE login SET time_login='$time_now' WHERE id='$user->id'");
							
							$this->core->plugin('browser');
							$browser = new Browser();
							$bro = $browser->getBrowser() . " " . $browser->getVersion();
							$platform = $browser->getPlatform();
							$id = $user->id;
							$this->core->mysql->query("INSERT INTO login_ok(user, browser, ip, platform, time) 
							VALUES('$id','$bro','$_SERVER[REMOTE_ADDR]','$platform','$time_now')");
							$user->set_user($user->id, 'id');

							//$time_end = time() + 60*60*24*7;
							//setcookie('cache_sessid', sha1($user->cache), $time_end, '/', $_SERVER['SERVER_REQUIRE'], false/*true*/);

							//return true;
						}
						else
						{
							$this->core->error->error('login','003');
						}
					}
					else
					{
						$this->core->error->error('login','000');
						
						$this->core->plugin('browser');
						$browser = new Browser();
						$bro = $browser->getBrowser() . " " . $browser->getVersion();
						$platform = $browser->getPlatform();
						$id = $user->id;
						$time_now = time();
						$this->core->mysql->query("INSERT INTO login_fail(user, browser, ip, platform, time) 
						VALUES('$id','$bro','$_SERVER[REMOTE_ADDR]','$platform','$time_now')");
					}
				}
				else
				{
					$this->core->error->error('login','002');
				}
			}
			else
			{
				$this->core->error->error('login','001');
			}
		}
		else
		{
			$this->core->error->error('login','000');
		}
		else
		$this->core->error->error('server','403');
		
		return $this->json();
	}
	
	//Деавторизация пользователя
	protected function logout()
	{
		if($_SESSION['loggedin'])
		session_destroy();
		else
		$this->core->error->error('server','403');
		
		$returned = array();
		
		return $this->json($returned);
	}
	
	//Активация аккаунта с помощью email
	protected function activate()
	{
		$this->wip();
	}
	
	//Восстановление аккаунта
	protected function restore()
	{
		$this->wip();
	}
	
	//launcher, client, server in other one file
}
?>