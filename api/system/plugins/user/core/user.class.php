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
		//$_SESSION['cache'] = $this->cache;
	}
	
	//Генерация md5 пароля
	public function password_md5($pass, $reg, $time=null, $salt=null)
	{
		if($reg == true)
		{
			if(empty($time))$time = time();
			if(empty($salt))$salt = rand(0, 99999999);
			$pass_md5 = md5($time . $this->core->conf->system->core->salt[0] . $pass . $salt . $this->core->conf->system->core->salt[1]);
			$array = Array('salt' => $salt, 'time' => $time, 'pass' => $pass_md5);
			return $array;
		}
		elseif($reg==false)
		{
			$pass_md5 = md5($time . $this->core->conf->system->core->salt[0] . $pass . $salt . $this->core->conf->system->core->salt[1]);
			return $pass_md5;
		}
	}
	
	public function generate_code($type, $params=array())
	{
		switch($type)
		{
			case 'signup':
				$code = sha1($params['id'].md5($params['login']));
				$data = $this->core->sanString(json_encode($params),'mysql');
				$this->core->mysql->query("INSERT INTO code(type,value,data) VALUES('signup','$code','$data')");
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
		if(empty($rank))$rank = $this->core->conf->system->core->ranks['user'];
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
			$this->core->error->error('signup','004');
			$this->core->error->error('signup','005');
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
			$this->core->error->error('user','000');
			return false;
		}
		
		#NAMES
		if($what=='name')
		{
			if(mb_strlen($after, 'UTF-8')>$this->core->conf->system->core->length['name']['max'])
			{
				$this->core->error->error('profile','002');
				return false;
			}
			
			if(mb_strlen($after, 'UTF-8')<$this->core->conf->system->core->length['name']['min'])
			{
				$this->core->error->error('profile','001');
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
			if(mb_strlen($after, 'UTF-8')>$this->core->conf->system->core->length['surname']['max'])
			{
				$this->core->error->error('profile','004');
				return false;
			}
			
			if(mb_strlen($after, 'UTF-8')<$this->core->conf->system->core->length['surname']['min'])
			{
				$this->core->error->error('profile','003');
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
			if(mb_strlen($after, 'UTF-8')>$this->core->conf->system->core->length['nickname']['max'])
			{
				$this->core->error->error('profile','006');
				return false;
			}
			
			if(mb_strlen($after, 'UTF-8')<$this->core->conf->system->core->length['nickname']['min'])
			{
				$this->core->error->error('profile','005');
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
					$this->core->error->error('profile','007');
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
				$this->core->error->error('profile','010');
				return false;
			}
			
			if(strlen($after[0])>$this->core->conf->system->core->length['password']['max'])
			{
				$this->core->error->error('profile','009');
				return false;
			}
			
			if(strlen($after[0])<$this->core->conf->system->core->length['password']['min'])
			{
				$this->core->error->error('profile','008');
				return false;
			}
			
			if($after[0]==$this->login)
			{
				$this->core->error->error('profile','011');
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
				$this->core->error->error('profile','012');
				return false;
			}

			$pass = $this->password_md5($after[2], false, $this->time_reg, $this->salt);
			if($this->pass!=$pass)
			{		
				$this->core->error->error('profile','013');
				return false;
			}	
			
			
			$pass2 = $this->password_md5($after[0], false, $this->time_reg, $this->salt);
			$this->core->mysql->query("UPDATE login SET password='$pass2' WHERE id='$this->id'");
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
	/*
	public function email($subj, $type, $params='')
	{
		$domain = '178.140.61.70';
		
		$date = date('d M Y');
		$body = <<<BLOCK
<html>
<head>   
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">   	   
</head>
<body>
<table bgcolor="#DDDDDD" width="100%">
<tbody>
<tr>
<td>
<table style="margin: 15px auto;" align="center" border="0" cellpadding="0" cellspacing="0" width="603">
<tbody>
<tr>
<td style="border-bottom: 3px solid #DDD" bgcolor="#FFFFFF" height="113px">
<table align="center" bgcolor="#cadff2" border="0" cellpadding="0" cellspacing="0" height="93" width="583">
<tbody>
<tr>
<td>
<table background="logo" border="0" cellpadding="0" cellspacing="0" height="93" width="100%">
<tbody>
<tr>
<td rowspan="2">
<a href="http://178.140.61.70/" target="_blank"><h1>KachalovCRAFT</h1></a></td>
<td style="padding-right:10px" align="right" height="50" width="150">
<a href="https://www.facebook.com/groups/kachalovcraft/" target="_blank"><img src="//af19.mail.ru/cgi-bin/readmsg?id=13630881670000000767;0;3&amp;mode=attachment&amp;bs=14868&amp;bl=2590&amp;ct=image%2fpng&amp;cn=facebook.png&amp;cte=base64" height="28" width="29"></a>&nbsp;<a href="https://twitter.com/KachalovCRAFT" target="_blank"><img src="//af12.mail.ru/cgi-bin/readmsg?id=13630881670000000767;0;4&amp;mode=attachment&amp;bs=17689&amp;bl=2556&amp;ct=image%2fpng&amp;cn=twitter.png&amp;cte=base64" height="28" width="29"></a>&nbsp;<a href="http://vk.com/kachalovcraft" target="_blank"><img src="//af8.mail.ru/cgi-bin/readmsg?id=13630881670000000767;0;5&amp;mode=attachment&amp;bs=20482&amp;bl=2601&amp;ct=image%2fpng&amp;cn=vkontakte.png&amp;cte=base64" height="28" width="29"></a></td>
</tr>
<tr>
<td style="color:#FFF;font-weight:bold;padding-right:10px" align="right" width="150">
<strong>$date</strong></td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>
</td>
</tr>
<tr>
<td style="border-bottom: 3px solid #DDD" bgcolor="#FFFFFF" height="45">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
<td>
&nbsp;</td>
<td style="color:#434343;font-size:16px;font-family:Verdana, Geneva, sans-serif" align="center">
\$header</td>
<td>
&nbsp;</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td>
</td>						
</tr>
<tr>
<td style="border-bottom: 3px solid #DDD;padding:15px;font-size:12px;font-family:Verdana, Geneva, sans-serif;color:#434343" bgcolor="#FFFFFF">
<p>
<strong>Здравствуйте, дорогой подписчик!</strong><br>
<br>
\$main
</td>
</tr>
<tr>
<td>
</td>
</tr>
<!--<tr>
<td style="border-bottom: 3px solid #a22a2a;padding:15px;font-size:12px;font-family:Verdana, Geneva, sans-serif;color:#991a1a;font-weight:bold" bgcolor="#FFFFFF">
<p>
Не отвечайте на это письмо, используйте следующий email для связи с нами: <a href="admin@easyflash.org" target="_blank">dddd</a></p>
</td>
</tr>
<tr>
<td>
</td>
</tr>-->
<tr>
<td style="border-bottom: 3px solid #DDD;padding:15px;font-size:12px;font-family:Verdana, Geneva, sans-serif;color:#999" bgcolor="#FFFFFF">
<p>
C уважением,<br>
Алексей Качалов и команда KachalovCRAFT NET.<br>
Основной сайт: <a href="http://$domain" target="_blank">$domain</a><br>
</td>
</tr>
<tr>
<td>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
<table>
</body>   	  
</html>
BLOCK;
		
		switch($type)
		{
			case 'activate':
			if(!isset($_SESSION['activate_msg_req']))$_SESSION['activate_msg_req']=time()-32;
			if($_SESSION['activate_msg_req']<=time()-30)
			{
				$code = sha1($GLOBALS['_CONF']['server']['salt']['password']['site'][1] . $params[0] . $params[1]);
				//$msg = "Ваш код:http://$domain/login/enter_code/$code/$params[1]/ \n$code:$params[1]\n";
				$header = 'Активация аккаунта';
				$main = 'Ваш e-mail был указан, как основной на сайте KachalovCRAFT NET при регистрации. 
				Если Вы не производили никаках действий, то просто удалите это сообщение. Если же Вы 
				проходили регистрацию на нашем сайте, то пройдите по ссылке:
				<a href="http://'.$domain.'/login/enter_code/'.$code.'/'.$params[1].'/">
				http://'.$domain.'/login/enter_code/'.$code.'/'.$params[1].'/</a>
				или введите код: '.$code.':'.$params[1].' на <a href="http://'.$domain.'/login/enter_code/">http://'.$domain.'/login/enter_code/</a>';
				$body = str_replace('$main',$main,$body);
				$body = str_replace('$header',$header,$body);
				$msg = $body;
				
				mail($subj, "Регистрация - KachalovCRAFT NET", $msg, "Content-Type:text/html;\n");
			}
			break;
			case 'restore':
			if(!isset($_SESSION['restore_msg_req']))$_SESSION['restore_msg_req']=time()-32;
			if($_SESSION['restore_msg_req']<=time()-30)
			{
				//$msg = "Ваш ключ для восстановления пароля: $params";
				$header = 'Восстановление пароля';
				$main = 'Ваш ключ для восстановления пароля: '.$params.'';
				$body = str_replace('$main',$main,$body);
				$body = str_replace('$header',$header,$body);
				$msg = $body;
				
				mail($subj, "Восстановление пароля - KachalovCRAFT NET", $msg, "Content-Type:text/html;\n");
				return true;
			}
			break;
			case 'news':
				$header = $params[0];
				$main = $params[1];
				$body = str_replace('$main',$main,$body);
				$body = str_replace('$header',$header,$body);
				$msg = $body;
				
				mail($subj, "Новости - KachalovCRAFT NET", $msg, "Content-Type:text/html;\n");
				return true;
			case 'new_user':
				$header = 'Регистрация';
				if($params[0]=='yes')
				{
					$main = 'Поздравляем! Ваш аккаунт был подтверждён. Теперь Вы 
					можете авторизироваться в системе.';
				}
				else
				{
					$main = 'Ваша заявка была отклонена. Сожалеем об этом.';
				}
				$body = str_replace('$main',$main,$body);
				$body = str_replace('$header',$header,$body);
				$msg = $body;
				
				mail($subj, "Регистрация - KachalovCRAFT NET", $msg, "Content-Type:text/html;\n");
				return true;
			default:
			return false;
			$_SESSION['ERROR'][] = 'Неизвестный тип сообщения.';
			break;
		}
	}*/
}
?>