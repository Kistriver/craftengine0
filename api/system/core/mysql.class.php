<?php
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class mysql
{
	public			$db							= array(),//Объекты БД
					$result;//Результат последнего запроса
	
	protected		$core;
	protected       $lock = false;

	public function __construct($core)
	{
		$this->core = &$core;

		//Подключение БД
		$this->connect('site');

		$this->core->timer->mark('mysql.class.php/__construct');
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

	public function is_connect($name)
	{
		if(isset($this->db[$name]))
		if(is_object($this->db[$name]))
		return true;

		return false;
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
			$db = $this->core->conf->system->core->db[$i];

			if(!in_array($this->core->conf->system->core->db[$i][0],$name))continue;

			//Rewrite databases keys
			if(!isset($db[2]))
			{
				$this->connect($db[1]);
				continue;
			}

			if($this->is_connect($db[0]))continue;

			$this->connect_db($db[0], 	//Имя
							  $db[1], 	//Хост
							  $db[2], 	//БД
							  $db[3], 	//Пользователь
							  $db[4]);	//Пароль
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
		if($this->lock)return;
		try
		{
		if(empty($name))$name = $this->core->conf->system->core->db[0][0];//Если не передано имя, использовать имя по умолчанию

		if(!$this->is_connect($this->core->conf->system->core->db[0][0]))
		{
			$this->lock = true;
			throw new Exception('Query abort: no connection');
		}

		if(!$this->result = $this->db[$name]->query($query))throw new Exception(mysqli_error($this->db[$name]));

		return $this->result;
		}
		catch(exception $e)
		{
			$tr = $e->getTrace();

			$this->core->error->error_php(E_USER_ERROR,$e->getMessage(),$tr[0]['file'],$tr[0]['line']);

			//return false;
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