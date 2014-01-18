<?php
namespace CRAFTEngine\core\utilities;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */

class update
{
	protected $core;

	public function __construct($core)
	{
		$this->core = &$core;
	}

}