<?php
namespace CRAFTEngine\plugins\articles\features;
class modify implements \CRAFTEngine\plugins\articles\featureInterface
{
	public function __construct($core,$articles_core)
	{
		$this->core = &$core;
		$this->articles_core = &$articles_core;
		$this->confs = &$this->core->conf->plugins->articles;
	}

	private function modifyEditPost($p=array())
	{
		$art = $this->articles_core->article;
		$prop = $art->getPropertiesList();
		$uid = $this->articles_core->users_core->user->currentUser();

		if(!isset($p['id']))return false;
		$id = intval($p['id']);

		$return = false;
		foreach($prop as $pr)
		{
			if(!isset($p[$pr]))continue;
			$value = $art->$pr->canSetProperty($id,$uid);
			if($value===false)$return = true;
		}
		if($return)return false;

		$return = false;
		foreach($prop as $pr)
		{
			if(!isset($p[$pr]))continue;
			$val = $p[$pr];
			$value = $art->$pr->validateProperty($val,$id);
			if($value===false)$return = true;
		}
		if($return)return false;

		$return = false;
		foreach($prop as $pr)
		{
			if(!isset($p[$pr]))continue;
			$val = $p[$pr];
			$value = $art->$pr->setProperty($id,$val);
			if($value===false)$return = true;
		}
		if($return)return false;

		return true;
	}

	public function editPost($p=array())
	{
		$this->core->mysql->query("START TRANSACTION");
		$st = $this->modifyEditPost($p);
		if($st!==false)
		{
			$this->core->mysql->query("COMMIT");
		}
		else
		{
			$this->core->mysql->query("ROLLBACK");
		}

		return $st;
	}

	public function changeMode($id,$mode)
	{
		return false;
	}
}