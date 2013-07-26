<?php
class mysql
{
	public	$core,								//Ядро
			$db			=		array(),		//Объекты БД
			$result;							//Результат последнего запроса
	
	public function __construct($core)
	{
		$this->core = $core;
		
		
	}
	
	/**
	 * Подключение к БД
	 */
	public function connect_db($name, $host, $db, $user, $pass)
	{
		$mysqli = new mysqli($host,$user,$pass,$db);
		if($mysqli->connect_errno) trigger_error($mysqli->connect_errno, E_USER_ERROR);
		$mysqli->set_charset("utf8");
		$this->db[$name] = $mysqli;
	}
	
	/**
	 * Подключение к БД только по имени
	 * 
	 * @access public
	 * @param unsizable
	 */
	public function connect()
	{
		$name = func_get_args();//Получение аргументов функции
		for($i=0;$i<sizeof($this->core->conf->system->core->db);$i++)
		{
			if(!in_array($this->core->conf->system->core->db[$i][0],$name))continue;
			$this->connect_db($this->core->conf->system->core->db[$i][0], 	//Имя
							  $this->core->conf->system->core->db[$i][1], 	//Хост
							  $this->core->conf->system->core->db[$i][2], 	//БД
							  $this->core->conf->system->core->db[$i][3], 	//Пользователь
							  $this->core->conf->system->core->db[$i][4]);	//Пароль
		}
	}
	
	/**
	 * Подключение всех БД
	 */
	public function connect_all()
	{
		for($i=0;$i<sizeof($this->core->conf->system->core->db);$i++)
		{
			$this->connect_db($this->core->conf->system->core->db[$i][0], 
							$this->core->conf->system->core->db[$i][1], 
							$this->core->conf->system->core->db[$i][2], 
							$this->core->conf->system->core->db[$i][3], 
							$this->core->conf->system->core->db[$i][4]);
		}
	}
	
	/**
	 * Запрос к БД
	 */
	public function query($query, $name = null)
	{
		try
		{
		if(empty($name))$name = $this->core->conf->system->core->db[0][0];//Если не передано имя, использовать имя по умолчанию
		if(!$this->result = $this->db[$name]->query($query))throw new Exception(mysqli_error($this->db[$name]));
		
		return $this->result;
		}
		catch(exception $e)
		{
			$tr = $e->getTrace();
			
			$this->core->error->error_php(E_USER_ERROR,$e->getMessage(),$tr[0]['file'],$tr[0]['line']);
		}
	}
	
	/**
	 * Возвращает ассоциативный массив
	 */
	public function fetch($result = null)
	{
		if(empty($result))$result = $this->result;
		if(empty($result))return false;
		return $result->fetch_array(MYSQLI_BOTH);
	}
	
	/**
	 * Возвращает число строк в запросе
	 */
	public function rows($result = null)
	{
		if(empty($result))$result = $this->result;
		if(empty($result))return false;
		return $result->num_rows;
	}
}
?>