<?php
class conf
{
	public	$core,						//Ядро
			$admin_mail,				//Мыло сисадмина
			$version,					//Версия ядра
			$debug,						//Режим дебага
			$send_mail_report,			//Отправка сообщений об ошибках
			$includes,					//Массив модулей ядра и плагинов
			$db;						//Подключаемые БД
	
	public function __construct($core)
	{
		$this->core = $core;
		
		$conf = $core->file->get_all_file('core');
		$conf = json_decode($conf, true);
		$this->db = $conf['db'];
		$this->admin_mail = $conf['admin_mail'];
		$this->version = $conf['version'];
		$this->debug = $conf['debug'];
		$this->send_mail_report = $conf['send_mail_report'];
		$this->includes['plugins'] = $conf['includes']['plugins'];
		$this->salt = $conf['salt'];
		$this->ranks = $conf['ranks'];
		$this->ranks_name = $conf['ranks_name'];
		$this->length = $conf['length'];
		/*		
		$this->db = array(//array('example','127.0.0.1','test','root','pass'),
						  array('site','127.0.0.1','site','root','pass'),
						  array('social','127.0.0.1','social','root','pass'),
						  array('test','127.0.0.1','test','root','pass'),
		);
		$this->admin_mail = 'alex-kachalov@mail.ru';
		$this->version = '0.0.1_alpha';
		$this->debug = true;
		$this->send_mail_report = false;
		$this->includes['plugins'] = array(	'fdgf',			//
										);
		
		json_encode(array(
			'version'=>$this->version,
			'admin_mail'=>$this->admin_mail,
			'debug'=>$this->debug,
			'send_mail_report'=>$this->send_mail_report,
			'db'=>$this->db,
			'includes'=>array('plugins'=>$this->includes['plugins']),
		));*/
	}
	
	
}
?>