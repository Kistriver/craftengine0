<?php
namespace CRAFTEngine\plugins\articles;
class load
{
	public function __construct($core)
	{
		$this->core = &$core;

		//$art = $this->core->plugin->initPl('articles','core');
		//$st = $art->get->post(array('id'=>'4'));
		//var_dump($st);
	}

	public function OnEnable()
	{
		$art = $this->core->plugin->initPl('articles','core');

		foreach($art->article->getPropertiesList() as $p)
		{
			$art->article->$p->install();
		}
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