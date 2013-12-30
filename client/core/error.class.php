<?php
namespace CRAFTEngine\client\core;
class error
{
	public $core;
	private $errors;
	
	public function __construct($core)
	{
		$this->core = $core;

		set_error_handler(array($this,'error_php'));
		register_shutdown_function(array($this, 'fatal_error_php'));

		ob_start();
	}
	
	public function error($er=null)
	{
		if(empty($er))
		return $this->errors;
		
		
		if(!is_array($er))
		{
			$this->errors[] = $er;
		}
		elseif(is_array($er[2]))
		{
			$this->errors[] = "[".$er[2][2].":".$er[2][3]."] ".$er[2][1]." #".$er[2][0]."";
		}
		else
		{
			$this->errors[] = $er[2]." #".$er[0]."-".$er[1];
		}
	}

	public function error_php($code,$msg,$file,$line)
	{
		$file_fr = str_replace('/system','',$this->core->getCoreConfs()['root']);
		$file = str_replace($file_fr,'{{CLIENT_ROOT}}/',$file);

		$this->error("[$file:$line]$msg #client-$code");
	}

	public function fatal_error_php()
	{
		$error = error_get_last();
		if (isset($error))//Если фатальная ошибка, то обработка этой ошибки
			if($error['type'] == E_ERROR
				|| $error['type'] == E_PARSE
				|| $error['type'] == E_COMPILE_ERROR
				|| $error['type'] == E_CORE_ERROR)
			{
				ob_end_clean();

				header('HTTP/1.0 500');

				$error['file'] = str_replace('\\','/',$error['file']);
				$file_fr = str_replace('/core','',dirname(__FILE__));
				$error['file'] = str_replace($file_fr,'{{CLIENT_ROOT}}',$error['file']);

				echo("<b>[$error[type]][$error[file]:$error[line]]</b> $error[message]\r\n");
			}
		ob_end_flush();
	}
}
?>