<?php
class api_plugin extends api
{
	public function init()
	{
		#$this->functions['act']='function';
		$this->functions['list']='list_of_plugins';
		$this->functions['on']='on_plugin';
		$this->functions['off']='off_plugin';
	}
	
	protected function list_of_plugins()
	{
		foreach($this->core->plugin->pluginsIncluded as $f=>$c)
		if($c->name=='user')
		if(!$_SESSION['loggedin'] AND true==false)
		{
			$ex = 1;
			return $this->json(array(false));
		}
		
		if(!isset($ex))
		if($_SERVER['REMOTE_ADDR']!='192.168.1.1' AND true==false)
		{
			return $this->json(array(false));
		}
		
		//AND IF YOU HAVE PERMISSIONS
		return $this->json(array(
								'all'=>$this->core->plugin->pluginsExist,
								'included'=>$this->core->plugin->pluginsIncluded,
								'loaded'=>$this->core->plugin->pluginsLoaded,
								));
	}
	
	protected function on_plugin()
	{
		$this->input('name');
		foreach($this->core->plugin->pluginsIncluded as $f=>$c)
		if($c->name=='user')
		if(!$_SESSION['loggedin'] AND true==false)
		{
			return $this->json(array(false));
		}
		else
		if($_SERVER['REMOTE_ADDR']!='192.168.1.1' AND true==false)
		{
			return $this->json(array(false));
		}
		
		$plugin = $this->core->sanString($this->data['name']);
		
		$this->core->plugin->on($plugin);
	}
	
	protected function off_plugin()
	{
		foreach($this->core->plugin->pluginsIncluded as $f=>$c)
		if($c->name=='user')
		if(!$_SESSION['loggedin'] AND true==false)
		{
			return $this->json(array(false));
		}
		else
		if($_SERVER['REMOTE_ADDR']!='192.168.1.1' AND true==false)
		{
			return $this->json(array(false));
		}
		
		$plugin = $this->core->sanString($this->data['name']);
		
		$this->core->plugin->off($plugin);
	}
}