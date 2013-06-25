<?php
class core
{
	public	$root = 'http://178.140.61.70/new/www/api/',
			$url,
			$answer,
			$answer_decode,
			$errors = array(),
			$render = array(),
			$tpl;
	
	public function __construct()
	{
		include_once(dirname(__FILE__)."/tpl.class.php");
		$this->tpl = new tpl(/*Mobile(m) or PC(pc)*/);
		
		$name = 'KachalovCRAFT NET';
		$this->render = array(
			'MAIN'=> array(
				'NAME'=>$name,
				'TITLE'=>$name,
				'KEYWORDS'=>$name,
				'DESC'=>$name,
				'HEADER'=>$name,
				'ROOT'=>'/new/www/client/php/',
				'ROOT_HTTP'=>'/new/www/',
				'V'=>'pc',
				'ERRORS'=>$this->error(),
			),
		);
	}
	
	//public function get($module, $act, $data)
	public function get($method, $data)
	{
		//$post['act'] = $act;
		$keys = array_keys($data);
		for($i=0;$i<sizeof($data);$i++)
		{
			$key = $keys[$i];
			$post[$key] = $data[$key];
		}
		
		$post = json_encode($post);
		//$this->url = $this->root . '?module=' . $module . '&data=' . $post;
		$this->url = $this->root . '?method=' . $method . '&data=' . $post;
		$this->answer = @file_get_contents($this->url);
		if(!$this->answer)
		{
			header('HTTP/1.0 500');
			//die('It looks like there is an error there. We have just noticed about it and we\'ll do everything what we can.');
			die('Service unavaliable('. $method .')');
		}
		$this->answer_decode = json_decode($this->answer, true);
		if(!$this->answer_decode)
		{
			header('HTTP/1.0 500');
			die('Unavaliable data format('. $method .')');
		}
		
		if(sizeof($this->answer_decode['errors'])!=0)
		foreach ($this->answer_decode['errors'] as $er)
		{
			$this->errors[] = $er;
		}
		return $this->answer_decode;
	}
	
	public function error($value=null)
	{
		if(!empty($value))
		{
			$this->error[] = array('00',$value);
		}
		else
		{
			return $this->errors;
		}
	}
	
	public function render()
	{
		$this->render['MAIN']['ERRORS'] = $this->error();
		return $this->render;
	}
}
?>