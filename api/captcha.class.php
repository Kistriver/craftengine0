<?php
class api_captcha extends api
{
	public function init()
	{
		#$this->functions['act']='function';
		//$this->functions['pict']='get';
		$this->functions['get']='pict';
	}
	
	protected function pict()
	{
		
	}
}
?>