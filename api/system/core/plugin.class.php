<?php
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://178.140.61.70/
 */
class plugin
{
	public			$root,
					$pluginsExist				= array(),
					$pluginsIncluded			= array(),
					$pluginsLoaded				= array(),
					$pluginsDenied				= array('libs','system','core','plugin','plugins','api');
	
	protected 		$core;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->root = dirname(__FILE__).'/../plugins/';
		
		if(method_exists($core,'stat'))
		{
			$this->core->stat();
			if(!isset($this->core->stat))
			{
				die;
			}
		}
		else
		{
			die;
		}
		
		$this->pluginsList();
		$this->core->timer->mark('plugin.class.php/pluginsList');
		$this->pluginsInclude();
		$this->core->timer->mark('plugin.class.php/pluginsInclude');
		
		if(!empty($_GET['about']))die('CRAFTEngine Framework by Alexey Kachalov');
		
		ksort($this->pluginsExist,SORT_STRING);
		ksort($this->pluginsIncluded,SORT_STRING);
		ksort($this->pluginsLoaded,SORT_STRING);
		
		$this->core->timer->mark('plugin.class.php/__construct');
	}
	
	/**
	 * Загрузка основного конфига плагина
	 * 
	 * @access public
	 * @param $folder папка с плагином
	 */
	public function mainLoad($folder)
	{
		$main = $this->core->conf->load_conf('pluginConf',array('folder'=>$folder,'write'=>false));
		
		if(isset($main->name,$main->version,$main->web,$main->id,$main->author,
		$main->loadClass,$main->confs,$main->api,$main->requires,$main->permissions))
		{}else
		{
			return array(false,1);
		}
		
		if(!preg_match("'^([0-9]).([0-9])(.([0-9]*)|)(_(alpha|beta|release|)|)$'i", $main->version, $version))
		{
			return array(false,2);
		}
		
		$version[4] = empty($version[4])?0:$version[4];
		$version[6] = empty($version[6])?'release':$version[6];
		
		$main->version = array(
								'update'=>$version[1],
								'smallUpdate'=>$version[2],
								'smallFixes'=>$version[4],
								'stage'=>$version[6],
								'version'=>"$version[1].$version[2].$version[4]_$version[6]"
								);
		
		$main->web = parse_url($main->web);
		
		return $main;
	}
	
	/**
	 * Находит все плагины в папке plugins, которые соответствуют конструкции:
	 * someplug
	 * .|-api
	 * .|..|-
	 * .|-confs
	 * .|..|-
	 * .|-core
	 * .|..|-
	 * .|-main
	 * 
	 * @access public
	 * @return array 
	 */
	public function pluginsList()
	{
		$dir = opendir($this->root);
		while($folders = readdir($dir))
		{
			foreach($this->pluginsDenied as $non)
			if($folders!=$non)
			{
			if($folders!="." && $folders!=".." && is_dir($this->root.$folders))
			if(file_exists($this->root.$folders.'/main'))
			if(is_dir($this->root.$folders.'/core'))
			if(is_dir($this->root.$folders.'/api'))
			if(is_dir($this->root.$folders.'/confs'))
			{
				$main = $this->mainLoad($folders);
				if(is_array($main))
				if(!$main[0])
				{
					return array(false,1);
				}
				
				$this->pluginsExist[$folders] = $main;
				//$this->pluginsIncluded[$folders] = $main;//DELETE IT
			}
			break;
			}
		}
		closedir($dir);
		return array(true);
	}
	
	/**
	 * Включает активированные плагины
	 * 
	 * @return array
	 * @param string $name of plugin
	 * @access public
	 */
	public function add($name)
	{
		$main1 = null;
		$folder1 = null;
		
		//Есть ли такой плагин
		foreach($this->pluginsExist as $f=>$config)
		{
			if($config->name==$name)
			{
				$main1 = $config;
				$folder1 = $f;
			}
		}
		
		if(empty($main1) or empty($folder1))
		{
			return array(false,1);
		}
		
		$main = null;
		$folder = null;
		
		//Нужно ли подключать этот плагин
		foreach($this->pluginsIncluded as $f=>$c)
		{
			if($f==$folder1 AND $c->name==$main1->name)
			{
				$main = $main1;
				$folder = $folder1;
			}
		}
		
		if(empty($main) or empty($folder))
		{
			return array(false,2);
		}
		
		//Не подключен ли он
		foreach($this->pluginsLoaded as $f=>$c)
		{
			if($f==$folder AND $c->name==$main->name)
			{
				return array(false,3);
			}
		}
		
		//Есть ли запрашиваемые плагины
		foreach($main->requires as $r)
		{
			foreach($this->pluginsIncluded as $f=>$c)
			{
				$ex = 0;
				if($r==$c->name)
				{
					$ex = 1;
					break;
				}
			}
			
			if($ex==0)
			{
				$this->off($c->name);
				return array(false,4);
			}
		}
		
		$configs = $main->confs;
		foreach($configs as $c=>$p)
		{
			switch($p[0])
			{
				case 'self':
					$selfConf = $this->core->conf->load_conf('plugin',array('write'=>true,'folder'=>$folder,'name'=>$main->name,'conf'=>$c));
					//print_r($this->core->conf->plugins);
					break;
				
				/* //FIXME: IT'S VERY DANGEROUS
				case 'core':
					$coreConf = $this->core->conf->load_conf('plugin',array('write'=>false,'folder'=>$folder,'name'=>$main->name,'conf'=>$c));
					if(!$coreConf)break;
					$this->core->conf->system->{$c} = $this->merge($this->core->conf->system->{$c},$coreConf,$p[1]);
					break;
				*/
				
				case 'plugin':
					$pluginConf = $this->core->conf->load_conf('plugin',array('write'=>false,'folder'=>$folder,'name'=>$p[1],'conf'=>$c));
					if(!$pluginConf)break;
					$this->core->conf->plugins->$p[1]->$p[2] = $this->merge($this->core->conf->plugins->$p[1]->$p[2],$pluginConf,$p[3]);
					break;
				default:
					return array(false,9);
					break;
			}
		}
		
		$this->core->conf->system->api->modules = array_merge($this->core->conf->system->api->modules,$main->api);
		
		$this->core->conf->system->api->plugins[$folder] = $main->api;
		
		if(!empty($main->loadClass))
		$this->initPl($main->name,$main->loadClass);
		
		return array(true);
	}
	
	/**
	 * Подключает файл класса $class плагина $name
	 * 
	 * @access public
	 * @param $name имя плагина
	 * @param $class[optional]
	 * @return object boolean
	 */
	public function initPl($name,$class='main')
	{
		foreach($this->pluginsIncluded as $f=>$c)
		{
			$ex = 0;
			if($c->name==$name)
			{
				$ex = 1;
				$folder = $f;
				$config = $c;
				break;
			}
		}
		
		if($ex == 1)
		{
			$fi = $this->root.$folder.'/core/'.$class.'.class.php';
			if(file_exists($fi))
			require_once($fi);
			
			if(class_exists('plugin_'.$config->name.'_'.$class))
			{
				$cl = 'plugin_'.$config->name.'_'.$class;
				return new $cl($this->core);
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Совмещение конфигураций
	 * 
	 * @access public
	 * @param $p Основной конфиг
	 * @param $e Дополнение
	 * @param $t='replacement' тип совмещения(replacement, combination)
	 * @return object
	 */
	public function merge($p,$e,$t='replacement'/*combination*/)
	{
		$p = (array)$p;
		$e = (array)$e;
		
		$r = $e+$p;
		$r = (object)$r;
		
		return $r;
	}
	
	/**
	 * Добавляет плагин в список подключаемых $this->pluginsIncluded плагинов
	 * 
	 * @access public
	 */
	public function pluginsInclude()
	{
		$inc = $this->core->conf->load_conf('core',array('name'=>'plugins','write'=>true));
		if(!$inc)
		{
			return false;
		}
		
		$inc = $this->core->conf->system->plugins;
		
		//Есть ли такие плагины, какие нужно подключить
		foreach((object)$inc as $pl)
		{
			foreach($this->pluginsExist as $f=>$c)
			{
				if($c->name==$pl)
				{
					$this->pluginsIncluded[$f] = $c;
				}
			}
		}
		
		foreach($this->pluginsIncluded as $f=>$c)
		{
			$this->add($c->name);
		}
	}
	
	/**
	 * Включение плагина
	 * 
	 * @access public
	 * @param $name имя плагина
	 */
	public function on($name)
	{
		foreach($this->pluginsExist as $f=>$c)
		{
			$ex = 0;
			if($c->name==$name)
			{
				$ex = 1;
				$folder = $f;
				$config = $c;
				break;
			}
		}
		
		if($ex == 1)
		$this->pluginsIncluded[$folder] = $config;
		
		foreach($config->requires as $r)
		{
			foreach($this->pluginsIncluded as $f=>$c)
			{
				$exr = 0;
				if($r==$c->name)
				{
					$exr = 1;
					break;
				}
			}
			
			if($exr!=1)
			{
				return array(false,4);
			}
		}
		
		$list = array();
		foreach($this->pluginsIncluded as $f=>$c)
		{
			$list[] = $c->name;
		}
		
		$this->core->file->set_file('plugins',json_encode($list/*, JSON_PRETTY_PRINT*/));
	}
	
	/**
	 * Выключение плагина
	 * 
	 * @access public
	 * @param $name имя плагина
	 */
	public function off($name)
	{
		foreach($this->pluginsExist as $f=>$c)
		{
			$ex = 0;
			if($c->name==$name)
			{
				$ex = 1;
				$folder = $f;
				$config = $c;
				break;
			}
		}
		
		if($ex == 1)
		unset($this->pluginsIncluded[$folder]);
		
		$list = array();
		foreach($this->pluginsIncluded as $f=>$c)
		{
			$list[] = $c->name;
		}
		
		$this->core->file->set_file('plugins',json_encode($list/*, JSON_PRETTY_PRINT*/));
	}
	
	/**
	 * Подключение независимых библиотек в папке libs
	 * 
	 * @access public
	 * @param $lib имя файла
	 */
	public function lib($lib)
	{
		require_once(dirname(__FILE__).'/../libs/'.$lib.'.php');
	}
}
?>