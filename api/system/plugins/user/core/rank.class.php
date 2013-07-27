<?php
class plugin_user_rank
{
	public $core;//Ядро
	public $rank;//Ранк
	public $rank_main;//Ранк
	public $rank_name;//Наименование
	public $rank_name_en;//Наименование
	public $warnings;//Предупреждений
	
	public function __construct($core)
	{
		$this->core = $core;
		
		
	}
	
	//Имеет ли пользователь с id $id доступ к $subj
	public function init($id, $subj)
	{
		if(empty($id))$id=0;
		$is = $this->get_rank($id);//Получение данных
		if(!$is)return false;
		for($i=0;$i<sizeof($this->rank);$i++)
		{
			$this->rank_name_get($this->rank[$i]);//Получение ранка
			if($this->access($this->rank_name_en, $subj))
			{
				$this->rank_name_get($this->rank_main);//Получение ранка
				return true;
				break;
			}//Определение возможности доступа к $subj
		}
		$this->rank_name_get($this->rank_main);
		return false;
	}
	
	//Получение основной информации
	public function get_rank($id)
	{
		$id = $this->core->sanString($id);
		$u = $this->core->plugin->initPl('user','user');//new user($this->core);
		$is = $u->get_user($id, 'id');
		if(!$is)return false;
		$this->rank = $u->rank;
		$this->rank_main = $u->rank_main;
		$this->warnings = $u->warnings;
		return true;
	}
	
	//Получение наименования ранка
	public function rank_name_get($rank)
	{
		$this->rank_name = $this->core->conf->system->core->ranks_name[$rank][0];
		$this->rank_name_en = $this->core->conf->system->core->ranks_name[$rank][1];
	}
	
	private function get_rank_confs($subj)
	{	
		//$conf = $this->core->file->get_all_file('ranks');
		$conf = (array)$this->core->conf->plugins->user->rank;
		//$conf = json_decode($conf, true);
		$keys = array_keys($conf);
		for($i=0;$i<sizeof($conf);$i++)
		{
			$key = $keys[$i];
			if($key==$subj)
			{
				$ranks = $conf[$key]['ranks'];
				$warn = $conf[$key]['warnings'];
				$warn = explode(',',$warn);
				if(sizeof($warn)==1)$warn = array('0',$warn[0]);
				$other = $conf[$key]['other'];
				$done = array($ranks,$warn,$other);
				return $done;
			}
		}
		#$CONFS = ARRAY(
		#
		#'example'=>Array(array('Main Administrator', 'Journalist'), '0'/*vip*/, '10'/*warnings*/),
		#'delete_wall_post'=>Array(array('Main Administrator', 'Administrator', 'Moderator'), '0'/*vip*/, '5'/*warnings*/),
		#'edit_wall_post'=>Array(array('Main Administrator', 'Administrator', 'Moderator'), '0'/*vip*/, '5'/*warnings*/),
		#'write_new_art'=>Array(array('Main Administrator', 'Administrator', 'Journalist'), '0'/*vip*/, '5'/*warnings*/),
		#'edit_art'=>Array(array('Main Administrator', 'Administrator', 'Journalist'), '0'/*vip*/, '5'/*warnings*/),
		#'delete_art'=>Array(array('Main Administrator', 'Administrator', 'Journalist'), '0'/*vip*/, '0'/*warnings*/),
		#'admin_panel'=>Array(array('Main Administrator'), '0'/*vip*/, '5'/*warnings*/),
		#
		#);
		/*$f = new file();
		$perm = $f->get_line_array("rank.conf");
		for($i=0;$i<sizeof($perm);$i++)
		{
			$array = explode(':',$perm[$i]);//name:group1,group2,group3:vip:warnings(|%)
			if($array[0]==$subj)
			{
				$done = array(explode(',',$array[1]),$array[2],$array[3]);
				return $done;
			}
		}*/
	}
	
	private function access($rank_name_en, $subj)
	{
		$confs = $this->get_rank_confs($subj);
		$err_lev['rank'] = 1;
		//$err_lev['vip'] = 1;
		$err_lev['warnings'] = 1;
		
		/*if($this->vip>=$confs[1])
		{
			$err_lev['vip'] = 0;
		}*/
		
		if($this->warnings<=$confs[2])
		{
			$err_lev['warnings'] = 0;
		}
		
		for($i=0; $i<sizeof($confs[0]); ++$i)
		{
			if($confs[0][$i] == $rank_name_en)
			{
				$err_lev['rank'] = 0;
				break;
			}
		}
		
		//TEMP
		$err_lev['vip'] = 0;
		
		$sum_return = $err_lev['rank']+$err_lev['vip']+$err_lev['warnings'];
		if($sum_return==0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	//Пользовательские настройки доступа
	public function user_perms($subject, $target)
	{
		$result = $this->core->mysql->query("SELECT * FROM permissions WHERE id='$target'");
		$num = $this->core->mysql->rows($result);
		$result = $this->core->mysql->fetch($result);
		
		//Друзья
		$user = $this->core->plugin->initPl('user','user');//new user($this->core);
		$friend = $user->friends($subject, $target);
		
		//Я
		if($subject == $target)$self = true;
		else $self = false;
		
		//Плохой пользователь
		$this->get_rank($subject);
		if($rank->warnings>=50)$bad = true;
		else $bad = false;
		if($num == 0)$bad = true;
		
		//Забаненый пользователь
		if($rank->warnings==100)$ban = true;
		else $ban = false;
		if($num == 0)$ban = true;
		
		//Гость
		if($subject=='')$guest = true;
		else $guest = false;
		
		
		$return = Array();
		
		$return['info'] = false;
		$return['fr'] = false;
		$return['msg'] = false;
		$return['wall'] = false;
		
		switch($result['inf'])
		{
			case 1:
				if($self)$return['info'] = true;
				break;
			case 2:
				if($friend or $self)$return['info'] = true;
				break;
			case 3:
				if(!$bad and !$guest)$return['info'] = true;
				break;
			case 4:
				if(!$ban and !$guest)$return['info'] = true;
				break;
			case 5:
				if(!$guest)$return['info'] = true;
				break;
			case 6:
				$return['info'] = true;
				break;
		}
		
		switch($result['friends'])
		{
			case 1:
				if($self)$return['fr'] = true;
				break;
			case 2:
				if($friend or $self)$return['fr'] = true;
				break;
			case 3:
				if(!$bad and !$guest)$return['fr'] = true;
				break;
			case 4:
				if(!$ban and !$guest)$return['fr'] = true;
				break;
			case 5:
				if(!$guest)$return['fr'] = true;
				break;
			case 6:
				$return['fr'] = true;
				break;
		}
		
		switch($result['msg'])
		{
			case 1:
				if($self)$return['msg'] = true;
				break;
			case 2:
				if($friend or $self)$return['msg'] = true;
				break;
			case 3:
				if(!$bad and !$guest)$return['msg'] = true;
				break;
			case 4:
				if(!$ban and !$guest)$return['msg'] = true;
				break;
			case 5:
				$return['msg'] = true;
				break;
		}
		
		switch($result['wall'])
		{
			case 1:
				if($self)$return['wall'] = true;
				break;
			case 2:
				if($friend or $self)$return['wall'] = true;
				break;
			case 3:
				if(!$bad and !$guest)$return['wall'] = true;
				break;
			case 4:
				if(!$ban and !$guest)$return['wall'] = true;
				break;
			case 5:
				$return['wall'] = true;
				break;
		}
		
		
		return $return;
	}
}
?>