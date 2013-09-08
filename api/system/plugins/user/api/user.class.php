<?php
class api_user extends api
{
	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['list']='list_users';
		$this->functions['get']='user';
		$this->functions['confirm']='confirm_new_user';
		$this->functions['loggedin']='loggedin';
	}
	
	protected function loggedin()
	{
		if($_SESSION['loggedin'])
		{
			$ses = array(
				true,
				'nickname' => $_SESSION['nickname'],
				//'salt' => $_SESSION['salt'],
				//'pass' => $_SESSION['pass'],
				'email' => $_SESSION['email'],
				'id' => $_SESSION['id'],
				'login' => $_SESSION['login'],
				'rank' => $_SESSION['rank'],
				'rank_main' => $_SESSION['rank_main'],
				'auth' => $_SESSION['auth'],
			);
			
			return $this->json($ses);
		}
		else
		{
			if(!empty($this->data['auth']))
			{
				$au = $this->core->SanString($this->data['auth']);
				list($id, $sid) = explode(':',$au);
				
				$m = $this->core->mysql->query("SELECT * FROM login_sid WHERE sid='$sid' AND id='$id'");
				if($this->core->mysql->rows($m)==1)
				{
					$u = $this->core->plugin->initPl('user','user');
					$_SESSION['auth_time'] = time();
					$u->set_user($id,'id');
					$this->core->mysql->query("INSERT INTO login_sid(id,time,sid) VALUES('$id','$_SESSION[auth_time]','$_SESSION[auth]')");
					$this->core->mysql->query("DELETE FROM login_sid WHERE sid='$sid' AND id='$id'");
					
					$ses = array(
					true,
					'nickname' => $_SESSION['nickname'],
					//'salt' => $_SESSION['salt'],
					//'pass' => $_SESSION['pass'],
					'email' => $_SESSION['email'],
					'id' => $_SESSION['id'],
					'login' => $_SESSION['login'],
					'rank' => $_SESSION['rank'],
					'rank_main' => $_SESSION['rank_main'],
					'auth' => $_SESSION['auth'],
					);
					
					return $this->json($ses);
				}
				return $this->json(array(false));
			}
			else
			
			return $this->json(array(false));
		}
	}
	
	protected function list_users()
	{
		$this->input('page');
		
		$page = (int)$this->data['page'];
		$type = !empty($this->data['type'])?$this->core->sanString($this->data['type']):'default';
		$limit = 10;
		
		if($type=='signup')
		{
			$rank = $this->core->plugin->initPl('user','rank');//new rank($this->core);
			if(!$rank->init($_SESSION['id'], 'user_confirm_new'))
			{
				$this->core->error->error('server', 403);
				return $this->json(array(false));
			}
			
			$users_num = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(*) FROM signup"));
		}
		else
		{
			$users_num = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(*) FROM users"));
		}
		
		$pages = ceil($users_num[0]/$limit);
		
		if($page<1)$page=1;
		
		if($page<1 or ($page>$pages and $pages!=0))
		{
			$this->core->error->error('server', 404);
			return $this->json(array(false));
		}
		
		$offset = ($page-1) * $limit;
		if($type=='signup')
		$users_list = $this->core->mysql->query("SELECT * FROM signup LIMIT $offset, $limit");
		else
		$users_list = $this->core->mysql->query("SELECT id FROM users LIMIT $offset, $limit");
		
		if($type=='signup')
		{
			$users = array();
			for($i=0;$i<$this->core->mysql->rows($users_list);$i++)
			{
				$r = $this->core->mysql->fetch($users_list);
				
				$inf['name'] = $r['name'];
				$inf['surname'] = $r['surname'];
				$inf['login'] = $r['login'];
				$inf['bd'] = array($r['day'],$r['month'],$r['year']);
				$inf['email'] = $r['email'];
				$inf['sex'] = $r['sex'];
				$inf['invite'] = $r['invite'];
				$inf['about'] = $r['about'];
				$inf['status'] = $r['status'];
				
				$users[] = $inf;
			}
			
			return $this->json($users);
		}
		else
		{
			$user = $this->core->plugin->initPl('user','user');//new user($this->core);
			
			$users = array();
			for($i=0;$i<$this->core->mysql->rows($users_list);$i++)
			{
				$id = $this->core->mysql->fetch($users_list);
				
				$user->get_user($id[0], 'id');
				$arinf['login'] = $user->login;
				$arinf['nickname'] = $user->nickname;
				$arinf['id'] = $user->id;
				$arinf['rank'] = $user->rank;
				$arinf['rank_main'] = $user->rank_main;
				$arinf['bd'] = $user->birthday;
				$arinf['sex'] = $user->sex;
				$users[] = $arinf;
			}
			
			return $this->json($users);
		}
	}
	
	protected function user()
	{
		$this->input('type','value');
		
		$type = $this->core->sanString($this->data['type']);
		$value = $this->core->sanString($this->data['value']);
		
		$user = $this->core->plugin->initPl('user','user');//new user($this->core);
		
		$err = 1;
		switch($type)
		{
			case 'login':
				$is = $user->get_user($value,'login');
				if($is)$err = 0;
				break;
			case 'id':
				$is = $user->get_user($value,'id');
				if($is)$err = 0;
				break;
			default:
				//FIXME: $this->core->error->error(/*unexpected type*/);
				$this->core->error->error('server', 403);
				return $this->json(array(false));
				break;
		}
		
		if($err == 1)
		{
			$this->core->error->error('server', 404);
			return $this->json(array(false));
		}
		
		$u['login'] = $user->login;
		$u['nickname'] = $user->nickname;
		$u['id'] = $user->id;
		$u['rank'] = $user->rank;
		$u['rank_main'] = $user->rank_main;
		$u['bd'] = $user->birthday;
		$u['sex'] = $user->sex;
		$u['last_login'] = $user->time_login;
		
		return $this->json($u);
	}
	
	protected function confirm_new_user()
	{
		$this->input('login','confirm');
		
		$rank = $this->core->plugin->initPl('user','rank');//new rank($this->core);
		if(!$rank->init($_SESSION['id'], 'user_confirm_new'))
		{
			$this->core->error->error('server', 403);
			return $this->json(array(false));
		}
		
		$login = $this->core->sanString($this->data['login']);
		$confirm = $this->core->sanString($this->data['confirm']);
		
		if($confirm===true)
		{
			$q = $this->core->mysql->query("SELECT * FROM signup WHERE login='$login'");
			if($this->core->mysql->rows($q)!=1)
			{
				$this->core->error->error('server', 404);//replace
				return $this->json(array(false));
			}
			
			$r = $this->core->mysql->fetch($q);
			
			$status = $r['status'];
			$signid = (int)$r['id'];
			if($status==0 OR $status==2)
			{
				$this->core->mysql->query("UPDATE signup SET status='2' WHERE id='$signid' AND login='$login'");
				//return $this->json(array(true));
			}
			elseif($status==1 OR $status==3)
			{
				$this->core->mysql->query("UPDATE signup SET status='3' WHERE id='$signid' AND login='$login'");
				$u = $this->core->plugin->initPl('user','user');//new user($this->core);
				$u->new_user(
					$r['name'],
					$r['surname'],
					$r['email'],
					$r['password'],
					$r['login'],
					$r['sex'],
					$r['day'],
					$r['month'],
					$r['year'],
					null,
					null,
					$r['invite'],
					$r['time'],
					$r['about']
				);
				//return $this->json(array(true));
			}
			
			$q = $this->core->mysql->query("SELECT * FROM users WHERE login='$login'");
			if($this->core->mysql->rows($q)!=1 AND ($status!=0 AND $status!=2))
			{
				$this->core->error->error('server', 404);//replace
				return $this->json(array(false));
			}
			
			$id = $this->core->mysql->fetch($q);
			$id = (int)$id['id'];
			$editor = (int)$_SESSION['id'];
			$time = time();
			$type = 1;//added
			
			if($status==0 OR $status==1)
			{
				$this->core->mysql->query("INSERT INTO users_history(editor,user,type,time,data)
															VALUES('$editor','$id','$type','$time',null)");
			}
			
			if($status==1 OR $status==3)
			$this->core->mysql->query("DELETE FROM signup WHERE login='$login'");
			
			//NOT WORK//$this->core->mail->add_waiting_list($r['email'], '004', array($r['login'],true));
			return $this->json(array(true));
		}
		elseif($confirm===false)
		{
			$q = $this->core->mysql->query("SELECT * FROM signup WHERE login='$login'");
			if($this->core->mysql->rows($q)!=1)
			{
				$this->core->error->error('server', 404);//replace
				return $this->json(array(false));
			}
			
			$r = $this->core->mysql->fetch($q);
			$editor = $_SESSION['id'];
			$time = time();
			$type = 2;//removed
			$data = $this->core->json_encode_ru($r);
			$data = $this->core->SanString($data, 'mysql');
			$this->core->mysql->query("INSERT INTO users_history(editor,user,type,time,data)
														VALUES('$editor','$r[id]','$type','$time','$data')");
			$this->core->mysql->query("DELETE FROM signup WHERE login='$login'");
			
			//$this->core->mail->add_waiting_list($r['email'], 'reg_confirm', array($r['login'],false));
			
			return $this->json(array(true));
		}
		elseif($confirm==='mail')
		{
			$q = $this->core->mysql->query("SELECT * FROM signup WHERE login='$login'");
			if($this->core->mysql->rows($q)!=1)
			{
				$this->core->error->error('server', 404);//replace
				return $this->json(array(false));
			}

			$r = $this->core->mysql->fetch($q);

			$user = $this->core->plugin->initPl('user','user');
			$code = $user->generate_code('signup',array('login'=>$r['login'],'id'=>$r['id']),false);


			$this->core->mail->add_waiting_list($r['email'], '002', array('login'=>$r['login'], 'id'=>$r['id'], 'code'=>$code));
		}
		else
		{
			$this->core->error->error('server', 404);//replace
			return $this->json(array(false));
		}
	}
}