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
		$this->tpl = new tpl();
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
		$this->answer = @file_get_contents($this->url) or die('Service unavaliable');
		$this->answer_decode = json_decode($this->answer, true);
		return $this->answer_decode;
	}
}
?>