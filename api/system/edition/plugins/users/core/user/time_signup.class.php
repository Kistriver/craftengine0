<?php
namespace CRAFTEngine\plugins\users;
class time_signup implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'time_signup'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD time_signup DATETIME");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'time_signup'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD time_signup DATETIME");
			if(!$qr)return false;
		}
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT time_signup FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['time_signup'];
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET time_signup='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		return true;
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
		return true;
	}

	public function signup($id,$value)
	{
		$id = intval($id);
		$value = date('Y-m-d H:i:s');
		$qr = $this->core->mysql->query("UPDATE users_signup SET time_signup='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT time_signup FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $this->core->sanString($fr['time_signup']);
		$qr = $this->core->mysql->query("UPDATE users SET time_signup='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}

	public function canLogin($id)
	{
		return true;
	}
}