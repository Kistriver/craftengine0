<?php
namespace CRAFTEngine\client\plugins\users;
if(!defined('CE_HUB'))die('403');

if(!(new load($core))->loggedin())$core->f->quit(403);

$type = 'main';
if(!empty($_GET['type']))
{
	$type = $_GET['type'];
}
$_GET['type'] = $type;

if($type=='main')
{
	$core->render['icon_src'] = $core->conf->conf->core->api->files.$_SESSION['users']['avatar'];

	if(!empty($_POST['nickname']))
	{
		$nick = $_POST['nickname'];

		$core->api->get('users/profile/change',array('type'=>'nickname','value'=>$nick));
		$ans = $core->api->answer_decode['data'][0];
		if($ans===true)
		{
			$core->render['MAIN']['SUCCESS'][] = 'Никнейм изменён';
		}
	}
	
	if(!empty($_FILES['icon']['size']))
	{
		$file = file_get_contents($_FILES['icon']['tmp_name']);

		$name_orig = explode('.',$_FILES['icon']['name']);
		$format = strtolower(array_pop($name_orig));
		
		$core->api->get('users/profile/change',array('type'=>'icon','value'=>'icon','format'=>$format));
		if(!empty($core->api->answer_decode['data'][0]))
		$ans = $core->api->answer_decode['data'][0];
		else
		$ans = false;

		if($ans!=false)
		{
			$params = array('http' => array(
				'method' => 'POST',
				'content' => $file,
				'header' => "Content-type: application/x-www-form-urlencoded\r\n".
				"Content-Length: ".strlen($file)."\r\n"
			));
			$context = stream_context_create($params);
			
			if($remote = @fopen($ans, 'rb', false, $context)){
				$response = @stream_get_contents($remote);
			}
			$a = json_decode($response,true);
			$core->f->msg('success','Аватарка обновлена');
		}
		else
		{
			$core->f->msg('error','Аватарка не обновлена');
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

			$core->api->get('users/profile/change',array('type'=>'pass','value'=>$pass, 'value_old'=>$pass_old));
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