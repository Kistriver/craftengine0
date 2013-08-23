<?php
class api_catalog extends api
{
	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['list']='listPl';
	}
	
	protected function listPl()
	{
		$main = $this->core->plugin->initPl('pluginsCatalog','main');
		$main->listPl(0,1);
	}
}
?>