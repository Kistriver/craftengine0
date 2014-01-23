<?php
namespace CRAFTEngine\plugins\users;
class password_salt implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
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
		$id = intval($id);
		$value = $this->generatePass($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET password_salt='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
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