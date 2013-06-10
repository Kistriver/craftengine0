<?php
class file{
	public $core, $root;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->root = dirname(__FILE__).'/../confs/';
	}
	
	public function get_line_array($file)
	{
		$fp = fopen($this->root.$file, "r"); // Открываем файл в режиме чтения
		if ($fp)
		{
		while (!feof($fp))
		{
		$mytext = trim(fgets($fp, 1024));
		$arr[] = $mytext;
		}
		return $arr;
		}
		else return false;
		fclose($fp);
	}
	
	public function get_all_file($file)
	{
		$fp = fopen($this->root.$file, "r"); // Открываем файл в режиме чтения
		$arr='';
		if ($fp)
		{
		while (!feof($fp))
		{
		$mytext = fgets($fp, 1024);
		$arr = $arr.$mytext;
		}
		return $arr;
		}
		else return false;
		fclose($fp);
	}
	
	public function write_a_p($file, $text)
	{
		$text = $text."\r\n";
		$fh = fopen($this->root.$file, 'a+');
		fseek($fh, 0 ,SEEK_END);
		if(flock($fh, LOCK_EX))
		{
			fwrite($fh, $text);
			flock($fh, LOCK_UN);
		}
		fclose($fh);
	}
	
	public function write_w($file, $text)
	{
		$text = $text."\r\n";
		$fh = fopen($this->root.$file, 'w');
		fseek($fh, 0 ,SEEK_END);
		if(flock($fh, LOCK_EX))
		{
			fwrite($fh, $text);
			flock($fh, LOCK_UN);
		}
		fclose($fh);
	}
}
?>