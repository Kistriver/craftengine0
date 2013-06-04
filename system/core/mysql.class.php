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
	
	//Подключение к БД
	public function connect_db($name, $host, $db, $user, $pass)
	{
		$mysqli = new mysqli($host,$user,$pass,$db);
		if($mysqli->connect_errno) trigger_error($mysqli->connect_errno, E_USER_ERROR);
		$mysqli->set_charset("utf8");
		$this->db[$name] = $mysqli;
	}
	
	//Подключение к БД только по имени
	public function connect()
	{
		$name = func_get_args();//Получение аргументов функции
		for($i=0;$i<sizeof($this->core->conf->db);$i++)
		{
			if(!in_array($this->core->conf->db[$i][0],$name))continue;
			$this->connect_db($this->core->conf->db[$i][0], 	//Имя
							  $this->core->conf->db[$i][1], 	//Хост
							  $this->core->conf->db[$i][2], 	//БД
							  $this->core->conf->db[$i][3], 	//Пользователь
							  $this->core->conf->db[$i][4]);	//Пароль
		}
	}
	
	//Подключение всех БД
	public function connect_all()
	{
		for($i=0;$i<sizeof($this->core->conf->db);$i++)
		{
			$this->connect_db($this->core->conf->db[$i][0], 
							$this->core->conf->db[$i][1], 
							$this->core->conf->db[$i][2], 
							$this->core->conf->db[$i][3], 
							$this->core->conf->db[$i][4]);
		}
	}
	
	//Запрос к БД
	public function query($query, $name = null)
	{
		if(empty($name))$name = $this->core->conf->db[0][0];//Если не передано имя, использовать имя по умолчанию
		$this->result = $this->db[$name]->query($query) or trigger_error(mysqli_error($this->db[$name]), E_USER_ERROR);
		return $this->result;
	}
	//Возвращает ассоциативный массив последнего запроса
	
	public function fetch($result = null)
	{
		if(empty($result))$result = $this->result;
		if(empty($result))return false;
		return $result->fetch_array(MYSQLI_BOTH);
	}
	
	//Возвращает число строк в последнем запросе
	public function rows($result = null)
	{
		if(empty($result))$result = $this->result;
		if(empty($result))return false;
		return $result->num_rows;
	}
	
	/*public function connect($name, $host, $dbname, $user, $pass)
	{
		$db = mysql_connect($host, $user, $pass) or 
		trigger_error(mysql_error($db), E_USER_ERROR);
		mysql_select_db($dbname, $db) or 
		trigger_error(mysql_error($db), E_USER_ERROR);
		mysql_set_charset("utf8", $db);
		
		$this->db[$name] = $db;
	}
	
	public function connect_all()
	{
		for($i=0;$i<sizeof($this->core->conf->db);$i++)
		{
			$this->connect(	$this->core->conf->db[$i][0], 
							$this->core->conf->db[$i][1], 
							$this->core->conf->db[$i][2], 
							$this->core->conf->db[$i][3], 
							$this->core->conf->db[$i][4]);
		}
	}
	
	public function query($query, $name = null)
	{
		if(empty($name))$name = $this->core->conf->db[0][0];
		$this->result = mysql_query($query, $this->db[$name]) or trigger_error(mysql_error($this->db[$name]), E_USER_ERROR);
		return $this->result;
	}*/
}
?>