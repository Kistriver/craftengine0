<?php
class error
{
	public	$core,
			$error		=		Array();
	
	public function __construct($core)
	{
		$this->core = $core;
		
		error_reporting(E_ALL);
		//set_error_handler(array($this,'error_php'));
		//register_shutdown_function(array($this, 'fatal_error_php'));
	}
	
	public function error($err_mod,$err_no)
	{
		$err = $this->error_make($err_mod,$err_no);
		if($err)$this->error[] = $err;
	}
	
	public function error_php($code,$msg,$file,$line)
	{
		if(!$this->core->conf->debug)$this->error[] = $this->error_make('engine','003');
		else $this->error[] = array("$code,$msg,$file,$line",'01003');
		if($this->core->conf->send_mail_report)$this->core->mail->add_waiting_list($this->core->conf->admin_mail, '001', array($code,$msg,$file,$line));
	}
	
	public function fatal_error_php($code,$msg,$file,$line)
	{
		$error = error_get_last();
		if (isset($error))
			if($error['type'] == E_ERROR
			|| $error['type'] == E_PARSE
			|| $error['type'] == E_COMPILE_ERROR
			|| $error['type'] == E_CORE_ERROR)
			{
				ob_end_clean();
				if(!$this->core->conf->debug)echo 'Unfortunately, there is an error there. But our team is working on elimination of this problem.';
				else echo "[$error[type]][$error[file]:$error[line]] $error[message]<br />\r\n";
				header('HTTP/1.0 500');
				if($this->core->conf->send_mail_report)$this->core->mail->add_waiting_list($this->core->conf->admin_mail, '000', $error);
			}
		ob_end_flush();
	}
	
	public function error_make($err_mod,$err_no)
	{
		$mod = array(
					'server'=>'00',
					'engine'=>'01',
					'signup'=>'02',
					'login'=>'03',
					'users'=>'04',
					'articles'=>'05',
					'profile'=>'06',
					'wall'=>'07',
					'msg'=>'08',
		);
		
		if(isset($mod[$err_mod]))
		{
			$res1 = $mod[$err_mod];
		}
		
		if(empty($res1))
		{
			$this->error('engine','001');
			return false;
		}
		else
		{
			$error1 = $res1;
		}
		
		$num['server'] = array(
					'403'=>'Доступ запрещён',
					'404'=>'Страница не найдена',
		);
		
		$num['engine'] = array(
					'000'=>'Неизвестный тип',
					'001'=>'Ошибка при создании ошибки',
					'002'=>'Ошибка при создании ошибки',
					'003'=>'PHP error',
		);
		
		$num['signup'] = array(
					
		);
		
		$num['login'] = array(
					'000'=>'Такая пара e-mail, пароль не найдена',
					'001'=>'Вы не ввели e-mail и/или пароль',
					'002'=>'Неправильный формат e-mail и/или пароля',
					'003'=>'Вы слишком много раз нарушили правила, поэтому были забанены',
					'004'=>'Ваш аккаунт не был подтверждён администрацией',
					'005'=>'Вы не подтвердили свой аккаунт по e-mail. Ключ был выслан повторно. [url=/login/enter_code/]Подтвердить аккаунт[/url]',
		);
		
		$num['users'] = array(
					
		);
		
		$num['articles'] = array(
					'000'=>'Неправильный формат даты',
					'001'=>'Ошибка при занесении в БД',
					'002'=>'Слишком длинная статья или заголовок',
					'003'=>'Заполнены не все поля',
		);
		
		$num['profile'] = array(
					'000'=>'Неизвестный тип',
					'001'=>'Имя слишком короткое',
					'002'=>'Имя слишком длинное',
					'003'=>'Фамилия слишком короткая',
					'004'=>'Фамилия слишком длинная',
					'005'=>'Ник слишком короткий',
					'006'=>'Ник слишком длинный',
		);
		
		$num['wall'] = array(
					
		);
		
		$num['msg'] = array(
					
		);
		
		if(isset($num[$err_mod][$err_no]))
		{
			$res2 = $num[$err_mod][$err_no];
		}
		
		if(empty($res2))
		{
			$this->error('engine','002');
			return false;
		}
		else
		{
			$error2 = $err_no;
			$desc = $res2;
		}
		
		return array($error1 . $error2, $desc);
	}
}
?>