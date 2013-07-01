<?php
include_once(dirname(__FILE__).'/../core/include.php');

if(!empty($_GET['act']))
{
	if($_GET['act']=='logout')
	{
		$core->get('login.logout',array('sid'=>$_SESSION['sid']));
		header('Location: index');
	}
}
else
if(!empty($_POST['email']) and !empty($_POST['password']))
{
	$core->get('login.login',array('email'=>$_POST['email'], 'password'=>$_POST['password'],'sid'=>$_SESSION['sid']));
	$data = $core->answer_decode;
	
	if(sizeof($data['errors'])==0)
	{
		header('Location: users');
	}
}

$template = $twig->loadTemplate('login/main');
echo $template->render($core->render());
?>