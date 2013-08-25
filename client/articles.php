<?php
require_once(dirname(__FILE__).'/system/include.php');

if(!empty($_GET['act']))
{
	$act = $_GET['act'];
	
	if($act=='post' AND !empty($_GET['user_id']) AND !empty($_GET['post_id']))
	{
		$core->api->get('article.post',array('post_id'=>$_GET['post_id'],'user_id'=>$_GET['user_id'],'sid'=>$_SESSION['sid']));
		$data = $core->api->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		$core->f->quit(404);
		
		if($data['data']['status']!='publish')
		$core->f->quit(403);
		
		$post = $data['data'];
		//$post['tags'] = implode(', ',$post['tags']);	
		//$post['post_time'] = date('d-m-Y H:i',$post['post_time']);
		$post['article'] = str_replace("\r\n",'<br /> ',$post['article']);
		$post['article'] = str_replace('<br /> ',"<br />\r\n",$post['article']);
		
		$core->render['post'] = $post;
		
		$core->f->show('articles/post');
	}
	elseif($act=='posts')
	{
		$core->api->get('article.posts',array('page'=>'1'));
		
		$data = $core->api->answer_decode;
		
		$content = '';
		$tags = array();
		$desc = '';
		
		$posts = array();
		
		if(sizeof($data['errors'])==0)
		for($i=0;$i<sizeof($data['data']['posts']);$i++)
		{
			//$template = $twig->loadTemplate('articles/main');
			$post = $data['data']['posts'][$i];
			$post['article'] = str_replace("\r\n",'<br /> ',$post['article']);
			$post['article'] = str_replace('<br /> ',"<br />\r\n",$post['article']);
			$desc = mb_substr($post['article'], 0, 150, 'UTF-8');
			$desc = str_replace("<br />\r\n",' ',$desc);
			foreach($post['tags'] as $t)$tags[trim($t)] = trim($t);
			//$post['tags'] = implode(', ',$post['tags']);	
			//$post['post_time'] = date('d-m-Y H:i',$post['post_time']);
			
			$posts[] = $post;
		}
		$core->render['type'] = 'default';
		$core->render['posts'] = $posts;
		$core->f->show('articles/main');
	}
	elseif($act=='new')
	{
		if(!$_SESSION['loggedin'])display($core,$twig,403);
		
		if(!empty($_POST['title']) AND !empty($_POST['article']))
		{
			if(empty($_POST['tags']))$_POST['tags'] = '';
			
			$core->api->get('article.new',array('title'=>$_POST['title'],'article'=>$_POST['article'],'tags'=>$_POST['tags'],'sid'=>$_SESSION['sid']));
			$data = $core->api->answer_decode;
			
		}
		
		$core->f->show('articles/new');
	}
	elseif($act=='confirm')
	{
		if(!empty($_POST['vote']) AND !empty($_POST['id']))
		{
			if($_POST['vote']=='plus')$con = true;
			elseif($_POST['vote']=='minus')$con = false;
			else return;
			$core->api->get('article.confirm_new',array('id'=>$_POST['id'],'confirm'=>$con,'sid'=>$_SESSION['sid']));
		}
		
		
		$page = (!empty($_GET['page']) AND $_GET['page']>0)?$_GET['page']:'1';
		$core->api->get('article.posts',array('page'=>$page,'type'=>'unpublished','sid'=>$_SESSION['sid']));
		$data = $core->api->answer_decode;
		//print_r($data);
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		$core->f->quit(403);
		
		$core->render['type'] = 'unpublished';
		$core->render['posts'] = $data['data']['posts'];
		$core->render['pages'] = $data['data']['pages'];
		$core->render['page'] = $page;
		
		$core->f->show('articles/confirm');
	}
	else
		$core->f->quit(404);
}
else
{
	$core->f->quit(404);
}
?>