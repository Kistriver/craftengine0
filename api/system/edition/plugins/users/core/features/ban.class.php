<?php
namespace CRAFTEngine\plugins\users\features;
class ban implements \CRAFTEngine\plugins\users\featureInterface
{
	public function __construct($core,$users_core)
	{
		$this->core = &$core;
		$this->users_core = &$users_core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	public function ban($p=array())
	{
		if(!$this->users_core->permission->canDo('users_ban_ban',null,array()))return false;

		if(!isset($p['id'],$p['time']))return false;

		$id = $p['id'];
		$delta = empty($p['delta'])?false:$p['delta'];
		$time = $delta?time() + $p['time']:$p['time'];

		return $this->users_core->user->ban->setProperty($id,array('type'=>'ban','time'=>$time));
	}

	public function unban($p=array())
	{
		if(!$this->users_core->permission->canDo('users_ban_unban',null,array()))return false;

		if(!isset($p['id']))return false;

		$id = $p['id'];
		return $this->users_core->user->ban->setProperty($id,array('type'=>'unban'));
	}

	public function deleteBan($p=array())
	{
		if(!isset($p['id']))return false;

		$id = $p['id'];
		return $this->users_core->user->ban->setProperty($id,array('type'=>'delete'));
	}

	public function isBanned($p=array())
	{
		$ans = $this->bans($p);
		if($ans===false)return false;
		elseif(is_array($ans))
		{
			if(sizeof($ans)==0)return false;
			else
			{
				foreach($ans as $a)
				{
					if(strtotime($a['time_unban'])>=time() && $a['active']==1)
					{
						return true;
					}
				}

				return false;
			}
		}
		else return false;
	}

	public function bans($p=array())
	{
		if(!isset($p['uid']))return false;

		$uid = $p['uid'];
		return $this->users_core->user->ban->getProperty(array('uid'=>$uid));
	}
}