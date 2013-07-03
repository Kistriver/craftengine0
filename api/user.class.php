<?php
class api_user extends api
{
	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['list']='list_users';
		$this->functions['get']='user';
	}
	
	protected function list_users()
	{
		$this->input('page');
		
		$page = (int)$this->data['page'];
		$limit = 10;
		
		$users_num = $this->core->mysql->fetch($this->core->mysql->query("SELECT COUNT(*) FROM users"));
		
		$pages = ceil($users_num[0]/$limit);
		
		if($page<1 or $page>$pages)
		{
			$this->core->error->error('server', '404');
			return $this->json(array(false));
		}
		
		$offset = ($page-1) * $limit;
		$users_list = $this->core->mysql->query("SELECT id FROM users LIMIT $offset, $limit");
		
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
}