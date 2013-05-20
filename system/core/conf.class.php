<?php
class conf
{
	public	$core,
			$admin_mail,
			$version,
			$debug,
			$send_mail_report,
			$db;
	
	public function __construct($core)
	{
		$this->core = $core;
		
		$this->db = array(//array('example','127.0.0.1','test','root','pass'),
						  array('site','127.0.0.1','site','root','pass'),
		);
		$this->admin_mail = 'alex-kachalov@mail.ru';
		$this->version = '0.0.1_alpha';
		$this->debug = true;
		$this->send_mail_report = false;
	}
	
	
}
?>