<?php
class plugin_user_user
{
	public	$id,						//Айди
			$name,						//Имя
			$surname,					//Фамилия
			$nickname,					//Никнейм
			$login,						//Логин
			$birthday,					//День рождения
			$salt,						//Соль для пароля от сайта
			$rank,						//Ранк
			$rank_main,					//Ранк
			$sex,						//Пол
			$email,						//E-mail
			$time_reg,					//Время регистрации
			$time_login,				//Время последнего удачного входа
			$time_fail,					//Время последнего провала при входе
			$time_logout,				//Время последнего выхода
			$totaltime,					//Всего времени онлайн
			$pass,						//Пароль от сайта
			$carma = Array('plus'=>0,'minus'=>0),//Карма пользователя [-][+]
			//$carma_from = Array(),		//Карма другим пользователям [-][+]
			//$carma_from_art = Array(),	//Карма статьям [-][+]
			//$cache,						//Кэш для всех операций
			//$avatar,					//Адрес расположения аватарки
			$perm,						//Пермишнс пользователя
			//$money,						//Деньги
			//$checked,					//подтвердил e-mail
			$warnings,					//предупреждений о нарушениях
			$invite;					//кто пригласил

	public function __construct($core)
	{
		$this->core = $core;
	}

	//Получение инфы о пользователе, запись в публичные переменные класса
	public function get_user($user='', $search='id')
	{
		$user = $this->core->sanString($user);
		switch($search)
		{
			case 'id':
				$id = $this->core->mysql->query("SELECT * FROM users WHERE id='$user'");
				break;
			case 'login':
				$id = $this->core->mysql->query("SELECT * FROM users WHERE login='$user'");
				break;
			case 'email':
				$id = $this->core->mysql->query("SELECT * FROM users WHERE email='$user'");
				break;
		}
		
		if($this->core->mysql->rows()!=1)
		{
			return false;
		}
		
		$result = $this->core->mysql->fetch();
		$bday = $result['birthday'];
		if(strlen($bday)<8)
		{
			$bday = Array(0, $bday[0], $bday[1], $bday[2], $bday[3], $bday[4], $bday[5], $bday[6]);
		}
		$biday = '';
		$bimon = '';
		$biye = '';
		if($bday[0].$bday[1]!=99)$biday = $bday[0].$bday[1];//Получаем день рождения
		if($bday[2].$bday[3]!=99)$bimon = $bday[2].$bday[3];//Получаем месяц рождения
		if($bday[4].$bday[5].$bday[6].$bday[7]!=9999)$biye = $bday[4].$bday[5].$bday[6].$bday[7];//Получаем год рождения

		//Устанавливаем полученные из БД данные в публичные переменные класса
		$this->id = $result['id'];
		$this->name = $result['name'];
		$this->surname = $result['surname'];
		$this->nickname = $result['nickname'];
		$this->login = $result['login'];
		$this->birthday = Array($biday, $bimon, $biye);
		$this->salt = $result['salt'];
		$this->rank = explode(':',$result['rank']);
		$this->rank_main = $this->rank[0];
		$this->sex = $result['sex'];
		$this->email = $result['email'];
		$this->time_reg = $result['time_reg'];
		//$this->time_login = $result['time_login'];
		//$this->time_fail = $result['time_fail'];
		//$this->time_logout = $result['time_logout'];
		$this->totaltime = $result['totaltime'];
		$this->pass = $result['password'];
		//$this->cache = md5($this->time_reg . $this->login . $this->email . $this->time_login);
		//$this->money = floor($result5['balance']);
		//////////////////////////////////////////////$this->perm = array('inf'=>$result['inf'], 'friends'=>$result['friends'], 'wall'=>$result['wall'], 'msg'=>$result['msg'], 'robots'=>$result['robots']);
		$this->warnings = 0;
		//$this->checked = $result6['checked'];if($this->checked=='')$this->checked='3';
		$this->invite = $result['invite'];
		
		$carma = Array();//Карма пользователя [-][+]
		//$carma_from = Array();//Карма другим пользователям [-][+]
		//$carma_from_art = Array();//Карма статьям [-][+]
		
		return true;//Возвращаем true, т.к. всё удачно
	}
	
