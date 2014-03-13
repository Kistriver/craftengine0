<?php
namespace CRAFTEngine\plugins\users;
class user
{
	protected $denied = array('core','system','denied','properties','mode','id','user');
	protected $properties = array();

	public function __construct($core)
	{
		$this->core = &$core;

		require_once(dirname(__FILE__)."/interfaces/signupUser.interface.php");
		require_once(dirname(__FILE__)."/interfaces/loginUser.interface.php");
		require_once(dirname(__FILE__)."/interfaces/user.interface.php");
	}

	public function construct($users_core)
	{
		$this->users_core = &$users_core;

		$st = $this->initProperties();
		if($st===false)
		{
			$this->core->error->error('plugin_users_core',1);
			unset($this);
			return false;
		}
	}

	protected function initProperties()
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
			if($this->$pr->construct($this->users_core)===false)return false;
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

		if(sizeof($properties)!=0)$properties['id'] = intval($id);

		return $properties;
	}

	/**
	 * Get or set current user id
	 *
	 * @param null $id
	 * @return int
	 */
	public function currentUser($id=null)
	{
		if($id!==null)$_SESSION['users']['id'] = $id;

		return isset($_SESSION['users']['id'])?$_SESSION['users']['id']:0;
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
		$cl_name = 'CRAFTEngine\plugins\users\\'.$name;

		if(in_array($name,$this->denied))return false;

		if(class_exists($cl_name))
		{
			$this->$name = new $cl_name($this->core);
			$this->properties[] = $name;
		}

		if(method_exists($this->$name,'construct'))
			if($this->$name->construct($this->users_core)===false)
				return false;

		return true;
	}
}