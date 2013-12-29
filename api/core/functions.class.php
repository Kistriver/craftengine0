<?php
namespace CRAFTEngine\core;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class functions
{
	public function __construct($core)
	{
		$this->core = &$core;

		//$this->core->timer->mark('conf.class.php/__construct');
	}

	//return local time
	public function time()
	{
		$time_offset = $this->core->conf->system->core->GMT;
		$plus = $time_offset[0]=='+'?true:false;
		$time_offset = array($time_offset[1].$time_offset[2],$time_offset[3],$time_offset[4]);

		$time_offset = $time_offset[0] * 60 * 60 + $time_offset[1] * 60;

		$time = $plus?time()+$time_offset:time()-$time_offset;

		return $time;
	}

	//return converted local to GMT time
	public function GMT($NGMT=null,$to=null)
	{
		$NGMT = empty($NGMT)?$this->time():$NGMT;
		$time_offset = empty($to)?$this->core->conf->system->core->GMT:$to;
		$plus = $time_offset[0]=='+'?true:false;
		$time_offset = array($time_offset[1].$time_offset[2],$time_offset[3],$time_offset[4]);

		$time_offset = $time_offset[0] * 60 * 60 + $time_offset[1] * 60;

		$time = $plus?$NGMT-$time_offset:$NGMT+$time_offset;

		return $time;
	}

	//return GMT if true GMT or local if not
	public function timeToDate($format,$date=null,$GMT=true)
	{
		$date = empty($date)?
			date($format,$GMT==true?
				time():
				$this->time()):
			date($format,$GMT==true?
						$this->GMT($date):
						$date);
		return $date;
	}

	/**
	 * JSON с кирилицей
	 *
	 * @access public
	 * @param $str array
	 * @return string
	 */
	public function json($str)
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

	public function lastCall($offset=0,$count=1)
	{
		$debug = debug_backtrace(null);
		$calls = array();

		if($count==0)$count = sizeof($debug) - $offset - 1;

		//Определить, откуда вызвана функция
		for($i=0;$i<$count;$i++)
		{
			$d = $debug[$i+1+$offset];
			$calls[] = $d;
		}

		return $calls;
	}

	public function versionCompare($first,$sec)
	{
		$f = $this->versionParse($first);
		$s = $this->versionParse($sec);

		if($f['product']>$s['product'])return 1;
		if($f['product']<$s['product'])return -1;

		if($f['major']>$s['major'])return 1;
		if($f['major']<$s['major'])return -1;

		if($f['minor']>$s['minor'])return 1;
		if($f['minor']<$s['minor'])return -1;

		if($f['minor']>$s['minor'])return 1;
		if($f['minor']<$s['minor'])return -1;

		if($f['update']>$s['update'])return 1;
		if($f['update']<$s['update'])return -1;

		if($f['status']>$s['status'])return 1;
		if($f['status']<$s['status'])return -1;

		if($f['fix']>$s['fix'])return 1;
		if($f['fix']<$s['fix'])return -1;

		return 0;

		/*$preg = "'^([0-9]).([0-9])(.([0-9]*)|)(_(alpha|beta|release|)|)$'i";
		preg_match($preg,$first,$first);
		preg_match($preg,$sec,$sec);

		$vers = array('alpha'=>0,'beta'=>1,'release'=>2,
					  'a'=>0,'b'=>1,'r'=>2);

		$first[6] = (!empty($first[6]) && in_array($first[6],$vers))?$vers[$first[6]]:$vers['r'];
		$sec[6] = (!empty($sec[6]) && in_array($sec[6],$vers))?$vers[$sec[6]]:$vers['r'];

		$first[4] = empty($first[4])?0:$first[4];
		$sec[4] = empty($sec[4])?0:$sec[4];

		if($first[6]>$sec[6])return 1;
		if($first[6]<$sec[6])return -1;
		if($first[6]==$sec[6])
		{
			if($first[1]>$sec[1])return 1;
			if($first[1]<$sec[1])return -1;
			if($first[1]==$sec[1])
			{
				if($first[2]>$sec[2])return 1;
				if($first[2]<$sec[2])return -1;
				if($first[2]==$sec[2])
				{
					if($first[4]>$sec[4])return 1;
					if($first[4]==$sec[4])return 0;
					if($first[4]<$sec[4])return -1;
				}
			}
		}*/
	}

	public function versionParse($version)
	{
		$vers = array('alpha'=>0,'beta'=>1,'release'=>3,
			'a'=>0,'b'=>1,'rc'=>2,'r'=>3);

		$preg = "'^(v|)([0-9]*)\.([0-9]*)(\.([0-9]*)|)(a[0-9]{0,}|b[0-9]{0,}|r[0-9]{0,}|rc[0-9]{0,}|)(_(alpha|beta|release|)|)$'i";
		$preg_status = "'^(a|b|r|rc)([0-9]{0,})$'i";

		preg_match($preg,$version,$version);
		if(empty($version[6]))$version[6] = 'r';
		preg_match($preg_status,$version[6],$version[6]);

		if(sizeof($version)==0)return false;
		if(sizeof($version[6])<3)return false;

		$ver = array();
		$ver['major'] = $version[2];
		$ver['minor'] = $version[3];

		if(empty($version[5]))$ver['update'] = 0;
		else $ver['update'] = $version[5];

		if(isset($vers[$version[6][1]]))$ver['status'] = $vers[$version[6][1]];
		else $ver['status'] = $vers['r'];

		$ver['fix'] = empty($version[6][2])?0:$version[6][2];

		if(isset($vers[$version[8]]))$ver['product'] = $vers[$version[8]];
		else $ver['product'] = $vers['release'];

		return $ver;
	}

	public function startSession(&$sid)
	{
		//if(!empty($this->core->core_confs['sid']))$sid=$this->core->core_confs['sid'];

		$old = empty($_COOKIE['PHPSESSID'])?null:$_COOKIE['PHPSESSID'];

		//Создание сессии
		$sid_err = 0;
		if(empty($sid))
		{
			session_start();
			session_regenerate_id();
			$sid = session_id();
			$sid_err = 1;
		}
		else
		{
			session_id($sid);
			session_start();

			if(!isset($_SESSION['ip']))
				$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

			//if($_SERVER['REMOTE_ADDR']!=$_SESSION['ip'])
				//$sid_err = 2;
		}

		//FIXME: сделать некостыльное решение
		if(!empty($old)){setcookie("PHPSESSID", session_id(), time()-3600,'/');setcookie("PHPSESSID", $old, time()+3600,'/');}

		if($sid_err!=0)
		{
			switch($sid_err)
			{
				case 1:
					$sid = session_id();
					$this->core->error->error('api',3);
					break;
				case 2:
					$this->core->error->error('engine',4);
					break;
			}
			return false;
		}
		return true;
	}

	public function sysScript($file, $to=2, $params=array())
	{
		$host = empty($params['host'])?$this->core->conf->system->core->system_scripts[0]:$params['host'];
		$port = empty($params['port'])?$this->core->conf->system->core->system_scripts[1]:$params['port'];
		$root = empty($params['path'])?$this->core->conf->system->core->system_scripts[2]:$params['path'];

		$answer = fsockopen($host, $port);
		stream_set_timeout($answer, 0, $to * 1000);
		$req = "GET ".$root."system-scripts/".$file." HTTP/1.0\r\n";
		$req .= "User-agent: CraftEngine(".$this->core->conf->system->core->version.")\r\n";
		$req .= "Host: ".$host."\r\n";
		$req .= "Connection: Close\r\n\r\n";
		fwrite($answer, $req);
		return $answer;
		//fread($answer, 1024);
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
}