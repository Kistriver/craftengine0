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
	
	public function json_encode_ru($str)
	{
		$arr_replace_utf = array('\u0410', '\u0430','\u0411','\u0431','\u0412','\u0432',
		'\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',
		'\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',
		'\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',
		'\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',
		'\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',
		'\u0448','\u0429','\u0449','\u042a','\u044a','\u042b','\u044b','\u042c','\u044c',
		'\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');
		$arr_replace_cyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е',
		'Ё', 'ё', 'Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о',
		'П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш',
		'Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я');
		$str1 = json_encode($str, JSON_PRETTY_PRINT);
		$str2 = str_replace($arr_replace_utf,$arr_replace_cyr,$str1);
		return $str2;
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
		
		$post = $this->json_encode_ru($post);
		//$this->url = $this->root . '?module=' . $module . '&data=' . $post;
		$this->url = $this->root . '?method=' . $method/* . '&data=' . rawurlencode($post)*/;
		
		
		$data = http_build_query(
			array(
				'data' => $post
			)
		);
		
		//$data = http_build_query(array('data'=>$_GET['data'],));
		$context = stream_context_create(array(
			'http' => array(
			'method' => 'POST',
			'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL . 
			'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL,
			'content' => $data,
		),));
		$this->answer = @file_get_contents($this->url,false,$context);
		
		//$this->answer = @file_get_contents($this->url);
		if(!$this->answer)
		{
			header('HTTP/1.0 500');
			//die('It looks like there is an error there. We have just noticed about it and we\'ll do everything what we can.');
			die('Service unavaliable('. $method .')<br />'.$this->url);
		}
		$this->answer_decode = json_decode($this->answer, true);
		if(!$this->answer_decode)
		{
			header('HTTP/1.0 500');
			die('Unavaliable data format('. $method .')'.$this->answer);
		}
		
		if(sizeof($this->answer_decode['errors'])!=0)
		foreach ($this->answer_decode['errors'] as $er)
		{
			$this->errors[] = $er;
		}
		
		if(sizeof($this->answer_decode['errors'])==1)
		if($this->answer_decode['errors'][0][0]=='09003')
		{
		//echo 1;
		#session_id($this->answer_decode['sid']);
		#$_SESSION['sid'] = session_id().'php';
		}
		
		if(isset($_GET['debug']))$this->error('<br /><b>Request: </b><br /><pre>'.rawurldecode($this->url).'</pre><br /><br /><b>Answer: </b><br /><pre>'.$this->answer.'</pre><br />');
		
		return $this->answer_decode;
	}
	
	public function error($value=null)
	{
		if(!empty($value))
		{
			$this->errors[] = array('00',$value);
		}
		else
		{
			return $this->errors;
		}
	}
	
	public function render()
	{
		$this->render['MAIN']['ERRORS'] = $this->error();
		
		if($_SERVER['REMOTE_ADDR']!='192.168.1.1')
		foreach($this->render['MAIN']['ERRORS'] as &$er)
		{
			if($er[0]=='01003')
			$er[1] = 'PHP ERROR';
		}
		
		return $this->render;
	}
}
?>