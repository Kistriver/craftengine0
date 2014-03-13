<?php
namespace CRAFTEngine\plugins\users\features;
class signup implements \CRAFTEngine\plugins\users\featureInterface
{
	const SIGNUP_INVITE = 1;
	const SIGNUP_ADMIN = 2;
	const SIGNUP_MAIL = 4;
	const SIGNUP_MAX = 7;

	public function __construct($core,$users_core)
	{
		$this->core = &$core;
		$this->users_core = &$users_core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	/**
	 * Activate signup users by mail, admin etc
	 *
	 * @param array $p
	 * @return bool
	 */
	public function signupActivate($p=array())
	{
		if(!isset($p['id'],$p['add_mode']))return false;

		$id = intval($p['id']);
		$add_mode = intval($p['add_mode']);
		$code = isset($p['code'])?$this->core->sanString($p['code']):null;

		if($this->isSignupMode($add_mode,$this->signupModeNow($id)))return false;

		switch($add_mode)
		{
			case self::SIGNUP_INVITE:

				break;

			case self::SIGNUP_ADMIN:
				if($this->users_core->permission->canDo('users_signup_admin_confirm',null,array()))
				{
					return true;
				}
				else
				{
					return false;
				}
				break;

			case self::SIGNUP_MAIL:
				$isset = $this->users_core->code->getCode('signup_mail',$code);
				if($isset!==false)
				{
					if($isset['uid']==$id)
					{
						if($this->users_core->code->changeSignupMode($id,self::SIGNUP_MAIL) && $this->users_core->code->removeCode('signup_mail',$code))
						{
							return true;
						}
					}
					$this->core->error->error('server',500);
					return false;
				}
				else
				{
					$this->core->error->error('plugin_users_code',0);
					return false;
				}
				break;
		}
	}

	public function signupValuesCheck($p=array())
	{
		$u = &$this->users_core->user;
		$prop = $u->getPropertiesList();

		$return = false;
		foreach($prop as $pr)
		{
			if(!isset($p[$pr]))continue;

			$value = $u->$pr->canSignup($p[$pr]);
			if($value===false)$return = true;
		}
		if($return)return false;
	}

	/**
	 * Mode of registration
	 *
	 * @return int
	 */
	public function signupMode()
	{
		static $mode = 0;
		static $done = false;

		if(!$done)
			foreach($this->core->conf->plugins->users->user->signup_mode as $el)
				switch($el)
				{
					case 'admin':
						$mode += self::SIGNUP_ADMIN;
						break;

					case 'invite':
						$mode += self::SIGNUP_INVITE;
						break;

					case 'email':
						$mode += self::SIGNUP_MAIL;
						break;
				}

		$done = true;

		return $mode;
	}

	/**
	 * Is now registration mode include this parameter
	 *
	 * @param $is_mode
	 * @param $mode_now
	 * @return boolean
	 */
	public function isSignupMode($is_mode,$mode_now=null)
	{
		if($mode_now===null)$mode_now = $this->signupMode();
		$mode_now = decbin($mode_now);
		//$is_mode = decbin($is_mode);

		return $mode_now & $is_mode;
	}

	/**
	 * Register user in temporarily table
	 *
	 * @param array $p
	 * @return boolean
	 */
	public function signup($p=array())
	{
		$mode = $this->signupMode();
		$mode_in_db = self::SIGNUP_MAX - $mode;
		$u = &$this->users_core->user;
		$prop = $u->getPropertiesList();

		$return = false;
		foreach($prop as $pr)
		{
			if(!isset($p[$pr]))$p[$pr] = null;

			if(!is_array($p[$pr]))$p[$pr] = trim($p[$pr]);
			$value = $u->$pr->canSignup($p[$pr]);
			if($value===false)$return = true;
		}

		$c = $this->core->plugin->initPl('captcha','captcha');
		if($c)
		{
			$cap = $c->check(isset($p['captcha'])?$p['captcha']:null,'users_signup');
			if(!$cap)
			{
				$this->core->error->error('plugin_captcha_captcha',0);
				return false;
			}
		}

		if($return)return false;

		$qr = $this->core->mysql->query("INSERT INTO users_signup(mode) VALUE('$mode_in_db')");
		if(!$qr)return false;

		$qr = $this->core->mysql->query("SELECT LAST_INSERT_ID()");
		if(!$qr)return false;

		//$id = $this->core->mysql->fetch($qr);
		$id = $qr->fetch_array();
		$id = $id['LAST_INSERT_ID()'];

		$return = false;
		foreach($prop as $pr)
		{
			$value = $u->$pr->signup($id,$p[$pr]);
			if($value===false)$return = true;
		}
		if($return)return false;

		if($mode==0)
		{
			$this->register($id);
		}
		elseif($this->isSignupMode(self::SIGNUP_MAIL,$mode))
		{
			if(in_array('email',$prop))
			{
				$email = $this->core->sanString($p['email']);

				$code = sha1($id.$email.time());
				$this->users_core->code->addCode('signup_mail',$code,array('uid'=>$id));

				$this->core->mail->addWaitingList($email, '002', array('id'=>$id, 'code'=>$code));
			}
		}
		elseif($this->isSignupMode(self::SIGNUP_ADMIN,$mode))
		{

		}
		elseif($this->isSignupMode(self::SIGNUP_INVITE,$mode))
		{

		}

		return true;
	}

	/**
	 * Register user in primary table
	 *
	 * @param $id
	 * @return boolean
	 */
	public function register($id)
	{
		$u = &$this->users_core->user;
		$prop = $u->getPropertiesList();

		$qr = $this->core->mysql->query("INSERT INTO users(id) VALUE(NULL)");
		if(!$qr)return false;

		$qr = $this->core->mysql->query("SELECT LAST_INSERT_ID()");
		if(!$qr)return false;

		$idnew = $qr->fetch_array();
		$idnew = $idnew['LAST_INSERT_ID()'];

		$return = false;
		foreach($prop as $pr)
		{
			$value = $u->$pr->register($id,$idnew);
			if($value===false)$return = true;
		}
		if($return)return false;

		$this->core->mysql->query("DELETE FROM users_signup WHERE id='$id'");

		return true;
	}

	/**
	 * Mode of user with ID in signup table
	 *
	 * @param $id
	 * @return bool
	 */
	public function signupModeNow($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT * FROM users_signup WHERE id='$id'");
		if($this->core->mysql->rows($qr)==0)return false;
		$fr = $this->core->mysql->fetch($qr);
		$mode =  $fr['mode'];
		return $mode;
	}

	/**
	 * Change mode of user in temporarily table
	 *
	 * @param $id
	 * @param $mode_add
	 * @return boolean
	 */
	public function changeSignupMode($id,$mode_add)
	{
		$mode_add = intval($mode_add);
		$mode = $this->signupModeNow($id);
		if($mode===false)return false;

		if($this->isSignupMode($mode_add,$mode))return false;

		$mode += $mode_add;

		$qr = $this->core->mysql->query("UPDATE users_signup SET mode='$mode' WHERE id='$id'");
		if(!$qr)return false;

		if($mode==self::SIGNUP_MAX)
		{
			$this->register($id);
		}

		return true;
	}
}