<?php
namespace CRAFTEngine\plugins\users;
class rank implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM users LIKE 'rank'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE users ADD rank VARCHAR(2047)");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT rank FROM users WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return explode(':',$fr['rank']);
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = implode(':',$value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE users SET rank='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		if(in_array($value,$this->confs->modules->rank['list']))
		{
			if($id!==null)
			{
				if(!in_array($value,$this->getProperty($id)))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	public function canGetProperty($id,$idfrom)
	{
		return true;
	}

	public function canSetProperty($id,$idfrom)
	{
		if(in_array($_SERVER['REMOTE_ADDR'],$this->core->conf->system->core->admin_ip))
		{
			return true;
		}

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
		$qr = $this->setProperty($idnew,array('user'));

		if($qr)return true;
		else return false;
	}

	public function canLogin($id)
	{
		return true;
	}
}