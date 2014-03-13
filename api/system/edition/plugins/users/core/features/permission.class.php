<?php
namespace CRAFTEngine\plugins\users\features;
class permission implements \CRAFTEngine\plugins\users\featureInterface
{
	public function __construct($core,$users_core)
	{
		$this->core = &$core;
		$this->users_core = &$users_core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	/**
	 * Can user with id(if isn't set, will get id of current user) do act
	 *
	 * @param $act
	 * @param $id
	 * @param array $p
	 *
	 * @return bool
	 */
	public function canDo($act,$id=null,$p=array())
	{
		$id = $id===null?$this->users_core->user->currentUser():intval($id);

		switch($act)
		{
			case 'users_signup_admin_confirm':
				//If rank module doesn't exist, ip filter
				if(!in_array('rank',$this->users_core->user->getPropertiesList()))
				{
					if($this->confs->user->admin_ip==true)
					{
						if(in_array($_SERVER['REMOTE_ADDR'],$this->core->conf->system->core->admin_ip))
						{
							if($this->users_core->signup->changeSignupMode($id,SIGNUP_ADMIN))
							{
								return true;
							}

							$this->core->error->error('server',500);
							return false;
						}
					}

					$this->core->error->error('server',403);
					return false;
				}
				else
				{
					$ranks = $this->users_core->user->rank->getProperty($this->users_core->user->currentUser());
					$can = false;


					foreach($ranks as $r)
						if(in_array($r,$this->confs->modules->rank['access']['signup_admin']))
						{
							$can = true;
							break;
						}

					if($can)
					{
						return true;
					}

					$this->core->error->error('server',403);
					return false;
				}
				break;

			case 'users_ban_ban':
				if(!in_array('rank',$this->users_core->user->getPropertiesList()))
				{
					if($this->confs->user->admin_ip==true)
					{
						if(in_array($_SERVER['REMOTE_ADDR'],$this->core->conf->system->core->admin_ip))
						{
							return true;
						}
						else
						{
							return false;
						}
					}

					$this->core->error->error('server',403);
					return false;
				}
				else
				{
					$ranks = $this->users_core->user->rank->getProperty($this->users_core->user->currentUser());
					$can = false;


					foreach($ranks as $r)
						if(in_array($r,$this->confs->modules->rank['access']['ban_ban']))
						{
							$can = true;
							break;
						}

					if($can)
					{
						return true;
					}

					$this->core->error->error('server',403);
					return false;
				}
				break;

			case 'users_ban_unban':
				if(!in_array('rank',$this->users_core->user->getPropertiesList()))
				{
					if($this->confs->user->admin_ip==true)
					{
						if(in_array($_SERVER['REMOTE_ADDR'],$this->core->conf->system->core->admin_ip))
						{
							return true;
						}
						else
						{
							return false;
						}
					}

					$this->core->error->error('server',403);
					return false;
				}
				else
				{
					$ranks = $this->users_core->user->rank->getProperty($this->users_core->user->currentUser());
					$can = false;


					foreach($ranks as $r)
						if(in_array($r,$this->confs->modules->rank['access']['ban_unban']))
						{
							$can = true;
							break;
						}

					if($can)
					{
						return true;
					}

					$this->core->error->error('server',403);
					return false;
				}
				break;

			default:
				$p['uid'] = $id;
				$p['act'] = $act;
				return $this->core->plugin->makeEvent('canDo','users',false,$p);
				break;
		}

		return false;
	}
}