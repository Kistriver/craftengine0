<?php
class conf
{
	public $core;
	public $folder;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->folder = dirname(__FILE__).'/conf/';
	}
	
	public function get($file)
	{
		if(!is_readable($this->folder.$file))
		{
			return false;
		}
		
		$content = file_get_contents($this->folder.$file);
		return $content;
	}
	
	public function set($file,$content)
	{
		if(!is_writable($this->folder.$file))
		{
			return false;
		}
		
		$w = file_put_contents($this->folder.$file,$content);
		
		if($w===true)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>