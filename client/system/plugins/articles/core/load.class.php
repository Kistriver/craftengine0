<?php
namespace CRAFTEngine\client\plugins\articles;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function rules()
	{
		$this->core->plugins->newRule(array('preg'=>'^articles$','page'=>'articles.php','get'=>array('act'=>'posts','page'=>'1'),'plugin'=>'articles'));
		$this->core->plugins->newRule(array('preg'=>'^articles/page-([0-9]*)$','page'=>'articles.php','get'=>array('act'=>'posts','page'=>'$1'),'plugin'=>'articles'));
		$this->core->plugins->newRule(array('preg'=>'^articles/([0-9]*)$','page'=>'articles.php','get'=>array('act'=>'post','id'=>'$1'),'plugin'=>'articles'));
		$this->core->plugins->newRule(array('preg'=>'^articles/([a-z]*)$','page'=>'articles.php','get'=>array('act'=>'$1'),'plugin'=>'articles'));
		$this->core->plugins->newRule(array('preg'=>'^articles/edit/([0-9]*)$','page'=>'articles.php','get'=>array('act'=>'edit','id'=>'$1'),'plugin'=>'articles'));
	}

	public function  RegisterPluginEvent($id,$plugin,$info)
	{
		switch($id.'_'.$plugin)
		{
			case 'render_widget_menu':
					if((new \CRAFTEngine\client\plugins\users\load($this->core))->loggedin())$info[] = array('Написать статью','articles/new');
					$info[] = array('Новости','articles');
				break;
		}
		return $info;
	}
}