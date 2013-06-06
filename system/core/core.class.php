<?php
class core
{
	public	$runtime,			//Время запуска скрипта
			$plugins;			//Массив плагинов
	
	public function __construct()
	{
		$this->runtime = microtime(true);
		ob_start();
		
		//ignore_user_abort(1);
		
		$includes = array(	'file',			//Файлы
							'conf',			//Конфигурации
							'error',		//Ошибки
							'mysql',		//СУБД
							'mail',			//Мыло
						);
		
		for($i=0;$i<sizeof($includes);$i++)
		{
			//Подключение модулей ядра
			include_once(dirname(__FILE__)."/". $includes[$i] .".class.php");
			
			//Вызов модулей
			$this->$includes[$i] = new $includes[$i]($this);
		}
		
		//Авторизирован ли пользователь
		if(isset($_SESSION['id']) AND isset($_SESSION['login']))
		{
			if($_SESSION['id']!='' AND $_SESSION['login']!='')
			$_SESSION['loggedin'] = true;
		}
		else
		$_SESSION['loggedin'] = false;
		
		if(!$_SESSION['loggedin'])
		{
			$_SESSION['id'] = '';
		}
		
		//Подключение БД
		//$this->mysql->connect_all();
		$this->mysql->connect('site');
		
		//Объявление обработчиков ошибок
		set_error_handler(array($this->error,'error_php'));
		register_shutdown_function(array($this->error, 'fatal_error_php'));
	}
	
	//Время выполнения скрипта
	public function runtime($round = true/*Округление*/)
	{
		$time = microtime(true) - $this->runtime;
		if($round)$time = round($time, 3);
		return $time;
	}
	
	//Подключение плагинов
	public function plugin()
	{
		$name = func_get_args();//Получение аргументов функции
		
		for($i=0;$i<sizeof($this->conf->includes['plugins']);$i++)
		{
			if(!in_array($this->conf->includes['plugins'][$i],$name))continue;
			//Подключение модуля
			
			include_once(dirname(__FILE__)."/../plugins/". $this->conf->includes['plugins'][$i] .".class.php");
			
			//Вызов модуля
			//$this->plugins->$this->conf->includes['plugins'][$i] = new $this->conf->includes['plugins'][$i]($this);
		}
	}
	
	//Подготовка переменных к занесению в БД
	public function sanString($var, $san='all')
	{
		if(is_array($var))
		{
			//return false;
			return $var;
		}

		if($san!='mysql')$var_before = Array("<", ">");
		if($san!='mysql')$var_after = Array("&lt;", "&gt;");
		if($san!='mysql')$var = str_replace($var_before, $var_after, $var);
		if($san!='mysql')$var = strip_tags($var);
		if($san!='mysql')$var = htmlentities($var, ENT_COMPAT, 'utf-8');
		//$var = stripslashes($var);
		if($san!='html')$var = $this->mysql->db[$this->conf->db[0][0]]->real_escape_string($var);
		if($san!='mysql')$var = str_replace("&amp;", "&", $var);
		return $var;
	}
	
	//JSON с кирилицей
	public function json_encode_ru($str)
	{
		$arr_replace_utf = array('\u0410', '\u0430','\u0411','\u0431','\u0412','\u0432',
		'\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',
		'\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',
		'\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',
		'\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',
		'\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',
		'\u0448','\u0429','\u0449','\u042a','\u044a','\u042b','\u044b','\u042c','\u044c',
		'\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');
		$arr_replace_cyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е',
		'Ё', 'ё', 'Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о',
		'П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш',
		'Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я');
		$str1 = json_encode($str);
		$str2 = str_replace($arr_replace_utf,$arr_replace_cyr,$str1);
		return $str2;
	}
	
	public function preg($type)
	{
		switch($type)
		{
			case 'mail':
				$mails = implode('|',$this->conf->preg['mail']);
				$pattern = '/^[a-z0-9_-]{4,70}\@'.$mails.'/';
				break;
		}
		return $pattern;
	}
}
?>