<?php
include_once(dirname(__FILE__).'/../system/core/include.php');

if(!empty($_GET['act']))
{
	$act = $_GET['act'];
	if($act=='logout')
	{
		if($_SESSION['loggedin'])
		{
		$core->get('login.logout',array('sid'=>$_SESSION['sid']));
		//session_destroy();
		header('Location: index');
		}
		else
		{
			display($core, $twig, 403);
		}
	}
	elseif($act=='restore')
	{
		$template = $twig->loadTemplate('login/restore');
		echo $template->render($core->render());
		die;
	}
	elseif($act=='confirm')
	{
		if(!empty($_GET['code']) OR !empty($_POST['code']))
		{
			$code = !empty($_POST['code'])?$_POST['code']:$_GET['code'];
			$core->get('login.activate',array('code'=>$code,'sid'=>$_SESSION['sid']));
		}
	}
}
else
{
	if($_SESSION['loggedin'])display($core, $twig, 403);
	if(!empty($_POST['email']) and !empty($_POST['password']))
	{
		$core->get('login.login',array('email'=>$_POST['email'], 'password'=>$_POST['password'],'sid'=>$_SESSION['sid']));
		$data = $core->answer_decode;
		
		if(sizeof($data['errors'])==0)
		{
			$core->get('user.loggedin',array('sid'=>$_SESSION['sid']));
			$loggedin = $core->answer_decode;
			header('Location: users/'.$loggedin['data']['login']);
		}
	}
}

$template = $twig->loadTemplate('login/main');
echo $template->render($core->render());
?>