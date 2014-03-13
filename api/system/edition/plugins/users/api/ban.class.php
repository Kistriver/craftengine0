<?php
namespace CRAFTEngine\api\users;
class ban extends \CRAFTEngine\core\api
{
	private $users_core;

	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['ban']='ban';
		$this->functions['unban']='unban';
		$this->functions['bans']='bans';
		$this->functions['isBanned']='isBanned';

		$this->users_core = $this->core->plugin->initPl('users','core');
	}

	protected function ban()
	{
		if($this->users_core->user->currentUser()==0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$this->input('uid','time');
		$this->data['id'] = $this->data['uid'];
		unset($this->data['uid']);

		return $this->users_core->ban->ban($this->data);
	}

	protected function unban()
	{
		if($this->users_core->user->currentUser()==0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$this->input('uid');
		$this->data['id'] = $this->data['uid'];
		unset($this->data['uid']);

		return $this->users_core->ban->unban($this->data);
	}

	protected function bans()
	{
		$this->input('uid');

		return $this->users_core->ban->bans($this->data);
	}

	protected function isBanned()
	{
		$this->input('uid');

		return $this->users_core->ban->isBanned($this->data);
	}
}
?>