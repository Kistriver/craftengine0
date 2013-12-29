<?php
namespace CRAFTEngine\client\core;
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

	public function mime_content_type($filename)
	{
		$mime_types = array(

			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = strtolower(array_pop(explode('.',$filename)));
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else {
			return 'application/octet-stream';
		}
	}

	public function getThemesList()
	{
		$themes = array();
		$root = $this->core->core_confs['root'].'themes/';
		$dir = opendir($root);
		while($folders = readdir($dir))
		{
			if($folders!="." && $folders!=".." && is_dir($root.$folders))
				if(file_exists($root.$folders.'/main.json'))
					if(is_dir($root.$folders.'/styles'))
						if(is_dir($root.$folders.'/tpl'))
						{
							$main = $this->core->conf->get('../themes/'.$folders.'/main');

							if($main!==null)
							$themes[$folders] = array(
								'author'=>isset($main->author)?$main->author:'',
								'web'=>isset($main->web)?$main->web:'',
								'title'=>isset($main->title)?$main->title:'',
								'description'=>isset($main->description)?$main->description:'',
								'folder'=>$folders,
							);
						}
		}
		closedir($dir);
		return $themes;
	}

	public function pathBuilder($path)
	{
		$path = str_replace('\\','/',$path);
		$path_array = explode('/',$path);

		for($i=0;$i<sizeof($path_array);$i++)
		{
			$prev = isset($path_array[$i-1])?$path_array[$i-1]:null;
			$now = $path_array[$i];
			$next = isset($path_array[$i+1])?$path_array[$i+1]:null;
			if($now=='..')
			{
				if($prev!==null && $prev!=='')unset($path_array[$i-1]);
				unset($path_array[$i]);

				$path_array = explode('/',implode('/',$path_array));
				$i=0;
			}
		}

		$path = implode('/',$path_array);

		$path = str_replace('//','/',$path);

		return $path;
	}
}
?>