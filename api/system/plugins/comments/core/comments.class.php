<?php
namespace CRAFTEngine\plugins\comments;
class comments
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function getComment($p=array())
	{
		$where = array();
		if(isset($p['id']))$where['id'] = 'id=\''.$this->core->sanString($p['id']).'\'';
		if(isset($p['type']))$where['type'] = 'type=\''.$this->core->sanString($p['type']).'\'';

		$addP = isset($p['add'])?$p['add']:null;
		if($addP!==null)
		{
			foreach($addP as $k=>&$ap)
			{
				$ap = 'params LIKE \'%"'.$this->core->sanString($k).'":"'.$this->core->sanString($ap).'"%\'';
			}

			$where['add'] = implode(' AND ',$addP);
		}

		$where = implode(' AND ',$where);
		if(trim($where)!=='')$where = ' WHERE '.$where;

		$qt = "SELECT * FROM comments{$where}";

		$q = $this->core->mysql->query($qt);
		$rows = $this->core->mysql->rows($q);
		if($rows==0)return false;

		$re = array();

		for($i=0;$i<$rows;$i++)
		{
			$r = $this->core->mysql->fetch($q);
			$r['params'] = json_decode($r['params'],true);

			$re[] = $r;
		}

		return $re;
	}

	public function publishComment($p=array())
	{
		if(!isset($p['value']))return false;
		$value = $this->core->sanString($p['value']);
		$add = isset($p['add'])?$p['add']:'[]';
		$type = isset($p['type'])?$this->core->sanString($p['type']):null;
		$date = date('Y-m-d H:i:s');

		$new_add = array();
		foreach($add as $k=>$a)
		{
			if(!is_array($a))
			{
				$new_add[$k] = $a;
			}
		}
		$add = $this->core->sanString(json_encode($new_add,JSON_UNESCAPED_UNICODE),'mysql');

		$r = $this->core->mysql->query("INSERT INTO comments(type,value,params,date) VALUE('$type','$value','$add','$date')");

		if($r)return true;
		else return false;
	}
}
?>