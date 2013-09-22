<?php
class conf
{
	public $core;
	public $folder;
	public $conf;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->folder = dirname(__FILE__).'/confs/';
		$this->conf = new stdClass();
	}
	
	public function get($file,$type='json')
	{
		if(!is_readable($this->folder.$file))
		{
			return false;
		}
		$content = file_get_contents($this->folder.$file);
		
		$content = json_decode($content,false);
		if($content===false)$this->core->f->quit(500,'can\'t load config \''.$file.'\'');
		
		$this->conf->$file = $content;
		return $content;
	}
	
	public function set($file,$content,$type='json')
	{
		if(!is_writable($this->folder.$file))
		{
			return false;
		}
		
		if(defined(JSON_PRETTY_PRINT))
		$content = json_encode($content, JSON_PRETTY_PRINT);
		else
		$content = json_encode($content);
		
		$w = file_put_contents($this->folder.$file,$content);
		
		if($w===true)
		{
			$this->conf->$file = $content;
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>