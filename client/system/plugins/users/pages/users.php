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
		$core->render['SYS']['NOHEADER'] = true;
		$core->render['SYS']['NOMAINBORDER'] = true;
		//$core->render['type'] = 'user';
		
		if(!empty($_GET['id']))
		$core->api->get('users/user/get',array('id'=>$_GET['id']));
		elseif(!empty($_GET['login']))
		$core->api->get('users/user/get',array('login'=>$_GET['login']));
		else
		$core->f->quit(404);
		
		$data = $core->api->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]===false)
		$core->f->quit(404);

		foreach($data['data']['rank'] as $r)
		{
			$data['data']['rank_name'][] = $LC->appointment($r)!==$r?$LC->appointment($r):'Undefined';
		}

		$data['data']['appointment'] = $data['data']['rank_name'][0];
		
		$core->render['user'] = $data['data'];
		$core->render['avatar_src'] = $core->conf->conf->core->api->files.$data['data']['avatar'];
	}
	elseif($act=='all')
	{
		$core->render['SYS']['NOHEADER'] = true;
		$core->render['SYS']['NOMAINBORDER'] = true;

		$core->api->get('users/user/list',array('page'=>$_GET['page']));
		$data = $core->api->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		$core->f->quit(404);

		for($i=0;$i<sizeof($data['data']);$i++)
		{
			$user = &$data['data'][$i];

			$user['rank_name'] = array();
			foreach($user['rank'] as $r)
			{
				$user['rank_name'][] = $LC->appointment($r)!==$r?$LC->appointment($r):'Undefined';
			}

			$user['appointment'] = $user['rank_name'][0];
			$user['avatar'] = $core->conf->conf->core->api->files.$user['avatar'];
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