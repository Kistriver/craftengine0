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

	public function registerPluginEvent($id,$plugin,$addInfo,$staticInfo=null)
	{
		switch($id.'_'.$plugin)
		{
			case 'canDo_users':
				if(isset($staticInfo['act']))
				switch($staticInfo['act'])
				{
					case 'articles_edit':
						if(isset($staticInfo['property']))
						switch($staticInfo['property'])
						{
							case 'author':
								$addInfo = false;
								break;

							case 'publish_time':
								$addInfo = false;
								break;

							case 'body':
							case 'tags':
							case 'title':
								$addInfo = true;
								break;

							case 'views':
								$addInfo = false;
								break;
						}
						break;
				}
				break;
		}

		return $addInfo;
	}
}
?>