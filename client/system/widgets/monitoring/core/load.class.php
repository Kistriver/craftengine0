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

		//При желании можно прикрутить любой монитроинг, прописав его код в main.twig виджета, в секцию "other"
		$this->core->render['WIDGETS']['monitoring']['type'] = 'radial1';//linear1, linear2, radial1, other
		$this->core->render['WIDGETS']['monitoring']['list'] = array(
			'example'=>array('name'=>'My Server #1'),
			'example2'=>array('name'=>'My Server #2')
		);
	}
}