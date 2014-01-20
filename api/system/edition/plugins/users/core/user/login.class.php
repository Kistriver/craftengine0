<?php
namespace CRAFTEngine\plugins\users;
class login implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
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
		$qr = $this->core->mysql->query("SELECT login FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['login'];
	}

	public function setProperty($id,$value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET login='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($id,$value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("SELECT login FROM users WHERE login='$value'");

		if($this->core->mysql->rows($qr)==0)return true;
		else return false;
	}

	public function canGetProperty($id,$idfrom)
	{
		return true;
	}

	public function canSignup($value)
	{
		$value = $this->core->sanString($value);

		$qr = $this->core->mysql->query("SELECT login FROM users WHERE login='$value'");
		$qr2 = $this->core->mysql->query("SELECT login FROM users_signup WHERE login='$value'");

		if($this->core->mysql->rows($qr)!=0 || $this->core->mysql->rows($qr2)!=0)return false;
		else return true;
	}

	public function signup($id,$value)
	{
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
		$qr = $this->core->mysql->query("UPDATE users SET login='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}
}