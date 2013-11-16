<?php
class plugin
{
	public $list = array();
	
	public function __construct($core)
	{
		$this->core = $core;
		
		$pl = (array)$this->core->conf->get('plugins');
		
		if(sizeof($pl)!=0)
		{
			foreach($pl as $p)
			{
				if(file_exists($this->core->core_confs['root'].'/plugins/'.$p))
				{
					$pag = (array)$this->core->conf->get('../plugins/'.$p.'/confs/pages');
					
					$this->list[$p] = array('pages'=>$pag);
				}
			}
		}
	}
}
?>