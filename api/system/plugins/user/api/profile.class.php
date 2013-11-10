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
		if(!$_SESSION['loggedin'])
		{
			$this->core->error->error('server',403);
			return $this->json(array(false));
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
			
			case 'icon':
				/*$name = time();
				if(file_put_contents(dirname(__FILE__).'/../../../../files/'.$name,$value))
				return $this->json(array($name));
				else*/
				
				$time = time();
				$hash = sha1(microtime(true));
				$ip = $_SERVER['REMOTE_ADDR'];
				$type = 1;
				
				if(!$this->core->mysql->query("INSERT INTO upload_sid(hash,time,ip,type) 
														VALUES('$hash','$time','$ip','$type')"))
				$this->json(array(false));
				
				$this->json(array("http://".$this->core->conf->system->core->system_scripts[0].":".
				$this->core->conf->system->core->system_scripts[1].$this->core->conf->system->core->system_scripts[2].
				"system-scripts/"."upload.php?hash=$hash&time=$time"));
				break;
		}
	}
}
?>