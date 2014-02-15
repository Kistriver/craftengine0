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

		$this->core->plugins->newRule(array('preg'=>'^admin/other/articles/import','page'=>'import.php','get'=>array(),'plugin'=>'articles'));
	}

	public function  RegisterPluginEvent($id,$plugin,$info)
	{
		switch($id.'_'.$plugin)
		{
			case 'admin_menu_render_admin':
				foreach($_SESSION['users']['rank'] as $r)
				{
					if(in_array($r,array('main_admin')))
						$info['other']['articles_import'] = array('icon'=>'arrow-right','value'=>'Импортирование статей','href'=>'articles/import');
				}
				break;

			case 'render_widget_menu':
					if((new \CRAFTEngine\client\plugins\users\load($this->core))->loggedin())$info[] = array('Написать статью','articles/new');
					$info[] = array('Новости','articles');
				break;
		}
		return $info;
	}
}