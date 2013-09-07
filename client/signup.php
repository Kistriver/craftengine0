<?php
require_once(dirname(__FILE__).'/system/include.php');

if($_SESSION['loggedin'])$core->f->quit(403);

if(isset($_POST['login']) and isset($_POST['pass']))
{
	$err = 0;
	if(!isset($_POST['agree']))
	{
	$_POST['agree']='off';
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
	'captcha'=>'',
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
}

$core->f->show('signup/main');
?>