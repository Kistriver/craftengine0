<?php
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
class timer
{
	public			$round						= 4;
	
	protected 		$core,
			 		$start,
					$mark						= array(/*'name'=>'timeStart'*/),
					$stop;
	
	public function __construct($core)
	{
		$this->core = &$core;
	}
	
	public function startMark()
	{
		/* TODO: Начало метки $this->mark[] = array('name','startTime','endTime=startTime') */
	}
	
	public function endMark()
	{
		/* TODO: конец метки array('name','startTime','endTime') */
	}
	
	public function start($time=null)
	{
		$this->start = empty($time)?microtime(true):$time;
	}
	
	public function stop($time=null)
	{
		$this->stop = empty($time)?microtime(true):$time;
	}
	
	public function mark($name)
	{
		$this->mark[] = array($name, microtime(true));
	}
	
	public function display($type,$param=null)
	{
		switch($type)
		{
			default:
			case 'all':
				$start = $this->start;
				
				if(isset($this->stop))
				$stop = $this->stop;
				else
				$stop = microtime(true);
				
				$time = $stop - $start;
				
				return round($time, $this->round);
				break;
			case 'mark':
				for($i=0;$i<count($this->mark);$i++)
				{
					if($param==$this->mark[$i][0])
					{
						if(isset($this->mark[$i-1]))
						{
							$time = $this->mark[$i][1] - $this->mark[$i-1][1];
							return round($time, $this->round);
						}
						else
						{
							$time = $this->mark[$i][1] - $this->start;
							return round($time, $this->round);
						}
					}
				}
				
				
				break;
			case 'marks':
				$times = array();
				foreach($this->mark as $m)
				{
					$times[] = array($m[0], round($this->display('mark',$m[0]), $this->round));
				}
				
				return $times;
				break;
			case 'other':
				if(count($this->mark)!=0)
				$last = $this->mark[count($this->mark)-1][1];
				else
				$last = $this->start;
				$time = $this->stop - $last;
				return round($time,4);
				break;
		}
	}
}