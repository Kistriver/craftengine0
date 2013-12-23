<?php
namespace CRAFTEngine\core;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class api
{
	public	$data_json,			//Информация в JSON формате
			$data,				//Информация в массиве
			$sid,
			$returned;			//Сгенерированный ответ
	protected $core;				//Ядро
	
	//Массив разрешённых функций
	public $functions = array(
	   #'act'=>'func',
	);

	/**
	 * Инициализация ядра и API
	 *
	 * @param object $core объект ядра, передаётся только дочерним классам API
	 * @param string $module модуль, который надо подключить
	 * @param string $function функция, которую надо вызвать
	 */
	public function __construct($core=null,$module=null,$function=null)
	{
		if(empty($core))
		{
			exit('Empty core resource');
			//require_once(dirname(__FILE__)."/core.class.php");
			//$core = new core();
		}
		
		$this->core = &$core;
		$this->sid = &$this->core->sid;
		
		if(!empty($module) AND !empty($function))
		{
			$mod = $core->conf->system->api->modules;
			$pl = $core->conf->system->api->plugins;
			
			$modules = in_array($module,$mod);
			if(empty($modules) or is_array($modules))
			{
				$core->error->error('api',0);
				echo $this->json();
			}
			else
			{
				$plugin = null;
				foreach($pl as $n=>$p)
				{
					if(array_search($module,$p)!==false)
					{
						$plugin = $n;
					}
				}
				
				if(empty($plugin))
				{
					require_once(dirname(__FILE__)."/api/".$module.".class.php");

					$cl_n = '\CRAFTEngine\api\\'.$module;
					$class = new $cl_n($core);
				}
				else
				{
					require_once($this->core->plugin->root.$plugin."/api/".$module.".class.php");

					//$cl_n = "api_" . $module;
					$cl_n = '\CRAFTEngine\api\\'.$plugin.'\\'.$module;
					$class = new $cl_n($core);
					//$class->init();
				}
				
				if(!isset($class->functions[$function]))
				{
					$core->error->error('api',0);
					echo $this->json();
				}
				else
				{
					$func = $class->functions[$function];

					$type = strtoupper($this->core->core_confs['api']['type']);
					$type_arr = array('GET','POST','PUT','DELETE');
					$this->method = in_array($type,$type_arr)?$type:'GET';
					$class->method($func);
					if(!empty($this->core->core_confs['api']['code']))header('HTTP/1.0 '.$this->core->core_confs['api']['code']);
					echo $class->returned;
				}
			}
		}
		else
		{
			if(method_exists($this,'init'))$this->init();//Если есть метод init, вызвать его
			$this->initalize();//Вызвать инициализацию ядра API
		}
		//$this->core->timer->mark('api.class.php/__construct');
		$this->core->timer->mark('Подключение модуля API');
	}
	
	//Инициализация ядра API
	private function initalize()
	{
		//Получение информации и её декодирование
		$this->data_json = isset($_POST['data'])? $_POST['data'] : (isset($_GET['data'])?$_GET['data']:"{}");
		$this->data = json_decode($this->data_json, true);
		$this->data['sid'] = &$this->sid;


		foreach($this->core->error->error as $er)
		{
			if($er[0]=='api' && $er[1]==3)
			{
				echo $this->json();
				exit();
			}
		}
	}
	
	public function method($m)
	{
		return $this->$m();
	}
	
	//Создание ответа
	public function json($data=array())
	{
		$this->core->timer->stop();
		
		if($this->core->conf->system->core->debug)
		$marks = $this->core->timer->display('marks');
		else
		$marks = array();
		
		$r_a = array(
				'data'=>$data,
				'sid'=>$this->sid,
				'errors'=>$this->core->error->error,
				'runtime'=>array(
								$this->core->timer->display('all'),
								$marks,
								$this->core->timer->display('other'),
								),
		);
		$this->returned = $this->core->json_encode_ru($r_a);
		return $this->returned;
	}
	
	protected function input()
	{
		$args = func_get_args();
		foreach($args as $arg)
		{
			if(!isset($this->data[$arg]))
			{
				$this->core->error->error('api',5);
				echo $this->json($args);
				exit;
			}
		}
	}
	
	//Затычка для ещё неосуществлённого функционала
	protected function wip()
	{
		$this->core->error->error('api',2);
		return $this->json(array(false));
	}
}