<?php
namespace CRAFTEngine\core\utilities\migrate;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */

class patch_2014011520
{
	protected $core;

	const VER = 2014011520;

	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function set()
	{
		$mysql = &$this->core->mysql;

		$result = $mysql->db['craftengine']->multi_query(file_get_contents(dirname(__FILE__).'/'.self::VER.'.sql'));
		while($mysql->db['craftengine']->more_results())
		{
			$mysql->db['craftengine']->next_result();
		}

		if(!$result)
		return false;

		if(!$this->core->utilities->system->migrate->markAsInstalled('system',self::VER))
		return false;

		return true;
	}
}