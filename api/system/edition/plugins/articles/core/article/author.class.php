<?php
namespace CRAFTEngine\plugins\articles;
class author implements articleInterface
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
			$this->core->mysql->query("SHOW COLUMNS FROM articles LIKE 'author'")
		)==0)
		{
			$qr = $this->core->mysql->query("ALTER TABLE articles ADD author INT(10)");
			if(!$qr)return false;
		}
		return true;
	}

	public function getProperty($id)
	{
		$id = intval($id);
		$qr = $this->core->mysql->query("SELECT author FROM articles WHERE id='$id'");

		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch();

		return $fr['author'];
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
		$qr = $this->core->mysql->query("UPDATE articles SET author='$value' WHERE id='$id'");

		if($qr)return true;
		else return false;
	}

	public function validateProperty($value,$id=null)
	{
		$len = mb_strlen($value,'UTF-8');
		if(!is_int($value))return false;
		if(!($len>=1 && $len<=10))return false;
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

		$r = $uc->permission->canDo('articles_edit',$uid,array('id'=>$id,'property'=>'author'));
		return $r;
	}

	public function newPost($id,$value)
	{
		if(!$this->setProperty($id,$this->articles_core->users_core->user->currentUser()))return false;
		return true;
	}
}