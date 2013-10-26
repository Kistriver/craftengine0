<?php
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

		$this->core->timer->mark('conf.class.php/__construct');
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
	public function time_date($format,$date=null,$GMT=true)
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
	 * @param $srt array
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
		if(defined('JSON_PRETTY_PRINT'))
			$str1 = json_encode($str, JSON_PRETTY_PRINT);
		else
			$str1 = json_encode($str);
		$str2 = str_replace($arr_replace_utf,$arr_replace_cyr,$str1);
		return $str2;
	}

	public function last_call($offset=0,$count=1)
	{
		$debug = debug_backtrace(null);
		$calls = array();
		//Определить, откуда вызвана функция
		for($i=0;$i<$count;$i++)
		{
			$d = $debug[$i+1+$offset];
			$calls[] = $d;
		}

		return $calls;
	}

	public function version_compare($first,$sec)
	{
		$preg = "'^([0-9]).([0-9])(.([0-9]*)|)(_(alpha|beta|release|)|)$'i";
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
		}
	}

	public function start_session(&$sid)
	{
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

			if($_SERVER['REMOTE_ADDR']!=$_SESSION['ip'])
				$sid_err = 2;
		}

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
}