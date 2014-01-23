<?php
namespace CRAFTEngine\plugins\users;
class about implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'about'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD about VARCHAR(5000)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'about'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD about VARCHAR(5000)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT about FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['about'];
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
		$qr = $this->core->mysql->query("UPDATE users SET about='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
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
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET about='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT about FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $this->core->sanString($fr['about']);
		$qr = $this->core->mysql->query("UPDATE users SET about='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}
}