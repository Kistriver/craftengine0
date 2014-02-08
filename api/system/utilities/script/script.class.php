<?php
namespace CRAFTEngine\core\utilities\script;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */

class script
{
	protected $core;


	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function process($module,$script)
	{
		$script = str_replace('..','',$script);

		switch($module)
		{
			case 'system':
				$file = dirname(__FILE__)."/scripts/$script.class.php";
				if(!is_readable($file))return false;
				require_once($file);
				$name = '\CRAFTEngine\core\scripts\\'.$script;
				$result = new $name($this->core);
				if($result)return true;
				else return false;
				break;

			default:
				$file = $this->core->getParams()['root']."plugins/$module/utilities/script/scripts/$script.class.php";
				if(!is_readable($file))return false;

				$loaded = false;
				foreach($this->core->plugin->pluginsLoaded as $pl)
				{
					if($module==$pl->name)
					{
						$loaded = true;
						break;
					}
				}

				if(!$loaded)return false;

				require_once($file);
				$name = '\CRAFTEngine\plugins\\'.$module.'\scripts\\'.$script;
				$result = new $name($this->core);
				if($result)return true;
				else return false;
				break;
		}
	}
}