<?php
namespace CRAFTEngine\plugins\articles;
class core
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->aritlces;
	}

	public function newPost($p=array())
	{
		$art = $this->core->plugin->initPl('articles','article');
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
			$value = $art->$pr->validateProperty($val,$id);
			if($value===false)$return = true;
		}
		if($return)return false;

		$return = false;
		foreach($prop as $pr)
		{
			$val = isset($p[$pr])?$p[$pr]:null;
			$value = $art->$pr->setProperty($id,$val);
			if($value===false)$return = true;
		}
		if($return)return false;

		return $id;
	}

	public function editPost($p=array())
	{
		$art = $this->core->plugin->initPl('articles','article');
		$prop = $art->getPropertiesList();

		if(!isset($p['id']))return false;
		$id = intval($p['id']);

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

	public function changeMode($id,$mode)
	{

	}

	public function postsList($p=array())
	{
		$art = $this->core->plugin->initPl('articles','article');
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

	public function makeEvent($id,$module,$addInfo,$staticInfo=null)
	{
		static $a = null;
		if($a===null)$a = $this->core->plugin->initPl('articles','article');
		foreach($a->getPropertiesList() as $mod)
		{
			if(method_exists($a->$mod,'registerEvent'))
				$addInfo = $a->$mod->registerEvent($id,$module,$addInfo,$staticInfo=null);
		}

		return $addInfo;
	}
}