	//Установка некоторых данных в сессии
	public function set_user($user, $search='id')
	{
		$this->get_user($user, $search);
		$_SESSION['nickname'] = $this->nickname;
		$_SESSION['salt'] = $this->salt;
		$_SESSION['pass'] = $this->pass;
		$_SESSION['email'] = $this->email;
		$_SESSION['id'] = $this->id;
		$_SESSION['login'] = $this->login;
		$_SESSION['rank'] = $this->rank;
		$_SESSION['rank_main'] = $this->rank_main;
		
		$time = time();
		$_SESSION['auth_time'] = $time;
		$_SESSION['auth'] = sha1($time.$this->pass.md5($this->salt));
		
		//$_SESSION['cache'] = $this->cache;
	}
	
	//Генерация md5 пароля
	public function password_md5($pass, $reg, $time=null, $salt=null)
	{
		if($reg == true)
		{
			if(empty($time))$time = time();
			if(empty($salt))$salt = rand(0, 99999999);
			$pass_md5 = md5($time . $this->core->conf->plugins->user->user->salt[0] . $pass . $salt . $this->core->conf->plugins->user->user->salt[1]);
			$array = Array('salt' => $salt, 'time' => $time, 'pass' => $pass_md5);
			return $array;
		}
		elseif($reg==false)
		{
			$pass_md5 = md5($time . $this->core->conf->plugins->user->user->salt[0] . $pass . $salt . $this->core->conf->plugins->user->user->salt[1]);
			return $pass_md5;
		}
	}
	
	public function generate_code($type, $params=array(),$mysql=true)
	{
		switch($type)
		{
			case 'signup':
				$code = sha1($params['id'].md5($params['login']));
				$data = $this->core->sanString(json_encode($params),'mysql');
				if($mysql===true)$this->core->mysql->query("INSERT INTO code(type,value,data) VALUES('signup','$code','$data')");
				return $code;
				break;
		}
	}
	
