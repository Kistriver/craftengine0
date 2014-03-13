<?php
namespace CRAFTEngine\plugins\users;
class name implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'name'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD name VARCHAR(255)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'name'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD name VARCHAR(255)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT name FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['name'];
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = trim($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET name='$value' WHERE id='$id'");

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
		if($id==$idfrom)
			return true;
		else return false;
	}

	public function canSetProperty($id,$idfrom)
	{
		if($id==$idfrom)return true;
		else return false;
	}

	public function canSignup($value)
	{
		$value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');

		if(mb_strlen($value,'UTF-8')>$this->confs->modules->name['length']['max'])
		{
			$this->core->error->error('plugin_users_module_name',1);
			return false;
		}
		if(mb_strlen($value,'UTF-8')<$this->confs->modules->name['length']['min'])
		{
			$this->core->error->error('plugin_users_module_name',1);
			return false;
		}

		return true;
	}

	public function signup($id,$value)
	{
		$id = intval($id);
		$value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET name='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT name FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $this->core->sanString($fr['name']);
		$qr = $this->core->mysql->query("UPDATE users SET name='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}

	public function canLogin($id)
	{
		return true;
	}
}