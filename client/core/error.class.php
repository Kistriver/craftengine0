<?php
class error
{
	public $core;
	private $errors;
	
	public function __construct($core)
	{
		$this->core = $core;

		set_error_handler(array($this,'error_php'));
		//register_shutdown_function(array($this, 'fatal_error_php'));
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
		$file_fr = str_replace('/system','',$this->core->core_confs['root']);
		$file = str_replace($file_fr,'{{CLIENT_ROOT}}/',$file);

		$this->error("[$file:$line]$msg #client-$code");
	}
}
?>