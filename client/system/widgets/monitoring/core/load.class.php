<?php
namespace CRAFTEngine\client\widgets\monitoring;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function render()
	{
		$this->core->render['SYS']['WIDGETS'][] = array('monitoring','main');
	}

	public function OnEnable()
	{

	}

	public function OnDisable()
	{

	}

	public function registerWidgetEvent($id,$widget,$addInfo)
	{
		return $addInfo;
	}
}