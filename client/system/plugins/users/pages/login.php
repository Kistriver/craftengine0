<?php
if(!defined('CE_HUB'))die('403');

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
		if($_SESSION['loggedin'])$core->f->quit(403);

		if(isset($_GET['code']))
		{
			if(!empty($_GET['code']))
			{
				$core->api->get('login.restore',array(
					'step'=>'2',
					'code'=>$_GET['code'],
				));
				if(isset($core->api->answer_decode['data'][0]))
					if($core->api->answer_decode['data'][0]==true)
					{
						$core->render['MAIN']['SUCCESS'][] = "Новый пароль: ".$core->api->answer_decode['data'][1];
					}
					else
					{
						$core->error->error("Код неправильный");
					}
			}
			else
			{
				$core->error->error("Код не введён");
			}
		}
		elseif(isset($_POST['email']) && isset($_POST['email']))
		{
			$err = 0;
			
			if(empty($_POST['email']))
			{
				$core->error->error("Вы не заполнили поле \"e-mail\"");
				$err = 1;
			}
			if(empty($_POST['captcha']))
			{
				$core->error->error("Вы не ввели капчу");
				$err = 1;
			}
			
			if($err==0)
			{
				$core->api->get('login.restore',array(
				'step'=>'1',
				'captcha'=>$_POST['captcha'],
				'email'=>$_POST['email'],
				'sid'=>$_SESSION['sid'],
				));
				if(isset($core->api->answer_decode['data'][0]))
					if($core->api->answer_decode['data'][0]==true)
					{
						$core->render['MAIN']['SUCCESS'][] = "Дальнейшие инструкции по восстановлению Вы получите по e-mail";
					}
					else
					{
						$core->render['_POST'] = $_POST;
					}
			}
		}
		
		$core->api->get('captcha.set',array('type'=>'user_pass_restore'));
		$core->render['cap_src'] = $core->conf->conf->core->api->url."system-scripts/captcha.php?type=user_pass_restore&sid=".$_SESSION['sid'];
		
		$core->f->show('login/restore','users');
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

$core->f->show('login/main','users');
?>