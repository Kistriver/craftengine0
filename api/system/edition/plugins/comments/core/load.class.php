<?php
namespace CRAFTEngine\plugins\comments;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function OnEnable()
	{

	}

	public function OnDisable()
	{

	}

	public function registerPluginEvent($id,$plugin,$addInfo)
	{
		switch($id.'_'.$plugin)
		{

		}

		return $addInfo;
	}
}
?>