<?php
namespace CRAFTEngine\plugins\users;
class invited implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = $this->core->conf->plugins->users;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'invited'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD invited INT(8)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'invited'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD invited INT(8)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT invited FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['invited'];
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET invited='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("SELECT id FROM users WHERE id='$value'");

		if($this->core->mysql->rows($qr)==0)return true;
		else return false;
	}

	public function canGetProperty($id,$idfrom)
	{
		if($id==$idfrom)
			return true;
		else return false;
	}

	public function canSetProperty($id,$idfrom)
	{
		return false;
	}

	public function canSignup($value)
	{
		$value = trim($value);
		if($value===null || strlen($value)==0)return true;

		$value = $this->core->sanString($value);

		$qr = $this->core->mysql->query("SELECT id FROM users WHERE id='$value'");

		if($this->core->mysql->rows($qr)==0)
		{
			$this->core->error->error('plugin_users_module_invited',0);
			return false;
		}
		else return true;
	}

	public function signup($id,$value)
	{
		$id = intval($id);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET invited='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT invited FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $fr['invited'];
		//$value = $this->core->sanString($value);
		$st = $this->setProperty($idnew,$value);

		if($st)return true;
		else return false;
	}

	public function canLogin($id)
	{
		return true;
	}
}