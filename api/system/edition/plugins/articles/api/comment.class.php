<?php
namespace CRAFTEngine\api\articles;
class comment extends \CRAFTEngine\core\api
{
	public function init()
	{
		$comments_core = $this->core->plugin->initPl('comments','comments');
		if(!$comments_core)return;
		$this->comments_core = &$comments_core;
		#$this->functions['act']='function';
		$this->functions['get']='getComment';
		$this->functions['new']='newComment';
	}

	protected function getComment()
	{
		$this->input('article');

		$art = (int)$this->data['article'];

		$com = $this->comments_core->getComment(array('type'=>'article','add'=>array('article'=>$art)));

		if($com===false)return array(false);

		$re = array();
		foreach($com as $co)
		{
			$a = array(
				'id'=>$co['id'],
				'uid'=>$co['params']['uid'],
				'time'=>$co['date'],
				'value'=>$co['value'],
			);
			$re[] = $a;
		}
		return $re;
	}

	protected function newComment()
	{
		$users_core = $this->core->plugin->initPl('users','core');
		$articles_core = $this->core->plugin->initPl('articles','core');

		if($users_core->user->currentUser()===0)
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$this->input('article','value');

		$id = (int)$this->data['article'];
		$value = $this->data['value'];

		if(sizeof($articles_core->article->getProperties($id))==0)return array(false);

		$s = $this->comments_core->publishComment(array('value'=>$value,'type'=>'article','add'=>array('uid'=>$users_core->user->currentUser(),'article'=>(string)$id)));

		if($s)return array(true);
		else return array(false);
	}
}
?>