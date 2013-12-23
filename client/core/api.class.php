<?php
namespace CRAFTEngine\client\core;
class api
{
	public $core;
	public $url='http://localhost/api/';
	public $answer;
	public $answer_decode;
	public $api_ver = 4;
	
	public function __construct($core)
	{
		$this->core = $core;
	}
	
	public function get($method, $data=array(), $post_method="GET")
	{
		static $repeat_req = 0;
		
		$keys = array_keys($data);
		$_data = $data;
		$post = array();
		for($i=0;$i<sizeof($data);$i++)
		{
			$key = $keys[$i];
			$post[$key] = $data[$key];
		}
		//$post['sid'] = !empty($_SESSION['sid'])?$_SESSION['sid']:'';
		//$post['sid'] = $_SESSION['sid'];
		$sid = !empty($_SESSION['sid'])?$_SESSION['sid']:'';
		
		$post = $this->core->f->json_encode_ru($post);
		$url = $this->url . '?v=' . $this->api_ver . '&status_code=200&method=' . $method . '&sid=' . $sid . '&post=' . $post_method;
		
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
				if($er[0]=='api' AND $er[1]==3 AND $repeat_req==0)
				{
					$_SESSION['sid']=$this->answer_decode['sid'];
					$repeat_req = 1;
					return $this->get($method, $_data);
				}
				
				$this->core->error->error($er);
				$repeat_req = 0;
			}
			
			$cc = $this->core->conf->get('core');
			if($cc->core->detailed_req===true)
			{
				$times = array();

				$runs = $this->answer_decode['runtime'][1];
				$runs[] = array('Other',$this->answer_decode['runtime'][2]);
				foreach($runs as $t)
				{
					$prec = 0;
					if($t[1]==0)
					$prec = 0;
					else
					$prec = round($t[1]/$this->answer_decode['runtime'][0]*100,1);

					$color = '#00FF00';
					if($prec>5)$color = '#40FF00';
					if($prec>10)$color = '#80FF00';
					if($prec>15)$color = '#BFFF00';
					if($prec>20)$color = '#FFFF00';
					if($prec>25)$color = '#FFBF00';
					if($prec>30)$color = '#FF8000';
					if($prec>35)$color = '#FF4000';
					if($prec>40)$color = '#FF0000';

					$times[] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'$t[0]': ". $t[1]*1000 ."ms<!-- ($prec%)-->"
						.'<div style="float:right; background-color: '.$color.'; min-width:'.$prec*4 .'px">&nbsp;</div>
						<!--<div style="float:right; background-color:grey; width:'.(100-$prec)*4 .'px;">&nbsp;</div>-->
						<div style="float:right;">'.$prec.'%</div>';
				}

				$this->core->render['MAIN']['INFO'][] = ($method.': '.$this->answer_decode['runtime'][0]*1000 .'ms <br />('."<br />". implode("<br />", $times) .'<br />)'
					.'<br />Request:<pre style="max-height: 300px;overflow: scroll;">'.$url."\r\n".$post.'</pre>'.'Answer:<pre style="max-height: 300px;overflow: scroll;">'."\r\n".$this->answer.'</pre>');
			}
		}
		
		return $this->answer_decode;
	}
}
?>