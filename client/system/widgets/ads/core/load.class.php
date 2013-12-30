<?php
namespace CRAFTEngine\client\widgets\ads;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function render()
	{
		$ads = array();
		$ads[] = 'Продам старые, проржавевшие жигули. Дорого...';
		$ads[] = 'Куплю однокомнатную квартиру на окраине Сыктывкара. Без гарантий оплаты...';
		$ads[] = 'Здесь могла бы быть Ваша реклама... Но её здесь нет и не будет)';
		$ads[] = 'Вы думали это последняя "рекламка"? Неа.';
		$ads[] = 'Здесь будет много рекламы. Реально много.';

		$this->core->render['SYS']['WIDGETS'][] = array('ads','main');
		$this->core->render['WIDGETS']['ads'] = $ads;
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