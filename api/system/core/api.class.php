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
	public function __construct($core_confs/*$core=null,$module=null,$function=null*/)
	{
		$this->core_confs = $core_confs;
		if(!isset($core_confs['core']))
		{
			exit('Empty core resource');
		}

		$format = isset($core_confs['format'])?$core_confs['format']:'json';

		$module = isset($core_confs['module'])?$core_confs['module']:null;
		$function = isset($core_confs['method'])?$core_confs['method']:null;
		$plugin = isset($core_confs['plugin'])?$core_confs['plugin']:false;

		$this->core = &$core_confs['core'];
		$this->sid = &$this->core->sid;
		
		if(!empty($module) AND !empty($function))
		{
			$mod = $this->core->conf->system->api->modules;
			//$pl = $this->core->conf->system->api->plugins;
			
			$modules = in_array($module,$mod);
			if(empty($modules) or is_array($modules))
			{
				$this->core->error->error('api',0);
				echo $this->json();
			}
			else
			{
				/*$plugin = null;
				foreach($pl as $n=>$p)
				{
					if(array_search($module,$p)!==false)
					{
						$plugin = $this->core->plugin->pluginsLoaded[$n];
						$plugin_folder = $n;
					}
				}*/
				$plugin_folder = null;
				foreach($this->core->plugin->pluginsLoaded as $n=>$p)
				{
					if($p->name==$plugin)
					{
						$plugin_folder = $n;
						$plugin = $this->core->plugin->pluginsLoaded[$n];
					}
				}

				if($plugin_folder===null && !in_array($plugin,array('system')))
				{
					$this->core->error->error('api',6);
					echo $this->json();
					exit;
				}
				
				if(empty($plugin_folder))
				{
					require_once(dirname(__FILE__)."/../api/".$module.".class.php");

					$cl_n = '\CRAFTEngine\api\\'.$module;
					$class = new $cl_n(array('core'=>$this->core));
				}
				else
				{
					require_once($this->core->plugin->root.$plugin_folder."/api/".$module.".class.php");

					//$cl_n = "api_" . $module;
					$cl_n = '\CRAFTEngine\api\\'.$plugin->name.'\\'.$module;
					$class = new $cl_n(array('core'=>$this->core));
					//$class->init();
				}
				
				if(!isset($class->functions[$function]))
				{
					$this->core->error->error('api',0);
					echo $this->json();
				}
				else
				{
					$func = $class->functions[$function];

					$type = strtoupper($this->core->getParams()['api']['type']);
					$type_arr = array('GET','POST','PUT','DELETE');
					$this->method = in_array($type,$type_arr)?$type:'GET';
					$return = $class->method($func);
					if(!empty($this->core->getParams()['api']['code']))header('HTTP/1.0 '.$this->core->getParams()['api']['code']);

					//ob_end_clean();
					echo $this->json($return);
					//ob_end_flush();
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
			'memory'=>memory_get_usage(),
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
		$this->core->error->error('api',7);
		return (array(false));
	}

	protected function niy()
	{
		$this->core->error->error('api',2);
		return (array(false));
	}
}