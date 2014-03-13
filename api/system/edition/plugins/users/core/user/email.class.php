<?php
namespace CRAFTEngine\plugins\users;
class email implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'email'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD email VARCHAR(255)");
			if(!$qr)return false;
		}

		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users_signup LIKE 'email'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users_signup ADD email VARCHAR(255)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT email FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['email'];
	}

	public function getPropertyByValue($value)
	{
		$value = mb_convert_case($value, MB_CASE_LOWER, 'UTF-8');
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("SELECT id FROM users WHERE email='$value'");

		if($this->core->mysql->rows($qr)!=1)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['id'];
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = trim($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET email='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		$id = intval($id);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("SELECT email FROM users WHERE email='$value'");

		if($this->core->mysql->rows($qr)==0)return true;
		else return false;
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
		$value = mb_convert_case($value, MB_CASE_LOWER, 'UTF-8');

		if(!preg_match("'^([.a-z0-9_-]{4,60})@(.*?)$'",$value,$matched))
		{
			$this->core->error->error('plugin_users_module_email',1);
			return false;
		}

		$in_list = in_array($matched[2],$this->confs->modules->email['list']['list']);

		if($this->confs->modules->email['list']['enabled']==true)
		{
			if($this->confs->modules->email['list']['whitelist']==true)
			{
				if(!$in_list)
				{
					$this->core->error->error('plugin_users_module_email',2);
					return false;
				}
			}
			else
			{
				if($in_list)
				{
					$this->core->error->error('plugin_users_module_email',2);
					return false;
				}
			}
		}

		$value = $this->core->sanString($value);

		$qr = $this->core->mysql->query("SELECT email FROM users WHERE email='$value'");
		$qr2 = $this->core->mysql->query("SELECT email FROM users_signup WHERE email='$value'");

		if($this->core->mysql->rows($qr)!=0 || $this->core->mysql->rows($qr2)!=0)
		{
			$this->core->error->error('plugin_users_module_email',0);
			return false;
		}
		else return true;
	}

	public function signup($id,$value)
	{
		$id = intval($id);
		$value = mb_convert_case($value, MB_CASE_LOWER, 'UTF-8');
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users_signup SET email='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function register($id,$idnew)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT email FROM users_signup WHERE id='$id'");
		$fr = $this->core->mysql->fetch($qr);
		$value = $this->core->sanString($fr['email']);
		$qr = $this->core->mysql->query("UPDATE users SET email='$value' WHERE id='$idnew'");

		if($qr)return true;
		else return false;
	}

	public function canLogin($id)
	{
		return true;
	}
}