<?php
class api_user extends api
{
	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['list']='list_users';
		$this->functions['get']='user';
		$this->functions['confirm']='confirm_new_user';
	}
	
	protected function list_users()
	{
		$this->input('page');
		
		$page = (int)$this->data['page'];
		$type = !empty($this->data['type'])?$this->core->sanString($this->data['type']):'default';
		$limit = 10;
		
		if($type=='signup')
		{
			$this->core->plugin('rank');
			$rank = new rank($this->core);
			if(!$rank->init($_SESSION['id'], 'user_confirm_new'))
			{
				$this->core->error->error('server', '403');
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
			$this->core->error->error('server', '404');
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
				
				$users[] = $inf;
			}
			
			return $this->json($users);
		}
		else
		{
			$this->core->plugin('user');
			$user = new user($this->core);
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
		
		$this->core->plugin('user');
		$user = new user($this->core);
		
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
				//$this->core->error->error(/*unexpected type*/);
				$this->core->error->error('server', '403');
				return $this->json(array(false));
				break;
		}
		
		if($err == 1)
		{
			$this->core->error->error('server', '404');
			return $this->json(array(false));
		}
		
		$u['login'] = $user->login;
		$u['nickname'] = $user->nickname;
		$u['id'] = $user->id;
		$u['rank'] = $user->rank;
		$u['rank_main'] = $user->rank_main;
		$u['bd'] = $user->birthday;
		$u['sex'] = $user->sex;
		
		return $this->json($u);
	}
	
	protected function confirm_new_user()
	{
		$this->input('login','confirm');
		
		$this->core->plugin('rank');
		$rank = new rank($this->core);
		if(!$rank->init($_SESSION['id'], 'user_confirm_new'))
		{
			$this->core->error->error('server', '403');
			return $this->json(array(false));
		}
		
		$login = $this->core->sanString($this->data['login']);
		$confirm = $this->core->sanString($this->data['confirm']);
		
		if($confirm==true)
		{
			$q = $this->core->mysql->query("SELECT * FROM signup WHERE login='$login'");
			if($this->core->mysql->rows($q)!=1)
			{
				$this->core->error->error('server', '404');//replace
				return $this->json(array(false));
			}
			
			$r = $this->core->mysql->fetch($q);
			
			$this->core->plugin('user');
			$u = new user($this->core);
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
			
			$q = $this->core->mysql->query("SELECT * FROM users WHERE login='$login'");
			if($this->core->mysql->rows($q)!=1)
			{
				$this->core->error->error('server', '404');//replace
				return $this->json(array(false));
			}
			
			$id = $this->core->mysql->fetch($q);
			$id = (int)$id['id'];
			$editor = (int)$_SESSION['id'];
			$time = time();
			$type = 1;//added
			$this->core->mysql->query("INSERT INTO users_history(editor,user,type,time,data)
														VALUES('$editor','$id','$type','$time',null)");
			$this->core->mysql->query("DELETE FROM signup WHERE login='$login'");
			
			$this->core->mail->add_waiting_list($r['email'], 'reg_confirm', array($r['login'],true));
			
			return $this->json(array(true));
		}
		elseif($confirm==false)
		{
			$q = $this->core->mysql->query("SELECT * FROM signup WHERE login='$login'");
			if($this->core->mysql->rows($q)!=1)
			{
				$this->core->error->error('server', '404');//replace
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
			
			$this->core->mail->add_waiting_list($r['email'], 'reg_confirm', array($r['login'],false));
			
			return $this->json(array(true));
		}
		else
		{
			$this->core->error->error('server', '404');//replace
			return $this->json(array(false));
		}
	}
}