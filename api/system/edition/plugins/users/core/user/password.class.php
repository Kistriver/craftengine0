<?php
namespace CRAFTEngine\plugins\users;
class password implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function construct($users_core)
	{
		$this->users_core = &$users_core;
	}

	protected function generatePass($value)
	{
		$str = sha1('CRAFTEngine'.$value);
		return $str;
	}

	public function comparePass($id,$value)
	{
		list(,$value,$success) = $this->users_core->makeEvent('set','password',array($id,$value,true));
		if(!$success)return false;

		if($this->getProperty($id)==trim($this->generatePass($value)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'password'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD password VARCHAR(255)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'password'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD password VARCHAR(255)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT password FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['password'];
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = trim($value);
		list(,$value,$success) = $this->users_core->makeEvent('set','password',array($id,$value,true));
		if(!$success)return false;
		$value = $this->generatePass($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET password='$value' WHERE id='$id'");

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
		if($id==$idfrom)return true;
		else return false;
	}

	public function canSignup($value)
	{
		if(mb_strlen($value,'UTF-8')>$this->confs->modules->password['length']['max'])
		{
			$this->core->error->error('plugin_users_module_password',1);
			return false;
		}
		if(mb_strlen($value,'UTF-8')<$this->confs->modules->password['length']['min'])
		{
			$this->core->error->error('plugin_users_module_password',1);
			return false;
		}

		return true;
	}

	public function signup($id,$value)
	{
		$id = intval($id);
		list(,$value,$success) = $this->users_core->makeEvent('signup','password',array($id,$value,true));
		if(!$success)return false;
		$value = $this->generatePass($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET password='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT password FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $fr['password'];
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET password='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}

	public function canLogin($id)
	{
		return true;
	}
}