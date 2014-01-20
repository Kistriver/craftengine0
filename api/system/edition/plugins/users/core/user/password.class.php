<?php
namespace CRAFTEngine\plugins\users;
class password implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
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
		$qr = $this->core->mysql->query("SELECT password FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['password'];
	}

	public function setProperty($id,$value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET password='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($id,$value)
	{
		$value = $this->core->sanString($value);
		return true;
	}

	public function canGetProperty($id,$idfrom)
	{
		return false;
	}

	public function canSignup($value)
	{
		return true;
	}

	public function signup($id,$value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET password='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$qr = $this->core->mysql->query("SELECT password FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $this->core->sanString($fr['password']);
		$qr = $this->core->mysql->query("UPDATE users SET password='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}
}