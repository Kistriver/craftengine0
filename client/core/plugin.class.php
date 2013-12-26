<?php
namespace CRAFTEngine\client\core;
class plugin
{
	public $list = array();

	private $rules = array();
	
	public function __construct($core)
	{
		$this->core = $core;
		
		$pl = (array)$this->core->conf->get('plugins');
		$pl[] = 'admin';
		
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

	public function makeEvent($id,$plugin,$addInfo)
	{
		foreach($this->list as $plug=>$pages)
		{
			$fun = '\CRAFTEngine\client\plugins\\'.$plug.'\RegisterPluginEvent';
			if(function_exists($fun))
				$addInfo = $fun($id,$plugin,$addInfo);
		}

		return $addInfo;
	}

	public function newRule($array)
	{
		$this->rules[] = $array;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function coreLib($lib)
	{
		require_once(dirname(__FILE__).'/libs/'.$lib.'.php');
	}

	public function lib($lib)
	{
		require_once($this->core->core_confs['root'].'/libs/'.$lib.'.php');
	}
}
?>