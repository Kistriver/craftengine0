<?php
namespace CRAFTEngine\api\user;
class login extends \CRAFTEngine\core\api
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
		
		$user = $this->core->plugin->initPl('user','user');//new user($this->core);
		$u = $user->get_user($email, 'email');
		if(!$_SESSION['loggedin'])
		if($u)
		{
			if($email!=null AND $password!=null)
			{
				if(preg_match($user->preg('mail'), $email) AND 
				strlen($password)<=$this->core->conf->plugins->user->user->length['password']['max'] AND
				strlen($password)>=$this->core->conf->plugins->user->user->length['password']['min'])
				{
					$time = $user->time_reg;
					$salt = $user->salt;
					$password_md5 = $user->password_md5($password, false, $time, $salt);
					if($password_md5 == $user->pass)
					{
						$rank = $this->core->plugin->initPl('user','rank');//new rank($this->core);
						$rank->get_rank($user->id);
						
						if($rank->warnings!=100)
						{
							$time_now = time();
							//$this->core->mysql->query("UPDATE login SET time_login='$time_now' WHERE id='$user->id'");
							
							$this->core->plugin->lib('browser.class');
							$browser = new Browser();
							$bro = $browser->getBrowser() . " " . $browser->getVersion();
							$platform = $browser->getPlatform();
							$id = $user->id;
							$this->core->mysql->query("INSERT INTO login_ok(user, browser, ip, platform, time) 
							VALUES('$id','$bro','$_SERVER[REMOTE_ADDR]','$platform','$time_now')");
							$user->set_user($user->id, 'id');
							
							$this->core->mysql->query("INSERT INTO login_sid(id,time,sid) VALUES('$user->id','$_SESSION[auth_time]','$_SESSION[auth]')");
							
							//$time_end = time() + 60*60*24*7;
							//setcookie('cache_sessid', sha1($user->cache), $time_end, '/', $_SERVER['SERVER_REQUIRE'], false/*true*/);
							return $this->json(array(false));
						}
						else
						{
							$this->core->error->error('plugin_user_login',3);
						}
					}
					else
					{
						$this->core->error->error('plugin_user_login',0);
						
						$this->core->plugin->lib('browser.class');
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
					$this->core->error->error('plugin_user_login',2);
				}
			}
			else
			{
				$this->core->error->error('plugin_user_login',1);
			}
		}
		else
		{
			$this->core->error->error('plugin_user_login',0);
		}
		else
		$this->core->error->error('server',403);
		return $this->json(array(false));
	}
	
	//Деавторизация пользователя
	protected function logout()
	{
		if($_SESSION['loggedin'])
		{
			session_destroy();
		}
		else
		$this->core->error->error('server',403);
		
		$returned = array();
		
		return $this->json($returned);
	}
	
	//Активация аккаунта с помощью email
	protected function activate()
	{
		$code = $this->core->sanString($this->data['code']);
		$c = $this->core->mysql->query("SELECT * FROM code WHERE type='signup' AND value='$code'");
		if($this->core->mysql->rows($c)==1)
		{
			$r = $this->core->mysql->fetch($c);
			
			$data = json_decode($r['data'],true);
			$login = $this->core->sanString($data['login']);
			$id = (int)$data['id'];
			
			$us = $this->core->mysql->query("SELECT * FROM signup WHERE id='$id' AND login='$login'");
			if($this->core->mysql->rows($us)==1)
			{
				$signup = $this->core->mysql->fetch($us);
				
				$status = $signup['status'];
				if($status==2 OR $status==3)
				{
					$this->core->mysql->query("UPDATE signup SET status='3' WHERE id='$id' AND login='$login'");
					//REGISTER
					$u = $this->core->plugin->initPl('user','user');//new user($this->core);
					$u->new_user(
						$signup['name'],
						$signup['surname'],
						$signup['email'],
						$signup['password'],
						$signup['login'],
						$signup['sex'],
						$signup['day'],
						$signup['month'],
						$signup['year'],
						null,
						null,
						$signup['invite'],
						$signup['time'],
						$signup['about']
					);
					
					$this->core->mysql->query("DELETE FROM code WHERE type='signup' AND value='$code'");
					$this->core->mysql->query("DELETE FROM signup WHERE login='$login'");
					
					return $this->json(array(true));
				}
				//mail - 1, admin - 2
				elseif($status==0 OR $status==1)
				{
					$this->core->mysql->query("UPDATE signup SET status='1' WHERE id='$id' AND login='$login'");
					$this->core->mysql->query("DELETE FROM code WHERE type='signup' AND value='$code'");
					return $this->json(array(true));
				}
			}
			else
			{
				return $this->json(array(false));
			}
		}
		else
		{
			return $this->json(array(false));
		}
	}
	
	//Восстановление аккаунта
	protected function restore()
	{
		if($_SESSION['loggedin'])
		{
			$this->core->error->error('server',403);
			return $this->json(array(false));
		}


		$this->input('step');
		$step = trim($this->core->sanString($this->data['step']));

		if($step==1)
		{
			$this->input('email','captcha');

			$email = trim($this->core->sanString($this->data['email']));
			$captcha = strtoupper(trim($this->core->sanString($this->data['captcha'])));

			$c = $this->core->plugin->initPl('captcha','captcha');
			$cap = $c->check($captcha,'user_pass_restore');
			if(!$cap)
			{
				$this->core->error->error('plugin_captcha_captcha',0);
				return $this->json(array(false));
			}

			$u = $this->core->plugin->initPl('user','user');
			$get = $u->get_user($email,'email');
			if(!$get)
			{
				$this->core->error->error('plugin_user_user',0);
				return $this->json(array(false));
			}

			$code = $u->generate_code('restore_pass',array('login'=>$u->login,'email'=>$u->email));
			$this->core->mail->addWaitingList($u->email, '003', array('code'=>$code,'login'=>$u->login,'email'=>$u->email));

			return $this->json(array(true));
		}
		elseif($step==2)
		{
			$this->input('code');
			$code = trim($this->core->sanString($this->data['code']));

			$c = $this->core->mysql->query("SELECT * FROM code WHERE type='restore_pass' AND value='$code'");
			if($this->core->mysql->rows($c)!=0)
			{
				$r = $this->core->mysql->fetch($c);

				$data = json_decode($r['data'],true);
				$login = $this->core->sanString($data['login']);

				$u = $this->core->plugin->initPl('user','user');
				$u->get_user($login,'login');
				$pass = $u->change_user($u->id, '' , 'password_gen_new');
				$this->core->mysql->query("DELETE FROM code WHERE type='restore_pass' AND value='$code'");

				return $this->json(array(true,$pass));
			}
			else
			{
				return $this->json(array(false));
			}
		}
	}
	
	//launcher, client, server in other one file
}
?>