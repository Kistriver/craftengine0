<?php
class plugin
{
	public function __construct($core)
	{
		$this->core = $core;
		$this->root = dirname(__FILE__).'/../plugins/';
		$this->pugins = array();
	}
	
	public function add($folder)
	{
		//Не запрещено ли название папки
		//Есть ли папка
		//Есть ли основные файлы
		//Загрузка основной конфигурации
		//Есть ли запрашиваемые плагины
		//Проверка не работает ли этот плагин
		//Загрузка остальных конфигов
		//Слияние с другими конфигами
		//Подключение файлов
		//Вызов главного файла
		
		$denied = array('libs','system','core','plugin','plugins','api');
		
		foreach($denied as $non)
		if($folder==$non)
		{
			return array(false,1);
		}
		
		$root = $this->root.$folder;
		
		if(!is_dir($root))
		{
			return array(false,2);
		}
		
		if(!file_exists($root.'/main') OR !is_dir($root.'/core'))
		{
			return array(false,3);
		}
		
		$main = $this->core->conf->load_conf('pluginConf',array('folder'=>$folder,'write'=>false));
		if(!$main)
		{
			return array(false,4);
		}
		
		if(!file_exists($root.'/core/'.$main->loadClass))
		{
			return array(false,5);
		}
		
		foreach($main->requires as $r)
		if(!isset($this->plugins[$r]))
		{
			return array(false,6);
		}
		
		//echo "<pre>";
		//print_r($main);
		//echo "</pre><br /><br /><br /><br />";
		
		if(!preg_match("'^([0-9]).([0-9])(.([0-9]*)|)(_(alpha|beta|release|)|)$'i", $main->version, $version))
		{
			return array(false,7);
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
		
		if(isset($this->plugins[$main->name]))
		{
			if($this->plugins[$main->name]->author==$main->author)
			{
				return array(false,8);
			}
		}
		
		$this->plugins[$main->name] = $main;
		
		$configs = $main->confs;
		foreach($configs as $c=>$p)
		{
			switch($p[0])
			{
				case 'self':
					$selfConf = $this->core->conf->load_conf('plugin',array('write'=>true,'folder'=>$folder,'name'=>$main->name,'conf'=>$c));
					//print_r($this->core->conf->plugins);
					break;
				
				case 'core':
					$coreConf = $this->core->conf->load_conf('plugin',array('write'=>false,'folder'=>$folder,'name'=>$main->name,'conf'=>$c));
					if(!$coreConf)break;
					$this->core->conf->system->{$c} = $this->merge($this->core->conf->system->{$c},$coreConf);
					break;
				
				case 'plugin':
					$pluginConf = $this->core->conf->load_conf('plugin',array('write'=>false,'folder'=>$folder,'name'=>$p[1],'conf'=>$c));
					if(!$pluginConf)break;
					$this->core->conf->plugins->$p[1]->$p[2] = $this->merge($this->core->conf->plugins->$p[1]->$p[2],$pluginConf);
					break;
				default:
					return array(false,9);
					break;
			}
		}
		
		$this->core->conf->system->api->modules = array_merge($this->core->conf->system->api->modules,$main->api);
		
		$this->core->conf->system->api->plugins[$folder] = $main->api;
		
		include_once($root.'/core/'.$main->loadClass);
		//print_r($main);
		return array(true);
	}
	
	public function merge($p,$e)
	{
		$p = (array)$p;
		$e = (array)$e;
		
		$r = $e+$p;
		$r = (object)$r;
		
		return $r;
	}
	
	public function lib($lib)
	{
		include_once(dirname(__FILE__).'/../plugins/libs/'.$lib.'.php');
	}
	
	/*public function inc($plugin,$autoload=true,$type='name'/*name or catalog id*//*)
	{
		//more filter like \,/,.. etc
		
		if(isset($this->plugins[$plugin]))
		return array(1);
		
		$denied = array(
						'core',
						'root',
						'api',
						'conf',
						'error',
						'file',
						'mail',
						'mysql',
						'plugin',
						'plugins',
						'lib',
		);
		
		foreach($denied as $non)
		{
			if($plugin==$non)
			return array(2);
		}
		
		if(!is_dir($this->root.$plugin))
		return array(3);
		
		$main = $this->root.$plugin.'/core/'.$plugin.'.class.php';
		
		if(!file_exists($main))
		return array(4);
		
		include_once($main);
		
		if($autoload)
		$this->$plugin = new $plugin($this->core);
		
		if(isset($this->core->conf->conf->$plugin))
		$r;
		//print_r($this->core->conf->conf);
		
		if(!$this->core->conf->load_conf($plugin))
		return array(5);
		
		$conf = &$this->core->conf->conf->$plugin;
		$this->plugins[$plugin] = array(
									'author'=>$conf->author,
									'web'=>$conf->web,
									'version'=>$conf->version,
									);
		unset($conf->author,$conf->web,$conf->version);
		
		$this->merge($this->core->conf->conf->core, $conf);
		
		return array(6);
	}*/
	
	/*public function merge($primary, $extend)
	{
		$p = get_object_vars($primary);
		$e = get_object_vars($extend);
		
		$new = (object)array_merge_recursive($p,$e);
		//$new = $e+$p;
		//$new = (object)array_merge($p,$e);
		
		//print_r($new);
	}*/
	
	/*public function array_to_object($array = array())
	{
		if(!empty($array))
		{
			$data = false;
			foreach ($array as $akey => $aval)
			{
				if(is_array($aval))
				$aval = $this->array_to_object($aval);
				$data->$akey = $aval;
			}
			return $data;
		}
		return false;
	}*/
}
?>