<?php
namespace CRAFTEngine\plugins\articles\features;
class get implements \CRAFTEngine\plugins\articles\featureInterface
{
	public function __construct($core,$articles_core)
	{
		$this->core = &$core;
		$this->articles_core = &$articles_core;
		$this->confs = &$this->core->conf->plugins->articles;
	}

	public function posts($p=array())
	{
		$art = $this->articles_core->article;
		$prop = $art->getPropertiesList();

		if(!isset($p['page']))return false;
		$page = intval($p['page']);

		$qr = $this->core->mysql->query("SELECT id FROM articles");

		if(!$qr)return false;

		$return = array();

		for($i=0;$i<$this->core->mysql->rows($qr);$i++)
		{
			$fr = $this->core->mysql->fetch($qr);

			$r = $art->getProperties($fr['id']);
			$r['id'] = $fr['id'];
			$return[] = $r;
		}

		return $return;
	}

	public function post($p=array())
	{
		$art = $this->articles_core->article;

		if(!isset($p['id']))return false;
		$id = intval($p['id']);

		$st = $art->getProperties($id);
		if(sizeof($st)==0)return false;

		return $st;
	}
}