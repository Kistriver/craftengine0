<?php
class tpl
{
	public	$root,
			$content,
			$type,
			$vars;
	
	public function __construct($type='pc')
	{
		if($type=='pc'or $type=='computer')$this->type = 'pc';
		if($type=='m' or $type=='mobile')$this->type = 'm';
		$add = $this->type . '/';
		$this->root = dirname(__FILE__).'/../php/tpl/'.$add;
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
		$root = '/new/www/';
		$this->assign('MAIN.ROOT_HTTP', $root);
		$this->assign('MAIN.ROOT', $root.'client/php/');
		$this->assign('MAIN.V', $this->type);
		
		$this->content = preg_replace('/<!--\/*(.*?)\*\/-->/is', '', $this->content);
		
		$r1 = '([^{].*)';
		$r2 = '(.*)';
		$r = '/{if '.$r1.'}'.$r2.'{\/if}/isU';
		if(preg_match($r, $this->content))
		{
			$if = preg_replace($r, '$1', $this->content);
			foreach(explode("\r\n",$if) as $if)
			if(!empty($if))
			{
				$true = false;
				$eq1 = preg_match_all('/([=]{1})/', $if, $m);
				$eq2 = preg_match_all('/([=]{2})/', $if, $m);
				
				$eq3 = preg_match('/;/', $if);
				$eq4 = preg_match('/phpinfo\(\)/i', $if);
				$eq5 = preg_match('/exec\(\)/i', $if);
				if($eq3 or $eq1/2!=$eq2 or $eq4 or $eq5)die("Template hack attempt");
				eval("if($if)\$true = true;");
				//echo "if($if)\$true = true;\r\n";
				
				$if = str_replace(
				array('/','$','[',']','{','}'),
				array('\/','\$','\[','\]','\{','\}'),$if);
				
				if($true)
				$this->content = preg_replace('/{if '.$if.'}(.*?){\/if}/is', '$1', $this->content);
				else
				$this->content = preg_replace('/{if '.$if.'}(.*?){\/if}/is', '', $this->content);
			}
		}
		
		
		$this->foreachvar($this->vars);
		
		if(substr($this->content, 0, 6)=='{HTML}')
		{
			//$this->content = substr($this->content, 6);
			$params = preg_replace('/{HTML}.(.*?).{\/HTML}(.*?)/is', '$1', $this->content);
			$params = explode("\n",$params);
			$this->content = preg_replace('/{HTML}(.*?){\/HTML}/is', '', $this->content);
			$content = $this->content;
			$this->tpl($params[0]);
			$this->assign('MAIN.CONTENT',$content);
			return $this->render();
		}
		
		return $this->content;
	}
	
}
?>