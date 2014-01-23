<?php
namespace CRAFTEngine\plugins\captcha\scripts;
class captcha
{
	public function __construct($core)
	{
		$type = empty($_GET['type'])?'':$_GET['type'];

		if(!isset($_SESSION['captcha'][$type]))
		{
			echo json_encode(array("error"=>"Unexpected type",'s'=>session_id()));
			exit;
		}

		$c = $core->plugin->initPl('captcha','captcha');

		header('Content-type: image/png');
		$c->pict($type);
		return true;
	}
}