<?php
namespace CRAFTEngine\client\plugins\articles;
$core->plugins->newRule(array('preg'=>'^articles$','page'=>'articles.php','get'=>array('act'=>'posts','page'=>'1'),'plugin'=>'articles'));
$core->plugins->newRule(array('preg'=>'^articles/page-([0-9]*)$','page'=>'articles.php','get'=>array('act'=>'posts','page'=>'$1'),'plugin'=>'articles'));
$core->plugins->newRule(array('preg'=>'^articles/([0-9]*)/([0-9]*)$','page'=>'articles.php','get'=>array('act'=>'post','user_id'=>'$1','post_id'=>'$2'),'plugin'=>'articles'));
$core->plugins->newRule(array('preg'=>array('^articles/confirm/page-([0-9]*)$','^admin/other/articles/confirm/page-([0-9]*)$'),'page'=>'articles.php','get'=>array('act'=>'confirm','page'=>'$1'),'plugin'=>'articles'));
$core->plugins->newRule(array('preg'=>'^articles/([a-z]*)$','page'=>'articles.php','get'=>array('act'=>'$1'),'plugin'=>'articles'));
$core->plugins->newRule(array('preg'=>'^articles/edit/([0-9]*)-([0-9]*)$','page'=>'articles.php','get'=>array('act'=>'edit','user_id'=>'$1','post_id'=>'$2'),'plugin'=>'articles'));

function RegisterPluginEvent($id,$plugin,$info)
{
	if($id=='admin_menu_render' && $plugin=='admin')
	{
		$info['other'][] = array('icon'=>'plus','value'=>'Подтверждение новых статей','href'=>'articles/confirm/page-1');
	}
	return $info;
}
?>