	//Регистрация нового пользователя во временной БД
	public function signup(
	$name,
	$surname,
	$email,
	$password,
	$login,
	$sex,
	$day,
	$month,
	$year,
	$invite=null,
	$about=null
	)
	{
		$name = trim($this->core->sanString($name));
		$surname = trim($this->core->sanString($surname));
		$email = trim($this->core->sanString($email));
		$password = trim($this->core->sanString($password));
		$login = trim($this->core->sanString($login));
		$sex = trim($this->core->sanString($sex));
		$day = trim($this->core->sanString($day));
		$month = trim($this->core->sanString($month));
		$year = trim($this->core->sanString($year));
		$invite = trim($this->core->sanString($invite));
		$about = trim($this->core->sanString($about));
		
		if(!empty($invite))
		{
			$ch = $this->get_user($invite, "login");
			if($ch)$invite = $this->id;
			else $invite='';
		}
		if($sex=='male'){$sex=1;}elseif($sex=='female'){$sex=0;}
		
		$time = time();
		
		$this->core->mysql->query("INSERT INTO signup
		(name, surname, email, password, login, sex, day, month, year, time, invite, about, status) VALUES
		('$name', '$surname', '$email', '$password', '$login', '$sex', '$day', '$month', '$year', '$time', '$invite', '$about', '0')");
		
		$id = $this->core->mysql->query("SELECT id FROM signup WHERE email='$email'");
		$id = $this->core->mysql->fetch();
		$id = $id['id'];
		
		$code = $this->generate_code('signup',array('login'=>$login,'id'=>$id));
		
		$this->core->mail->add_waiting_list($email, '002', array('login'=>$login, 'id'=>$id, 'code'=>$code));
	}
	
	//Создание нового пользователя
	public function new_user(
	$name,
	$surname,
	$email,
	$password,
	$login,
	$sex,
	$day,
	$month,
	$year,
	$nickname = null,
	$rank = null,
	$invite = null,
	$time_reg = null,
	$about = nul
	)
	{
		$name = trim($this->core->sanString($name));
		$surname = trim($this->core->sanString($surname));
		$email = trim($this->core->sanString($email));
		$password = trim($this->core->sanString($password));
		$login = trim($this->core->sanString($login));
		$sex = trim($this->core->sanString($sex));
		$day = trim($this->core->sanString($day));
		$month = trim($this->core->sanString($month));
		$year = trim($this->core->sanString($year));
		$nickname = trim($this->core->sanString($nickname));
		$rank = trim($this->core->sanString($rank));
		$invite = trim($this->core->sanString($invite));
		$about = trim($this->core->sanString($about));
		if(empty($rank))$rank = $this->core->conf->plugins->user->user->ranks['user'];
		if(empty($nickname))$nickname = $login;
		if(empty($time_reg))$time_reg = time();
		
		$login = strtolower($login);
		$login = ucfirst($login);
		$email = mb_convert_case($email, MB_CASE_LOWER, 'UTF-8');
		$name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
		$surname = mb_convert_case($surname, MB_CASE_TITLE, 'UTF-8');
		
		$time = time();
		$pass_arr = $this->password_md5($password, true, $time_reg);
		
		$salt = $pass_arr['salt'];
		$pass_md5 = $pass_arr['pass'];
		
		if($sex=='male'){$sex=1;}elseif($sex=='female'){$sex=0;}
		if(!$day)$day = 99;
		if(!$month)$month = 99;
		if(!$year)$year = 9999;
		if($day<10)$day = '0' . $day;
		if($month<10)$month = '0' . $month;
		$birthday = $day . $month . $year;
		
		$check = $this->core->mysql->query("SELECT * FROM users WHERE login='$login' OR email='$email'");
		if($this->core->mysql->rows($check)!=0)
		{
			$this->core->error->error('plugin_user_signup',4);
			$this->core->error->error('plugin_user_signup',5);
			return false;
		}
		
		$this->core->mysql->query("INSERT INTO users( name, surname, nickname, 
													  login, birthday, rank, 
													  password, salt, email,
													  sex, totaltime, invite,
													  time_reg, about
													) 
		VALUES( '$name', '$surname', '$nickname', 
				'$login', '$birthday', '$rank', 
				'$pass_md5', '$salt', '$email',
				'$sex', '0', '$invite',
				'$time_reg', '$about'
			  )
		");
		
		$pass_authme = md5($password);
		$this->core->mysql->query("INSERT INTO authme(username,password) VALUES('$login','$pass_authme')","mcprimary");
		
		//$this->email($email, 'activate', array($login, $id));
	}
	
	//Проверяет, online ли пользователь
	public function online($id)
	{
		$id = $this->core->SanString($id);
		$time_left = time()-62;
		$r = $this->core->mysql->query("SELECT id FROM online WHERE time>'$time_left' AND id='$id'");
		if($this->core->mysql->rows($r)==1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	//Изменение данных пользователя
	public function change_user($user, $after, $what)
	{
		$user = $this->core->SanString($user);
		$after = $this->core->SanString($after);
		$exist = $this->get_user($user);
		if(!$exist)
		{
			$this->core->error->error('plugin_user_user',0);
			return false;
		}
		
		#NAMES
		if($what=='name')
		{
			if(mb_strlen($after, 'UTF-8')>$this->core->conf->plugins->user->user->length['name']['max'])
			{
				$this->core->error->error('plugin_user_profile',2);
				return false;
			}
			
			if(mb_strlen($after, 'UTF-8')<$this->core->conf->plugins->user->user->length['name']['min'])
			{
				$this->core->error->error('plugin_user_profile',1);
				return false;
			}
			
			if($this->name!=mb_convert_case($after, MB_CASE_TITLE, 'UTF-8'))
			{
				$after = mb_convert_case($after, MB_CASE_TITLE, 'UTF-8');
				$this->core->mysql->query("UPDATE users SET name='$after' WHERE id='$this->id'");
				return true;
			}
			
			return true;
		}
		
		if($what=='surname')
		{
			if(mb_strlen($after, 'UTF-8')>$this->core->conf->plugins->user->user->length['surname']['max'])
			{
				$this->core->error->error('plugin_user_profile',4);
				return false;
			}
			
			if(mb_strlen($after, 'UTF-8')<$this->core->conf->plugins->user->user->length['surname']['min'])
			{
				$this->core->error->error('plugin_user_profile',3);
				return false;
			}
			
			if($this->surname!=mb_convert_case($after, MB_CASE_TITLE, 'UTF-8'))
			{
				$after = mb_convert_case($after, MB_CASE_TITLE, 'UTF-8');
				$this->core->mysql->query("UPDATE users SET surname='$after' WHERE id='$this->id'");
				return true;
			}
			
			return true;
		}
		
		if($what=='nickname')
		{
			if(mb_strlen($after, 'UTF-8')>$this->core->conf->plugins->user->user->length['nickname']['max'])
			{
				$this->core->error->error('plugin_user_profile',6);
				return false;
			}
			
			if(mb_strlen($after, 'UTF-8')<$this->core->conf->plugins->user->user->length['nickname']['min'])
			{
				$this->core->error->error('plugin_user_profile',5);
				return false;
			}
			
			if($this->nickname!=mb_convert_case($after, MB_CASE_TITLE, 'UTF-8'))
			{
				$result1 = $this->core->mysql->rows($this->core->mysql->query("SELECT * FROM users WHERE login='$after' AND id='$this->id'"));
				$result2 = $this->core->mysql->rows($this->core->mysql->query("SELECT * FROM users WHERE login='$after'"));
				
				$result3 = $this->core->mysql->rows($this->core->mysql->query("SELECT * FROM users WHERE nickname='$after'"));
				
				$r1 = $result2 - $result1;
				$r2 = $result3;
				if($r1==0 AND $r2==0)
				{
					$after = mb_convert_case($after, MB_CASE_TITLE, 'UTF-8');
					$this->core->mysql->query("UPDATE users SET nickname='$after' WHERE id='$this->id'");
					$_SESSION['nickname'] = $after;
					return true;
				}
				else
				{
					$this->core->error->error('plugin_user_profile',7);
					return false;
				}
			}
			
			return true;
		}
		#NAMES
		
		#PASSWORDS
		if($what=='password' and $after[0]!='')
		{
			$after[0] = $this->core->SanString($after[0]);
			$after[1] = $this->core->SanString($after[1]);
			$after[2] = $this->core->SanString($after[2]);
			
			if($after[0]!=$after[1])
			{
				$this->core->error->error('plugin_user_profile',10);
				return false;
			}
			
			if(strlen($after[0])>$this->core->conf->plugins->user->user->length['password']['max'])
			{
				$this->core->error->error('plugin_user_profile',9);
				return false;
			}
			
			if(strlen($after[0])<$this->core->conf->plugins->user->user->length['password']['min'])
			{
				$this->core->error->error('plugin_user_profile',8);
				return false;
			}
			
			if($after[0]==$this->login)
			{
				$this->core->error->error('plugin_user_profile',11);
				return false;
			}
			
			$f=$this->core->file->get_line_array('blacklist/password');	
			$pass_base = 0;		
			//print_r(sizeof($f));			
			for($i=0;$i<sizeof($f);++$i)
			{
				if($f[$i]==$after[0])
				{
					$pass_base = 1;
					break;
				}
			}
			
			if($pass_base==1)
			{
				$this->core->error->error('plugin_user_profile',12);
				return false;
			}

			$pass = $this->password_md5($after[2], false, $this->time_reg, $this->salt);
			if(empty($after[3]))
			if($this->pass!=$pass)
			{		
				$this->core->error->error('plugin_user_profile',13);
				return false;
			}	
			
			
			$pass2 = $this->password_md5($after[0], false, $this->time_reg, $this->salt);
			$this->core->mysql->query("UPDATE users SET password='$pass2' WHERE id='$this->id'");

			$passmd5 = md5($after[0]);
			$this->core->mysql->query("UPDATE authme SET password='$passmd5' WHERE username='$this->login'",'mcprimary');

			$_SESSION['pass'] = $pass2;
			return true;
		}
		#PASSWORDS
		
		//EDIT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		#PERMISSIONS
		if($what=='permissions_inf')
		{
			if($after>=1 and $after<=6)
			{
				if($after!=$this->perm['inf'])
				{
					queryMysql("UPDATE permissions SET inf='$after' WHERE id='$this->id'");
					$_SESSION['SUCCESS'][] = "Доступ к основной информации изменён";
				}
			}
			else
			{
				$_SESSION['ERROR'][] = "Неправильный числовой формат: $after";
			}
		}
		
		if($what=='permissions_friends')
		{
			if($after>=1 and $after<=6)
			{
				if($after!=$this->perm['friends'])
				{
					queryMysql("UPDATE permissions SET friends='$after' WHERE id='$this->id'");
					$_SESSION['SUCCESS'][] = "Доступ к списку друзей изменён";
				}
			}
			else
			{
				$_SESSION['ERROR'][] = "Неправильный числовой формат: $after";
			}
		}
		
		if($what=='permissions_msg')
		{
			if($after>=1 and $after<=5)
			{
				if($after!=$this->perm['msg'])
				{
					queryMysql("UPDATE permissions SET msg='$after' WHERE id='$this->id'");
					$_SESSION['SUCCESS'][] = "Доступ к сообщениям изменён";
				}
			}
			else
			{
				$_SESSION['ERROR'][] = "Неправильный числовой формат: $after";
			}
		}
		
		if($what=='permissions_wall')
		{
			if($after>=1 and $after<=5)
			{
				if($after!=$this->perm['wall'])
				{
					queryMysql("UPDATE permissions SET wall='$after' WHERE id='$this->id'");
					$_SESSION['SUCCESS'][] = "Доступ к стене изменён";
				}
			}
			else
			{
				$_SESSION['ERROR'][] = "Неправильный числовой формат: $after";
			}
		}
		
		if($what=='permissions_robots')
		{
			if($after==1 or $after==0)
			{
				if($after!=$this->perm['robots'])
				{
					queryMysql("UPDATE permissions SET robots='$after' WHERE id='$this->id'");
					$_SESSION['SUCCESS'][] = "Доступ на индексацию страницы изменён";
				}
			}
			else
			{
				$_SESSION['ERROR'][] = "Неправильный числовой формат: $after";
			}
		}
		#PERMISSIONS
	}
	
	public function followed($follower_id, $following_id)
	{
		$this->get_user($follower_id, 'id');
		$follower = $this->id;
		$this->get_user($following_id, 'id');
		$following = $this->id;
		
		$result = $this->core->mysql->query("SELECT * FROM friends WHERE id='$follower' AND friend='$following'");
		if($this->core->mysql->rows($result)!=0 AND $follower_id!=$following_id)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function friends($fr1, $fr2)
	{
		$val1 = $this->followed($fr1, $fr2);
		$val2 = $this->followed($fr2, $fr1);
		if($val1==true AND $val2==true AND $fr1!=$fr2)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function follow($follower_id, $following_id)
	{
		if(!$this->friends($follower_id, $following_id) AND !$this->followed($follower_id, $following_id) AND $follower_id!=$following_id)
		{
			$time = time();
			$result = $this->core->mysql->query("INSERT INTO friends(id, friend, time) VALUES('$follower_id', '$following_id', '$time')");
		}
	}
	
	public function unfollow($follower_id, $following_id)
	{
		if($this->friends($follower_id, $following_id) OR $this->followed($follower_id, $following_id) AND $follower_id!=$following_id)
		{
			$result = $this->core->mysql->query("DELETE FROM friends WHERE id='$follower_id' AND friend='$following_id'");
		}
	}
}
?>