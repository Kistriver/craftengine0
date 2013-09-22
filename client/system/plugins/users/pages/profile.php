<?php
if(!defined('CE_HUB'))die('403');

if(!$_SESSION['loggedin'])$core->f->quit(403);

$type = 'main';
if(!empty($_GET['type']))
{
	$type = $_GET['type'];
}
$_GET['type'] = $type;

if($type=='main')
{
	if(!empty($_POST['nickname']))
	{
		$nick = $_POST['nickname'];

		$core->api->get('profile.change',array('type'=>'nickname','value'=>$nick));
		$ans = $core->api->answer_decode['data'][0];
		if($ans===true)
		{
			$core->render['MAIN']['SUCCESS'][] = 'Никнейм изменён';
		}
	}
}
elseif($type=='private')
{

}
elseif($type=='security')
{
	if(!empty($_POST['password']) && !empty($_POST['password_r']) && !empty($_POST['password_old']))
	{
		if($_POST['password']!==$_POST['password_r'])
		{
			$core->error->error('Пароли не совпадают');
		}
		else
		{
			$pass = $_POST['password'];
			$pass_old = $_POST['password_old'];

			$core->api->get('profile.change',array('type'=>'pass','value'=>$pass, 'value_old'=>$pass_old));
			$ans = $core->api->answer_decode['data'][0];
			if($ans===true)
			{
				$core->render['MAIN']['SUCCESS'][] = 'Пароль изменён';
			}
		}
	}
}

$core->f->show('profile/main');
?>