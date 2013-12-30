<?php
namespace CRAFTEngine\client\widgets\vk_group;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function render()
	{
		$this->core->render['SYS']['WIDGETS'][] = array('vk_group','main');
		$this->core->render['WIDGETS']['vk_group'] = array(
			'mode'=>1,
			'width'=>220,
			'height'=>400,
			'color1'=>'FFFFFF',
			'color2'=>'2B587A',
			'color3'=>'5B7FA6',
			'id'=>38551210,
		);
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