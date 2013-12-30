<?php
namespace CRAFTEngine\client\plugins\users;
if(!defined('CE_HUB'))die('403');

if(isset($_GET['act']))
{
	$LC = '\CRAFTEngine\client\plugins\users\\'.$core->plugins->getList()['users']['loadClass'];
	$LC = new $LC($core);

	$act = $_GET['act'];
	$core->render['type'] = $act;
	if($act=='user')
	{
		//$core->render['type'] = 'user';
		
		if(!empty($_GET['id']))
		$core->api->get('user/user/get',array('type'=>'id','value'=>$_GET['id']));
		elseif(!empty($_GET['login']))
		$core->api->get('user/user/get',array('type'=>'login','value'=>$_GET['login']));
		else
		$core->f->quit(404);
		
		$data = $core->api->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		$core->f->quit(404);

		foreach($data['data']['rank'] as &$r)
		{
			$r = $LC->appointment($r)?$LC->appointment($r):'Undefined';
			//$r = 'Undefined';
		}
		
		//$data['data']['rank'] = implode(', ',$data['data']['rank']);
		//$data['data']['appointment'] = 'Undefined';
		$data['data']['appointment'] = $LC->appointment($data['data']['rank_main'])?$LC->appointment($data['data']['rank_main']):'Undefined';
		
		$core->render['user'] = $data['data'];
		$core->render['icon_src'] = $core->conf->conf->core->api->files.'users/avatars/id'.$data['data']['id'].'.'.$data['data']['avatar_format'];
	}
	elseif($act=='all' or $act=='confirm')
	{
		//$core->render['type'] = 'all';
		if(!empty($_POST['vote']) AND !empty($_POST['user']))
		{
			if($_POST['vote']=='plus')$con = 'true';
			elseif($_POST['vote']=='minus')$con = 'false';
			elseif($_POST['vote']=='mail')$con = 'mail';
			else return;
			$core->api->get('user/user/confirm',array('login'=>$_POST['user'],'confirm'=>$con));
		}
		
		$type = ($act=='all')?'':'signup';
		$core->api->get('user/user/list',array('page'=>$_GET['page'],'type'=>$type));
		$data = $core->api->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		$core->f->quit(404);
		
		if($act=='all')
		for($i=0;$i<sizeof($data['data']);$i++)
		{
			$user = &$data['data'][$i];
			$user['appointment'] = $LC->appointment($user['rank_main'])?$LC->appointment($user['rank_main']):'Undefined';
			//$user['appointment'] = 'Undefined';
		}
		$core->render['users'] = $data['data'];
	}
}
else
{
	$core->f->quit(404);
}

$core->f->show('users/main','users');
?>