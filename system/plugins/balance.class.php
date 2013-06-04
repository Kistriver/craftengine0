<?php
class balance
{
	public $error;
	public $time;
	public $username;
	public $balance;
	public $error_name;
	
	public function __construct()
	{
		
	}
	
	public function error($err)
	{
		switch($err)
		{
			case 1: $name = "Дублирование записи во временной таблице";break;
			case 2: $name = "Не удалось соединиться с основной базой данных";break;
			case 3: $name = "Запись не найдена в основной базе данных";break;
			case 4: $name = "Дублирование записи в основной таблице";break;
			case 5: $name = "Не найдена запись во временной таблице";break;
			case 6: $name = "Дублирование записи во временной таблице";break;
			case 7: case 1;break;
			case 8: case 3;break;
			case 9: case 4;break;
			default: return false;break;
		}
		return $this->error_name=$name;
	}
	
	public function get($login, $server)
	{
		$query = queryMysql("SELECT * FROM iconomy_$server WHERE username='$login'");
		$nums = mysql_num_rows($query);
		if($nums>1)//0 OR 1
		{
			$this->error = 1;
			return false;
		}
		
		$result = mysql_fetch_array($query);
		if($result['time']<time()-600 or $nums==0)
		{
			if(!isset($_DB_[$server]) or $_DB_[$server]=="ERROR")
			{
				$this->error = 2;
				return false;
			}
			$query = queryMysql("SELECT * FROM iconomy WHERE username='$login'", $server);
			$nums_serv = mysql_num_rows($query);
			if($nums_serv==0)
			{
				$this->error = 3;
				return false;
			}
			
			if($nums_serv>1)
			{
				$this->error = 4;
				return false;
			}
			
			$result = mysql_fetch_array($query);
			$time = time();
			if($nums==0)
			queryMysql("INSERT INTO iconomy_$server(username, balance, status, time) VALUES('$result[username]','$result[balance]','$result[status]', '$time')");
			else
			queryMysql("UPDATE iconomy_$server SET balance='$result[balance]', status='$result[status]', time='$time' WHERE username='$login'");
		}
		$query = queryMysql("SELECT * FROM iconomy_$server WHERE username='$login'");
		if($nums==0)
		{
			$this->error = 5;
			return false;
		}
		if($nums>1)
		{
			$this->error = 6;
			return false;
		}
		$result = mysql_fetch_array($query);
		$this->time = $result['time'];
		$this->username = $result['username'];
		$this->balance = $result['balance'];
		
		return true;
	}
	
	public function edit($login, $new_balance, $server)
	{
		if(!isset($_DB_[$server]) or $_DB_[$server]=="ERROR")
		{
			$this->error = 7;
			return false;
		}
		
		$query = queryMysql("SELECT * FROM iconomy WHERE username='$login'", $server);
		$nums_serv = mysql_num_rows($query);
		if($nums_serv==0)
		{
			$this->error = 8;
			return false;
		}
		if($nums_serv>1)
		{
			$this->error = 9;
			return false;
		}
		
		queryMysql("UPDATE iconomy SET balance='$new_balance' WHERE username='$login'", $server);
		queryMysql("UPDATE iconomy_$server SET balance='$new_balance' WHERE username='$login'");
		return $this->get($login, $server);
	}
}
?>