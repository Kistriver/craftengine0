<?php
class error
{
	public $core;
	private $errors;
	
	public function __construct($core)
	{
		$this->core = $core;
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
}
?>