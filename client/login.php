<?php
require_once(dirname(__FILE__).'/system/include.php');

if(!empty($_GET['act']))
{
	$act = $_GET['act'];
	if($act=='logout')
	{
		if($_SESSION['loggedin'])
		{
		$core->api->get('login.logout',array('sid'=>$_SESSION['sid']));
		setcookie("authed", "", 0,'/');
		//session_destroy();
		header('Location: index');
		}
		else
		{
			$core->f->quit(403);
		}
	}
	elseif($act=='restore')
	{
		$core->f->show('login/restore');
		die;
	}
	elseif($act=='confirm')
	{
		if(!empty($_GET['code']) OR !empty($_POST['code']))
		{
			$code = !empty($_POST['code'])?$_POST['code']:$_GET['code'];
			$core->api->get('login.activate',array('code'=>$code));
			if($core->api->answer_decode['data'][0]===true)
			{
				$core->render['MAIN']['SUCCESS'][] = 'Код активации принят';
			}
			elseif($core->api->answer_decode['data'][0]===false)
			{
				$core->error->error('Код активации не принят');
			}
		}
	}
}
else
{
	if($_SESSION['loggedin'])$core->f->quit(403);
	if(!empty($_POST['email']) and !empty($_POST['password']))
	{
		$core->api->get('login.login',array('email'=>$_POST['email'], 'password'=>$_POST['password'],'sid'=>$_SESSION['sid']));
		$data = $core->api->answer_decode;
		
		if(sizeof($data['errors'])==0)
		{
			$core->api->get('user.loggedin',array('sid'=>$_SESSION['sid']));
			$loggedin = $core->api->answer_decode;
			setcookie("authed", $loggedin['data']['id'].':'.$loggedin['data']['auth'], time()+36000,'/');
			header('Location: users/'.$loggedin['data']['login']);
		}
	}
}

$core->f->show('login/main');
?>