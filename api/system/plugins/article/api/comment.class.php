<?php
namespace CRAFTEngine\api\article;
class comment extends \CRAFTEngine\core\api
{
	public function init()
	{
		if(!isset($this->core->conf->plugins->comments))return;
		#$this->functions['act']='function';
		$this->functions['get']='get_comment';
		$this->functions['new']='new_comment';
	}

	protected function get_comment()
	{
		$this->input('article');

		$art = (int)$this->data['article'];

		$c = $this->core->plugin->initPl('comments','comments');

		$com = $c->getComment(array('type'=>'article','add'=>array('article'=>$art)));

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

		$s = $c->publishComment(array('value'=>$value,'type'=>'article','add'=>array('user'=>$_SESSION['id'],'article'=>(string)$id)));

		if($s)return array(true);
		else return array(false);
	}
}
?>