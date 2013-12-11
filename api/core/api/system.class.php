<?php
class api_system extends api
{
	public function init()
	{
		#$this->functions['act']='function';
		$this->functions['getEditConfs']='getEditConfs';
		$this->functions['setEditConfs']='setEditConfs';

		$this->functions['pluginList']='list_of_plugins';
		$this->functions['pluginOn']='on_plugin';
		$this->functions['pluginOff']='off_plugin';
	}

	protected function getEditConfs()
	{
		$this->input('plugin');
		$plugin = $this->core->sanString($this->data['plugin']);
		$c = $this->core->plugin->getEditConfs($plugin);

		if($c===false)return $this->json(array(false));

		return $this->json($c);
	}

	protected function setEditConfs()
	{
		$this->input('plugin','config');

		$plugin = $this->data['plugin'];
		$config = $this->data['config'];
		foreach($config as $f=>$v)
		if(preg_match("'\.\.'is",$f))return $this->json(array(false));

		$c = $this->core->plugin->setEditConfs($plugin,$config);

		if($c===false)return $this->json(array(false));

		//return $this->wip();
		return $this->json(array(true));
		return $this->json($c);
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

		return $this->json(array(true));
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

		return $this->json(array(true));
	}
}