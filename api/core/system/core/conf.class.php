<?php
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */

class conf
{
	public $plugins;
	public $system;
	protected $core;
	
	public function __construct($core)
	{
		$this->core = &$core;
		
		$this->system = new stdClass();
		$this->plugins = new stdClass();
	}

	public function construct()
	{
		$this->loadConf('core',array('name'=>'core','write'=>true));

		define('CORE_ADMIN_MAIL', $this->system->core->admin_mail);
		if($this->system->core->tech===true)
		{
			$j = array('error'=>'Technical works');
			echo json_encode($j);
			exit();
		}

		$this->loadConf('core',array('name'=>'api','write'=>true));
		$this->loadConf('core',array('name'=>'errors','write'=>true));

		//$this->core->timer->mark('conf.class.php/__construct');
	}
	
	public function loadConf($type,$params=array())
	{
		switch($type)
		{
			case 'core':
				$file = 'confs/'.$params['name'];

				$conf = $this->core->file->readAsString($file);

				if($conf===false)return false;

				$conf = json_decode($conf, true);
				//print_r(json_last_error());echo JSON_ERROR_SYNTAX."<-".$params['name'];
				$conf = (object)$conf;
				
				if($params['write'])
				$this->system->$params['name'] = $conf;
				else
				return $conf;
				
				return true;
				break;
			
			/*case 'pluginConf':
				$file = 'plugins/'.$params['folder'].'/main';
				
				$conf = $this->core->file->readAsString($file);

				if($conf===false)return false;

				$conf = json_decode($conf, true);
				$conf = (object)$conf;
				
				if($params['write'])
				$conf;
				else
				return $conf;
				
				return true;
				break;
			case 'plugin':
				$file = 'plugins/'.$params['folder'].'/confs/'.$params['conf'];
				
				$conf = $this->core->file->readAsString($file);

				if($conf===false)return false;

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
				
				return true;
				break;*/
			case 'plugin':
				$file = 'plugins/'.$params['folder'].'/confs/'.$params['conf'];

				$conf = $this->core->file->readAsString($file);

				if($conf===false)return false;

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

				return true;
				break;
		}
	}
}