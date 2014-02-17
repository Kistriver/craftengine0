<?php
namespace CRAFTEngine\api\article;
class comment extends \CRAFTEngine\core\api
{
	public function init()
	{
		$comments_core = $this->core->plugin->initPl('comments','comments');
		if(!$comments_core)return;
		$this->comments_core = &$comments_core;
		#$this->functions['act']='function';
		$this->functions['get']='get_comment';
		$this->functions['new']='new_comment';
	}

	protected function get_comment()
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
				'user'=>$co['params']['user'],
				'time'=>$co['date'],
				'value'=>$co['value'],
			);
			$re[] = $a;
		}
		return $re;
	}

	protected function new_comment()
	{
		if(!$_SESSION['loggedin'])
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$this->input('article','value');

		$id = (int)$this->data['article'];
		$value = /*$this->core->sanString(*/$this->data['value']/*)*/;

		$res = $this->core->mysql->query("SELECT * FROM articles WHERE id='$id'");
		if($this->core->mysql->rows($res)==0)return array(false);

		$c = $this->core->plugin->initPl('comments','comments');

		$s = $this->comments_core->publishComment(array('value'=>$value,'type'=>'article','add'=>array('user'=>$_SESSION['id'],'article'=>(string)$id)));

		if($s)return array(true);
		else return array(false);
	}
}
?>