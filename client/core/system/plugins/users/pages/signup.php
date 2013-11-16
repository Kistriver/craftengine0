<?php
if(!defined('CE_HUB'))die('403');

if($_SESSION['loggedin'])$core->f->quit(403);

if(isset($_POST['login']) and isset($_POST['pass']))
{
	$err = 0;
	if(!isset($_POST['agree']))
	{
	$_POST['agree']='off';
	$core->error->error("Вы не согласились с условиями пользовательского соглашения");
	$err = 1;
	}
	if(!isset($_POST['sex']))
	{
	$_POST['sex']='';
	$err = 1;
	}
	if(!preg_match("'[0-9]{2}\/[0-9]{2}\/[0-9]{4}'is",$_POST['birthday']))
	{
	$core->error->error("Неправильный формат даты");
	$err = 1;
	}
	if($_POST['pass']!=$_POST['pass_r'])
	{
	$core->error->error("Пароли не совпадают");
	$err = 1;
	}
	if($_POST['email']!=$_POST['email_r'])
	{
	$core->error->error("E-mail'ы не совпадают");
	$err = 1;
	}
	
	if($err==0)
	{
	$core->api->get('signup.signup',array(
	'captcha'=>$_POST['captcha'],
	'name'=>$_POST['name'],
	'surname'=>$_POST['surname'],
	'login'=>$_POST['login'],
	'invite'=>$_POST['invite'],
	'password'=>$_POST['pass'],
	'email'=>$_POST['email'],
	'sex'=>$_POST['sex'],
	'birthday'=>$_POST['birthday'],
	'about'=>$_POST['about'],
	'agree'=>$_POST['agree'],
	'sid'=>$_SESSION['sid'],
	));
	if(isset($core->api->answer_decode['data'][0]))
	if($core->api->answer_decode['data'][0]==true)
	$core->render['MAIN']['SUCCESS'][] = "Регистрация прошла успешно";
	}
	else
	{
		$core->render['_POST'] = $_POST;
		$core->render['_GET'] = $_GET;
	}

	$core->render['_POST'] = $_POST;
	$core->render['_GET'] = $_GET;
}

$core->api->get('captcha.set',array('type'=>'user_signup'));
$core->render['cap_src'] = $core->conf->conf->core->api->url."system-scripts/captcha.php?type=user_signup&sid=".$_SESSION['sid'];

$core->f->show('signup/main','users');
?>