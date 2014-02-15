<?php
namespace CRAFTEngine\plugins\articles;
class tags implements articleInterface
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
			$this->core->mysql->query("SHOW COLUMNS FROM articles LIKE 'tags'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE articles ADD tags VARCHAR(4096)");
			if(!$qr)return false;
		}
		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT tags FROM articles WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();
		$re = explode(',',$fr['tags']);

		return $re;
	}

	public function getPropertyByValue($value)
	{
		return false;
	}

	public function setProperty($id,$value)
	{
		$id = intval($id);

		if(!is_array($value))
		{
			$value = explode(',',$value);
		}

		$value = array_map('trim',$value);
		$value = implode(',',$value);
		$value = $this->core->sanString($value);
		$qr = $this->core->mysql->query("UPDATE articles SET tags='$value' WHERE id='$id'");

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
		$uc = $this->articles_core->users_core;
		$art = $this->articles_core->article;

		$r = $uc->permission->canDo('articles_edit',$uid,array('id'=>$id,'property'=>'tags'));
		return $r;
	}

	public function newPost($id,$value)
	{
		if(!$this->validateProperty($value,$id))return false;
		if(!$this->setProperty($id,$value))return false;
		return true;
	}
}