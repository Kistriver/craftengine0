<?php
namespace CRAFTEngine\client\plugins\users;
if(!defined('CE_HUB'))die('403');

if((new load($core))->loggedin())$core->f->quit(403);

if(isset($_GET['act']))
{
	switch($_GET['act'])
	{
		case 'activate':
			if(!empty($_GET['code']) && !empty($_GET['id']))
			{
				$code = $_GET['code'];
				$id = $_GET['id'];
				$core->api->get('users/signup/activate',array('code'=>$code,'id'=>$id,'type'=>'mail'));
				if($core->api->answer_decode['data'][0]===true)
				{
					$core->f->msg('success','Код активации принят');
				}
				elseif($core->api->answer_decode['data'][0]===false)
				{
					$core->f->msg('error','Код активации не принят');
				}
			}
			break;
	}

	$core->f->show('signup/main','users');
	exit;
}

if(isset($_POST['login']) and isset($_POST['pass']))
{
	$err = 0;
	if(!isset($_POST['agree']))
	{
	$_POST['agree']='off';
		$core->f->msg('error',"Вы не согласились с условиями пользовательского соглашения");
	$err = 1;
	}
	if(!isset($_POST['sex']))
	{
	$_POST['sex']='';
	$err = 1;
	}
	if($_POST['pass']!=$_POST['pass_r'])
	{
		$core->f->msg('error',"Пароли не совпадают");
	$err = 1;
	}
	if($_POST['email']!=$_POST['email_r'])
	{
		$core->f->msg('error',"E-mail'ы не совпадают");
	$err = 1;
	}
	
	if($err==0)
	{
	$core->api->get('users/signup/signup',array(
	'captcha'=>isset($_POST['captcha'])?$_POST['captcha']:null,
	'name'=>isset($_POST['name'])?$_POST['name']:null,
	'surname'=>isset($_POST['surname'])?$_POST['surname']:null,
	'login'=>isset($_POST['login'])?$_POST['login']:null,
	'invited'=>isset($_POST['invited'])?$_POST['invited']:null,
	'password'=>isset($_POST['pass'])?$_POST['pass']:null,
	'email'=>isset($_POST['email'])?$_POST['email']:null,
	'sex'=>isset($_POST['sex'])?$_POST['sex']:null,
	'birthday'=>isset($_POST['birthday'])?$_POST['birthday']:null,
	'about'=>isset($_POST['about'])?$_POST['about']:null,
	'agree'=>isset($_POST['agree'])?$_POST['agree']:null,
	));
	if(isset($core->api->answer_decode['data'][0]))
	if($core->api->answer_decode['data'][0]==true)
		$core->f->msg('success',"Регистрация прошла успешно");
	}
	else
	{
		$core->render['_POST'] = $_POST;
		$core->render['_GET'] = $_GET;
	}

	$core->render['_POST'] = $_POST;
	$core->render['_GET'] = $_GET;
}

$core->api->get('captcha/captcha/set',array('type'=>'users_signup'));
$core->render['cap_src'] = $core->conf->conf->core->api->url."script.php?module=captcha&script=captcha&type=users_signup&sid=".$_SESSION['sid'];

$core->f->show('signup/main','users');
?>