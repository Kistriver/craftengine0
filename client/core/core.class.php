<?php
class core
{
	public	$root = 'http://178.140.61.70/new/www/api/',
			$url,
			$answer,
			$answer_decode,
			$tpl;
	
	public function __construct()
	{
		include_once(dirname(__FILE__)."/tpl.class.php");
		$this->tpl = new tpl(/*Mobile(m) or PC(pc)*/);
	}
	
	public function get($module, $act, $data)
	{
		$post['act'] = $act;
		$keys = array_keys($data);
		for($i=0;$i<sizeof($data);$i++)
		{
			$key = $keys[$i];
			$post[$key] = $data[$key];
		}
		
		$post = json_encode($post);
		$this->url = $this->root . '?module=' . $module . '&data=' . $post;
		$this->answer = @file_get_contents($this->url);
		if(!$this->answer)
		{
			header('HTTP/1.0 500');
			die('Service unavaliable');
		}
		$this->answer_decode = json_decode($this->answer, true);
		if(!$this->answer_decode)
		{
			header('HTTP/1.0 500');
			die('Unavaliable data format');
		}
		return $this->answer_decode;
	}
}
?>