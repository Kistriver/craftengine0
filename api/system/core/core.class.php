<?php
/**
 * @package core
 * @copyright Alexey Kachalov <alex-kachalov@mail.ru>
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */

ini_set('display_errors',"1");
ini_set('display_startup_errors',"1");
ini_set('log_errors',"1");
ini_set('html_errors',"0");
date_default_timezone_set('GMT');
//error_reporting(E_ALL ^ E_NOTICE);

class core
{
	final function __construct()
	{
		//Подходит ли версия PHP
		$php_min = '5.3.0';
		if(version_compare(PHP_VERSION, $php_min) <= 0)
		{
			$j = array('error'=>'Your PHP version is: '.PHP_VERSION.'. But required version above: '.$php_min);
			echo json_encode($j);
			exit();
		}

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
							'functions',	//Функции
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

		if($this->functions->version_compare('0.1.6_alpha',$this->conf->system->core->version)!==0)
		{
			$j = array('error'=>'Core conf file doesn\'t compatible');
			echo json_encode($j);
			exit();
		}

		$this->issetFatalError();
		$this->mail();
	}

	final function statCache($type=null, $value=null, $set=true)
	{
		$stat_file_name = dirname(__FILE__).'/cache/Stat';
		if($set===true)
		{
			$file = file_exists($stat_file_name)
					?file_get_contents($stat_file_name):
					"\r\n:\r\n";
			$file_p = explode("\r\n:\r\n",$file);
			/*
			 * 0 - last request
			 * 1 - error:time
			 */

			if(sizeof($file_p)<2)
			{
				$file_p[0] = empty($file_p[0])?'':$file_p[0];
				$file_p[1] = empty($file_p[1])?'':$file_p[1];
			}

			switch($type)
			{
				case 'error':
					if(str_replace("\r\n",'',$file_p[1]))
						$file_p[1] = explode("\r\n",$file_p[1]);
					$file_p[1][] = $this->cacheDataEncode($value);
					$file_p[1] = implode("\r\n",$file_p[1]);
					break;

				case 'time':
					$file_p[0] = $this->cacheDataEncode($value);
					break;

				case 'clear':
					$file_p[0] = $this->cacheDataEncode(time());
					$file_p[1] = "";
					break;
			}

			$file = implode("\r\n:\r\n",$file_p);
			file_put_contents(dirname(__FILE__).'/cache/Stat', $file);
		}
		else
		{
			$file = file_exists($stat_file_name)
				?file_get_contents($stat_file_name):
				"\r\n:\r\n";
			$file_p = explode("\r\n:\r\n",$file);

			switch($type)
			{
				case 'error':
					$file_p[1] = explode("\r\n",$file_p[1]);
					foreach($file_p[1] as &$i)
					{
						$i = $this->cacheDataDecode($i);
					}
					return $file_p[1];
					break;

				case 'time':
					return $this->cacheDataDecode($file_p[0]);
					break;
			}
		}
	}

	/**
	 * Сбор статистики об использовании движка. Просьба не убирать
	 */
	final function stat()
	{
		$this->about();
		$updatetime = 60 * 60 * 12;
		$file = dirname(__FILE__).'/cache/Stat';

		if(file_exists($file))
		{
			$time = (int)$this->statCache('time',null,false);
		}
		else
		{
			$time = time();
			$this->statCache('clear',$time,true);
		}
		
		$ft = file_exists(dirname(__FILE__).'/cache/StatLock')?file_get_contents(dirname(__FILE__).'/cache/StatLock'):0;
		
		if($time<time()-$updatetime && (int)$ft<time()-60)
		{
			file_put_contents(dirname(__FILE__).'/cache/StatLock',time());
			$answer = fsockopen($this->conf->system->core->system_scripts[0], $this->conf->system->core->system_scripts[1]);
			stream_set_timeout($answer, 0, 10 * 1000);
			fwrite($answer, "GET ".$this->conf->system->core->http_root."system-scripts/stat.php HTTP/1.0\r\n\r\n");
			fread($answer, 1024);
			
			if($answer)$this->stat = true;
			else $this->stat = false;
		}
		else
		{
			$this->stat = false;
		}

		$this->timer->mark('core.class.php/stat');
	}
	
	final function about()
	{
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
				case 'edition':
					die($this->conf->system->core->name);
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
			$fe = explode("\r\n",$f);
			
			if(sizeof($fe)<2)
			{
				$fe[0] = empty($fe[0])?'':$fe[0];
				$fe[1] = empty($fe[1])?'':$fe[1];
			}
			
			list($time,$status) = $fe;
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
			$answer = fsockopen("stat.kcraft.su", 80);
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

	public function mail()
	{
		$mt = file_exists(dirname(__FILE__).'/cache/MailLock')?file_get_contents(dirname(__FILE__).'/cache/MailLock'):0;
		
		if($mt<time()-6)
		{
			file_put_contents(dirname(__FILE__).'/cache/MailLock',time());
			$answer = fsockopen($this->conf->system->core->system_scripts[0], $this->conf->system->core->system_scripts[1]);
			stream_set_timeout($answer, 0, 10 * 1000);
			fwrite($answer, "GET ".$this->conf->system->core->http_root."system-scripts/mail.php HTTP/1.0\r\n\r\n");
			fread($answer, 1024);
		}
		
		$this->timer->mark('core.class.php/mail');
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
		$data = @base64_decode($data);
		$data = @unserialize($data);
		
		$da = '';
		if(!is_array($data))return false;
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

	public function json_encode_ru($str)
	{
		/*if(!defined('OLDFUNJSON'))
		{
			define('OLDFUNJSON',true);
			$this->error->error_php(0,'Old function JSON',__FILE__,__LINE__);
		}*/
		return $this->functions->json($str);
	}
}