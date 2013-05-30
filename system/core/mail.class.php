<?php
/*
загрузка tpl писем
проврка на идентичное письмо, занесение письма в БД, отправка по крону
*/
class mail
{
	public	$core;//Ядро
	
	public function __construct($core)
	{
		$this->core = $core;
		
		
	}
	
	//Подключение шаблонов писем
	public function init_tpl()
	{
		//tpls of messages
	}
	
	//Незамедлительная отправка письма
	public function send($to, $type, $params=null)
	{
		$headers  = "Content-type: text/html; charset=utf-8 \r\n";
		$headers .= "From: \"KachalovCRAFT NET\" <kachalov92@yandex.ru>\r\n";
		
		foreach($params as $p){$par .= $p.', ';}
		
		mail($to, 'test', 'test. params: '.$par, $headers);
	}
	
	//Добавления письма в лист ожидания на отправку
	public function add_waiting_list($to, $type, $params=null)
	{
		$types = array(	'000' => 'Fatal PHP error',
						'001' => 'PHP error',
						'002' => 'Register',
						'003' => 'Restore',
						'004' => 'Regular',
						'005' => 'News',
					);
		$time = time();
		$time_loop = 60;
		$time_left = $time - $time_loop;
		$this->core->mysql->query("SELECT * FROM mail WHERE adress='$to' AND typeid='$type' AND date>='$time_left'");
		if(!$this->core->mysql->rows($this->core->mysql->result))
		{
			if(!empty($params))$params = $this->core->json_encode_ru($params);
			$this->core->mysql->query(	"INSERT INTO mail(adress, typeid, params, date) 
										VALUES('$to', '$type', '$params', '$time')");
			return true;
		}
		else
		{
			return false;
		}
	}
	
	//Получения листа ожидания писем на отправку
	public function get_waiting_list($to, $type, $params=null)
	{
		//mysql
	}
}
?>