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
		
		switch($type)
		{
			case 'login':
				$login = strtolower($value);
				$login = ucfirst($login);
				
				$f = $this->core->file->get_line_array('blacklist/login');
				for($i=0;$i<sizeof($f);++$i)
				{
					if(strtolower($f[$i])==strtolower($login))
					//if(preg_match("/^$f[$i]\$/i", $login))
					{
						$this->core->error->error('signup','001');
						return false;
						//break;
					}
				}
				if(!preg_match($this->core->preg('login'), $login))
				{
					$this->core->error->error('signup','001');
					return false;
				}
				elseif
				(
						$this->core->mysql->rows($this->core->mysql->query("SELECT login FROM users WHERE login='$login'"))!=0
						OR
						$this->core->mysql->rows($this->core->mysql->query("SELECT login FROM signup WHERE login='$login'"))!=0
				)
				{
					$this->core->error->error('signup','004');
					return false;
					//break;
				}
				else
				{
					return true;
				}
				break;
			case 'name':
				if(!preg_match($this->core->preg('name'), $value))
				{
					$this->core->error->error('signup','010');
					return false;
				}
				return true;
				break;
			case 'surname':
				if(!preg_match($this->core->preg('surname'), $value))
				{
					$this->core->error->error('signup','011');
					return false;
				}
				return true;
				break;
			case 'password':
				if(!preg_match($this->core->preg('password'), $value))
				{
					$this->core->error->error('signup','012');
					return false;
				}
				
				$f = $this->core->file->get_line_array('blacklist/password');
				for($i=0;$i<sizeof($f);++$i)
				{
					if($f[$i] == $value)
					{
						
						$this->core->error->error('signup','013');
						return false;
						//break;
					}
				}
				return true;
				break;
			case 'invite':
				$this->core->plugin('user');
				$u = new user($this->core);
				$ch = $u->get_user($value, 'login');
				if(!$ch)
				{
					$this->core->error->error('signup','014');
					return false;
				}
				return true;
				break;
			case 'email':
				if(!preg_match($this->core->preg('mail'), $value))
				{
					$this->core->error->error('signup','016');
					return false;
				}
				elseif
				(
						$this->core->mysql->rows($this->core->mysql->query("SELECT email FROM users WHERE email='$value'"))!=0
						OR
						$this->core->mysql->rows($this->core->mysql->query("SELECT email FROM signup WHERE email='$value'"))!=0
				)
				{
					$this->core->error->error('signup','005');
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
		$err_lev = 0;
		
		$this->input('name','surname','password','login','email','sex','birthday','invite','about','agree','captcha');
		
		$cap = $this->core->sanString($this->data['captcha']);
		//$cap = text_from_rus_to_en($cap);
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
		/*if($cap!=$_SESSION['captcha'])
		{
			$_SESSION['WARNING'][] = "Капча введена не правильно";
			$err_lev = 1;
		}*/
		if(!$this->check('email',$email))
			$err_lev = 1;
		if($sex!='male' AND $sex!='female')
		{
			$this->core->error->error('signup','008');
			$err_lev = 1;
		}
		if(!$this->check('login',$login))
		$err_lev = 1;
		if(strtolower($password)==strtolower($login))
		{
		$this->core->error->error('signup','009');
		$err_lev = 1;
		}
		
		$date = explode('/', $birthday);
		
		if($date[0]<=0 OR $date[0]>31)
		{
			$this->core->error->error('signup','015');
			$err_lev = 1;
		}
		if($date[1]<=0 AND $date[1]>12)
		{
			$this->core->error->error('signup','015');
			$err_lev = 1;
		}
		$now = date('Y', time());
		if($date[2]<1970 AND $date[2]>($now - 90))
		{
			$this->core->error->error('signup','015');
			$err_lev = 1;
		}
		if(!$this->check('password',$login))
		$err_lev = 1;
		if(!empty($invite))
			if(!$this->check('invite',$invite))
			$err_lev = 1;
		
		if($err_lev == 0)
		{
			$this->core->plugin('user');
			$user = new user($this->core);
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