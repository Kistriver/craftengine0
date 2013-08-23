<?php
class plugin_pluginsCatalog_main
{
	protected $core;
	
	public function __construct($core)
	{
		$this->core = $core;
		
	}
	
	public function listPl($offset,$limit,$types='all')
	{
		$offset = (int)$offset;
		$limit = (int)$limit;
		
		/*
		 * id
		 * name
		 * author
		 * href
		 */
		$list = $this->core->mysql->query("SELECT * FROM pluginsCatalog LIMIT $offset,$limit");
		
	}
}
?>