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

	const DB_VER = 2014011520;

	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function set()
	{
		$mysql = &$this->core->mysql;

		die;
		//$mysql->query("ALTER TABLE articles_main RENAME articles");

		if($this->core->utilities->migrate->addVersion('system',self::DB_VER))
		return true;
		else
		return false;
	}
}