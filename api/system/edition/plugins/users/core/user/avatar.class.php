<?php
namespace CRAFTEngine\plugins\users;
class avatar implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'avatar'")
		)!=0)
			return true;

		$qr = $this->core->mysql->query("ALTER TABLE users ADD avatar VARCHAR(255)");

		if($qr)return true;
		else return false;
	}

	public function getProperty($id)
	{
		$qr = $this->core->mysql->query("SELECT avatar FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['avatar'];
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		return false;
		/*$id = intval($id);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET avatar='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;*/
	}

	public function validateProperty($value,$id=null)
	{
		$id = intval($id);
		//$value = $this->core->sanString($value);
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

	public function canLogin($id)
	{
		return true;
	}
}