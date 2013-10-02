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
	
	if(!empty($_FILES['icon']['size']) && $_FILES['icon']['size']!=0)
	{
		$file = file_get_contents($_FILES['icon']['tmp_name']);
		
		/*$params = array('http' => array(
			'method' => 'POST',
			'content' => $fileString
		));
		$context = stream_context_create($params);
		
		if($remote = @fopen('http://server2.ru/filesend.php', 'rb', false, $context)){
			$response = @stream_get_contents($remote);
		}*/
		
		$core->api->get('profile.change',array('type'=>'icon','value'=>$file));
		if(!empty($core->api->answer_decode['data'][0]))
		$ans = $core->api->answer_decode['data'][0];
		else
		$ans = false;
		
		if($ans!=false)
		{
			$core->render['MAIN']['SUCCESS'][] = 'Аватарка обновлена: '.$ans;
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

$core->f->show('profile/main','users');
?>