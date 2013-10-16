<?php
class api_stat extends api
{
	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['set']='set';
	}

	protected function set()
	{
		$data = $this->data['value'];
		//$ver = $this->core->sanString($this->data['stat_ver']);
		$ip = $this->core->sanString($this->data['server']['ip']);
		$host = $this->core->sanString($this->data['server']['host']);
		$port = $this->core->sanString($this->data['server']['port']);
		$v = $this->core->sanString($this->data['server']['version']);
		$am = $this->core->sanString($this->data['server']['admin_mail']);
		$time = time();

		$data = explode("\r\n:\r\n",$data);

		if(sizeof($data)<2)
		{
			return $this->json(array(false));
		}

		$data[1] = explode("\r\n",$data[1]);
		foreach($data[1] as &$i)
		{
			$i = $this->core->cacheDataDecode($i);
		}
		$data[1] = implode("\r\n",$data[1]);
		$err = $this->core->sanString($data[1]);

		$req_ip = $_SERVER['REMOTE_ADDR'];

		$st = $this->core->mysql->query("INSERT INTO stat(time,errors,version,mail,ip,host,port,req_ip)
		VALUES('$time','$err','$v','$am','$ip','$host','$port','$req_ip')");

		if($st===false)
		{
			return $this->json(array(false));
		}
		else
		{
			return $this->json(array(true));
		}
	}
}
?>