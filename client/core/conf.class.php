<?php
namespace CRAFTEngine\client\core;
class conf
{
	public $core;
	public $folder;
	public $conf;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->folder = $this->core->getCoreConfs()['root'].'confs/';
		$this->conf = new \stdClass();
	}
	
	public function get($file,$type='json')
	{
		$dtype = '.'.$type;
		if(!is_readable($this->folder.$file.$dtype))
		{
			return false;
		}
		$content = file_get_contents($this->folder.$file.$dtype);
		
		$content = json_decode($content,false);
		if($content===false)$this->core->f->quit(500,'can\'t load config \''.$file.$dtype.'\'');
		
		$this->conf->$file = $content;
		return $content;
	}
	
	public function set($file,$content,$type='json')
	{
		$dtype = '.'.$type;
		if(!is_writable($this->folder.$file.$dtype))
		{
			return false;
		}

		$content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		
		$w = file_put_contents($this->folder.$file.$dtype,$content);

		if($w!==false)
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