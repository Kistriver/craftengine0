<?php
namespace CRAFTEngine\api\users;
class user extends \CRAFTEngine\core\api
{
	private $user_core;

	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['get']='get';
		$this->functions['search']='search';
		$this->functions['list']='usersList';

		$this->user_core = $this->core->plugin->initPl('users','core');
		$this->user = $this->core->plugin->initPl('users','user');
	}

	protected function get()
	{
		if(isset($this->data['id']))
		{
			$id = $this->data['id'];
			$status = $this->user->getProperties($id,$this->user_core->currentUser());
			if(sizeof($status)==0)return array(false);

			$status['id'] = intval($id);
			return $status;
		}
		else
		{
			$modes = $this->core->conf->plugins->users->user->get_user_mode;
			$id = null;
			$type = null;
			$ids = 0;

			foreach($this->data as $k=>$v)
			{
				if(in_array($k,$modes))
				{
					$id = $v;
					$type = $k;
					$ids++;
				}
			}

			if($ids!=1)
			{
				return array(false);
			}

			if(!isset($this->user->$type))
			{
				return array(false);
			}

			$id = $this->user->$type->getPropertyByValue($id);

			if($id===false)
			{
				return array(false);
			}

			$status = $this->user->getProperties($id,$this->user_core->currentUser());
			if(sizeof($status)==0)return array(false);

			$status['id'] = intval($id);

			return $status;
		}
	}

	protected function search()
	{
		$this->niy();
	}

	protected function usersList()
	{
		$this->input('page');
		$pp = 10;
		$page = intval($this->data['page']);
		if($page<1)return array(false);
		$offset = $pp*($page-1);

		$qr = $this->core->mysql->query("SELECT id FROM users LIMIT $offset,$pp");
		if($this->core->mysql->rows($qr)==0)return array(false);


		$u = $this->core->plugin->initPl('users','user');
		$return = array();
		for($i=0;$i<$pp;$i++)
		{
			$fr = $this->core->mysql->fetch($qr);

			$res = $u->getProperties($fr['id'],$this->user_core->currentUser());
			if(sizeof($res)!=0)$return[] = $res;
		}

		return $return;
	}
}
?>