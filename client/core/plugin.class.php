<?php
namespace CRAFTEngine\client\core;
class plugin
{
	private $list = array();

	private $rules = array();
	
	public function __construct($core)
	{
		$this->core = $core;
		
		/*$pl = (array)$this->core->conf->get('plugins');
		$pl[] = 'admin';
		
		if(sizeof($pl)!=0)
		{
			foreach($pl as $p)
			{
				if(file_exists($this->core->getCoreConfs()['root'].'/plugins/'.$p))
				{
					$pag = (array)$this->core->conf->get('../plugins/'.$p.'/confs/pages');
					$this->list[$p] = array('pages'=>$pag);
				}
			}
		}*/

		$pl = $this->pluginsList();
		if(sizeof($pl)!=0)
		{
			foreach($pl as $k=>$p)
			{
				if($p['active']==true)
				{
					$this->list[$k] = $p;
				}
			}
		}
		//$this->list = $this->pluginsList();
	}

	public function construct()
	{
		if(sizeof($this->list)!=0)
		{
			foreach($this->list as $f=>&$p)
			{
				if(!empty($p['loadClass']))
				{
					require_once($this->core->getCoreConfs()['root'].'plugins/'.$f.'/core/'.$p['loadClass'].'.class.php');

					$class = '\CRAFTEngine\client\plugins\\'.$f.'\\'.$p['loadClass'];

					$lc = new $class($this->core);
					if(method_exists($lc,'rules'))
					{
						$lc->rules();
					}
				}
			}
		}
	}

	public function makeEvent($id,$plugin,$addInfo)
	{
		foreach($this->list as $plug=>$inf)
		{
			//if(empty($inf['loadClass']))continue;
			$cl = '\CRAFTEngine\client\plugins\\'.$plug.'\\'.$inf['loadClass'];
			$class = new $cl($this->core);
			if(method_exists($class,'registerPluginEvent'))
				$addInfo = $class->registerPluginEvent($id,$plugin,$addInfo);
		}

		return $addInfo;
	}

	public function pluginsList()
	{
		$plugins = array();
		$root = $this->core->getCoreConfs()['root'].'plugins/';
		$dir = opendir($root);
		while($folders = readdir($dir))
		{
			if($folders!="." && $folders!=".." && is_dir($root.$folders))
				if(file_exists($root.$folders.'/main.json'))
					if(is_dir($root.$folders.'/core'))
						if(is_dir($root.$folders.'/pages'))
							if(is_dir($root.$folders.'/tpl'))
							{
								$main = $this->core->conf->get('../plugins/'.$folders.'/main');

								if($main!==null)
								{
									$active = false;
									if(in_array($folders,$this->core->conf->get('plugins')))
										$active = true;

									$plugins[$folders] = array(
										'author'=>isset($main->author)?$main->author:'',
										'web'=>isset($main->web)?$main->web:'',
										'title'=>isset($main->title)?$main->title:'',
										'description'=>isset($main->description)?$main->description:'',
										'loadClass'=>isset($main->loadClass)?$main->loadClass:'',
										'folder'=>$folders,
										'active'=>$active,
									);
								}
							}
		}
		closedir($dir);
		return $plugins;
	}

	public function newRule($array)
	{
		$this->rules[] = $array;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function getList()
	{
		return $this->list;
	}

	public function coreLib($lib)
	{
		require_once(dirname(__FILE__).'/libs/'.$lib.'.php');
	}

	public function lib($lib)
	{
		require_once($this->core->getCoreConfs()['root'].'/libs/'.$lib.'.php');
	}
}