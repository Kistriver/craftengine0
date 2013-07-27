<?php
class error
{
	public	$core,								//Ядро
			$error		=		Array();		//Массив ошибок
	
	public function __construct($core)
	{
		$this->core = $core;
		
		error_reporting(E_ALL);
		
		//Объявление обработчиков ошибок
		set_error_handler(array($this/*->error*/,'error_php'));
		register_shutdown_function(array($this/*->error*/, 'fatal_error_php'));
		
		//set_error_handler(array($this,'error_php'));
		//register_shutdown_function(array($this, 'fatal_error_php'));
	}
	
	//Добавление ошибки
	public function error($err_mod,$err_no)
	{
		$err = $this->error_make($err_mod,$err_no);
		if($err)$this->error[] = $err;
	}
	
	//Добавление ошибки PHP
	public function error_php($code,$msg,$file,$line)
	{
		$file_fr = str_replace('/api/system/core','',dirname(__FILE__));
		$file = str_replace($file_fr,'{{FRAMEWORK_ROOT}}',$file);
		
		if(!$this->core->conf->system->core->debug)$this->error[] = $this->error_make('engine','003');
		else $this->error[] = array('01003',"[$code][$file:$line]$msg");
		if($this->core->conf->system->core->send_mail_report)
		$this->core->mail->add_waiting_list(
		$this->core->conf->system->core->admin_mail, 
		'001', 
		array(
			'code'=>$code,
			'msg'=>$msg,
			'file'=>$file,
			'line'=>$line
			)
		);
	}
	
	//Отлов завершения работы скрипта
	public function fatal_error_php()
	{
		$error = error_get_last();
		if (isset($error))//Если фатальная ошибка, то обработка этой ошибки
			if($error['type'] == E_ERROR
			|| $error['type'] == E_PARSE
			|| $error['type'] == E_COMPILE_ERROR
			|| $error['type'] == E_CORE_ERROR)
			{
				ob_end_clean();
				
				$file_fr = str_replace('/api/system/core','',dirname(__FILE__));
				$error['file'] = str_replace($file_fr,'{{FRAMEWORK_ROOT}}',$error['file']);
				
				if(!$this->core->conf->system->core->debug)$errA = 
				array('error'=>'Unfortunately, there is an error there. But our team is working on elimination of this problem.');
				else $errA = array('error'=>"[$error[type]][$error[file]:$error[line]] $error[message]<br />\r\n");
				//header('HTTP/1.0 500');
				echo json_encode($errA);
				if($this->core->conf->system->core->send_mail_report)
				$this->core->mail->add_waiting_list(
				$this->core->conf->system->core->admin_mail, 
				'000', 
				array(
					'code'=>$error['type'],
					'msg'=>$error['message'],
					'file'=>$error['file'],
					'line'=>$error['line']
					)
				);
			}
		ob_end_flush();
	}
	
	//Создание ошибки
	public function error_make($err_mod,$err_no)
	{
		//Модули и их код
		$mod = array(
					'server'=>'00',
					'engine'=>'01',
					'signup'=>'02',
					'login'=>'03',
					'user'=>'04',
					'article'=>'05',
					'profile'=>'06',
					'wall'=>'07',
					'msg'=>'08',
					'api'=>'09',
					'mysql'=>'10',
		);
		
		if(isset($mod[$err_mod]))
		{
			$res1 = $mod[$err_mod];
		}
		
		if(empty($res1))//Если передан неизвестный модуль, то выдать ошибку
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
					'004'=>'Hack attempt',
		);
		
		$num['mysql'] = array(
					'000'=>'non-object',
		);
		
		$num['signup'] = array(
					'000'=>'Недопустимое значение',
					'001'=>'Неправильный формат логина',
					'002'=>'Значение слишком короткое',
					'003'=>'Значение слишком длинное',
					'004'=>'Учётная запись с таким логином уже зарегистрирована',
					'005'=>'Учётная запись с таким e-mail уже зарегистрирована',
					'006'=>'Значение слишком длинное',
					'007'=>'Вы не согласились с правилами лицензионного соглашения',
					'008'=>'Неправильно указан пол',
					'009'=>'Пароль не должен совпадать с логином',
					'010'=>'Неправильный формат имени',
					'011'=>'Неправильный формат фамилии',
					'012'=>'Неправильный формат пароля',
					'013'=>'Пароль содержится в базе брутфорсера',
					'014'=>'Приглашение недействительно',
					'015'=>'Недопустимая дата',
					'016'=>'Неправильный формат e-mail',
		);
		
		$num['login'] = array(
					'000'=>'Такая пара e-mail, пароль не найдена',
					'001'=>'Вы не ввели e-mail и/или пароль',
					'002'=>'Неправильный формат e-mail и/или пароля',
					'003'=>'Вы слишком много раз нарушили правила, поэтому были забанены',
					'004'=>'Ваш аккаунт не был подтверждён администрацией',
					'005'=>'Вы не подтвердили свой аккаунт по e-mail. Ключ был выслан повторно. [url=/login/enter_code/]Подтвердить аккаунт[/url]',
		);
		
		$num['user'] = array(
					'000'=>'Пользователь не найден',
		);
		
		$num['article'] = array(
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
					'007'=>'Такой ник уже используется',
					'008'=>'Пароль слишком короткий',
					'009'=>'Пароль слишком длинный',
					'010'=>'Пароли не совпадают',
					'011'=>'Пароль не должен совпадать с логином',
					'012'=>'Пароль содержится в базе брутфорсера',
					'013'=>'Вы ввели неправильно старый пароль',
		);
		
		$num['wall'] = array(
					
		);
		
		$num['msg'] = array(
					
		);
		
		$num['api'] = array(
					'000'=>'Unexpected module',
					'001'=>'Unexpected function',
					'002'=>'Not implemented yet',
					'003'=>'Sid not get',
					'004'=>'Unexpected method',
					'005'=>'Not all parameters',
		);
		
		if(isset($num[$err_mod][$err_no]))
		{
			$res2 = $num[$err_mod][$err_no];
		}
		
		if(empty($res2))//Если передан неизвестный код ошибки, выдать ошибку
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