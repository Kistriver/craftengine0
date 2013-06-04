<?php
class api_article extends api
{
	public function init()
	{
	   #$this->functions['function']='act';
		$this->functions['posts']='posts';
		$this->functions['post']='post';
		$this->functions['new_post']='new';
		$this->functions['edit_post']='edit';
		$this->functions['status']='status';
	}
	
	protected function posts()
	{
		$page=(int)$this->core->SanString($this->data['page']);
		$limit = 10;
		$time = time();
		
		$this->core->plugin('rank');
		$rank = new rank($this->core);
		$r = $rank->init($_SESSION['id'], 'article_show_unpublished');
		
		if($r)
		$num = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(id) FROM articles"));
		else
		$num = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(id) FROM articles WHERE time<='$time' and status='1'"));
		$num = $num[0];
		$pages = ceil($num/$limit);
		if($pages<1)$pages=1;
		//if($page>$pages)$page=$pages;
		//if($page<1)$page=1;
		$cl=0;
		if($page>$pages){$cl=1;$this->core->error->error('server','404');}
		if($page<1){$cl=1;$this->core->error->error('server','404');}
		if($cl==1)return $this->json();
		
		$from_post = ($page - 1)*$limit;
		if($r)
		$result = $this->core->mysql->query("SELECT * FROM articles ORDER BY time DESC LIMIT $from_post,$limit");
		else
		$result = $this->core->mysql->query("SELECT * FROM articles WHERE time<='$time' and status='1' ORDER BY time DESC LIMIT $from_post,$limit");
		$rows = $this->core->mysql->rows($result);
		
		$posts = array();
		for($i=0; $i<$rows; ++$i)
		{
			$results = $this->core->mysql->fetch($result);
			
			//$num_msg = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(id) FROM wall_art WHERE id='$results[id]' and status='0'"));
			//$results['num_msg'] = $num_msg[0];
			
			$this->core->plugin('user');
			$user = new user($this->core);
			$user->get_user($results['user']);
			$results['userid'] = $results['user'];
			$results['user'] = $user->login;
			
			
			$results['edited'] = $rank->init($_SESSION['id'], 'article_edit')? 1:0;
			$results['deleted'] = $rank->init($_SESSION['id'], 'article_delete')? 1:0;
			
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
		
		return $this->json($returned);
	}
        
	protected function post()
	{
		$id = (int)$this->core->SanString($this->data['post_id']);
		$userid = (int)$this->core->SanString($this->data['user_id']);
		$res = $this->core->mysql->query("SELECT * FROM articles WHERE id='$id' and user='$userid'");
		if($this->core->mysql->rows($res))
		{
			$results = $this->core->mysql->fetch($res);

			$this->core->plugin('user');
			$user = new user($this->core);
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

			$this->core->plugin('rank');
			$rank = new rank($this->core);
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
				$this->core->error->error('server','404');
		}
		
		return $this->json($post);
	}
	
	protected function new_post()
	{
		if($_SESSION['loggedin'])
		{
			$this->core->plugin('rank');
			$rank = new rank($this->core);
			if($rank->init($_SESSION['id'], 'article_write_new'))
			{
				if((isset($this->data['title']) AND isset($this->data['article'])) AND (!empty($this->data['title']) AND !empty($this->data['article'])))
				{
					$title = $this->core->SanString($this->data['title']);
					$body = $this->core->SanString($this->data['article']);
					$tags = $this->core->SanString($this->data['tags']);
					$user = $_SESSION['id'];
					$time = time();
					$date = $this->core->SanString($this->data['date']);
					if(!empty($date))
					{
						$d = $this->art_date($date);
						if($d!=false)
						{
							$time = $date;
						}
						else
						{
							$this->core->error->error('article','000');
							return;
						}
					}
					
					if(mb_strlen($title, 'UTF-8')<=64 OR mb_strlen($body, 'UTF-8')<=5000)
					{
						$tags = explode(',', $tags);
						//$tags = explode(';', $tags);
						for($i=0;$i<sizeof($tags);$i++)$tags[$i] = trim($tags[$i]);
						$tags = implode(',', $tags);
						
						$this->core->mysql->query("INSERT INTO articles_new(user, title, article, time, tags) VALUES('$user', '$title', '$body', '$time', '$tags')");
						
						$r = $this->core->mysql->query("SELECT * FROM articles_new WHERE user='$user' and title='$title' and time='$time'");
						if($this->core->mysql->rows($r)!=1)
						{
							$this->core->error->error('article','001');
							
						}
						
						$r = $this->core->mysql->fetch($r);
						$post = array(
									'author_id'		=>	$r['user'],
									'post_id'		=>	$r['id'],
									'post_time'		=>	$r['time'],
									'title'			=>	$r['title'],
									'article'		=>	$r['article'],
									'tags'			=>	explode(',',$r['tags']),
						);
						return $this->json($post);
					}
					else
					{
						$this->core->error->error('article','002');
					}
				}
				else
				{
					$this->core->error->error('article','003');
				}
			}
			else
			{
				$this->core->error->error('server','403');
			}
		}
		else
		{
			$this->core->error->error('server','403');
		}
		return $this->json();
	}
	
	protected function edit_post()
	{
		if($_SESSION['loggedin'])
		{
			$rank = new rank();
			if($rank->init($_SESSION['id'], 'edit_art'))
			{
				if((isset($this->data['title']) AND isset($this->data['body'])) AND (!empty($this->data['title']) AND !empty($this->data['body'])))
				{
					$title = sanString($this->data['title']);
					$body = sanString($this->data['body']);
					$user = $_SESSION['id'];
					$time = time();
					$date = sanString($this->data['date']);
					if(!empty($date))
					{
						$d = $this->art_date($date);
						if($d!=false)
						{
							$time = $date;
						}
						else
						{
							$this->error('articles','000');
							return;
						}
					}
					
					if(strlen($title)<=64 OR strlen($body)<=5000)
					{
						$post_id = (int)sanString($this->data['art']);
						queryMysql("UPDATE articles SET user_edit='$user', title='$title', article='$body', time='$time' WHERE id='$post_id'");
						
						$r = mysql_num_rows(queryMysql("SELECT * FROM articles WHERE title='$title' and time='$time' and id='$post_id'"));
						if($r!=1)$this->error('articles','001');
					}
					else
					{
						$this->error('articles','002');
					}
				}
				else
				{
					$this->error('articles','003');
				}
			}
			else
			{
				$this->error('server','403');
			}
		}
		else
		{
			$this->error('server','403');
		}
		
		$post = array(
		);
		return $this->json($post);
	}
	
	protected function status()
	{
		if($_SESSION['loggedin'])
		{
			$rank = new rank();
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
						$this->error('server','403');
					}
					break;
					
					case 'publish':
					if($rank->init($_SESSION['id'], 'publish_art'))
					{
						queryMysql("UPDATE articles SET checked='1' WHERE id='$target'");
					}
					else
					{
						$this->error('server','403');
					}
					break;
					
					case 'delete':
					if($rank->init($_SESSION['id'], 'delete_art'))
					{
						queryMysql("UPDATE articles SET checked='2' WHERE id='$target'");
					}
					else
					{
						$this->error('server','403');
					}
					break;
				}
			}
		}
		else
		{
			$this->error('server','403');
		}
		
		$status = array(
		);
		return $this->json($status);
	}

	protected function art_status($state)
	{
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
		
		return $status;
	}
	
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