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
		$ip = $this->core->sanString($this->data['server']['ip']);
		$host = $this->core->sanString($this->data['server']['host']);
		$port = $this->core->sanString($this->data['server']['port']);
		$v = $this->core->sanString($this->data['server']['version']);
		$am = $this->core->sanString($this->data['server']['admin_mail']);
		$time = time();

		$data = explode("\r\n:\r\n",$data);

		if(sizeof($data)!=3)
		{
			return $this->json(array(false));
		}

		$req = $this->core->sanString('');

		$data[2] = explode("\r\n",$data[2]);
		foreach($data[2] as &$i)
		{
			$i = $this->core->cacheDataDecode($i);
		}
		$data[2] = implode("\r\n",$data[2]);
		$err = $this->core->sanString($data[2]);

		$req_ip = $_SERVER['REMOTE_ADDR'];

		$st = $this->core->mysql->query("INSERT INTO stat(time,requests,errors,version,mail,ip,host,port,req_ip)
		VALUES('$time','$req','$err','$v','$am','$ip','$host','$port','$req_ip')");

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