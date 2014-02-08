<?php
namespace CRAFTEngine\api\articles;
class article extends \CRAFTEngine\core\api
{
	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['posts']='posts';
		$this->functions['post']='post';
		$this->functions['new']='newPost';
		$this->functions['edit']='editPost';
	}
	
	//Получение одной страницы статей
	protected function posts()
	{
		$art = $this->core->plugin->initPl('articles','core');
		$st = $art->get->posts($this->data);
		if($st!==false)
			return array(true,$st);
		else return array(false);
	}

	//Получение одной статьи, установка просмотра++
	protected function post()
	{
		$art = $this->core->plugin->initPl('articles','core');
		$st = $art->get->post($this->data);
		if($st!==false)
			return array(true,$st);
		else return array(false);
	}

	//Добавление новой статьи во временную таблицу
	protected function newPost()
	{
		$art = $this->core->plugin->initPl('articles','core');
		$st = $art->create->newPost($this->data);
		if($st)
		return array(true,$st);
		else return array(false);
	}

	//Редактирование статьи в основной таблице
	protected function editPost()
	{
		$art = $this->core->plugin->initPl('articles','core');
		$st = $art->modify->editPost($this->data);
		return array($st);
	}
}
?>