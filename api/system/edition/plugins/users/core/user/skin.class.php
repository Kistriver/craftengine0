<?php
namespace CRAFTEngine\plugins\users;
class skin implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'skin'")
		)!=0)
			return true;

		$qr = $this->core->mysql->query("ALTER TABLE users ADD skin VARCHAR(255)");

		if($qr)return true;
		else return false;
	}

	public function getProperty($id)
	{
		$qr = $this->core->mysql->query("SELECT skin FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['skin'];
	}

	public function setProperty($id,$value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET skin='$value' WHERE id='$id'");

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
		return true;
	}

	public function register($id,$idnew)
	{
		return true;
	}
}