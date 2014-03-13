<?php
namespace CRAFTEngine\plugins\users;
class ban implements userInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function construct($users_core)
	{
		$this->users_core = &$users_core;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW TABLES LIKE 'users_bans'")
		)==0)
		{
			$qr = $this->core->mysql->query("
			CREATE TABLE IF NOT EXISTS `users_bans` (
			`id` int(8) NOT NULL AUTO_INCREMENT,
			`uid` int(8) NOT NULL,
			`uid_banner` int(8) NOT NULL,
			`active` int(1) NOT NULL,
			`time_ban` datetime,
			`time_unban` datetime,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			");
			if(!$qr)return false;
		}

		return true;
	}

	public function getProperty($p)
	{
		if(is_array($p))
		{
			if(isset($p['id']))
			{
				$id = intval($p['id']);
				$kid = 'id';
			}
			elseif(isset($p['uid']))
			{
				$id = intval($p['uid']);
				$kid = 'uid';
			}
			else
			{
				return false;
			}
		}
		else
		{
			$id = intval($p);
			$kid = 'uid';
		}

		$qr = $this->core->mysql->query("SELECT * FROM users_bans WHERE $kid='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		for($i=0;$i<$this->core->mysql->rows($qr);$i++)
		{
			$fre = $this->core->mysql->fetch($qr);
			$fr[] = array(
				'id'=>$fre['id'],
				'uid'=>$fre['uid'],
				'uid_banner'=>$fre['uid_banner'],
				'active'=>$fre['active'],
				'time_ban'=>$fre['time_ban'],
				'time_unban'=>$fre['time_unban'],
			);
		}

		return $fr;
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($uid,$p)
	{
		$uid = intval($uid);

		switch($p['type'])
		{
			case 'ban':
				$uid_banner = $this->users_core->user->currentUser();
				$active = 1;
				$time_ban = date('Y-m-d H:i:s');
				$time_unban = date('Y-m-d H:i:s',$p['time']);

				$qr = $this->core->mysql->query("INSERT INTO users_bans(uid,uid_banner,active,time_ban,time_unban)
																VALUE('$uid','$uid_banner','$active','$time_ban','$time_unban')");

				if($qr)return true;
				else return false;
				break;

			case 'unban':
			case 'delete':
				$time = date('Y-m-d H:i:s');

				$addInfo = array();

				if(isset($p['bid']))
				$addInfo[] = 'id=\''.intval($p['bid']).'\'';

				if(sizeof($addInfo)!=0)
				$add = implode(' AND ',$addInfo);
				else $add = '';

				$qt = $p['type']=='unban'?"UPDATE users_bans SET active='0'":"DELETE FROM users_bans";
				$qr = $this->core->mysql->query($qt." WHERE uid='$uid' AND active>'0' AND time_unban>'$time'"
				.$add);

				if($qr)return true;
				else return false;
				break;

			default:
				return false;
				break;
		}
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
		return $this->validateProperty($value);
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
		if($this->users_core->ban->isBanned(array('uid'=>$id)))
		{
			$this->core->error->error('plugin_users_module_ban',0);
			return false;
		}
		else
		{
			return true;
		}
	}
}