<?php
class api_profile extends api
{
	public function init()
	{
	   #$this->functions['act']='function';
		//$this->functions['change_name']='change';
		$this->functions['change']='change_name';
	}

	protected function change_name()
	{
		$this->input('type','value');

		switch($this->data['type'])
		{
			case 'name':
				break;

			case 'surname':
				break;

			case 'nickname':
				$nick = $this->core->sanString($this->data['value']);

				$u = $this->core->plugin->initPl('user','user');

				$s = $u->change_user($_SESSION['id'], $nick , 'nickname');

				if($s!==true)
				{
					$this->json(array(false));
				}
				else
				{
					$this->json(array(true));
				}
				break;

			case 'pass':
				$this->input('value_old');

				$pass = $this->core->sanString($this->data['value']);
				$pass_old = $this->core->sanString($this->data['value_old']);

				$u = $this->core->plugin->initPl('user','user');

				$s = $u->change_user($_SESSION['id'], array($pass, $pass, $pass_old) , 'password');

				if($s!==true)
				{
					$this->json(array(false));
				}
				else
				{
					$this->json(array(true));
				}
				break;
		}
	}
}
?>