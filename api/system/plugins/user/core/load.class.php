<?php
class plugin_user_load
{
	public function __construct($core)
	{
		$this->core = $core;
		
		$this->core->mysql->connect("mcprimary");
	}
}
?>