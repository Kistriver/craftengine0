<?php
class api
{
	public $core;
	public $url='http://api.localhost/';
	public $answer;
	public $answer_decode;
	
	public function __construct($core)
	{
		$this->core = $core;
	}
	
	public function get($method, $data=array())
	{
		$keys = array_keys($data);
		$_data = $data;
		for($i=0;$i<sizeof($data);$i++)
		{
			$key = $keys[$i];
			$post[$key] = $data[$key];
		}
		$post['sid'] = !empty($_SESSION['sid'])?$_SESSION['sid']:'';
		//$post['sid'] = $_SESSION['sid'];
		
		$post = $this->core->f->json_encode_ru($post);
		$url = $this->url . '?method=' . $method/* . '&data=' . rawurlencode($post)*/;
		
		$data = http_build_query
		(
			array
			(
				'data' => $post
			)
		);
		
		$context = stream_context_create
		(
			array
			(
				'http' => array
				(
					'method' => 'POST',
					'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL . 
					'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL,
					'content' => $data,
				)
			)
		);
		
		$this->answer = @file_get_contents($url,false,$context);
		
		if(!$this->answer)
		{
			$this->core->f->quit(500,'<br />Service unavaliable('. $method .')<br />url: '.$url.'<br />data: '.$post);
		}
		$this->answer_decode = json_decode($this->answer, true);
		if(!$this->answer_decode)
		{
			$this->core->f->quit(500,'<pre>Unavaliable data format('. $method .")\r\n".$this->answer.'</pre>');
		}
		
		if(!empty($this->answer_decode['error']))
		{
			$this->core->error->error($this->answer_decode['error']);
			$this->core->error->_FatalError_ = true;
		}
		
		if(isset($this->answer_decode['errors']))
		{
			if(sizeof($this->answer_decode['errors'])!=0)
			foreach ($this->answer_decode['errors'] as $er)
			{
				$this->core->error->error($er);
				
				if($er[0]=='api' AND $er[1]==3)
				{
					$_SESSION['sid']=$this->answer_decode['sid'];
					return $this->get($method, $_data);
				}
			}
			
			//TODO: work on it
			$cc = $this->core->conf->get('core');
			$cc = json_decode($cc,false);
			if($cc==false)$this->core->f->quit(500,'can\'t load config');
			
			if($cc->core->detailed_req===true)
			{
				$times = array();
				foreach($this->answer_decode['runtime'][1] as $t)
				{
					$prec = 0;
					if($t[1]==0)
					$prec = 0;
					else
					$prec = round($t[1]/$this->answer_decode['runtime'][0]*100,1);
					
					$times[] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'$t[0]': ". $t[1]*1000 ."ms ($prec%)";
				}
				
				if($this->answer_decode['runtime'][2]==0)
				$prec = 0;
				else
				$prec = round($this->answer_decode['runtime'][2]/$this->answer_decode['runtime'][0]*100,1);
				
				$times[] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other: ". $this->answer_decode['runtime'][2]*1000 ."ms ($prec%)";
				
				$this->core->render['MAIN']['INFO'][] = ($method.': '.$this->answer_decode['runtime'][0]*1000 .'ms <br />('."<br />". implode("<br />", $times) .'<br />)');
			}
		}
		
		return $this->answer_decode;
	}
}
?>