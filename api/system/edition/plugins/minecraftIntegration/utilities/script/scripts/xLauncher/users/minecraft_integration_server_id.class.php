<?php
namespace CRAFTEngine\plugins\users;
class minecraft_integration_server_id implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'minecraft_integration_server_id'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD minecraft_integration_server_id VARCHAR(255)");
			if(!$qr)return false;
		}
		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT minecraft_integration_server_id FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['minecraft_integration_server_id'];
	}

	public function getPropertyByValue($value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("SELECT id FROM users WHERE minecraft_integration_server_id='$value'");

		if($this->core->mysql->rows($qr)!=1)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['id'];
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = trim($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET minecraft_integration_server_id='$value' WHERE id='$id'");

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
		return true;
	}

	public function register($id,$idnew)
	{
		return true;
	}
}