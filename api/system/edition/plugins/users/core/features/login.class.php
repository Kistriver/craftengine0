<?php
namespace CRAFTEngine\plugins\users\features;
class login implements \CRAFTEngine\plugins\users\featureInterface
{
	public function __construct($core,$users_core)
	{
		$this->core = &$core;
		$this->users_core = &$users_core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	/**
	 * Login into system
	 *
	 * @param array $p
	 * @return bool
	 */
	public function login($p=array())
	{
		$u = &$this->users_core->user;
		if(!isset($u->password))
		{
			$this->core->error->error('server',500);
			return false;
		}

		$password = isset($p['password'])?$p['password']:null;
		$modes = $this->confs->user->login_mode;
		$id = null;
		$type = null;
		$ids = 0;

		foreach($p as $k=>$v)
		{
			if(in_array($k,$modes))
			{
				$id = $v;
				$type = $k;
				$ids++;
			}
		}

		if($ids!=1)
		{
			$this->core->error->error('plugin_users_login',3);
			return false;
		}

		if(!isset($u->$type))
		{
			return false;
		}

		$id = $u->$type->getPropertyByValue($id);

		if($id===false)
		{
			$this->core->error->error('plugin_users_login',0);
			return false;
		}

		if($u->password->comparePass($id,$password))
		{
			$u->currentUser($id);
			return true;
		}
		else
		{
			$this->core->error->error('plugin_users_login',0);
			return false;
		}
	}

	/**
	 * Restore user password
	 *
	 * @param array $p
	 * @return bool|string
	 */
	public function restorePassword($p=array())
	{
		$u = &$this->users_core->user;
		if(!isset($u->password) || !isset($u->email))
		{
			$this->core->error->error('server',500);
			return false;
		}

		$step =  isset($p['step'])?$p['step']:null;

		if($step==1)
		{
			$c = $this->core->plugin->initPl('captcha','captcha');
			if($c)
			{
				$cap = $c->check(isset($p['captcha'])?$p['captcha']:null,'users_pass_restore');
				if(!$cap)
				{
					$this->core->error->error('plugin_captcha_captcha',0);
					return false;
				}
			}


			$email = isset($p['email'])?$p['email']:null;
			$email = $this->core->sanString($p['email']);

			$id = $u->email->getPropertyByValue($email);

			if($id===false)
			{
				$this->core->error->error('plugin_users_restore',0);
				return false;
			}

			$code = sha1($id.$email.time());
			$this->users_core->code->addCode('restore_mail',$code,array('uid'=>$id));
			$this->core->mail->addWaitingList($email, '003', array('code'=>$code));
			return true;
		}
		elseif($step==2)
		{
			$code = isset($p['code'])?$p['code']:null;
			$code = $this->core->sanString($p['code']);
			$isset = $this->users_core->code->getCode('restore_mail',$code);
			if($isset!==false)
			{
				$id = $isset['uid'];

				$symbols = array(
					'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
					'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
				);
				shuffle($symbols);
				$pass = array_rand($symbols, 8);
				$password = '';
				foreach((array)$pass as $c)
				{
					$password .= $symbols[$c];
				}

				if(!$u->password->setProperty($id,$password))return false;

				$this->users_core->code->removeCode('restore_mail',$code);

				return $password;
			}
			else
			{
				$this->core->error->error('plugin_users_code',0);
				return false;
			}
		}
	}
}