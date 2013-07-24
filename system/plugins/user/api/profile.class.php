<?php
class api_profile extends api
{
	public function init()
	{
	   #$this->functions['act']='function';
		//$this->functions['change_name']='change';
		$this->functions['change']='change_name';
	}
	
	protected function change_name()
	{
		
	}
}
?>