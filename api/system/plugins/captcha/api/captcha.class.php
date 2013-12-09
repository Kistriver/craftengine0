<?php
class api_captcha extends api
{
	public function init()
	{
		#$this->functions['act']='function';
		$this->functions['set']='set';
	}
	
	protected function set()
	{
		$this->input('type');
		$c = $this->core->plugin->initPl('captcha','captcha');

		$c->generate($this->data['type']);
		
		return $this->json(array(true));
	}
}
?>