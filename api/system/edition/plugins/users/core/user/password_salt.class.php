<?php
namespace CRAFTEngine\plugins\users;
class password_salt implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function registerEvent($id,$module,$info)
	{
		switch("{$id}_{$module}")
		{
			case 'signup_password':
				$symbols = array(
					'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
					'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
					'0','1','2','3','4','5','6','7','8','9',
				);
				shuffle($symbols);
				$sal = array_rand($symbols, 10);
				$salt = '';
				foreach((array)$sal as $c)
				{
					$salt .= $symbols[$c];
				}
				$uid = intval($info[0]);
				$qr = $this->core->mysql->query("UPDATE users_signup SET password_salt='$salt' WHERE id='$uid'");

				if(!$qr)
				{
					$info[2] = false;
				}
				else
				{
					$info[1] = $info[1].$salt;
				}
				break;

			case 'set_password':
				$salt = $this->getProperty($info[0]);
				if($salt)
				{
					$info[1] = $info[1].$salt;
				}
				else
				{
					$info[2] = false;
				}
				break;
		}

		return $info;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'password_salt'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD password_salt VARCHAR(255)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'password_salt'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD password_salt VARCHAR(255)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT password_salt FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['password_salt'];
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = trim($value);
		$value = $this->generatePass($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET password_salt='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		$value = $this->core->sanString($value);
		return true;
	}

	public function canGetProperty($id,$idfrom)
	{
		return false;
	}

	public function canSetProperty($id,$idfrom)
	{
		return false;
	}

	public function canSignup($value)
	{
		return true;
	}

	public function signup($id,$value)
	{
		return true;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT password_salt FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $fr['password_salt'];
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET password_salt='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}
}