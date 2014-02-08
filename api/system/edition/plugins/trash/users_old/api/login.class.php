<?php
namespace CRAFTEngine\api\users;
class login extends \CRAFTEngine\core\api
{
	private $user_core;

	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['login']='login';
		$this->functions['logout']='logout';
		$this->functions['restore']='restore';

		$this->user_core = $this->core->plugin->initPl('users','core');
	}
	
	protected function login()
	{
		if($this->user_core->currentUser()!=0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$status = $this->user_core->login($this->data);

		if($status)return array(true,$this->user_core->currentUser());
		else return array(false);
	}

	protected function logout()
	{
		if($this->user_core->currentUser()==0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$this->user_core->currentUser(0);
		return array(true);
	}

	protected function restore()
	{
		if($this->user_core->currentUser()!=0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$status = $this->user_core->restorePassword($this->data);

		if($status!==true && $status!==false)return array(true,$status);
		elseif($status===true)return array(true);
		else return array(false);
	}
}
?>