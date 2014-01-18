<?php
namespace CRAFTEngine\core;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class mysql
{
	const DB_NAME = 'craftengine';

	public			$db							= array(),//Объекты БД
					$result;//Результат последнего запроса
	
	protected		$core;
	public			$lock = false;

	public function __construct($core)
	{
		$this->core = &$core;

		//$this->core->timer->mark('mysql.class.php/__construct');
	}

	public function construct()
	{
		//Подключение БД
		if(empty($this->core->conf->system->core->db[self::DB_NAME]))
		{
			$j = array('error'=>'DB with name \''.self::DB_NAME.'\' not found');
			echo json_encode($j);
			exit();
		}

		$this->connect(self::DB_NAME);
	}

	/*public function dbName()
	{
		return self::DB_NAME;
	}*/

	/**
	 * Подключение к БД
	 */
	public function connectDb($name, $host, $db, $user, $pass)
	{
		$mysqli = new \mysqli($host,$user,$pass,$db);
		if($mysqli->connect_errno)
		{
			//trigger_error($mysqli->connect_errno, E_USER_ERROR);
			$this->db[$name] = false;
			$this->core->timer->mark('Подключение к БД');
			return false;
		}
		else
		{
			$mysqli->set_charset("utf8");
			//$mysqli->query("SET timezone = '+0:00'");
			$this->db[$name] = $mysqli;
			$this->core->timer->mark('Подключение к БД');
			return true;
		}
	}

	public function isConnect($name)
	{
		if(isset($this->db[$name]))
		{
			if($this->db[$name]===false)
			{
				return false;
			}
			elseif(is_object($this->db[$name]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
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
		foreach($this->core->conf->system->core->db as $n=>$i)
		{
			if(!in_array($n,$name))continue;

			//Rewrite databases keys
			if(!empty($i['alias']))
			{
				$this->connect($i['alias']);
				$this->db[$n] = &$this->db[$i['alias']];

				/*unset($this->core);
				print_r($this);die;*/
				continue;
			}

			if($this->isConnect($n))continue;

			$this->connectDb($n, $i['host'], $i['db'], $i['user'], $i['pass']);
		}
	}

	/**
	 * Подключение всех БД
	 */
	public function connectAll()
	{
		foreach($this->core->conf->system->core->db as $name=>$i)
		{
			$this->connectDb($name, $i['host'], $i['db'], $i['user'], $i['pass']);
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
		if(empty($name))$name = self::DB_NAME;//Если не передано имя, использовать имя по умолчанию

		if(!$this->isConnect($name))
		{
			$this->lock = true;
			throw new \Exception('Query abort: no connection');
		}

		if(!$this->result = $this->db[$name]->query($query))throw new \Exception(mysqli_error($this->db[$name]));

		$this->core->timer->mark('SQL запрос');

		return $this->result;
		}
		catch(\exception $e)
		{
			$tr = $e->getTrace();

			$this->core->error->errorPhp(E_USER_ERROR,$e->getMessage(),$tr[0]['file'],$tr[0]['line']);

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