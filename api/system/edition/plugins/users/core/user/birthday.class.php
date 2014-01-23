<?php
namespace CRAFTEngine\plugins\users;
class birthday implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'birthday'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD birthday VARCHAR(255)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'birthday'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD birthday VARCHAR(255)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT birthday FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return sscanf($fr['birthday'],"%d-%d-%d");
	}

	public function getPropertyByValue($value)
	{
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("SELECT id FROM users WHERE birthday='$value'");

		if($this->core->mysql->rows($qr)!=1)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['id'];
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		if(!isset($value['year'],$value['month'],$value['day']))return false;
		$value = sprintf("%04d-%02d-%02d",$value['year'],$value['month'],$value['day']);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET birthday='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		if(!isset($value['year'],$value['month'],$value['day']))return false;
		if(checkdate($value['month'],$value['day'],$value['year']))return true;

		$this->core->error->error('plugin_users_module_birthday',0);

		return false;
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
		if(!isset($value['year'],$value['month'],$value['day']))return false;
		if(checkdate($value['month'],$value['day'],$value['year']))return true;

		$this->core->error->error('plugin_users_module_birthday',0);

		return false;
	}

	public function signup($id,$value)
	{
		$id = intval($id);
		$value = sprintf("%d-%d-%d",$value['year'],$value['month'],$value['day']);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET birthday='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT birthday FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $this->core->sanString($fr['birthday']);
		$qr = $this->core->mysql->query("UPDATE users SET birthday='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}
}