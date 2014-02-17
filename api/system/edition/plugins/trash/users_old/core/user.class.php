<?php
namespace CRAFTEngine\plugins\users;
class user
{
	protected $denied = array('core','system','denied','properties','mode');
	protected $properties = array();

	public function __construct($core)
	{
		$this->core = &$core;

		require_once(dirname(__FILE__) . "/user.interface.php");

		$st = $this->initProperties();
		if($st===false)die('cannot load constuct class in users plugin');
	}

	protected  function initProperties()
	{
		$root = dirname(__FILE__)."/user/";

		$dir = opendir($root);
		while($folders = readdir($dir))
		{
			if(preg_match("'(.*?).class.php'",$folders,$preg) && !is_dir($root.$folders))
			{
				$name = $preg[1];

				if(in_array($name,$this->denied))continue;

				require_once("{$root}{$name}.class.php");
				$cl_name = 'CRAFTEngine\plugins\users\\'.$name;

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
			if($this->$pr->construct($this)===false)return false;
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

		return $properties;
	}

	public function getProperties($id,$idfrom=null)
	{
		if($idfrom===null)$idfrom = $id;
		$properties = array();
		foreach($this->getPropertiesList() as $p)
		{
			if($this->$p->canGetProperty($id,$idfrom))
			{
				$prop = $this->$p->getProperty($id);
				if($prop!==false)$properties[$p] = $prop;
			}
		}

		return $properties;
	}
}