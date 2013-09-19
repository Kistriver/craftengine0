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
		$this->core = $core;
		
		$this->system = new stdClass();
		$this->plugins = new stdClass();
		
		$this->load_conf('core',array('name'=>'core','write'=>true));
		
		define('CORE_ADMIN_MAIL', $this->system->core->admin_mail);
		
		$this->load_conf('core',array('name'=>'api','write'=>true));
		$this->load_conf('core',array('name'=>'errors','write'=>true));
		
		$this->core->timer->mark('conf.class.php/__construct');
	}
	
	public function load_conf($type,$params=array())
	{
		switch($type)
		{
			case 'core':
				$root = '/../confs/';
				
				if(!file_exists(dirname(__FILE__).$root.$params['name']))
				return false;
				
				$conf = $this->core->file->get_all_file($root.$params['name']);
				$conf = json_decode($conf, true);
				//print_r(json_last_error());echo JSON_ERROR_SYNTAX."<-".$params['name'];
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
				
				return true;
				break;
		}
	}
}
?>