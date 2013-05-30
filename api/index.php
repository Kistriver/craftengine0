<?php
if(isset($_GET['module']))
{
	$m = $_GET['module'];
	
	$mod = array(
				'article',
				'login',
				'profile',
	);
	
	$modules = in_array($m,$mod);
	include_once(dirname(__FILE__)."/../system/core/api.class.php");
	if(empty($modules) or is_array($modules))
	{
		$api = new api();
		$api->core->error->error('api','000');
		echo $api->json();
	}
	else
	{
		include_once(dirname(__FILE__)."/".$m.".class.php");
		
		$cl_n = "api_" . $m;
		$class = new $cl_n();
		
		header('Access-Control-Allow-Origin: *');
		//header('Content-type: application/json');
		
		echo $class->returned;
	}
}
?>