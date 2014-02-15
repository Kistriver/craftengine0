<?php
namespace CRAFTEngine\plugins\articles;
class core
{
	private $denied = array('denied','system','plugins','core','confs','article','users_core');
	private $features = array();


	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->articles;

		require_once(dirname(__FILE__)."/interfaces/feature.interface.php");

		$st = $this->initFeatures();
		if($st===false)
		{
			$this->core->error->error('plugin_articles_core',0);
			unset($this);
			return false;
		}

		$this->users_core = $this->core->plugin->initPl('users','core');
		$this->article = $this->core->plugin->initPl('articles','article');
		$this->article->construct($this);
	}

	protected function initFeatures()
	{
		$root = dirname(__FILE__)."/features/";

		$dir = opendir($root);
		while($folders = readdir($dir))
		{
			if(preg_match("'(.*?).class.php'",$folders,$preg) && !is_dir($root.$folders))
			{
				$name = $preg[1];

				if(in_array($name,$this->denied))continue;

				require_once("{$root}{$name}.class.php");
				$cl_name = 'CRAFTEngine\plugins\articles\features\\'.$name;

				if(class_exists($cl_name))
				{
					$this->$name = new $cl_name($this->core,$this);
					$this->features[] = $name;
				}
			}
		}
		closedir($dir);

		foreach($this->features as $pr)
		{
			if(method_exists($this->$pr,'construct'))
				if($this->$pr->construct($this)===false)return false;
		}

		return true;
	}

	public function getFeaturesList()
	{
		return $this->features;
	}


	public function makeEvent($id,$module,$addInfo,$staticInfo=null)
	{
		$a = &$this->article;
		foreach($a->getPropertiesList() as $mod)
		{
			if(method_exists($a->$mod,'registerEvent'))
				$addInfo = $a->$mod->registerEvent($id,$module,$addInfo,$staticInfo);
		}

		return $addInfo;
	}


}