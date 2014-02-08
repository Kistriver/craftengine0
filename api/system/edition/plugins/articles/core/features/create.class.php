<?php
namespace CRAFTEngine\plugins\articles\features;
class create implements \CRAFTEngine\plugins\articles\featureInterface
{
	public function __construct($core,$articles_core)
	{
		$this->core = &$core;
		$this->articles_core = &$articles_core;
		$this->confs = &$this->core->conf->plugins->articles;
	}

	private function createNewPost($p=array())
	{
		$art = $this->articles_core->article;
		$prop = $art->getPropertiesList();

		$qr = $this->core->mysql->query("INSERT INTO articles(id) VALUE(NULL)");
		if(!$qr)return false;

		$qr = $this->core->mysql->query("SELECT LAST_INSERT_ID()");
		if(!$qr)return false;

		$id = $qr->fetch_array();
		$id = $id['LAST_INSERT_ID()'];

		$return = false;
		foreach($prop as $pr)
		{
			$val = isset($p[$pr])?$p[$pr]:null;
			$value = $art->$pr->newPost($id,$val);
			if($value===false)$return = true;
		}
		if($return)return false;

		return $id;
	}

	public function newPost($p=array())
	{
		$this->core->mysql->query("START TRANSACTION");
		$st = $this->createNewPost($p);
		if($st!==false)$this->core->mysql->query("COMMIT");
		else $this->core->mysql->query("ROLLBACK");

		return $st;
	}
}