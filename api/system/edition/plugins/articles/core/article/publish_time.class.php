<?php
namespace CRAFTEngine\plugins\articles;
class publish_time implements articleInterface
{
	public function __construct($core)
	{
		$this->core = &$core;
		$this->confs = &$this->core->conf->plugins->articles;
	}

	public function construct($articles_core)
	{
		$this->articles_core = &$articles_core;
	}

	public function install()
	{
		if($this->core->mysql->rows(
			$this->core->mysql->query("SHOW COLUMNS FROM articles LIKE 'publish_time'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE articles ADD publish_time DATETIME");
			if(!$qr)return false;
		}
		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT publish_time FROM articles WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['publish_time'];
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);
		$value = trim($value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE articles SET publish_time='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		return true;
	}

	public function canGetProperty($id,$uid=null)
	{
		return true;
	}

	public function canSetProperty($id,$uid)
	{
		$uc = $this->articles_core;
		$art = $this->articles_core->article;

		$pr = $art->getProperties($id,$uid);
		if(sizeof($pr)==0)
		{
			return false;
		}

		$r = $uc->canDo('articles_edit',$uid,array());
		return $r;
	}

	public function newPost($id,$value)
	{
		$value = date("Y-m-d H:i:s");
		if(!$this->validateProperty($value,$id))return false;
		if(!$this->setProperty($id,$value))return false;
		return true;
	}
}