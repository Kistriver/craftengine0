<?php
class plugin
{
	public function __construct($core)
	{
		$this->core = $core;
		$this->root = dirname(__FILE__).'/../plugins/';
		/*$this->pugins = array();
		$this->denied = array('libs','system','core','plugin','plugins','api');
		$this->pluginsExists = array();
		$this->included = array();
		
		$this->pluginsExists();
		$this->included();*/
		
		$this->pluginsExist = array();
		$this->pluginsIncluded = array();
		$this->pluginsLoaded = array();
		$this->pluginsDenied = array('libs','system','core','plugin','plugins','api');
		
		$this->pluginsList();
		$this->pluginsInclude();
		
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
	}
	
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
				
				case 'core':
					$coreConf = $this->core->conf->load_conf('plugin',array('write'=>false,'folder'=>$folder,'name'=>$main->name,'conf'=>$c));
					if(!$coreConf)break;
					$this->core->conf->system->{$c} = $this->merge($this->core->conf->system->{$c},$coreConf,$p[1]);
					break;
				
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
		//include_once($this->root.$folder.'/core/'.$main->loadClass);
		
		$this->initPl($main->name,$main->loadClass);
		
		return array(true);
	}
	
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
		
		if($ex = 1)
		{
			$fi = $this->root.$folder.'/core/'.$class.'.class.php';
			if(file_exists($fi))
			include_once($fi);
			
			if(/*1===1 or */class_exists('plugin_'.$config->name.'_'.$class))
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
	
	public function merge($p,$e,$t='replacement'/*combination*/)
	{
		$p = (array)$p;
		$e = (array)$e;
		
		$r = $e+$p;
		$r = (object)$r;
		
		return $r;
	}
	
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
		
		if($ex = 1)
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
			
			if($exr==0)
			{
				return array(false,4);
			}
		}
		
		$list = array();
		foreach($this->pluginsIncluded as $f=>$c)
		{
			$list[] = $c->name;
		}
		
		$this->core->file->set_file('plugins',json_encode($list, JSON_PRETTY_PRINT));
	}
	
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
		
		if($ex = 1)
		unset($this->pluginsIncluded[$folder]);
		
		$list = array();
		foreach($this->pluginsIncluded as $f=>$c)
		{
			$list[] = $c->name;
		}
		
		$this->core->file->set_file('plugins',json_encode($list, JSON_PRETTY_PRINT));
	}
	
	public function lib($lib)
	{
		include_once(dirname(__FILE__).'/../plugins/libs/'.$lib.'.php');
	}
	
	/*public function add($folder)
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
		
		/*$denied = $this->denied;
		
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
		}*//*
		$root = $this->root.$folder;
		$er1 = 0;
		foreach($this->pluginsExists as $folderPlugin=>$plugin)
		if($folder==$folderPlugin)
		{
			$er1 = 1;
		}
		
		if($er1==0)
		{
			return array(false,1);
		}
		
		$main = $this->core->conf->load_conf('pluginConf',array('folder'=>$folder,'write'=>false));
		if(!$main)
		{
			return array(false,4);
		}
		
		if(!empty($main->loadClass))
		if(!file_exists($root.'/core/'.$main->loadClass))
		{
			return array(false,5);
		}
		
		$plNamesInc = array();
		foreach($this->pluginsExists as $pf=>$pe)
		{
			if(isset($this->included[$pf]))
			$plNamesInc[$pf] = $pe->name;
		}
		
		foreach($main->requires as $r)
		if(array_search($r,$plNamesInc)==false)
		{print_r($this->pluginsExists);
			return array(false,6);
		}
		
		//echo "<pre>";
		//print_r($main);
		//echo "</pre><br /><br /><br /><br />";
		/*
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
		*//*
		
		$main = $this->pluginConfParse($main);
		
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
		
		if(!empty($main->loadClass))
		include_once($root.'/core/'.$main->loadClass);
		
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
	
	public function pluginsExists()
	{
		$dir = opendir($this->root);
		while($folders = readdir($dir))
		{
			//if(is_dir($folders))
			//if(file_exists($this->root.'main'))
			foreach($this->denied as $non)
			if($folders!=$non)
			{
			if($folders!="." && $folders!=".." && is_dir($this->root.$folders))
			if(file_exists($this->root.$folders.'/main'))
			if(is_dir($this->root.$folders.'/core'))
			{
				//$this->pluginsExists[] = $folders;
				$main = $this->core->conf->load_conf('pluginConf',array('folder'=>$folders,'write'=>false));
				if(!$main)
				{
					return array(false,1);
				}
				
				$this->pluginsExists[$folders] = $this->pluginConfParse($main);
			}
			break;
			}
		}
		closedir($dir);
		return array(true);
	}
	
	public function pluginConfParse($conf)
	{
		if(!preg_match("'^([0-9]).([0-9])(.([0-9]*)|)(_(alpha|beta|release|)|)$'i", $conf->version, $version))
		{
			return array(false,7);
		}
		
		$version[4] = empty($version[4])?0:$version[4];
		$version[6] = empty($version[6])?'release':$version[6];
		
		$conf->version = array(
								'update'=>$version[1],
								'smallUpdate'=>$version[2],
								'smallFixes'=>$version[4],
								'stage'=>$version[6],
								'version'=>"$version[1].$version[2].$version[4]_$version[6]"
								);
		
		$conf->web = parse_url($conf->web);
		
		return $conf;
	}
	
	public function included()
	{
		//LOAD CONF WITH INCLUDED PLUGINS plugins
		//$conf = array();
		//foreach($conf as $)
		$this->included = array("user"=>"user","article"=>"article");
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