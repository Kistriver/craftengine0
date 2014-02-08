<?php
namespace CRAFTEngine\api\users;
class signup extends \CRAFTEngine\core\api
{
	private $user_core;

	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['signup']='signup';
		$this->functions['check']='check';
		$this->functions['activate']='activate';

		$this->user_core = $this->core->plugin->initPl('users','core');
	}
	
	protected function signup()
	{
		$status = $this->user_core->signup($this->data);

		if($status)return array(true);
		else return array(false);
	}

	protected function check()
	{
		$status = $this->user_core->signupValuesCheck($this->data);

		if($status)return array(true);
		else return array(false);
	}

	protected function activate()
	{
		$this->input('id','type');

		$data = array();
		$data['id'] = $this->data['id'];

		switch($this->data['type'])
		{
			case 'admin':
				$data['add_mode'] = \CRAFTEngine\plugins\users\SIGNUP_ADMIN;
				break;

			case 'mail':
				$this->input('code');
				$data['add_mode'] = \CRAFTEngine\plugins\users\SIGNUP_MAIL;
				$data['code'] = $this->data['code'];
				break;

			case 'invite':
				$data['add_mode'] = \CRAFTEngine\plugins\users\SIGNUP_INVITE;
				break;

			default:
				return array(false);
				break;
		}
		$status = $this->user_core->signupActivate($data);

		if($status)return array(true);
		else return array(false);
	}
}
?>