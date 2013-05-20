<?php
/*
загрузка tpl писем
проврка на идентичное письмо, занесение письма в БД, отправка по крону
*/
class mail
{
	public	$core;
	
	public function __construct($core)
	{
		$this->core = $core;
		
		
	}
	
	public function init_tpl()
	{
		//tpls of messages
	}
	
	public function send($to, $type, $params=null)
	{
		$headers  = "Content-type: text/html; charset=utf-8 \r\n";
		$headers .= "From: \"Kachalov\" <kachalov92@yandex.ru>\r\n";
		
		foreach($params as $p){$par .= $p.', ';}
		
		mail($to, 'test', 'test. params: '.$par, $headers);
	}
	
	public function add_waiting_list($to, $type, $params=null)
	{
		$types = array(	'000' => 'Fatal PHP error',
						'001' => 'PHP error',
					);
		if($type=='000')$this->send($to, $type, $params);
		//mysql
	}
	
	public function get_waiting_list($to, $type, $params=null)
	{
		//mysql
	}
}
?>