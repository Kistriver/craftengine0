<?php
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class functions
{
	public function __construct($core)
	{
		$this->core = $core;
		
		$this->core->timer->mark('conf.class.php/__construct');
	}
}
?>