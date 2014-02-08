<?php
namespace CRAFTEngine\plugins\articles;
class article
{
	protected $denied = array('core','system','denied','properties','mode','id','article');
	protected $properties = array();

	public function __construct($core)
	{
		$this->core = &$core;

		require_once(dirname(__FILE__)."/interfaces/createArticle.interface.php");
		require_once(dirname(__FILE__)."/interfaces/article.interface.php");
	}

	public function construct($articles_core)
	{
		$this->articles_core = &$articles_core;

		$st = $this->initProperties();
		if($st===false)
		{
			$this->core->error->error('plugin_articles_core',1);
			unset($this);
			return false;
		}
	}

	protected function initProperties()
	{
		$root = dirname(__FILE__)."/article/";

		$dir = opendir($root);
		while($folders = readdir($dir))
		{
			if(preg_match("'(.*?).class.php'",$folders,$preg) && !is_dir($root.$folders))
			{
				$name = $preg[1];

				if(in_array($name,$this->denied))continue;

				require_once("{$root}{$name}.class.php");
				$cl_name = 'CRAFTEngine\plugins\articles\\'.$name;

				if(class_exists($cl_name))
				{
					$this->$name = new $cl_name($this->core);
					$this->properties[] = $name;
				}
			}
		}
		closedir($dir);

		foreach($this->properties as $pr)
		{
			if(method_exists($this->$pr,'construct'))
			if($this->$pr->construct($this->articles_core)===false)return false;
		}

		return true;
	}

	public function getPropertiesList()
	{
		return $this->properties;
	}

	public function getAllProperties($id)
	{
		$properties = array();
		foreach($this->getPropertiesList() as $p)
		{
			$prop = $this->$p->getProperty($id);
			if($prop!==false)$properties[$p] = $prop;
		}

		if(sizeof($properties)!=0)$properties['id'] = intval($id);

		return $properties;
	}

	public function getProperties($id,$uid=null)
	{
		if($uid===null)$uid = $this->core->plugin->initPl('users','core')->user->currentUser();
		$properties = array();
		foreach($this->getPropertiesList() as $p)
		{
			if($this->$p->canGetProperty($id,$uid))
			{
				$prop = $this->$p->getProperty($id);
				if($prop!==false)$properties[$p] = $prop;
			}
		}

		if(sizeof($properties)!=0)$properties['id'] = intval($id);

		return $properties;
	}

	/**
	 * Dynamicly add property
	 *
	 * @param $path
	 * @param $name
	 * @return bool
	 */
	public function addProperty($path,$name)
	{
		require_once("{$path}{$name}.class.php");
		$cl_name = 'CRAFTEngine\plugins\articles\\'.$name;

		if(in_array($name,$this->denied))return false;

		if(class_exists($cl_name))
		{
			$this->$name = new $cl_name($this->core);
			$this->properties[] = $name;
		}

		if(method_exists($this->$name,'construct'))
			if($this->$name->construct($this->articles_core)===false)
				return false;

		return true;
	}
}