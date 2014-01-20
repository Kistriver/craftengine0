<?php
namespace CRAFTEngine\plugins\users;
class surname implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'surname'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD surname VARCHAR(255)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'surname'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD surname VARCHAR(255)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$qr = $this->core->mysql->query("SELECT surname FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['surname'];
	}

	public function setProperty($id,$value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET surname='$value' WHERE id='$id'");

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
		return true;
	}

	public function canSignup($value)
	{
		return true;
	}

	public function signup($id,$value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET surname='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$qr = $this->core->mysql->query("SELECT surname FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $this->core->sanString($fr['surname']);
		$qr = $this->core->mysql->query("UPDATE users SET surname='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}
}