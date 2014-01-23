<?php
namespace CRAFTEngine\plugins\users\scripts;
class import
{
	public function __construct($core)
	{
		if(!in_array($_SERVER['REMOTE_ADDR'],$core->conf->system->core->admin_ip))
		{
			die(json_encode(array(false,0,'403')));
		}

		$engine = $core->sanString(isset($_GET['engine'])?$_GET['engine']:null);
		$version = $core->sanString(isset($_GET['version'])?$_GET['version']:null);

		if(empty($engine) || empty($version))
			die(json_encode(array(false,1,'empty engine parametr')));

		$con = fopen('php://input','rb');
		if(empty($con))
			die(json_encode(array(false,2,'empty dump')));

		if(file_put_contents(dirname(__FILE__).'/../../../confs/import/'.str_replace('..','',$engine).' '.str_replace('..','',$version).'.sql',$con))
		{
			die(json_encode(array(true,0,$core->error->error)));
		}
	}
}