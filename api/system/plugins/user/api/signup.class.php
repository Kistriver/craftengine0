<?php
class api_signup extends api
{
	public function init()
	{
		#$this->functions['act']='function';
		//$this->functions['check_j']='check';
		//$this->functions['signup']='signup';
		$this->functions['check']='check_j';
		$this->functions['signup']='signup';
	}
	
	//Проверка полей для API
	protected function check_j()
	{
		$this->input('type', 'value');
		$type = $this->core->sanString($this->data['type']);
		$value = $this->core->sanString($this->data['value']);
		$r = $this->check($type, $value);
		if($r)return $this->json(array(true));
		else return $this->json(array(false));
	}
	
	//Проверка полей для программы
	protected function check($type, $value)
	{
		$u = $this->core->plugin->initPl('user','user');
		
		switch($type)
		{
			case 'login':
				$login = strtolower($value);
				$login = ucfirst($login);
				
				/*$f = $this->core->file->get_line_array('blacklist/login');
				for($i=0;$i<sizeof($f);++$i)
				{
					if(strtolower($f[$i])==strtolower($login))
					//if(preg_match("/^$f[$i]\$/i", $login))
					{
						$this->core->error->error('plugin_user_signup',1);
						return false;
						//break;
					}
				}*/
				
				
				if(!preg_match($u->preg('login'), $login))
				{
					$this->core->error->error('plugin_user_signup',1);
					return false;
				}
				elseif
				(
						$this->core->mysql->rows($this->core->mysql->query("SELECT login FROM users WHERE login='$login'"))!=0
						OR
						$this->core->mysql->rows($this->core->mysql->query("SELECT login FROM signup WHERE login='$login'"))!=0
				)
				{
					$this->core->error->error('plugin_user_signup',4);
					return false;
					//break;
				}
				else
				{
					return true;
				}
				break;
			case 'name':
				if(!preg_match($u->preg('name'), $value))
				{
					$this->core->error->error('plugin_user_signup',10);
					return false;
				}
				return true;
				break;
			case 'surname':
				if(!preg_match($u->preg('surname'), $value))
				{
					$this->core->error->error('plugin_user_signup',11);
					return false;
				}
				return true;
				break;
			case 'password':
				if(!preg_match($u->preg('password'), $value))
				{
					$this->core->error->error('plugin_user_signup',12);
					return false;
				}
				
				/*$f = $this->core->file->get_line_array('blacklist/password');
				for($i=0;$i<sizeof($f);++$i)
				{
					if($f[$i] == $value)
					{
						
						$this->core->error->error('plugin_user_signup',13);
						return false;
						//break;
					}
				}*/
				return true;
				break;
			case 'invite':
				$u = $this->core->plugin->initPl('user','user');
				$ch = $u->get_user($value, 'login');
				if(!$ch)
				{
					$this->core->error->error('plugin_user_signup',14);
					return false;
				}
				return true;
				break;
			case 'email':
				if(!preg_match($u->preg('mail'), $value))
				{
					$this->core->error->error('plugin_user_signup',16);
					return false;
				}
				elseif
				(
						$this->core->mysql->rows($this->core->mysql->query("SELECT email FROM users WHERE email='$value'"))!=0
						OR
						$this->core->mysql->rows($this->core->mysql->query("SELECT email FROM signup WHERE email='$value'"))!=0
				)
				{
					$this->core->error->error('plugin_user_signup',5);
					return false;
				}
				return true;
				break;
			case 'captcha':
				$c = $this->core->plugin->initPl('captcha','captcha');
				$cap = $c->check($value,'user_signup');
				if(!$cap)
				{
					$this->core->error->error('plugin_captcha_captcha',0);
					return false;
				}
				return true;
				break;
		}
		return false;
	}
	
	//Занесение пользователя в таблицу signup
	protected function signup()
	{
		if($_SESSION['loggedin'])
		{
			$this->core->error->error('server',403);
			return $this->json(array(false));
		}


		$err_lev = 0;
		
		$this->input('name','surname','password','login','email','sex','birthday','invite','about','agree','captcha');
		
		$cap = trim($this->core->sanString($this->data['captcha']));
		$cap = strtoupper($cap);
		

		$name = trim($this->core->sanString($this->data['name']));
		$surname = trim($this->core->sanString($this->data['surname']));
		$email = trim($this->core->sanString($this->data['email']));
		//$email2 = trim($this->core->sanString($this->data['email_confirm']));
		$password = trim($this->core->sanString($this->data['password']));
		//$password2 = trim(sanString($this->data['password_confirm']));
		$login = trim($this->core->sanString($this->data['login']));
		$sex = trim($this->core->sanString($this->data['sex']));
		$birthday = trim($this->core->sanString($this->data['birthday']));
		$invite = trim($this->core->sanString($this->data['invite']));
		$about = trim($this->core->sanString($this->data['about']));
		$agree = trim($this->core->sanString($this->data['agree']));

		//$login = text_from_rus_to_en($login);
		$login = strtolower($login);
		$login = ucfirst($login);
		$email = mb_convert_case($email, MB_CASE_LOWER, 'UTF-8');
		//$email2 = mb_convert_case($email2, MB_CASE_LOWER, 'UTF-8');
		$name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
		$surname = mb_convert_case($surname, MB_CASE_TITLE, 'UTF-8');
		
		if($agree!='on')
			$err_lev = 1;
		if(!$this->check('email',$email))
			$err_lev = 2;
		if($sex!='male' AND $sex!='female')
		{
			$this->core->error->error('plugin_user_signup',8);
			$err_lev = 3;
		}
		if(!$this->check('login',$login))
		$err_lev = 4;
		if(strtolower($password)==strtolower($login))
		{
		$this->core->error->error('plugin_user_signup',9);
		$err_lev = 5;
		}
		
		$date = explode('/', $birthday);
		
		if($date[0]<=0 OR $date[0]>31)
		{
			$this->core->error->error('plugin_user_signup',15);
			$err_lev = 6;
		}
		if($date[1]<=0 AND $date[1]>12)
		{
			$this->core->error->error('plugin_user_signup',15);
			$err_lev = 7;
		}
		$now = date('Y', time());
		if($date[2]<1970 AND $date[2]>($now - 90))
		{
			$this->core->error->error('plugin_user_signup',15);
			$err_lev = 8;
		}
		if(!$this->check('password',$password))
		$err_lev = 9;
		if(!empty($invite))
			if(!$this->check('invite',$invite))
			$err_lev = 10;
		if(!$this->check('captcha',$cap))
		$err_lev = 11;

		if($err_lev == 0)
		{
			$user = $this->core->plugin->initPl('user','user');//new user($this->core);
			$user->signup(
			$name,
			$surname,
			$email,
			$password,
			$login,
			$sex,
			$date[0],
			$date[1],
			$date[2],
			$invite,
			$about
			);
			return $this->json(array(true));
		}
		return $this->json(array(false));
	}
}
?>