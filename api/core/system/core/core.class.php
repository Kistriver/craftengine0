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
error_reporting(E_ALL ^ E_NOTICE);

class core
{
	const PHP_MIN = '5.4.0';
	const CORE_VER = '0.2.3_alpha';

	public $core_confs;
	public $api;
	public $sid;

	final function __construct($confs=array())
	{
		//Добавляем конфиги
		$this->core_confs = $confs;

		//Алиас
		$this->core = &$this;

		//Разбор способа передачи ключа сессии
		if(!empty($this->core_confs['sid']))$this->sid = $this->core_confs['sid'];
		elseif(!empty($this->core_confs['api']))
		{
			if(isset($_POST['sid']))$this->sid = $_POST['sid'];
			elseif(isset($_GET['sid']))$this->sid = $_GET['sid'];
			else $this->sid = false;
		}
		else $this->sid = false;

		//Время старта скрипта
		$start = isset($this->core_confs['start_time'])?$this->core_confs['start_time']:microtime(true);

		//Подходит ли версия PHP
		if(version_compare(PHP_VERSION, self::PHP_MIN) <= 0)
		{
			$j = array('error'=>'Your PHP version is: '.PHP_VERSION.'. But required version above: '.self::PHP_MIN);
			echo json_encode($j);
			exit();
		}
		
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
				$tr['file'] = str_replace($rm,'{{FRAMEWORK_ROOT}}',$tr['file']);
				echo "[$tr[file]:$tr[line]] '$tr[class]' object in function '$tr[function]'\r\n";
			}
			die;
		}
		
		if(!defined('CORECALLONCE'))
		define('CORECALLONCE', true);

		$includes = array(  'timer', 		//Подсчёт времени выполнения скрипта
							'functions',	//Функции
							'file',			//Файлы
							'conf',			//Конфигурации
							'error',		//Ошибки
							'mysql',		//СУБД
							'mail',			//Мыло
							'plugin',		//Плагинридер
						);

		foreach($includes as $inc)
		{
			//Подключение модулей ядра
			require_once(dirname(__FILE__)."/". $inc .".class.php");
			
			//Вызов модулей
			$this->$inc = new $inc($this);
		}

		foreach($includes as $inc)
		{
			if(method_exists($this->$inc,'construct'))$this->$inc->construct();
		}

		//Присваение времени старта скрипта
		$this->timer->start($start);
		//$this->timer->mark('IncludeCoreModules');

		$this->core->timer->mark('Loading core');

		//Сравнение версий ядра и конфига
		if($this->functions->versionCompare(self::CORE_VER,$this->conf->system->core->version)!==0)
		{
			$j = array('error'=>'Core conf file doesn\'t compatible(core version: '.self::CORE_VER.', conf version: '.$this->conf->system->core->version.')');
			echo json_encode($j);
			exit();
		}

		//Проверка на уязвимости
		$this->issetFatalError();

		//Отправка накопленной почты
		$this->mail();

		//Подключение API(если это требуется)
		if(isset($this->core_confs['api']['module'],$this->core_confs['api']['method']))
		{
			require_once(dirname(__FILE__)."/api.class.php");
			$this->api = new api($this,$this->core_confs['api']['module'],$this->core_confs['api']['method']);
		}
	}

	/**
	 * Установка/получение кеша
	 *
	 * @param null $type
	 * @param null $value
	 * @param bool $set
	 * @return array|string
	 */
	final function statCache($type=null, $value=null, $set=true)
	{
		//Путь до файла
		$stat_file_name = empty($this->core->core_confs['cache']['root'])?
						dirname(__FILE__).'/cache/Stat':
						$this->core->core_confs['cache']['root'].'Stat';
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
			file_put_contents($stat_file_name, $file);
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
		$file = empty($this->core->core_confs['cache']['root'])?
			dirname(__FILE__).'/cache/Stat':
			$this->core->core_confs['cache']['root'].'Stat';

		if(file_exists($file))
		{
			$time = (int)$this->statCache('time',null,false);
		}
		else
		{
			$time = time();
			$this->statCache('clear',$time,true);
		}


		$fsl = empty($this->core->core_confs['cache']['root'])?
			dirname(__FILE__).'/cache/StatLock':
			$this->core->core_confs['cache']['root'].'StatLock';


		$ft = file_exists($fsl)?file_get_contents($fsl):0;

		if($time<time()-$updatetime && (int)$ft<time()-60)
		{
			file_put_contents($fsl,time());
			$answer = $this->functions->sysScript('stat.php',1);

			if($answer)$this->stat = true;
			else $this->stat = false;
		}
		else
		{
			$this->stat = false;
		}

		//$this->timer->mark('core.class.php/stat');
	}

	/**
	 * Копирайт. Просьба не убирать
	 */
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
					echo('usage: ?about=(framework|author|version|edition)');
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
		$file = empty($this->core->core_confs['cache']['root'])?
			dirname(__FILE__).'/cache/LastExploitRequest':
			$this->core->core_confs['cache']['root'].'LastExploitRequest';
		
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
		
		$expm = '{"error":"This framework has some exploits, so it\'s been blocked until cover exploits. Last update: '.(time() - $time).'sec ago."}';
		
		if($time<time()-$updatetime)
		{
			$answer = $this->functions->sysScript('exploit.php',10,array('host'=>'stat.kcraft.su','path'=>'/'));
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
		//$this->timer->mark('core.class.php/exploitPrevent');
		$this->timer->mark('Exploit prevent');
	}

	public function mail()
	{
		$fml = empty($this->core->core_confs['cache']['root'])?
			dirname(__FILE__).'/cache/MailLock':
			$this->core->core_confs['cache']['root'].'MailLock';

		$mt = file_exists($fml)?file_get_contents($fml):0;
		
		if($mt<time()-60)
		{
			file_put_contents($fml,time());
			$answer = $this->functions->sysScript('mail.php',0.5);
			fread($answer, 1024);
		}
		
		//$this->timer->mark('core.class.php/mail');
		$this->timer->mark('Mail cron');
	}
	
	/**
	 * Кодирование информации для кеша
	 * 
	 * @access public
	 * @param $data информация
	 * @return array
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
	 * @param $data информация
	 * @return array
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
	public function sanString($var, $san='all',$cycle=0)
	{
		if(is_array($var))
		{
			if($cycle==10)return false;

			foreach($var as $k=>$v)
				$var[$k] = $this->sanString($v,$cycle+1);
			return $var;
		}

		if($san!='mysql')$var_before = Array("<", ">");
		if($san!='mysql')$var_after = Array("&lt;", "&gt;");
		if($san!='mysql')$var = str_replace($var_before, $var_after, $var);
		if($san!='mysql')$var = strip_tags($var);
		if($san!='mysql')$var = htmlentities($var, ENT_COMPAT, 'utf-8');
		//$var = stripslashes($var);
		if($san!='html')@$var = $this->mysql->db['craftengine']->real_escape_string($var);
		if($san!='mysql')$var = str_replace("&amp;", "&", $var);
		return $var;
	}

	public function json_encode_ru($str)
	{
		/*if(!defined('OLDFUNJSON'))
		{
			define('OLDFUNJSON',true);
			$this->error->errorPhp(0,'Old function JSON',__FILE__,__LINE__);
		}*/
		return $this->functions->json($str);
	}
}
