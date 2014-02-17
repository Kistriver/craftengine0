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
		$st = $art->postsList($this->data);
		if($st!==false)
			return array(true,$st);
		else return array(false);
	}

	//Получение одной статьи, установка просмотра++
	protected function post()
	{
		$this->input('id');
		$id = (int)$this->core->SanString($this->data['post_id']);
		$userid = (int)$this->core->SanString($this->data['user_id']);
		$res = $this->core->mysql->query("SELECT * FROM articles WHERE id='$id' and user='$userid'");
		if($this->core->mysql->rows($res))
		{
			$results = $this->core->mysql->fetch($res);

			$user = $this->core->plugin->initPl('user','user');//new user($this->core);
			$user->get_user($results['user']);
			$results['userid'] = $results['user'];
			$results['user'] = $user->login;
			//$results['article'] = bbcode($results['article']);
			//$results['time'] = date('d ', $results['time']) . month_name_rus(date('m', $results['time']), 'р') . date(' Y в H:i', $results['time']);

			if(!isset($_SESSION['tmp']['art'][$id]['times']))
			{
					$this->core->mysql->query("UPDATE articles SET times = $results[times]+1 WHERE id='$id' and user='$userid'");
					$_SESSION['tmp']['art'][$id]['times'] = time();
					$results['times']++;
			}

			$status = $this->art_status($results['status']);

			$rank = $this->core->plugin->initPl('user','rank');//new rank($this->core);
			$results['edited'] = $rank->init($_SESSION['id'], 'edit_art')? 1:0;
			$results['deleted'] = $rank->init($_SESSION['id'], 'delete_art')? 1:0;

			if($status=='publish')
			$post = array(
									'author_id'		=>	$results['userid'],
									'author_login'	=>	$results['user'],
									'post_id'		=>	$results['id'],
									'post_views'	=>	$results['times'],
									'post_time'		=>	$results['time'],
									'edit'			=>	$results['edited'],
									'delete'		=>	$results['deleted'],
									'title'			=>	$results['title'],
									'article'		=>	$results['article'],
									'status'		=>	$status,
									'tags'			=>	explode(',',$results['tags']),
			);
			else
			$post = array(
									'post_id'		=>	$results['id'],
									'post_time'		=>	$results['time'],
									'status'		=>	$status,
			);
		}
		else
		{
			$this->core->error->error('server',404);
			return (array(false));
		}

		return ($post);
	}

	//Добавление новой статьи во временную таблицу
	protected function newPost()
	{
		$art = $this->core->plugin->initPl('articles','core');
		$st = $art->newPost($this->data);
		if($st)
		return array(true,$st);
		else return array(false);
	}

	//Редактирование статьи в основной таблице
	protected function editPost()
	{
		$art = $this->core->plugin->initPl('articles','core');
		$st = $art->editPost($this->data);
		return array($st);
	}
}
?>