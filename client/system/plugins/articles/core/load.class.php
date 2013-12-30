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
		$this->core->plugins->newRule(array('preg'=>'^articles/([0-9]*)/([0-9]*)$','page'=>'articles.php','get'=>array('act'=>'post','user_id'=>'$1','post_id'=>'$2'),'plugin'=>'articles'));
		$this->core->plugins->newRule(array('preg'=>array('^articles/confirm/page-([0-9]*)$','^admin/other/articles/confirm/page-([0-9]*)$'),'page'=>'articles.php','get'=>array('act'=>'confirm','page'=>'$1'),'plugin'=>'articles'));
		$this->core->plugins->newRule(array('preg'=>'^articles/([a-z]*)$','page'=>'articles.php','get'=>array('act'=>'$1'),'plugin'=>'articles'));
		$this->core->plugins->newRule(array('preg'=>'^articles/edit/([0-9]*)-([0-9]*)$','page'=>'articles.php','get'=>array('act'=>'edit','user_id'=>'$1','post_id'=>'$2'),'plugin'=>'articles'));
	}

	public function  RegisterPluginEvent($id,$plugin,$info)
	{
		switch($id.'_'.$plugin)
		{
			case 'admin_menu_render_admin':
				if(in_array($_SESSION['rank_main'],array(1,2,3)))$info['other']['articles_confirm'] = array('icon'=>'plus','value'=>'Подтверждение новых статей','href'=>'articles/confirm/page-1');
				break;

			case 'admin_access_admin':
				if(preg_match("'^other/artilces/confirm/'i",$info[1]) && $_SESSION['rank_main']<5)
				{
					$info[0] = false;
				}
				break;

			case 'render_widget_menu':
					$info[] = array('Новости','articles');
				break;
		}
		return $info;
	}
}