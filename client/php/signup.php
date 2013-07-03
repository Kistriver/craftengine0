<?php
include_once(dirname(__FILE__).'/../system/core/include.php');

if($_SESSION['loggedin'])display($core, $twig, 403);

if(isset($_POST['login']) and isset($_POST['pass']))
{
	if(!isset($_POST['agree']))$_POST['agree']='off';
	if(!isset($_POST['sex']))$_POST['sex']='';
	if($_POST['pass']!=$_POST['pass_r'])$core->errors[] = "Пароли не совпадают";
	if($_POST['email']!=$_POST['email_r'])$core->errors[] = "E-mail'ы не совпадают";
	$core->get('signup.signup',array(
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
	'sid'=>$_SESSION['sid']
	));
	if($core->answer_decode['data'][0]==true)$core->errors[] = "Регистрация прошла успешно";
}

$template = $twig->loadTemplate('signup/main');
echo $template->render($core->render());
?>