<?php
namespace CRAFTEngine\client\widgets\menu;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function render()
	{
		$this->core->render['SYS']['WIDGETS'][] = array('menu','main');

		$menu = $this->core->widgets->makeEvent('render','menu',array( array('Главная','') ));

		if(sizeof($menu)!=0)
			foreach($menu as &$m)
			{
				if($m[1]==$this->core->uri)
				{
					$m[2] = true;
				}
				else
				{
					list(,$m[2]) = $this->core->widgets->makeEvent('render_selected','menu',array($m[1],false));
				}
			}

		$this->core->render['WIDGETS']['menu'] = $menu;
	}

	public function OnEnable()
	{

	}

	public function OnDisable()
	{

	}
}