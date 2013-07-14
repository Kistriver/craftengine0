<?php
class api_plugin extends api
{
	public function init()
	{
		#$this->functions['act']='function';
		$this->functions['list']='list_of_plugins';
	}
	
	protected function list_of_plugins()
	{
		if($_SESSION['loggedin'])
		//AND IF YOU HAVE PERMISSIONS
		return $this->json(array(
								'all'=>$this->core->plugin->pluginsExist,
								'included'=>$this->core->plugin->pluginsIncluded,
								'loaded'=>$this->core->plugin->pluginsLoaded,
								));
	}
}