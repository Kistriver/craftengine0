<?php
class api
{
	public	$data_json,			//Информация в JSON формате
			$data,				//Информация в массиве
			$returned,			//Сгенерированный ответ
			$core;				//Ядро
	
	//Массив разрешённых функций
	public $functions = array(
				   #'funct'=>'act',
	);
	
	public function __construct()
	{
		ini_set('session.gc_maxlifetime', 120960);
		ini_set('session.cookie_lifetime', 120960);
		
		if(method_exists($this,'init'))$this->init();//Если есть метод init, вызвать его
		$f = $this->initalize();//Вызвать инициализацию ядра API
		if($f==false)$this->json();//Если неинициализировано, отобразить ошибку
	}
	
	//Инициализация ядра API
	public function initalize()
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
			$sid_err = 2;
			
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
		
		include_once(dirname(__FILE__)."/core.class.php");//Подключение ядра
		$this->core = new core();//Вызов ядра
		
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
		
		//Разрешена ли функция
		$func = array_search($this->data['act'],$this->functions);
		if(empty($func) or is_array($this->data['act']))
		{
			$this->core->error->error('api','001');
			return false;
		}
		else
		{
			$this->$func();
		}
		
		return true;
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
	
	//Затычка для ещё неосуществлённого функционала
	protected function wip()
	{
		$this->core->error->error('api','002');
		return $this->json(array());
	}
}
?>