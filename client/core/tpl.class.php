<?php
class tpl
{
	public	$root,
			$content,
			$vars;
	
	public function __construct()
	{
		$this->root = dirname(__FILE__).'/../php/tpl/';
	}
	
	public function tpl($tpl)
	{
		if (!file_exists($this->root.$tpl))return false;
		$this->content = file_get_contents($this->root.$tpl);
	}
	
	public function assign($tpl_var, $value)
	{
		$this->vars[$tpl_var] = $value;
	}
	
	private function foreachvar($var, $deep = 0, $before='')
	{
		foreach($var as $blockname => $value)
		{
			if(is_array($value))
			{
				if(empty($before))$var_next = $blockname;
				else $var_next = $before.'.'.$blockname;
				$this->foreachvar($value, $deep++, $var_next);
			}
			
			if(empty($before))$block = $blockname;
			else $block = $before.'.'.$blockname;
			if(is_array($value))$this->content = preg_replace('/{\$' . $block . '}/i', '[Array]', $this->content);
			else $this->content = preg_replace('/{\$' . $block . '}/is', $value, $this->content);
		}
	}
	
	public function render()
	{
		$this->foreachvar($this->vars);
		
		//if(substr($this->content, 0, 6))
		
		return $this->content;
	}
	
}
?>