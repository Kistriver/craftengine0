<?php
class api_system extends api
{
	public function init()
	{
		#$this->functions['act']='function';
		$this->functions['getEditConfs']='getEditConfs';
		$this->functions['setEditConfs']='setEditConfs';
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
}