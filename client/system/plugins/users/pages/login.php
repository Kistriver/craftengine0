<?php
namespace CRAFTEngine\client\plugins\users;
if(!defined('CE_HUB'))die('403');

if(!empty($_GET['act']))
{
	$act = $_GET['act'];
	if($act=='login')
	{
		if((new load($core))->loggedin())$core->f->quit(403);

		if(!empty($_POST['email']) and !empty($_POST['password']))
		{
			$core->api->get('users/login/login',array('email'=>$_POST['email'], 'password'=>$_POST['password']));
			$data = $core->api->answer_decode;

			if($data['data'][0]!==false)
			{
				$core->api->get('users/user/get',array('id'=>$data['data'][1]));
				//(new load($core))->currentUser($data['data'][1]);
				$loggedin = $core->api->answer_decode;

				if(isset($loggedin['data'][0]))
					if($loggedin['data'][0]===false)
					{
						$core->f->show('login/main','users');
						exit;
					}

				foreach($loggedin['data'] as $k=>$v)
				{
					$_SESSION['users'][$k] = $v;
				}

				//setcookie("authed", $loggedin['data']['id'].':'.$loggedin['data']['auth'], time()+36000,'/');
				header('Location: users/'.$loggedin['data']['login']);
			}
		}
	}
	elseif($act=='logout')
	{
		if((new load($core))->loggedin())
		{
		$core->api->get('users/login/logout');
		setcookie("authed", "", 0,'/');
		//session_destroy();
		(new load($core))->currentUser(0);
		header('Location: index');
		}
		else
		{
			$core->f->quit(403);
		}
	}
	elseif($act=='restore')
	{
		if((new load($core))->loggedin())$core->f->quit(403);

		if(isset($_GET['code']))
		{
			if(!empty($_GET['code']))
			{
				$core->api->get('users/login/restore',array(
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
				$core->api->get('users/login/restore',array(
				'step'=>'1',
				'captcha'=>$_POST['captcha'],
				'email'=>$_POST['email'],
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
		
		$core->api->get('captcha/captcha/set',array('type'=>'users_pass_restore'));
		$core->render['cap_src'] = $core->conf->conf->core->api->url."script.php?module=captcha&script=captcha&type=users_pass_restore&sid=".$_SESSION['sid'];
		
		$core->f->show('login/restore','users');
		die;
	}
}
else
{
	$core->f->quit(404);
}

$core->f->show('login/main','users');
?>