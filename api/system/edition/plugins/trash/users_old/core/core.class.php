<?php
namespace CRAFTEngine\plugins\users;

define('CRAFTEngine\plugins\users\SIGNUP_INVITE',1<<0);
define('CRAFTEngine\plugins\users\SIGNUP_ADMIN',1<<1);
define('CRAFTEngine\plugins\users\SIGNUP_MAIL',1<<2);
define('CRAFTEngine\plugins\users\SIGNUP_MAX',(1<<3) - 1);

class core
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	/**
	 * Add code with type ans value
	 *
	 * @param $type
	 * @param $value
	 * @param $data
	 * @return bool
	 */
	public function addCode($type,$value,$data)
	{
		$value = $this->core->sanString($value);
		$type = $this->core->sanString($type);
		$data = $this->core->sanString(json_encode($data,JSON_UNESCAPED_UNICODE));
		$timestamp = time();

		$qr = $this->core->mysql->query("INSERT INTO users_code(type,value,data,time) VALUE('$type','$value','$data','$timestamp')");
		if($qr)return true;
		else return false;
	}

	/**
	 * Delete code with type and value
	 *
	 * @param $type
	 * @param $value
	 * @return bool
	 */
	public function removeCode($type,$value)
	{
		$qr = $this->core->mysql->query("DELETE FROM users_code WHERE type='$type' AND value='$value'");
		if($qr)return true;
		else return false;
	}

	/**
	 * Get code with type and value
	 *
	 * @param $type
	 * @param $value
	 * @return array|bool|mixed
	 */
	public function getCode($type,$value)
	{
		$value = $this->core->sanString($value);
		$type = $this->core->sanString($type);

		$qr = $this->core->mysql->query("SELECT * FROM users_code WHERE type='$type' AND value='$value'");
		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch($qr);
		return json_decode($fr['data'],true);
	}

	/**
	 * Get or set current user id
	 *
	 * @param null $id
	 * @return int
	 */
	public function currentUser($id=null)
	{
		if($id!==null)$_SESSION['users']['id'] = $id;

		return isset($_SESSION['users']['id'])?$_SESSION['users']['id']:0;
	}

	/**
	 * Can user with id(if isn't set, will get id of current user) do act
	 *
	 * @param $act
	 * @param $id
	 * @return bool
	 */
	public function canDo($act,$id=null,$p=array())
	{
		$id = $id===null?$this->currentUser():intval($id);

		switch($act)
		{
			case 'users_signup_admin_confirm':
				$u = $this->core->plugin->initPl('users','user');
				//If rank module doesn't exist, ip filter
				if(!in_array('rank',$u->getPropertiesList()))
				{
					if($this->confs->user->admin_ip==true)
					{
						if(in_array($_SERVER['REMOTE_ADDR'],$this->core->conf->system->core->admin_ip))
						{
							if($this->changeSignupMode($id,SIGNUP_ADMIN))
							{
								return true;
							}

							$this->core->error->error('server',500);
							return false;
						}
					}

					$this->core->error->error('server',403);
					return false;
				}
				else
				{
					$ranks = $u->rank->getProprety($this->currentUser());
					$can = false;


					foreach($ranks as $r)
					if(in_array($r,$this->confs->modules['rank']['access']['signup_admin']))
					{
						$can = true;
						break;
					}

					if($can)
					{
						return true;
					}

					$this->core->error->error('server',403);
					return false;
				}
				break;

			default:
				return $this->core->plugin('canDo','articles',false,$p);
				break;
		}

		return false;
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
			case SIGNUP_INVITE:

				break;

			case SIGNUP_ADMIN:
				if($this->canDo('users','signup_admin_confirm',null,array()))
				{
					return true;
				}
				else
				{
					return false;
				}
				break;

			case SIGNUP_MAIL:
				$isset = $this->getCode('signup_mail',$code);
				if($isset!==false)
				{
					if($isset['uid']==$id)
					{
						if($this->changeSignupMode($id,SIGNUP_MAIL) && $this->removeCode('signup_mail',$code))
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
		$u = $this->core->plugin->initPl('users','user');
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
				$mode += SIGNUP_ADMIN;
				break;

			case 'invite':
				$mode += SIGNUP_INVITE;
				break;

			case 'email':
				$mode += SIGNUP_MAIL;
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
		$mode_in_db = SIGNUP_MAX - $mode;
		$u = $this->core->plugin->initPl('users','user');
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
			$cap = $c->check($p['captcha'],'users_signup');
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
		elseif($this->isSignupMode(SIGNUP_MAIL,$mode))
		{
			if(in_array('email',$prop))
			{
				$email = $this->core->sanString($p['email']);

				$code = sha1($id.$email.time());
				$this->addCode('signup_mail',$code,array('uid'=>$id));

				$this->core->mail->addWaitingList($email, '002', array('id'=>$id, 'code'=>$code));
			}
		}
		elseif($this->isSignupMode(SIGNUP_ADMIN,$mode))
		{

		}
		elseif($this->isSignupMode(SIGNUP_INVITE,$mode))
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
		$u = $this->core->plugin->initPl('users','user');
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

		if($mode==SIGNUP_MAX)
		{
			$this->register($id);
		}

		return true;
	}

	/**
	 * Login into system
	 *
	 * @param array $p
	 * @return bool
	 */
	public function login($p=array())
	{
		$u = $this->core->plugin->initPl('users','user');
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
			$this->currentUser($id);
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
		$u = $this->core->plugin->initPl('users','user');
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
			$this->addCode('restore_mail',$code,array('uid'=>$id));
			$this->core->mail->addWaitingList($email, '003', array('code'=>$code));
			return true;
		}
		elseif($step==2)
		{
			$code = isset($p['code'])?$p['code']:null;
			$code = $this->core->sanString($p['code']);
			$isset = $this->getCode('restore_mail',$code);
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

				$u->password->setProperty($id,$password);

				$isset = $this->removeCode('restore_mail',$code);

				return $password;
			}
			else
			{
				$this->core->error->error('plugin_users_code',0);
				return false;
			}
		}
	}

	public function makeEvent($id,$module,$addInfo,$staticInfo=null)
	{
		static $u = null;
		if($u===null)$u = $this->core->plugin->initPl('users','user');
		foreach($u->getPropertiesList() as $mod)
		{
			if(method_exists($u->$mod,'registerEvent'))
				$addInfo = $u->$mod->registerEvent($id,$module,$addInfo,$staticInfo);
		}

		return $addInfo;
	}
}