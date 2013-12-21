<?php
namespace CRAFTEngine\core;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class file
{
	public			$root;
	
	protected		$core;
	
	public function __construct($core)
	{
		$this->core = &$core;
		/*$this->root = empty($this->core->core_confs['confs']['root'])?
							dirname(__FILE__).'/../confs/':
							$this->core->core_confs['confs']['root'];*/

		$this->root = $this->core->core_confs['root'].'';

		//$this->core->timer->mark('file.class.php/__construct');
	}

	public function readAsArray($file)
	{
		$file = $this->root.$file;

		if(!is_readable($file))return false;

		$fo = fopen($file, "rb");

		if($fo===false)return false;

		$r = array();

		while(!feof($fo))
		{
			$get = fgets($fo, 2048);
			$r[] = $get;
		}
		fclose($fo);

		return (array)$r;
	}

	public function readAsString($file)
	{
		$file = $this->root.$file;

		if(!is_readable($file))return false;

		$r = file_get_contents($file,false);

		if($r===false)return false;

		return (string)$r;
	}

	public function writeFromArray($file, $array, $mode='zero',$bin=true)
	{
		$file = $this->root.$file;

		if(!is_writable($file))return false;

		$flag = '';

		switch($mode)
		{
			case 'zero':
			case 'all':
				$flag .= 'w';
				break;

			case 'end':
				$flag .= 'a';
				break;

			case 'begin':
				$flag .= 'x';
				break;

			default:
				return false;
				break;
		}

		if($bin===true)$flag .= 'b';
		elseif($bin===false)$flag .= 't';
		else return false;


		$fo = fopen($file, $flag);

		if($fo===false)return false;

		fseek($fo, 0 ,SEEK_END);
		if(flock($fo, LOCK_EX))
		{
			foreach($array as $line)
			{
				fwrite($fo, $line);
			}
			flock($fo, LOCK_UN);
		}
		else
		{
			return false;
		}
		fclose($fo);
		return true;
	}

	public function writeFromString($file, $string, $mode='zero',$bin=true)
	{
		$file = $this->root.$file;

		if(!is_writable($file))return false;

		$flag = '';

		switch($mode)
		{
			case 'zero':
			case 'all':
				$flag .= 'w';
				break;

			case 'end':
				$flag .= 'a';
				break;

			case 'begin':
				$flag .= 'x';
				break;

			default:
				return false;
				break;
		}

		if($bin===true)$flag .= 'b';
		elseif($bin===false)$flag .= 't';
		else return false;


		$fo = fopen($file, $flag);

		if($fo===false)return false;

		fseek($fo, 0 ,SEEK_END);
		if(flock($fo, LOCK_EX))
		{
			fwrite($fo, $string);
			flock($fo, LOCK_UN);
		}
		else
		{
			return false;
		}
		fclose($fo);
		return true;
	}
}