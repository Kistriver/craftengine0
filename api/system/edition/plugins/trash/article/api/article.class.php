<?php
namespace CRAFTEngine\api\article;
class article extends \CRAFTEngine\core\api
{
	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['posts']='posts';
		$this->functions['post']='post';
		$this->functions['new']='new_post';
		$this->functions['confirm_new']='confirm_new_post';
		$this->functions['edit']='edit_post';
		$this->functions['status']='status';
	}
	
	//Получение одной страницы статей
	protected function posts()
	{
		$this->input('page');
		$page = (int)$this->core->SanString($this->data['page']);
		$type = isset($this->data['type'])?$this->core->SanString($this->data['type']):'default';
		$limit = 10;
		$time = time();
		
		$rank = $this->core->plugin->initPl('user','rank');//new rank($this->core);
		$r = $rank->init($_SESSION['id'], 'article_show_unpublished');
		
		if(!$r and $type=='unpublished'){$this->core->error->error('server',403);return (array(false));}
		$access = 0;
		if($r)$access++;
		if($type=='unpublished')$access++;

		if($access==2)
		$num = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(id) FROM articles_new WHERE time<='$time' and status='1'"));
		else
		$num = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(id) FROM articles WHERE time<='$time' and status='1'"));
		$num = $num[0];
		$pages = ceil($num/$limit);
		if($pages<1)$pages=1;
		//if($page>$pages)$page=$pages;
		//if($page<1)$page=1;
		$cl=0;
		if($page>$pages){$cl=1;$this->core->error->error('server',404);}
		if($page<1){$cl=1;$this->core->error->error('server',404);}
		if($cl==1)return (array(false));

		$from_post = ($page - 1)*$limit;
		if($access==2)
		$result = $this->core->mysql->query("SELECT * FROM articles_new WHERE time<='$time' and status='1' ORDER BY time DESC LIMIT $from_post,$limit");
		else
		$result = $this->core->mysql->query("SELECT * FROM articles WHERE time<='$time' and status='1' ORDER BY time DESC LIMIT $from_post,$limit");
		$rows = $this->core->mysql->rows($result);

		$posts = array();
		for($i=0; $i<$rows; ++$i)
		{
			$results = $this->core->mysql->fetch($result);

			//$num_msg = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(id) FROM wall_art WHERE id='$results[id]' and status='0'"));
			//$results['num_msg'] = $num_msg[0];

			$user = $this->core->plugin->initPl('user','user');//new user($this->core);
			$user->get_user($results['user']);
			$results['userid'] = $results['user'];
			$results['user'] = $user->login;


			$results['edited'] = $rank->init($_SESSION['id'], 'article_edit')? 1:0;
			$results['deleted'] = $rank->init($_SESSION['id'], 'article_delete')? 1:0;
			if($access==2)$results['times'] = 0;

			if($access==2)
			$status = $this->art_status($results['status'],'unpublished');
			else
			$status = $this->art_status($results['status']);

			$posts[] = array(
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
		}

		if(sizeof($posts)==0)$pages = 0;
		$returned = array('posts'=> $posts, 'pages'=>$pages);

		return ($returned);
	}

	//Получение одной статьи, установка просмотра++
	protected function post()
	{
		$this->input('post_id','user_id');
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
	protected function new_post()
	{
		if($_SESSION['loggedin'])
		{
			$rank = $this->core->plugin->initPl('user','rank');//new rank($this->core);
			if($rank->init($_SESSION['id'], 'article_write_new'))
			{
				if((isset($this->data['title']) AND isset($this->data['article'])) AND (!empty($this->data['title']) AND !empty($this->data['article'])))
				{
					$this->input('title','article','tags');
					$title = $this->core->SanString($this->data['title']);
					$body = $this->core->SanString($this->data['article']);
					$tags = $this->core->SanString($this->data['tags']);
					$user = $_SESSION['id'];
					$time = time();
					//$date = $this->core->SanString($this->data['date']);
					if(!empty($date))
					{
						$d = $this->art_date($date);
						if($d!=false)
						{
							$time = $date;
						}
						else
						{
							$this->core->error->error('plugin_article_article',0);
							return (array(false));
						}
					}

					if(mb_strlen($title, 'UTF-8')<=64 OR mb_strlen($body, 'UTF-8')<=5000)
					{
						$tags = explode(',', $tags);
						//$tags = explode(';', $tags);
						for($i=0;$i<sizeof($tags);$i++)$tags[$i] = trim($tags[$i]);
						$tags = implode(',', $tags);

						$this->core->mysql->query("INSERT INTO articles_new(user, title, article, time, tags, status) VALUES('$user', '$title', '$body', '$time', '$tags', '1')");

						$r = $this->core->mysql->query("SELECT * FROM articles_new WHERE user='$user' and title='$title' and time='$time'");
						if($this->core->mysql->rows($r)!=1)
						{
							$this->core->error->error('plugin_article_article',1);
							return (array(false));
						}
						$r = $this->core->mysql->fetch($r);

						$user = $this->core->plugin->initPl('user','user');//new user($this->core);
						$user->get_user($r['user']);
						$r['userid'] = $r['user'];
						$r['user'] = $user->login;

						//$r = $this->core->mysql->fetch($r);
						$post = array(
									'author_login'	=>	$r['user'],
									'author_id'		=>	$r['userid'],
									'post_id'		=>	$r['id'],
									'post_time'		=>	$r['time'],
									'title'			=>	$r['title'],
									'article'		=>	$r['article'],
									'tags'			=>	explode(',',$r['tags']),
						);
						return ($post);
					}
					else
					{
						$this->core->error->error('plugin_article_article',2);
					}
				}
				else
				{
					$this->core->error->error('plugin_article_article',3);
				}
			}
			else
			{
				$this->core->error->error('server',403);
			}
		}
		else
		{
			$this->core->error->error('server',403);
		}
		return (array(false));
	}

	//Подтверждение или удаление статьи во временной таблице
	protected function confirm_new_post()
	{
		$this->input('id','confirm');
		if($_SESSION['loggedin'])
		{
			$rank = $this->core->plugin->initPl('user','rank');//new rank($this->core);
			if($rank->init($_SESSION['id'], 'article_confirm_new'))
			{
				$userid = $_SESSION['id'];
				$art_id = $this->core->SanString($this->data['id']);
				$confirm = $this->core->SanString($this->data['confirm']);
				$time = time();

				$res = $this->core->mysql->query("SELECT * FROM articles_new WHERE id='$art_id'");
				if($this->core->mysql->rows($res)==1)
				{
					$art = $this->core->mysql->fetch($res);
					if($art['status']==1)
					{
						if($confirm)
						{
							$type = 1;//Adding into main article table

							foreach($art as &$i)
							$i = $this->core->SanString($i, 'mysql');

							$this->core->mysql->query("INSERT INTO articles(user, title, article, time, tags, times, status) VALUES('$art[user]', '$art[title]', '$art[article]', '$art[time]', '$art[tags]', '0', '1')");
							$this->core->mysql->query("UPDATE articles_new SET status='2' WHERE id='$art_id'");
							$this->core->mysql->query("SELECT * FROM articles WHERE time='$art[time]' and user='$art[user]' and title='$art[title]'");
							$art_new = $this->core->mysql->fetch();
							$this->core->mysql->query("INSERT INTO articles_history(editor, time, type, article) VALUES('$userid', '$time', '$type', '$art_new[id]')");
							return (array('status'=>'added','id'=>$art_new['id']));
						}
						else
						{
							$type = 2;//Delete article

							$this->core->mysql->query("UPDATE articles_new SET status='0' WHERE id='$art_id'");
							$this->core->mysql->query("INSERT INTO articles_history(editor, time, type, article) VALUES('$userid', '$time', '$type', '$art_id')");
							return (array('status'=>'deleted'));
						}
					}
					else
					{
						$this->core->error->error('server',404);
					}
				}
				else
				{
					$this->core->error->error('server',404);
				}
			}
			else
			{
				$this->core->error->error('server',403);
			}
		}
		else
		{
			$this->core->error->error('server',403);
		}
		return array();
	}

	//Редактирование статьи в основной таблице
	protected function edit_post()
	{
		if($_SESSION['loggedin'])
		{
			$rank = $this->core->plugin->initPl('user','rank');//new rank($this->core);

			$user = $_SESSION['id'];
			$post_id = (int)$this->core->SanString($this->data['id']);
			$art_own = $this->core->mysql->fetch($this->core->mysql->query("SELECT * FROM articles WHERE id='$post_id'"));
			if($rank->init($_SESSION['id'], 'article_edit') or $art_own['user']==$user)
			{
				if((isset($this->data['title']) AND isset($this->data['article'])) AND (!empty($this->data['title']) AND !empty($this->data['article'])))
				{
					$title = $this->core->SanString($this->data['title']);
					$body = $this->core->SanString($this->data['article']);
					$tags = $this->core->SanString($this->data['tags']);
					$time = time();
					$date = !empty($this->data['date'])?$this->core->SanString($this->data['date']):null;
					if(!empty($date))
					{
						$d = $this->art_date($date);
						if($d!=false)
						{
							$time = $date;
						}
						else
						{
							$this->core->error->error('plugin_article_article',0);
							return;
						}
					}

					if(mb_strlen($title, 'UTF-8')<=64 OR mb_strlen($body, 'UTF-8')<=5000)
					{
						$art_is = $this->core->mysql->query("SELECT * FROM articles WHERE id='$post_id'");
						if($this->core->mysql->rows($art_is)!=1)
						{
							$this->core->error->error('server',403);
							return array();
						}

						$this->core->mysql->query("UPDATE articles SET title='$title', article='$body', time='$time', tags='$tags' WHERE id='$post_id'");

						$r = $this->core->mysql->query("SELECT * FROM articles WHERE user='$user' and title='$title' and time='$time'");
						if($this->core->mysql->rows($r)!=1)
						{
							$this->core->error->error('plugin_article_article',1);
							return array();
						}

						$type = 3;
						$art_is = $this->core->mysql->fetch($art_is);
						$art = $this->core->json_encode_ru(array('title'=>$art_is['title'],'art'=>$art_is['article'],'time'=>$art_is['time'],'tags'=>$art_is['tags']));
						$art = $this->core->SanString($art, 'mysql');

						$time_now = time();
						$this->core->mysql->query("INSERT INTO articles_history(editor, data, time, type, article) VALUES('$user', '$art', '$time_now', '$type', '$post_id')");

						$r = $this->core->mysql->fetch($r);
						$user = $this->core->plugin->initPl('user','user');//new user($this->core);
						$user->get_user($r['user']);
						$r['userid'] = $r['user'];
						$r['user'] = $user->login;

						//$r = $this->core->mysql->fetch($r);
						$post = array(
									'author_login'	=>	$r['user'],
									'author_id'		=>	$r['userid'],
									'post_id'		=>	$r['id'],
									'post_time'		=>	$r['time'],
									'title'			=>	$r['title'],
									'article'		=>	$r['article'],
									'tags'			=>	explode(',',$r['tags']),
						);
						return ($post);
					}
					else
					{
						$this->core->error->error('plugin_article_article',2);
					}
				}
				else
				{
					$this->core->error->error('plugin_article_article',3);
				}
			}
			else
			{
				$this->core->error->error('server',403);
			}
		}
		else
		{
			$this->core->error->error('server',403);
		}

		$post = array(false);
		return ($post);
	}

	//Изменение статуса статьи в основной таблице
	protected function status()
	{
		if($_SESSION['loggedin'])
		{
			$rank = $this->core->plugin->initPl('user','rank');//new rank();
			$target = (int)sanString($this->data['target']);
			$type = sanString($this->data['type']);

			$r = mysql_num_rows(queryMysql("SELECT id FROM articles WHERE id='$target'"));

			if($r==1)
			{
				switch($type)
				{
					case 'draft':
					if($rank->init($_SESSION['id'], 'draft_art'))
					{
						queryMysql("UPDATE articles SET checked='0' WHERE id='$target'");
					}
					else
					{
						$this->error('server',403);
					}
					break;

					case 'publish':
					if($rank->init($_SESSION['id'], 'publish_art'))
					{
						queryMysql("UPDATE articles SET checked='1' WHERE id='$target'");
					}
					else
					{
						$this->error('server',403);
					}
					break;

					case 'delete':
					if($rank->init($_SESSION['id'], 'delete_art'))
					{
						queryMysql("UPDATE articles SET checked='2' WHERE id='$target'");
					}
					else
					{
						$this->error('server',403);
					}
					break;
				}
			}
		}
		else
		{
			$this->error('server',403);
		}

		$status = array(
		);
		return ($status);
	}
	
	//Получение статуса статьи(для методов)
	protected function art_status($state,$type='default')
	{
		switch($type)
		{
			case 'default':
				switch($state)
				{
					case 0:
						$status = 'draft';
						break;
					case 1:
						$status = 'publish';
						break;
					case 2:
						$status = 'delete';
						break;
				}
				break;
			case 'unpublished':
				switch($state)
				{
					case 0:
						$status = 'disallow';
						break;
					case 1:
						$status = 'vote';
						break;
					case 2:
						$status = 'allow';
						break;
				}
				break;
		}
		
		return $status;
	}
	
	//Преобразование даты(для методов)
	protected function art_date($date)
	{
		if(!preg_match('(\d{2}).(\d{2}).(\d{2}) (\d{2}):(\d{2}).(\d{2})',$date))
		{
			$pattern = "'(\d{2}).(\d{2}).(\d{2}) (\d{2}):(\d{2}).(\d{2})'";
			$year = preg_replace($pattern,"$3",$date);
			$month = preg_replace($pattern,"$2",$date);
			$day = preg_replace($pattern,"$1",$date);
			$hour = preg_replace($pattern,"$4",$date);
			$minute = preg_replace($pattern,"$5",$date);
			$second = preg_replace($pattern,"$6",$date);
			if($month<1 or $month>12 or 
			$day<1 or $day>31 or
			$hour<0 or $hour>23 or
			$minute<0 or $minute>59 or
			$second<0 or $second>59
			)
			{
				return false;
			}
			else
			{
				$date = mktime($hour,$minute,$second,$month,$day,$year,-1);
				return $date;
			}
		}
		else
		{
			return false;
		}
	}
}
?>