<?php
namespace CRAFTEngine\core;
/*
загрузка tpl писем
проврка на идентичное письмо, занесение письма в БД, отправка по крону
*/
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class mail
{
	protected		$core;//Ядро
	
	public function __construct($core)
	{
		$this->core = &$core;
		
		//$this->core->timer->mark('mail.class.php/__construct');
	}
	
	//Подключение шаблонов писем
	public function initTpl()
	{
		//tpls of messages
	}
	
	//Незамедлительная отправка письма
	public function send($to, $type, $msg_id, $params=null)
	{
		$headers  = "Content-type: text/html; charset=utf-8 \r\n";
		$headers .= "From: ".$this->core->conf->system->core->mail[1]."\r\n";
		
		$root = $this->core->getParams()['root'].'confs/tpl/mail/';
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
			$params['domain'] = $this->core->conf->system->core->mail[0];
			$params['header'] = $typeid[$id][1];
			
			foreach($params as $blockname => $value)
			{
				$content = preg_replace('/{\$' . $blockname . '}/i', $value, $content);
			}

			$m = mail($to, $typeid[$id][1], $content, $headers);

			if($m===true)
			$this->core->mysql->query("UPDATE system_mail SET status='0' WHERE id='$msg_id'");
		}
	}
	
	//Добавления письма в лист ожидания на отправку
	public function addWaitingList($to, $type, $params=null)
	{
		/**array(	'000' => 'Fatal PHP error',
						'001' => 'PHP error',
						'002' => 'Register',
						'003' => 'Restore',
						'004' => 'Regular',
						'005' => 'News',
					);*/
					
					
		$time = time();
		$time_loop = 60;
		$time_left = $time - $time_loop;

		$mysql = $this->core->mysql;
		if($mysql->isConnect($mysql::DB_NAME)===false)return false;
		if($mysql->lock)return false;

		$this->core->mysql->query("SELECT * FROM system_mail WHERE adress='$to' AND typeid='$type' AND date>='$time_left' AND status='1'");
		if(!$mysql->rows($mysql->result))
		{
			if(!empty($params))$params = $this->core->json_encode_ru($params);
			$params = $this->core->sanString($params, 'mysql');
			$mysql->query(	"INSERT INTO system_mail(adress, typeid, params, date, status)
										VALUES('$to', '$type', '$params', '$time', '1')");
			return true;
		}
		else
		{
			return false;
		}
	}
	
	//Получения листа ожидания писем на отправку
	public function getWaitingList($limit=20)
	{
		$r = $this->core->mysql->query("SELECT * FROM system_mail WHERE status='1' LIMIT 0,".$limit);
		for($i=0;$i<$this->core->mysql->rows($r);$i++)
		{
			$m = $this->core->mysql->fetch($r);
			$this->send($m['adress'], $m['typeid'], $m['id'], $m['params']);
		}
	}
}