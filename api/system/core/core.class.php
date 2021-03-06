<?php
namespace CRAFTEngine\core;
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
	const CORE_VER = '0.3.0r2_alpha';
	const MIN_CORE_VER = '0.3.0r_alpha';

	private $core_confs;
	public $api;
	public $sid;

	final function __construct($confs=array())
	{
		//Добавляем конфиги
		$this->core_confs = $confs;

		//Алиас
		$this->core = &$this;

		if(empty($this->core->core_confs['root']))
		{
			echo('Core parameter \'root\' not found');
			exit();
		}

		//Разбор способа передачи ключа сессии
		if(!empty($this->core_confs['sid']))$this->sid = $this->core_confs['sid'];
		elseif(!empty($this->core_confs['api']))
		{
			if(isset($_POST['sid']))$this->sid = $_POST['sid'];
			elseif(isset($_GET['sid']))$this->sid = $_GET['sid'];
			else $this->sid = false;
		}
		elseif(!empty($_COOKIE['PHPSESSID']))$this->sid = $_COOKIE['PHPSESSID'];
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
			throw new \Exception("CORE MAY BE CALLED ONLY ONCE", 1);
		}
		catch(\Exception $e)
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
							'utilities',	//Утилиты
						);

		foreach($includes as $inc)
		{
			//Подключение модулей ядра
			require_once(dirname(__FILE__)."/". $inc .".class.php");

			//Вызов модулей
			$name = '\CRAFTEngine\core\\'.$inc;
			$this->$inc = new $name($this);
		}

		foreach($includes as $inc)
		{
			if(method_exists($this->$inc,'construct'))$this->$inc->construct();
		}

		//Присвоение времени старта скрипта
		$this->timer->start($start);
		//$this->timer->mark('IncludeCoreModules');

		$this->core->timer->mark('Загрузка модулей ядра');

		if($this->functions->versionCompare(self::MIN_CORE_VER,$this->conf->system->core->version)===1)
		{
			$j = array('error'=>'Core conf file doesn\'t compatible(core version: '.self::CORE_VER.', conf version: '.$this->conf->system->core->version.')');
			echo json_encode($j);
			exit();
		}

		$this->core->timer->mark('Сравнение версий');

		//Проверка на уязвимости
		$this->issetFatalError();

		//Отправка накопленной почты
		$this->mail();

		//Подключение API(если это требуется)
		if(isset($this->core_confs['api']['module'],$this->core_confs['api']['method']))
		{
			require_once(dirname(__FILE__)."/api.class.php");
			$this->api = new api(array('core'=>$this,'plugin'=>$this->core_confs['api']['plugin'],'module'=>$this->core_confs['api']['module'],'method'=>$this->core_confs['api']['method']));
		}
	}

	public function getParams()
	{
		return $this->core_confs;
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
		$stat_file_name = $this->core->core_confs['root'].'cache/Stat';

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
		$file = $this->core->core_confs['root'].'cache/Stat';

		if(file_exists($file))
		{
			$time = (int)$this->statCache('time',null,false);
		}
		else
		{
			$time = time();
			$this->statCache('clear',$time,true);
		}

		$fsl = $this->core->core_confs['root'].'cache/StatLock';

		$ft = file_exists($fsl)?file_get_contents($fsl):0;

		if($time<time()-$updatetime && (int)$ft<time()-60)
		{
			file_put_contents($this->core->getParams()['root'].'cache/StatLock',time());
			$this->statCache('time',time(),true);
			$answer = $this->functions->sysScript('system','stat',10);
			$ans=fread($answer,2048);

			if($ans)$this->stat = true;
			else $this->stat = false;
		}
		else
		{
			$this->stat = false;
		}

		$this->core->timer->mark('Сбор статистики('. ($updatetime - (time() - $time)) .'sec)');
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
					die(self::CORE_VER);
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
		$enable = isset($this->core->conf->system->core->{'exploit-search'})?$this->core->conf->system->core->{'exploit-search'}:true;
		if(!$enable)return;

		$updatetime = 60 * 10;
		$file = $this->core->core_confs['root'].'cache/Exploit';

		if(file_exists($file))
		{
			$f = file_get_contents($file);
			$j = json_decode($f,true);

			if($j===false)
			{
				$time = time() - $updatetime - 10;
				$status = array('status'=>true);
				$msg = null;
			}
			else
			{
				$time = isset($j['time'])?$j['time']:(time() - $updatetime - 10);
				$status = isset($j['status'])?$j['status']:true;
				$msg = isset($j['msg'])?$j['msg']:null;
			}
		}
		else
		{
			$time = time() - $updatetime - 10;
			$status = true;
			$msg = null;
		}

		$expm = 'This version of framework has some exploits, so it\'s been blocked until cover exploits. Last update: '.(time() - $time).'sec ago.';
		$msg = $msg===null?$expm:$msg;

		if($time<time()-$updatetime)
		{
			$j['time'] = time();
			file_put_contents($file,json_encode($j));

			$version = self::CORE_VER;
			$url = 'http://stat.kcraft.su/api/v5/stat/exploit/get/craftengine/{"product":"CRAFTEngine","version":"'.$version.'"}';
			$context = stream_context_create
			(
				array
				(
					'http' => array
					(
						'method' => 'GET',
						'header' => 'Content-Type: application/x-www-form-urlencoded'.PHP_EOL.
							'User-agent: CraftEngine('.$this->conf->system->core->version.')',
					)
				)
			);

			$answer = @file_get_contents($url,false,$context);

			$answer_decode = json_decode($answer, true);
			if(!$answer_decode)
			{

			}
			else
			{
				if(sizeof($answer_decode['errors'])==0)
				{
					$answer_decode['data']['time'] = time();
					file_put_contents($this->getParams()['root'].'cache/Exploit',json_encode($answer_decode['data']));
				}
			}

			$this->issetFatalError();
		}

		if($status===false)
		{
			die(json_encode(array('error'=>$msg),JSON_UNESCAPED_UNICODE));
		}

		$this->core->timer->mark('Проверка на наличие эксплоитов');
	}

	public function mail()
	{
		$fml = $this->core->core_confs['root'].'cache/MailLock';

		$mt = file_exists($fml)?file_get_contents($fml):0;
		
		if($mt<time()-60)
		{
			file_put_contents($fml,time());
			$answer = $this->functions->sysScript('system','mail',1);
			fread($answer, 1024);
		}
		
		$this->core->timer->mark('Отправка почты ('. (60-(time()-$mt)) .'sec)');
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
	 * @param $cycle=0
	 * @return string
	 */
	public function sanString($var, $san='mysql',$cycle=0)
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
