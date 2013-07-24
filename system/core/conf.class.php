<?php
class conf
{
	//public	$core,						//Ядро
			//$admin_mail,				//Мыло сисадмина
			//$version,					//Версия ядра
			//$debug,						//Режим дебага
			//$send_mail_report,			//Отправка сообщений об ошибках
			//$includes,					//Массив модулей ядра и плагинов
			//$db;						//Подключаемые БД
	public $plugins;
	public $system;
	
	public function __construct($core)
	{
		$this->core = $core;
		
		$this->system = new stdClass();
		$this->plugins = new stdClass();
		
		$this->load_conf('core',array('name'=>'core','write'=>true));
		$this->load_conf('core',array('name'=>'api','write'=>true));
		
		/*$conf = $core->file->get_all_file('core');
		$conf = json_decode($conf, true);
		foreach($conf as $key=>$value)
		{
			$this->$key = $value;
		}*/
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
	
	public function load_conf(/*$name,$type=0*/$type,$params=array())
	{
		switch($type)
		{
			case 'core':
				$root = '/../confs/';
				
				if(!file_exists(dirname(__FILE__).$root.$params['name']))
				return false;
				
				$conf = $this->core->file->get_all_file($root.$params['name']);
				$conf = json_decode($conf, true);
				$conf = (object)$conf;
				
				if($params['write'])
				$this->system->$params['name'] = $conf;
				else
				return $conf;
				
				return true;
				break;
			
			case 'pluginConf':
				$root = '/../plugins/'.$params['folder'].'/main';
				
				if(!file_exists(dirname(__FILE__).$root))
				return false;
				
				$conf = $this->core->file->get_all_file($root);
				$conf = json_decode($conf, true);
				$conf = (object)$conf;
				
				if($params['write'])
				$conf;
				else
				return $conf;
				
				return true;
				break;
			case 'plugin':
				$root = '/../plugins/'.$params['folder'].'/confs/';
				
				if(!file_exists(dirname(__FILE__).$root.$params['conf']))
				return false;
				
				$conf = $this->core->file->get_all_file($root.$params['conf']);
				$conf = json_decode($conf, true);
				$conf = (object)$conf;
				
				if($params['write'])
				{
					if(!isset($this->plugins->$params['name']))
					$this->plugins->$params['name'] = new stdClass();
					$this->plugins->$params['name']->$params['conf'] = $conf;
				}
				else
				return $conf;
				
				//echo "\$this->plugins->$params[name]->$params[conf] = $conf;";
				
				return true;
				break;
		}
		
		/*if($type==0)
		//$root = dirname(__FILE__).'../confs/';
		$root = '/../confs/';
		elseif($type==1)
		//$root = dirname(__FILE__).'../plugins/'.$name.'/confs/';
		//$root = '/../plugins/'.$name.'/confs/';
		$root = '/../plugins/'.$name.'/main';
		elseif($type==2)
		$root = '/../plugins/'.$name[0].'/';
		
		if($type==0)
		if(!file_exists(dirname(__FILE__).$root.$name))
		return false;
		
		if($type==1)
		if(!file_exists(dirname(__FILE__).$root))
		return false;
		
		if($type==2)
		if(!file_exists(dirname(__FILE__).$root.$name[1]))
		return false;
		
		if($type==0)
		$conf = $this->core->file->get_all_file($root.$name);
		
		if($type==1)
		$conf = $this->core->file->get_all_file($root);
		
		if($type==2)
		$conf = $this->core->file->get_all_file($root.$name[1]);
		
		$conf = json_decode($conf, true);
		
		$conf = (object)$conf;
		/*foreach($conf as $key => $value)
		{
			$this->conf->$name->$key = $value;
		}
		
		if($type==0)
		@$this->conf->$name = $conf;
		
		if($type==1)
		return $conf;
		
		if($type==2)
		@$this->conf->$name[0].'/'.$name[1] = $conf;
		
		return true;*/
	}
}
?>