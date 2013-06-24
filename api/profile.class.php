<?php
class api_profile extends api
{
	public function init()
	{
	   #$this->functions['function']='act';
		//$this->functions['change_name']='change';
		$this->functions['change']='change_name';
	}
	
	protected function change_name()
	{
		if($_SESSION['loggedin'])
		{
			$err_lev1 = 0;
			
			$user = new user();
			$user->get_user($_SESSION['id']);
			
			if(empty($this->data['value']))$this->data['value']='';
			
			switch($this->data['type'])
			{
				case 'name':
					if(strlen($this->data['value'])<$GLOBALS['_CONF']['server']['string_length']['name']['min'])
					{
						$this->error('profile','001');
						$err_lev1 = 1;
					}
					if(strlen($this->data['value'])>$GLOBALS['_CONF']['server']['string_length']['name']['max'])
					{
						$this->error('profile','002');
						$err_lev1 = 1;
					}
					//if($err_lev1 == 0)$user->change_user($_SESSION['id'], $this->data['value'], 'name');
					if($err_lev1 == 0)$this->error[] = 'Access denied';
					break;
					
				case 'surname':
					if(strlen($this->data['value'])<$GLOBALS['_CONF']['server']['string_length']['surname']['min'])
					{
						$this->error('profile','003');
						$err_lev1 = 1;
					}
					if(strlen($this->data['value'])>$GLOBALS['_CONF']['server']['string_length']['surname']['max'])
					{
						$this->error('profile','004');
						$err_lev1 = 1;
					}
					//if($err_lev1 == 0)$user->change_user($_SESSION['id'], $this->data['value'], 'surname');
					if($err_lev1 == 0)$this->error[] = 'Access denied';
					break;
					
				case 'nickname':
					if(strlen($this->data['value'])<$GLOBALS['_CONF']['server']['string_length']['nickname']['min'])
					{
						$this->error('profile','005');
						$err_lev1 = 1;
					}
					if(strlen($this->data['value'])>$GLOBALS['_CONF']['server']['string_length']['nickname']['max'])
					{
						$this->error('profile','006');
						$err_lev1 = 1;
					}
					if($err_lev1 == 0)$user->change_user($_SESSION['id'], $this->data['value'], 'nickname');
					break;
				
				default:
					$this->error('profile','000');
					break;
			}
		}
		else
		{
			$this->error('server','403');
		}
		
		$returned = array();
		
		return $this->json($returned);
	}
}
?>