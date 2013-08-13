<?php
ini_set('display_errors',"1");
ini_set('display_startup_errors',"1");
ini_set('log_errors',"1");
ini_set('html_errors',"0");

/**
 * @package core
 * @copyright Alexey Kachalov <alex-kachalov@mail.ru>
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://178.140.61.70/
 */
class core
{
	final function __construct()
	{
		//Подключение модуля ядра timer
		require_once(dirname(__FILE__)."/". "timer" .".class.php");
		
		//Вызов модуля
		$this->timer = new timer($this);
		
		$this->timer->start();
		
		ob_start();
		
		try
		{
			if(defined('CORECALLONCE'))
			throw new Exception("CORE MAY BE CALLED ONLY ONCE", 1);
		}
		catch(Exception $e)
		{
			echo $e->getMessage()."\r\n";
			echo "#Trace: \r\n";
			$rm = str_replace('/system/core','',dirname(__FILE__));
			
			foreach($e->getTrace() as $tr)
			{
				$tr['file'] = str_replace($rm,'{{FRAMEWORK_ROOT}}/',$tr['file']);
				echo "[$tr[file]:$tr[line]] '$tr[class]' object in function '$tr[function]'\r\n";
			}
			die;
		}
		
		if(!defined('CORECALLONCE'))
		define('CORECALLONCE', true);
		
		$includes = array(//'timer', 		//Подсчёт времени выполнения скрипта	//ALREADY INCLUDED
							'file',			//Файлы
							'conf',			//Конфигурации
							'error',		//Ошибки
							'mysql',		//СУБД
							'mail',			//Мыло
							'plugin',		//Плагинридер
						);
		
		for($i=0;$i<sizeof($includes);$i++)
		{
			//Подключение модулей ядра
			require_once(dirname(__FILE__)."/". $includes[$i] .".class.php");
			
			//Вызов модулей
			$this->$includes[$i] = new $includes[$i]($this);
		}
		$this->timer->mark('IncludeCoreModules');
		
		$this->issetFatalError();
	}
	
	/**
	 * Сбор статистики об использовании движка. Просьба не убирать
	 */
	final function stat()
	{
		$this->about();
		
		/**
		 * TODO: Доделать
		 * TODO: Сделать защиту от удаления
		 */
		
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
		/*$answer = fsockopen("178.140.61.70", 80);
		stream_set_timeout($answer, 0, 10);
		fwrite($answer, "GET /api/?method=stat.add HTTP/1.0\r\n\r\n");*/
		//print_r(fread($answer, 2000));
		
		$answer = false;
		
		if($answer)$this->stat = true;
		else $this->stat = false;
		$this->timer->mark('core.class.php/stat');
	}
	
	final function update()
	{
		$updatetime = 60 * 60 *12;
		$file = dirname(__FILE__).'/cache/LastUpdateRequest';
		
		if(file_exists($file))
		$time = file_get_contents($file);
		else
		$time = null;
		
		$time = trim($time);
		
		if(!empty($time))
		{
			$time = $this->cacheDataDecode($time);
			$time = (int)$time;
		}
		else
		{
			$time = time() - $updatetime - 10;
		}
		
		if($time<time()-$updatetime)
		{
			$context = stream_context_create();
			$answer = fsockopen("localhost", 8081);
			stream_set_timeout($answer, 0, 10);
			fwrite($answer, "GET /system-scripts/update.php HTTP/1.0\r\n\r\n");
			//print_r(fread($answer, 2048));
			
			$data = $this->cacheDataEncode(time());
			file_put_contents($file, $data);
		}
		$this->timer->mark('core.class.php/update');
	}
	
	final function about()
	{
		$this->update();
		if(!empty($_GET['about']))
		{
			$a = $_GET['about'];
			switch($a)
			{
				case 'framework':
					die('CRAFTEngine Framework');
					break;
				case 'author':
					die('Alexey Kachalov <alex-kachalov@mail.ru>');
					break;
				case 'version':
					die($this->conf->system->core->version);
					break;
				default:
					exit;
					break;
			}
		}
	}
	
	/**
	 * В случае, если в движке будет найдена уязвимость, то доступ к нему будет закрыть уже через 10 минут, после обнаружения уязвимости
	 */
	public function issetFatalError()
	{
		$updatetime = 60 * 10;
		$file = dirname(__FILE__).'/cache/LastExploitRequest';
		
		if(file_exists($file))
		{
			$f = file_get_contents($file);
			list($time,$status) = explode("\r\n",$f);
		}
		else
		{
			$time = null;
		}
		
		$time = trim($time);
		
		if(!empty($time))
		{
			$time = $this->cacheDataDecode($time);
			$time = (int)$time;
			
			$status = $this->cacheDataDecode($status);
		}
		else
		{
			$time = time() - $updatetime - 10;
			$status = 'NO';
		}
		
		$expm = '{"error":"This framework has some exploits, so it\'s been blocked until cover exploits"}';
		
		if($time<time()-$updatetime)
		{
			$answer = fsockopen("178.140.61.70", 8081);
			stream_set_timeout($answer, 2);
			fwrite($answer, "GET /system-scripts/exploit.php HTTP/1.0\r\n\r\n");
			$ans = fread($answer, 1024);
			$ans = explode("\r\n", $ans);
			
			$data = $this->cacheDataEncode(time());
			$st = $this->cacheDataEncode($ans[sizeof($ans)-1]);
			$data = implode("\r\n",array($data,$st));
			file_put_contents($file, $data);
			
			if($ans[sizeof($ans)-1]=='YES')
			{
				die($expm);
			}
		}
		
		if($status=='YES')
		{
			die($expm);
		}
		$this->timer->mark('core.class.php/exploitPrevent');
	}
	
	/**
	 * Кодирование информации для кеша
	 * 
	 * @access public
	 * @param $data инормация
	 * @return 
	 */
	public function cacheDataEncode($data)
	{
		$data = base64_encode($data);
		$data = unpack('c*',$data);
		$data = serialize($data);
		$data = base64_encode($data);
		return $data;
	}
	
	/**
	 * Декодирование информации для кеша
	 * 
	 * @access public
	 * @param $data инормация
	 * @return 
	 */
	public function cacheDataDecode($data)
	{
		$data = base64_decode($data);
		$data = unserialize($data);
		
		$da = '';
		foreach($data as $d)
		$da .= pack('c*',$d);
		
		$data = base64_decode($da);
		return $data;
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
				$pattern = '/^[a-zA-Z0-9_]{'. $this->conf->plugins->user->user->length['nickname']['min'] .','. $this->conf->plugins->user->user->length['nickname']['max'] .'}$/';
				break;
			case 'password':
				$pattern = '/^[a-zA-Z0-9_-]{'. $this->conf->plugins->user->user->length['password']['min'] .','. $this->conf->plugins->user->user->length['password']['max'] .'}$/';
				break;
			case 'name':
				$pattern = '/^[\w]{'. $this->conf->plugins->user->user->length['name']['min'] .','. $this->conf->plugins->user->user->length['name']['max'] .'}$/';
				break;
			case 'surname':
				$pattern = '/^[\w]{'. $this->conf->plugins->user->user->length['surname']['min'] .','. $this->conf->plugins->user->user->length['surname']['max'] .'}$/';
				break;
		}
		return $pattern;
	}
}
?>