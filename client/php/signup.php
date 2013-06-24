<?php
include_once(dirname(__FILE__).'/../core/include.php');


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


$core->tpl->tpl('signup/main');
$content = $core->tpl->render();

$main['TITLE'] = 'Регистрация | '.$main['NAME'];
$main['KEYWORDS'] = $main['NAME'].', signup, register';
$main['DESC'] = 'Регистрация';
$main['HEADER'] = 'Регистрация';

foreach ($core->errors as $er)
{
	if(is_array($er))$main['ERRORS'] .= "[$er[0]] $er[1]<br />\r\n";
	else $main['ERRORS'] .= "$er<br />\r\n";
}
//$main['ERRORS'] = implode("<br />\r\n", $core->errors);

$core->tpl->tpl('html/main/main');
$main['CONTENT'] = $content;
$core->tpl->assign('MAIN',$main);
$core->tpl->assign('MOD',$mod);
echo $core->tpl->render();
?>