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
		if($_SESSION['loggedin'])
		{
		//AND IF YOU HAVE PERMISSIONS
		return $this->json(array(
								'all'=>$this->core->plugin->pluginsExist,
								'included'=>$this->core->plugin->pluginsIncluded,
								'loaded'=>$this->core->plugin->pluginsLoaded,
								));
		}
	}
	
	protected function on_plugin()
	{
		if($_SESSION['loggedin'])
		{
			$plugin = $this->core->sanString($this->data['name']);
			
			$this->core->plugin->on($plugin);
		}
	}
	
	protected function off_plugin()
	{
		if($_SESSION['loggedin'])
		{
			$plugin = $this->core->sanString($this->data['name']);
			
			$this->core->plugin->off($plugin);
		}
	}
}