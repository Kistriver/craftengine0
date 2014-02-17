<?php
namespace CRAFTEngine\plugins\articles;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function OnEnable()
	{
		$art = $this->core->plugin->initPl('articles','article');

		foreach($art->getPropertiesList() as $p)
		{
			$art->$p->install();
		}
	}

	public function OnDisable()
	{

	}

	public function registerPluginEvent($id,$plugin,$addInfo)
	{
		return $addInfo;
	}
}