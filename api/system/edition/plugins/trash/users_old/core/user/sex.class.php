<?php
namespace CRAFTEngine\plugins\users;
class sex implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'sex'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD sex INT(1)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'sex'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD sex INT(1)");
			if(!$qr)return false;
		}
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT sex FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['sex'];
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = intval($value);
		$qr = $this->core->mysql->query("UPDATE users SET sex='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		if(in_array($value,array(0,1)))
		return true;
	}

	public function canGetProperty($id,$idfrom)
	{
		return true;
	}

	public function canSetProperty($id,$idfrom)
	{
		if($id==$idfrom)return true;
		else return false;
	}

	public function canSignup($value)
	{
		if(in_array($value,array(0,1)))
		return true;
	}

	public function signup($id,$value)
	{
		$id = intval($id);
		$value = intval($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET sex='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT sex FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = intval($fr['sex']);
		$qr = $this->core->mysql->query("UPDATE users SET sex='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}
}