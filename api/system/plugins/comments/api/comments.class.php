<?php
namespace CRAFTEngine\api\comments;
class comments extends \CRAFTEngine\core\api
{
	public function init()
	{
		if($this->core->conf->plugins->comments->api->enable_api!==true)return;
		#$this->functions['act']='function';
		$this->functions['test']='test';
		$this->functions['test2']='test2';
	}

	protected function test()
	{
		return $this->core->plugin->initPl('comments','comments')->getComment(array('add'=>array('user'=>'lolka')));
	}

	protected function test2()
	{
		return $this->core->plugin->initPl('comments','comments')->publishComment(array('value'=>'Тестим &alpha;','add'=>array('user'=>'lolka','ff'=>'sd')));
	}
}
?>