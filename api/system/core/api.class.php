<?php
class api
{
	public	$data_json,			//Информация в JSON формате
			$data,				//Информация в массиве
			$returned,			//Сгенерированный ответ
			$core;				//Ядро
	
	//Массив разрешённых функций
	public $functions = array(
	   #'act'=>'func',
	);
	
	/**
	 * Инициализация ядра и API
	 * 
	 * @access public
	 * @param object $core[optional] объект ядра, передаётся только дочерним классам API
	 * @param $module[optional] модуль, который надо подключить
	 * @param $functions[optional] функция, которую надо вызвать
	 * @return void
	 */
	public function __construct($core=null,$module=null,$function=null)
	{
		if(empty($core))
		{
			include_once(dirname(__FILE__)."/core.class.php");
			$core = new core();
		}
		
		$this->core = &$core;
		
		if(!empty($module) AND !empty($function))
		{
			$mod = $core->conf->system->api->modules;
			$pl = $core->conf->system->api->plugins;
			
			$modules = in_array($module,$mod);
			if(empty($modules) or is_array($modules))
			{
				$core->error->error('api','000');
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
				include_once(dirname(__FILE__)."/../../".$module.".class.php");
				else
				include_once(dirname(__FILE__)."/../plugins/".$plugin."/api/".$module.".class.php");
				
				$cl_n = "api_" . $module;
				$class = new $cl_n($core);
				$class->init();
				
				//$func = array_search($m_f[1],$class->functions);
				//if(empty($func))
				if(!isset($class->functions[$function]))
				{
					$core->error->error('api','001');
					echo $this->json();
				}
				else
				{
					$func = $class->functions[$function];
					$class->method($func);
					echo $class->returned;
				}
			}
		}
		else
		{
			if(method_exists($this,'init'))$this->init();//Если есть метод init, вызвать его
			$f = $this->initalize();//Вызвать инициализацию ядра API
			if($f==false)$this->json();//Если неинициализировано, отобразить ошибку
		}
	}
	
	//Инициализация ядра API
	private function initalize()
	{
		//Получение информации и её декодирование
		$this->data_json = isset($_POST['data'])? $_POST['data'] : $_GET['data'];
		$this->data = json_decode($this->data_json, true);
		
		//Создание сессии
		$sid_err = 0;
		if(empty($this->data['sid']))
		{
			session_start();
			session_regenerate_id();
			$this->data['sid'] = session_id();
			$sid_err = 1;
			
			/*session_start();
			$this->data['sid'] = session_id();*/
		}
		else
		{			
			//$s = session_id($this->data['sid']);
			session_id($this->data['sid']);
			//if(empty($s))session_start();
			session_start();
			
			if(!isset($_SESSION['ip']))
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
			
			if($_SERVER['REMOTE_ADDR']!=$_SESSION['ip'])
			$sid_err = 2;
		}
		
		//include_once(dirname(__FILE__)."/core.class.php");//Подключение ядра
		//$this->core = new core();//Вызов ядра
		
		if($sid_err!=0)
		{
			switch($sid_err)
			{
				case 1:
					$this->data['sid'] = session_id();
					$this->core->error->error('api','003');
					break;
				case 2:
					$this->core->error->error('engine','004');
					break;
			}
			echo $this->json();
			die();
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
	}
	
	public function method($m)
	{
		return $this->$m();
	}
	
	//Создание ответа
	public function json($data=array())
	{
		$r_a = array(
				'data'=>$data,
				'sid'=>$this->data['sid'],
				'errors'=>$this->core->error->error,
				'runtime'=>$this->core->runtime(true),
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
				$this->core->error->error('api','005');
				echo $this->json($args);
				die;
				//return false;
			}
		}
	}
	
	//Затычка для ещё неосуществлённого функционала
	protected function wip()
	{
		$this->core->error->error('api','002');
		return $this->json(array());
	}
}
?>