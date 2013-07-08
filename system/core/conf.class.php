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
		foreach($conf as $key=>$value)
		{
			$this->$key = $value;
		}
		/*
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
		$this->preg = $conf['preg'];
		*/
		//if($_SERVER['REMOTE_ADDR']!='192.168.1.1')$this->debug = false;
	}
	
	
}
?>