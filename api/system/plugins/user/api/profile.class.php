<?php
namespace CRAFTEngine\api\user;
class profile extends \CRAFTEngine\core\api
{
	public function init()
	{
	   #$this->functions['act']='function';
		//$this->functions['change_name']='change';
		$this->functions['change']='change_name';
	}

	protected function change_name()
	{
		if(!$_SESSION['loggedin'])
		{
			$this->core->error->error('server',403);
			return (array(false));
		}


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
					return (array(false));
				}
				else
				{
					return (array(true));
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
					return (array(false));
				}
				else
				{
					return (array(true));
				}
				break;
			
			case 'icon':
				/*$name = time();
				if(file_put_contents(dirname(__FILE__).'/../../../../files/'.$name,$value))
				return (array($name));
				else*/

				$this->input('format');

				$time = time();
				$hash = sha1(microtime(true));
				$ip = $_SERVER['REMOTE_ADDR'];
				$type = 'user_user_avatar';
				$user = $_SESSION['id'];
				$format = $this->data['format'];
				$params = $this->core->sanString($this->core->functions->json(array(
					'name'=>'users/avatars/id'.$user,
					'formats'=>array('png','jpg','jpeg','bmp'),
				)),'mysql');
				
				if(!$this->core->mysql->query("INSERT INTO uploads(hash,time,ip,type,params)
														VALUES('$hash','$time','$ip','$type','$params')"))
				return (array(false));

				return (array("http://".$this->core->conf->system->core->system_scripts[0].":".
				$this->core->conf->system->core->system_scripts[1].$this->core->conf->system->core->system_scripts[2].
				"system-scripts/"."upload.php?hash=$hash&time=$time&format=$format&type=$type&sid=".session_id()));
				break;
		}
	}
}
?>