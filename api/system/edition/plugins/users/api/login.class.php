<?php
namespace CRAFTEngine\api\users;
class login extends \CRAFTEngine\core\api
{
	private $users_core;

	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['login']='login';
		$this->functions['logout']='logout';
		$this->functions['restore']='restore';

		$this->users_core = $this->core->plugin->initPl('users','core');
	}
	
	protected function login()
	{
		if($this->users_core->user->currentUser()!=0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$status = $this->users_core->login->login($this->data);

		if($status)return array(true,$this->users_core->user->currentUser());
		else return array(false);
	}

	protected function logout()
	{
		if($this->users_core->user->currentUser()==0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$this->users_core->user->currentUser(0);
		return array(true);
	}

	protected function restore()
	{
		if($this->users_core->user->currentUser()!=0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$status = $this->users_core->login->restorePassword($this->data);

		if($status!==true && $status!==false)return array(true,$status);
		elseif($status===true)return array(true);
		else return array(false);
	}
}
?>