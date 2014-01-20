<?php
namespace CRAFTEngine\core;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class utilities
{
	protected 		$core,$denied = array('denied','core');
	
	public function __construct($core)
	{
		$this->core = &$core;
	}
	
	public function construct()
	{
		$confs = $this->core->getParams()['utilities'];

		foreach($confs as $module=>$utilities)
		{
			if(!in_array($module,$this->denied))
			{
				foreach($utilities as $utility=>$params)
				{
					switch($module)
					{
						case 'system':
							$file = dirname(__FILE__)."/../utilities/$utility/$utility.class.php";
							if(!is_readable($file))continue;
							require_once($file);
							$name = '\CRAFTEngine\core\utilities\\'.$utility.'\\'.$utility;
							if(!property_exists($this,'system'))
								$this->system = new \stdClass();
							$this->system->$utility = new $name($this->core);
							if(method_exists($this->system->$utility,'construct'))
								$this->system->$utility->construct($params);
							break;

						default:
							$file = $this->core->getParams()['root']."/plugins/$module/utilities/$utility/$utility.class.php";
							if(!is_readable($file))continue;
							require_once($file);
							$name = '\CRAFTEngine\plugins\\'.$module.'\utilities\\'.$utility;
							if(!property_exists($this,$module))
								$this->$module = new \stdClass();
							$this->$module->$utility = new $name($this->core);
							if(method_exists($this->$module->$utility,'construct'))
								$this->$module->$utility->construct($params);
							break;
					}
				}
			}
		}
	}
}