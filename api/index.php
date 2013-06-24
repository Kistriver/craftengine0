<?php
		header('Access-Control-Allow-Origin: *');
		header('Content-type: application/json; charset=utf-8');
if(isset($_GET['method']))
{
	$m_f = $_GET['method'];
	if(!preg_match('/^[a-z_-]{1,25}\.[a-z0-9_-]{1,25}$/',$m_f))die('fuuu');
	$m_f = explode('.',$m_f);
	
	$mod = array(
				'article',
				'login',
				'profile',
				'system',
				'signup',
	);
	
	$modules = in_array($m_f[0],$mod);
	include_once(dirname(__FILE__)."/../system/core/api.class.php");
	if(empty($modules) or is_array($modules))
	{
		$api = new api();
		$api->core->error->error('api','000');
		echo $api->json();
	}
	else
	{
		include_once(dirname(__FILE__)."/".$m_f[0].".class.php");
		
		$cl_n = "api_" . $m_f[0];
		$class = new $cl_n();
		$class->init();
		
		//$func = array_search($m_f[1],$class->functions);
		//if(empty($func))
		if(!isset($class->functions[$m_f[1]]))
		{
			$class->core->error->error('api','001');
			echo $class->json();
		}
		else
		{
			$func = $class->functions[$m_f[1]];
			$class->method($func);
			echo $class->returned;
		}
	}
}


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
		header('Content-type: application/json; charset=utf-8');
		
		echo $class->returned;
	}
}
?>