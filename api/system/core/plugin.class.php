<?php
namespace CRAFTEngine\core;
/*
 * TODO: Из нескольких версий одного плагина включать самую свежею
 * TODO: Проработать вид возвращаемых ошибок
 */
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class plugin
{
	public			$root,
					$pluginsExist				= array(),
					$pluginsIncluded			= array(),
					$pluginsLoaded				= array(),
					$pluginsDenied				= array('libs','system','core','plugin','plugins','api','utilities');
	
	protected 		$core;

	public function __construct($core)
	{
		$this->core = &$core;

		$this->root = ($this->core->getParams()['root']).'plugins/';
	}

	public function construct()
	{
		if($this->core->sid!==false && !preg_match("'^[a-zA-Z0-9-]{1,32}$'is",$this->core->sid))
		{
			unset($_COOKIE['PHPSESSID']);
			session_start();
			$this->core->error->error('api',3);
		}
		elseif($this->core->sid!==false)
		{
			$this->core->functions->startSession($this->core->sid);
		}
		else
		{
			session_start();
			$this->core->error->error('api',3);
		}

		$this->core->sid = session_id();

		if(method_exists($this->core,'stat'))
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
		//$this->core->timer->mark('plugin.class.php/pluginsList');
		$this->pluginsInclude();
		//$this->core->timer->mark('plugin.class.php/pluginsInclude');

		if(!empty($_GET['about']))die('CRAFTEngine Framework by Alexey Kachalov');

		ksort($this->pluginsExist,SORT_STRING);
		ksort($this->pluginsIncluded,SORT_STRING);
		ksort($this->pluginsLoaded,SORT_STRING);

		//$this->core->timer->mark('plugin.class.php/__construct');
		//print_r($this->getEditConfs('captcha'));
	}
	
	/**
	 * Загрузка основного конфига плагина
	 * 
	 * @access public
	 * @param $folder папка с плагином
	 */
	public function mainLoad($folder)
	{
		//$main = $this->core->conf->loadConf('pluginConf',array('folder'=>$folder,'write'=>false));
		$main = $this->core->conf->loadConf('plugin',array('folder'=>$folder,'write'=>false,'conf'=>'../main'));

		/*if(isset($main->name,$main->version,$main->web,$main->id,$main->author,
		$main->loadClass,$main->confs,$main->api,$main->requires,$main->permissions))*/
		if(isset($main->name,$main->version,$main->author))
		{}else
		{
			$this->core->timer->mark('Подключение плагина '.@$main->name.' #1 не все параметры конфига');
			return array(false,1);
		}

		if(!isset($main->title))$main->title = $main->name;
		if(!isset($main->web))$main->web = '';
		if(!isset($main->loadClass))$main->loadClass = '';
		if(!isset($main->confs))$main->confs = array();
		if(!isset($main->api))$main->api = array();
		if(!isset($main->requires))$main->requires = array();
		if(!isset($main->since))$main->since = '0.0.1a0_alpha';
		if(!isset($main->folder))$folder;

		$main->versionSrc = $main->version;
		$main->version = $this->core->functions->versionParse($main->version);
		if($main->version===false)
		{
			$this->core->timer->mark('Подключение плагина '.$main->name.' #2 непрвильный формат версии');
			return array(false,2);
		}

		$ver['product'] = array(0=>'alpha',1=>'beta',3=>'release');
		$ver['fix'] = array(0=>'a',1=>'b',2=>'rc',3=>'r');
		$main->version['version'] = 'v'.$main->version['major'].'.'.$main->version['minor'].
									'.'.$main->version['update'].'.'.
									$ver['fix'][$main->version['status']].
									$main->version['fix'].'_'.
									$ver['product'][$main->version['product']];
		
		$main->web = parse_url($main->web);

		$this->core->timer->mark('Подключение плагина '.$main->name);
		return $main;
	}
	
	/**
	 * Находит все плагины в папке plugins, которые соответствуют конструкции:
	 * <pre>
	 * someplug
	 *  |-api
	 *  |  |-...
	 *  |-confs
	 *  |  |-...
	 *  |-core
	 *  |  |-...
	 *  |-main
	 * </pre>
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
						$this->core->timer->mark('Поиск плагинов #1 найден мусор');
						return array(false,1);
					}

					$this->pluginsExist[$folders] = $main;
				}
				break;
			}
		}
		closedir($dir);
		$this->core->timer->mark('Поиск плагинов');
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
				echo($this->core->functions->json(array('error'=>"Two similar plugins found. Plugin: {$main->name}. Folder: $f. Abort.")));
				exit;
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
				//$this->off($c->name);
				return array(false,4);
			}
		}
		
		$configs = $main->confs;
		foreach($configs as $c=>$p)
		{
			switch($p[0])
			{
				case 'self':
					$selfConf = $this->core->conf->loadConf('plugin',array('write'=>true,'folder'=>$folder,'name'=>$main->name,'conf'=>$c));
					break;
				
				/* //FIXME: IT'S VERY DANGEROUS
				case 'core':
					$coreConf = $this->core->conf->loadConf('plugin',array('write'=>false,'folder'=>$folder,'name'=>$main->name,'conf'=>$c));
					if(!$coreConf)break;
					$this->core->conf->system->{$c} = $this->merge($this->core->conf->system->{$c},$coreConf,$p[1]);
					break;
				*/
				
				case 'plugin':
					$pluginConf = $this->core->conf->loadConf('plugin',array('write'=>false,'folder'=>$folder,'name'=>$p[1],'conf'=>$c));
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

		$this->pluginsLoaded[$folder] = $main;
		
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
	public function initPl($name,$class='main',$class_dir='')
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
			$fi = $this->root.$folder.'/core/'.$class_dir.$class.'.class.php';
			if(file_exists($fi))
			require_once($fi);
			
			if(class_exists('\CRAFTEngine\plugins\\'.$config->name.'\\'.$class))
			{
				$cl = '\CRAFTEngine\plugins\\'.$config->name.'\\'.$class;
				$this->core->timer->mark('Инициализация класса '.$class.' плагина '.$name);
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
		$inc = $this->core->conf->loadConf('core',array('name'=>'plugins','write'=>true));
		if(!$inc)
		{
			return false;
		}
		
		$inc = $this->core->conf->system->plugins;

		$core = $this->core;

		//Есть ли такие плагины, какие нужно подключить
		foreach((object)$inc as $pl)
		{
			foreach($this->pluginsExist as $f=>$c)
			{
				if($c->name==$pl)
				{
					if($this->core->functions->versionCompare($c->since,$core::CORE_VER)<=0)
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
	 * @return array
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

		$pl = &$this->pluginsIncluded[$folder];
		if(!empty($pl->loadClass))
		{
			$plugin = $this->initPl($pl->name,$pl->loadClass);

			if(method_exists($plugin,'OnEnable'))
				$plugin->OnEnable();
		}

		$list = array();
		foreach($this->pluginsIncluded as $f=>$c)
		{
			$list[] = $c->name;
		}
		
		$this->core->file->writeFromString('confs/plugins',json_encode($list, JSON_PRETTY_PRINT));
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

		$pl = &$this->pluginsIncluded[$folder];
		if(!empty($pl->loadClass))
		{
			$plugin = $this->initPl($pl->name,$pl->loadClass);

			if(method_exists($plugin,'OnDisable'))
			$plugin->OnDisable();
		}

		if($ex == 1)
		unset($this->pluginsIncluded[$folder]);
		
		$list = array();
		foreach($this->pluginsIncluded as $f=>$c)
		{
			$list[] = $c->name;
		}
		
		$this->core->file->writeFromString('confs/plugins',json_encode($list, JSON_PRETTY_PRINT));
	}
	
	/**
	 * Подключение независимых библиотек в папке libs
	 * 
	 * @access public
	 * @param $lib имя файла
	 */
	public function lib($lib)
	{
		require_once($this->core->getParams()['root'].'libs/'.$lib.'.php');
		$this->core->timer->mark('Подключение библиотеки '.$lib);
	}

	/**
	 * Подключение независимых библиотек в папке libs
	 *
	 * @access public
	 * @param $lib имя файла
	 */
	public function coreLib($lib)
	{
		require_once(dirname(__FILE__).'/libs/'.$lib.'.php');
		$this->core->timer->mark('Подключение библиотеки '.$lib);
	}

	/**
	 * Получение конфигов плагина для редактирования
	 *
	 * @param $plugin
	 * @return array|bool
	 */
	public function getEditConfs($plugin)
	{
		$folder = '';
		foreach($this->pluginsIncluded as $f=>$c)
		{
			if($c->name==$plugin)
			{
				$folder = $f;
				break;
			}
		}

		if(empty($folder))return false;

		$config = $this->core->conf->loadConf('plugin',array('folder'=>$folder,'write'=>false,'name'=>$plugin,'conf'=>'edit'));
		$edit = array();

		foreach((array)$config as $file=>$mask)
		{
			$c = $this->core->conf->loadConf('plugin',array('folder'=>$folder,'write'=>false,'name'=>$plugin,'conf'=>$file));
			$edit[$file] = $this->getEditConfsMask($mask,(array)$c);
		}

		return $edit;
	}

	private function getEditConfsMask($mask,$array)
	{
		if(is_array($array))
		{
			$r = array();
			foreach($array as $key=>$value)
			{
				if(isset($mask[$key]))
				$r[$key] = $this->getEditConfsMask($mask[$key],$value);
			}

			return $r;
		}
		else
		{
			/*switch($mask['type'])
			{
				case 'text':
					$array['desc'] = $mask['desc'];
					$array['type'] =
					break;
			}*/
			if(!is_array($mask))
				$mask = array('type'=>array('text'),'desc'=>$mask);

			$r = $mask;
			$r['value'] = $array;
			return $r;
		}
	}

	/**
	 * Обновление конфигов плагина
	 *
	 * @param $plugin
	 * @param $config
	 * @return bool
	 */
	public function setEditConfs($plugin,$config)
	{
		$folder = '';
		foreach($this->pluginsIncluded as $f=>$c)
		{
			if($c->name==$plugin)
			{
				$folder = $f;
				break;
			}
		}

		if(empty($folder))return false;

		$main = $this->core->conf->loadConf('plugin',array('folder'=>$folder,'write'=>false,'name'=>$plugin,'conf'=>'edit'));
		/*$edit = array();

		foreach((array)$main as $file=>$mask)
		{
			$c = $this->core->conf->loadConf('plugin',array('folder'=>$folder,'write'=>false,'name'=>$plugin,'conf'=>$file));
			$edit[$file] = $this->getEditConfsMask($mask,(array)$c);
		}*/

		foreach($config as $file=>$edit)
		{
			$c = $this->core->conf->loadConf('plugin',array('folder'=>$folder,'write'=>false,'name'=>$plugin,'conf'=>$file));
			$f = $this->setEditConfsMask((array)$c,(array)$main->{$file},(array)$edit);

			if($f==false)return false;

			$file_addr = 'plugins/'.$folder.'/confs/'.$file;
			if($this->core->file->writeFromString($file_addr, $this->core->functions->json($f)))
				return true;
			else
				return false;
		}
	}

	private function setEditConfsMask($main,$mask,$edit)
	{
		foreach($main as $key=>&$val)
		{
			if(!isset($mask[$key]))continue;
			if(!isset($edit[$key]))continue;

			if(is_array($main[$key]))
			{
				$val = $this->setEditConfsMask($val,$mask[$key],$edit[$key]);
			}
			else
			{
				$main[$key] = $edit[$key];
			}
		}

		return $main;
	}

	/**
	 * Создаём события для плагина $plugin с ID события $id
	 *
	 * @param $id
	 * @param $plugin
	 * @param $addInfo
	 * @param null $staticInfo
	 *
	 * @return mixed
	 */
	public function makeEvent($id,$plugin,$addInfo,$staticInfo=null)
	{
		static $plLoaded = array();

		foreach($this->pluginsLoaded as $f=>$c)
		{
			if(!empty($c->loadClass))
			{
				if(!in_array($c->name,$plLoaded))
				{
					$pl = $this->initPl($c->name,$c->loadClass);
					$plLoaded[$c->name][$c->loadClass] = $pl;
				}

				$pl = $plLoaded[$c->name][$c->loadClass];

				if(method_exists($pl,'registerPluginEvent'))
					$addInfo = $pl->registerPluginEvent($id,$plugin,$addInfo,$staticInfo);
			}
		}

		return $addInfo;
	}
}