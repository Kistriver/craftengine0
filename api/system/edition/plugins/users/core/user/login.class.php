<?php
namespace CRAFTEngine\plugins\users;
class login implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'login'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD login VARCHAR(255)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'login'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD login VARCHAR(255)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT login FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['login'];
	}

	public function getPropertyByValue($value)
	{
		$value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("SELECT id FROM users WHERE login='$value'");

		if($this->core->mysql->rows($qr)!=1)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['id'];
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = trim($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET login='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		$id = intval($id);
		$value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');

		if(mb_strlen($value,'UTF-8')>$this->confs->modules->login['length']['max'])
		{
			$this->core->error->error('plugin_users_module_login',1);
			return false;
		}
		if(mb_strlen($value,'UTF-8')<$this->confs->modules->login['length']['min'])
		{
			$this->core->error->error('plugin_users_module_login',1);
			return false;
		}

		if(!preg_match("'^([a-zA-Z0-9_]*)$'",$value))
		{
			$this->core->error->error('plugin_users_module_login',1);
			return false;
		}

		$qr = $this->core->mysql->query("SELECT login FROM users WHERE login='$value'");
		$qr2 = $this->core->mysql->query("SELECT login FROM users_signup WHERE login='$value'");

		if($this->core->mysql->rows($qr)!=0 || $this->core->mysql->rows($qr2)!=0)
		{
			$this->core->error->error('plugin_users_module_login',0);
			return false;
		}
		else return true;
	}

	public function canGetProperty($id,$idfrom)
	{
		return true;
	}

	public function canSetProperty($id,$idfrom)
	{
		return false;
	}

	public function canSignup($value)
	{
		return $this->validateProperty($value);
	}

	public function signup($id,$value)
	{
		$value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET login='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$qr = $this->core->mysql->query("SELECT login FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $this->core->sanString($fr['login']);
		$qr = $this->setProperty($idnew,$value);

		if($qr)return true;
		else return false;
	}

	public function canLogin($id)
	{
		return true;
	}
}