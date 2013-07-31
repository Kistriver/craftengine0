<?php
/*
загрузка tpl писем
проврка на идентичное письмо, занесение письма в БД, отправка по крону
*/
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://178.140.61.70/
 */
class mail
{
	protected		$core;//Ядро
	
	public function __construct($core)
	{
		$this->core = $core;
		
		$this->core->timer->mark('mail.class.php/__construct');
	}
	
	//Подключение шаблонов писем
	public function init_tpl()
	{
		//tpls of messages
	}
	
	//Незамедлительная отправка письма
	public function send($to, $type, $msg_id, $params=null)
	{
		$headers  = "Content-type: text/html; charset=utf-8 \r\n";
		$headers .= "From: \"KachalovCRAFT NET\" <kachalov92@yandex.ru>\r\n";
		
		$root = dirname(__FILE__).'/../confs/tpl/mail/';
		$params = json_decode($params, true);
		
		$typeid = array(
				'000'=>array('error','Критическая ошибка PHP'),
				'001'=>array('error','Ошибка PHP'),
				'002'=>array('register','Регистрация'),
				'003'=>array('restore','Восстановление пароля'),
				'004'=>array('regular','Оповещение'),
				'005'=>array('news','Новости'),
		);
		
		$id = str_pad($type, 3, '0', STR_PAD_LEFT);
		if(isset($typeid[$id]))
		{
			$content  = file_get_contents($root.'header');
			$content .= file_get_contents($root.$typeid[$id][0]);
			$content .= file_get_contents($root.'footer');
			
			$params['date'] = date('d-m-Y');
			$params['domain'] = '178.140.61.70';
			$params['header'] = $typeid[$id][1];
			
			foreach($params as $blockname => $value)
			{
				$content = preg_replace('/{\$' . $blockname . '}/i', $value, $content);
			}
			$this->core->mysql->query("DELETE FROM mail WHERE id='$msg_id'");
			mail($to, $typeid[$id][1], $content, $headers);
		}
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
			$params = $this->core->sanString($params, 'mysql');
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
	public function get_waiting_list()
	{
		$r = $this->core->mysql->query("SELECT * FROM mail LIMIT 0,20");
		for($i=0;$i<$this->core->mysql->rows($r);$i++)
		{
			$m = $this->core->mysql->fetch($r);
			$this->send($m['adress'], $m['typeid'], $m['id'], $m['params']);
		}
	}
}
?>