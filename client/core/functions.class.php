<?php
class functions
{
	public $core;
	
	public function __construct($core)
	{
		$this->core = $core;
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
		
		$str1 = json_encode($str);
		$str2 = str_replace($arr_replace_utf,$arr_replace_cyr,$str1);
		return $str2;
	}
	
	public function quit($code,$msg='')
	{
		static $lock = false;
		
		$allow = array(403,404,500);
		$key = array_search($code, $allow);
		if(!isset($allow[$key]))$this->quit(500, 'error failed');
		
		if(isset($this->core->twig) && $lock!==true)
		{
			$lock = true;
			header('HTTP/1.1 '.$allow[$key]);
			if(!empty($msg))
			$this->core->render['desc'] = $msg;
			$this->show('errors/'.$allow[$key]);
			die;
		}
		else
		{
			echo "Exit with code: $code. Info: $msg";
			die;
		}
	}

	public function sanString($var,$cycle=0)
	{
		if(is_array($var))
		{
			if($cycle==10)return false;

			foreach($var as $k=>$v)
			$var[$k] = $this->sanString($v,$cycle+1);
			return $var;
		}

		$var_before = Array("<", ">");
		$var_after = Array("&lt;", "&gt;");
		$var = str_replace($var_before, $var_after, $var);
		$var = strip_tags($var);
		$var = htmlentities($var, ENT_COMPAT, 'utf-8');
		$var = str_replace("&amp;", "&", $var);
		return $var;
	}
	
	public function show($tpl,$plugin=null)
	{
		try
		{
			$path = '/'.(!empty($plugin)?'plugins/'.$plugin.'/tpl':'tpl').'/';
			$template = $this->core->twig->loadTemplate($path.$tpl.'.twig');
			$this->core->render['MAIN']['ERRORS'] = $this->core->error->error();
			$this->core->render['_GET'] = $this->sanString($_GET);
			$this->core->render['_POST'] = $this->sanString($_POST);
			$this->core->render['MAIN']['MENU'] = $this->core->conf->conf->core->menu;
			echo $template->render($this->core->render);
		}
		catch (Exception $e)
		{
			$this->quit(500,'Fatal Twig error: ' . $e->getMessage());
		}
	}

	public function msg($type,$value=null)
	{
		switch($type)
		{
			case 'error':
				$this->core->error->error($value);
				break;

			case 'success':
				$this->core->render['MAIN']['SUCCESS'][] = $value;
				break;

			case 'info':
				$this->core->render['MAIN']['INFO'][] = $value;
				break;

			default:
				$this->core->error->error('Value type not found(msg method)');
				break;
		}
	}
}
?>