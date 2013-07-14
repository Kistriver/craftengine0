<?php
include_once(dirname(__FILE__).'/../system/core/include.php');
if(isset($_GET['act']))
{
	$act = $_GET['act'];
	$core->render['type'] = $act;
	if($act=='user')
	{
		//$core->render['type'] = 'user';
		
		if(!empty($_GET['id']))
		$core->get('user.get',array('type'=>'id','value'=>$_GET['id'],'sid'=>$_SESSION['sid']));
		elseif(!empty($_GET['login']))
		$core->get('user.get',array('type'=>'login','value'=>$_GET['login'],'sid'=>$_SESSION['sid']));
		else
		display($core,$twig,404);
		
		$data = $core->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		display($core,$twig,404);
		
		$data['data']['rank'] = implode(', ',$data['data']['rank']);
		$data['data']['appointment'] = appointment($data['data']['rank_main'])?appointment($data['data']['rank_main']):'Undefined';
		
		$core->render['user'] = $data['data'];
	}
	elseif($act=='all' or $act=='confirm')
	{
		//$core->render['type'] = 'all';
		if(!empty($_POST['vote']) AND !empty($_POST['user']))
		{
			if($_POST['vote']=='plus')$con = true;
			elseif($_POST['vote']=='minus')$con = false;
			else return;
			$core->get('user.confirm',array('login'=>$_POST['user'],'confirm'=>$con,'sid'=>$_SESSION['sid']));
		}
		
		$type = ($act=='all')?'':'signup';
		$core->get('user.list',array('page'=>$_GET['page'],'type'=>$type,'sid'=>$_SESSION['sid']));
		$data = $core->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		display($core,$twig,404);
		
		if($act=='all')
		for($i=0;$i<sizeof($data['data']);$i++)
		{
			$user = &$data['data'][$i];
			$user['appointment'] = appointment($user['rank_main'])?appointment($user['rank_main']):'Undefined';
		}
		$core->render['users'] = $data['data'];
	}
}
else
{
	display($core,$twig,404);
}

$template = $twig->loadTemplate('users/main');
echo $template->render($core->render());
?>