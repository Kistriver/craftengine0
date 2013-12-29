<?php
namespace CRAFTEngine\client\widgets\menu;
class menu
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

	public function registerWidgetEvent($id,$widget,$addInfo)
	{
		return $addInfo;
	}
}