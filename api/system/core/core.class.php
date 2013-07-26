<?php
class core
{
	public	$runtime,			//Время запуска скрипта
			$plugins;			//Массив плагинов
	
	public function __construct()
	{
		$this->runtime = microtime(true);
		ob_start();
		
		try
		{
			if(defined('CORECALLONCE'))
			throw new Exception("CORE MAY BE CALLED ONLY ONCE", 1);
		}
		catch(exception $e)
		{
			//print_r($e);
			echo $e->getMessage()."\r\n";
			echo "#Trace: \r\n";
			$rm = str_replace('api/system/core','',dirname(__FILE__));
			
			foreach($e->getTrace() as $tr)
			{
				$tr['file'] = str_replace($rm,'{{FRAMEWORK_ROOT}}/',$tr['file']);
				echo "[$tr[file]:$tr[line]] '$tr[class]' object in function '$tr[function]'\r\n";
			}
			die;
		}
		
		if(!defined('CORECALLONCE'))
		define('CORECALLONCE', true);
		
		//ignore_user_abort(1);
		
		$includes = array(	'file',			//Файлы
							'conf',			//Конфигурации
							'error',		//Ошибки
							'mysql',		//СУБД
							'mail',			//Мыло
							'plugin',		//Плагинридер
						);
		
		for($i=0;$i<sizeof($includes);$i++)
		{
			//Подключение модулей ядра
			include_once(dirname(__FILE__)."/". $includes[$i] .".class.php");
			
			//Вызов модулей
			$this->$includes[$i] = new $includes[$i]($this);
		}
		
		//Объявление обработчиков ошибок
		set_error_handler(array($this->error,'error_php'));
		register_shutdown_function(array($this->error, 'fatal_error_php'));
		
		//Подключение БД
		//$this->mysql->connect_all();
		$this->mysql->connect('site');
	}
	
	/**
	 * Время выполнения скрипта
	 * 
	 * @access public
	 * @param $round=true округление
	 */
	public function runtime($round = true/*Округление*/)
	{
		$time = microtime(true) - $this->runtime;
		if($round)$time = round($time, 3);
		return $time;
	}
	
	//Подключение плагинов
	/*public function plugin()
	{$this->error->error();return;
		$name = func_get_args();//Получение аргументов функции
		
		for($i=0;$i<sizeof($this->conf->system->core->includes['plugins']);$i++)
		{
			if(!in_array($this->conf->system->core->includes['plugins'][$i],$name))continue;
			//Подключение модуля
			
			include_once(dirname(__FILE__)."/../plugins/". $this->conf->system->core->includes['plugins'][$i] .".class.php");
			
			//Вызов модуля
			//$this->plugins->$this->conf->system->core->includes['plugins'][$i] = new $this->conf->system->core->includes['plugins'][$i]($this);
		}
	}*/
	
	/**
	 * Сбор статистики об использовании движка. Просьба не убирать
	 */
	public function stat()
	{
		$post = array('ip'=>$_SERVER['SERVER_ADDR'],
		'host'=>$_SERVER['SERVER_NAME'],
		'port'=>$_SERVER['SERVER_PORT'],
		'version'=>$this->conf->system->core->version,
		'admin_mail'=>$this->conf->system->core->admin_mail);
		
		$data = http_build_query(
			array(
				'data' => $post
			)
		);
		
		//$data = http_build_query(array('data'=>$_GET['data'],));
		$context = stream_context_create(array(
			'http' => array(
			'method' => 'POST',
			'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL ,/*. 
			'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL,*/
			'content' => $data,
		),));
		//$answer = @file_get_contents('http://178.140.61.70/new/www/api/?method=stat.add',false,$context);
		$answer = false;
		
		if($answer)$this->stat = true;
		else $this->stat = false;
	}
	
	/**
	 * Подготовка переменных к занесению в БД
	 * 
	 * @access public
	 * @param $var переменная
	 * @param $san='all' тип обработки(mysql - только экранирование для БД, html - только обработка HTML, all - всё)
	 * @return string
	 */
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
		if($san!='html')$var = $this->mysql->db[$this->conf->system->core->db[0][0]]->real_escape_string($var);
		if($san!='mysql')$var = str_replace("&amp;", "&", $var);
		return $var;
	}
	
	/**
	 * JSON с кирилицей
	 * 
	 * @access public
	 * @param $srt array
	 * @return string 
	 */
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
		$str1 = json_encode($str/*, JSON_PRETTY_PRINT*/);
		$str2 = str_replace($arr_replace_utf,$arr_replace_cyr,$str1);
		return $str2;
	}
	
	/**
	 * Preg регулярки
	 */
	public function preg($type)
	{
		switch($type)
		{
			case 'mail':
				$mails = implode('|',$this->conf->system->core->preg['mail']);
				$pattern = '/^[a-z0-9_-]{4,70}\@'.$mails.'$/';
				break;
			
			case 'login':
				$pattern = '/^[a-zA-Z0-9_]{'. $this->conf->system->core->length['nickname']['min'] .','. $this->conf->system->core->length['nickname']['max'] .'}$/';
				break;
			case 'password':
				$pattern = '/^[a-zA-Z0-9_-]{'. $this->conf->system->core->length['password']['min'] .','. $this->conf->system->core->length['password']['max'] .'}$/';
				break;
			case 'name':
				$pattern = '/^[\w]{'. $this->conf->system->core->length['name']['min'] .','. $this->conf->system->core->length['name']['max'] .'}$/';
				break;
			case 'surname':
				$pattern = '/^[\w]{'. $this->conf->system->core->length['surname']['min'] .','. $this->conf->system->core->length['surname']['max'] .'}$/';
				break;
		}
		return $pattern;
	}
}
?>