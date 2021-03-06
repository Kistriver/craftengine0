<?php
namespace CRAFTEngine\api;
class system extends \CRAFTEngine\core\api
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

	private function admin()
	{
		if(in_array($_SERVER['REMOTE_ADDR'],$this->core->conf->system->core->admin_ip))
		{
			return true;
		}
		else
		{
			$this->core->error->error('server', 403);
			return false;
		}
	}

	protected function getEditConfs()
	{
		if(!$this->admin())return (array(false));
		$this->input('plugin');
		$plugin = $this->core->sanString($this->data['plugin']);
		$c = $this->core->plugin->getEditConfs($plugin);

		if($c===false)return (array(false));

		return ($c);
	}

	protected function setEditConfs()
	{
		if(!$this->admin())return (array(false));
		$this->input('plugin','config');

		$plugin = $this->data['plugin'];
		$config = $this->data['config'];
		foreach($config as $f=>$v)
		if(preg_match("'\.\.'is",$f))return (array(false));

		$c = $this->core->plugin->setEditConfs($plugin,$config);

		if($c===false)return (array(false));

		//return $this->wip();
		return (array(true));
		return ($c);
	}

	protected function list_of_plugins()
	{
		if(!$this->admin())return (array(false));

		//AND IF YOU HAVE PERMISSIONS
		return (array(
			'all'=>$this->core->plugin->pluginsExist,
			'included'=>$this->core->plugin->pluginsIncluded,
			'loaded'=>$this->core->plugin->pluginsLoaded,
		));
	}

	protected function on_plugin()
	{
		if(!$this->admin())return (array(false));
		$this->input('name');

		$plugin = $this->core->sanString($this->data['name']);

		$this->core->plugin->on($plugin);

		return (array(true));
	}

	protected function off_plugin()
	{
		if(!$this->admin())return (array(false));

		$plugin = $this->core->sanString($this->data['name']);

		$this->core->plugin->off($plugin);

		return (array(true));
	}
}