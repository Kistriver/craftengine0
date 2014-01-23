<?php
namespace CRAFTEngine\api\users;
class profile extends \CRAFTEngine\core\api
{
	public function init()
	{
		#$this->functions['act']='function';
		$this->functions['change']='change_name';

		$this->user_core = $this->core->plugin->initPl('users','core');
	}

	protected function change_name()
	{
		if($this->user_core->currentUser()==0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}


		$this->input('type','value');

		switch($this->data['type'])
		{
			case 'name':
				break;

			case 'surname':
				break;

			case 'nickname':
				return array(false);


				$nick = $this->core->sanString($this->data['value']);

				$u = $this->core->plugin->initPl('users','user');

				$s = $u->nickname->setProperty($this->user_core->currentUser(),$nick);

				if($s!==true)
				{
					return array(false);
				}
				else
				{
					return array(true);
				}
				break;

			case 'pass':
				$this->input('value_old');

				$pass = $this->core->sanString($this->data['value']);
				$pass_old = $this->core->sanString($this->data['value_old']);

				$u = $this->core->plugin->initPl('users','user');

				$s = $u->password->comparePass($this->user_core->currentUser(),$pass_old);

				if($s!==true)
				{
					return array(false);
				}
				else
				{
					if($u->password->setProperty($this->user_core->currentUser(),$pass))
					return array(true);
					else
					return array(false);
				}
				break;
			
			case 'icon':
				/*$this->input('format');

				$time = time();
				$hash = sha1(microtime(true));
				$ip = $_SERVER['REMOTE_ADDR'];
				$type = 'users_user_avatar';
				$user = $_SESSION['id'];
				$format = $this->data['format'];
				$params = $this->core->sanString($this->core->functions->json(array(
					'name'=>'users/avatars/id'.$user,
					'formats'=>array('png','jpg','jpeg','bmp'),
				)),'mysql');
				
				if(!$this->core->mysql->query("INSERT INTO system_uploads(hash,time,ip,type,params)
														VALUES('$hash','$time','$ip','$type','$params')"))
				return (array(false));

				return (array("http://".$this->core->conf->system->core->system_scripts[0].":".
				$this->core->conf->system->core->system_scripts[1].$this->core->conf->system->core->system_scripts[2].
				"system-scripts/"."upload.php?hash=$hash&time=$time&format=$format&type=$type&sid=".session_id()));*/
				break;
		}
	}
}
?>