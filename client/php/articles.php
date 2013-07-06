<?php
include_once(dirname(__FILE__).'/../system/core/include.php');

if(!empty($_GET['act']))
{
	$act = $_GET['act'];
	
	if($act=='post' AND !empty($_GET['user_id']) AND !empty($_GET['post_id']))
	{
		$core->get('article.post',array('post_id'=>$_GET['post_id'],'user_id'=>$_GET['user_id'],'sid'=>$_SESSION['sid']));
		$data = $core->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		display($core,$twig,404);
		
		if($data['data']['status']!='publish')
		display($core,$twig,403);
		
		$post = $data['data'];
		//$post['tags'] = implode(', ',$post['tags']);	
		//$post['post_time'] = date('d-m-Y H:i',$post['post_time']);
		$post['article'] = str_replace("\r\n",'<br /> ',$post['article']);
		$post['article'] = str_replace('<br /> ',"<br />\r\n",$post['article']);
		
		$core->render['post'] = $post;
		
		$template = $twig->loadTemplate('articles/post');
		
		echo $template->render($core->render());
	}
	elseif($act=='posts')
	{
		$core->get('article.posts',array('page'=>'1','sid'=>$_SESSION['sid']));
		$data = $core->answer_decode;
		
		$content = '';
		$tags = array();
		$desc = '';
		
		$template = $twig->loadTemplate('articles/main');
		$posts = array();
		
		if(sizeof($data['errors'])==0)
		for($i=0;$i<sizeof($data['data']['posts']);$i++)
		{
			$template = $twig->loadTemplate('articles/main');
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
		$core->render['posts'] = $posts;
		echo $template->render($core->render());
	}
	elseif($act=='new')
	{
		if(!$_SESSION['loggedin'])display($core,$twig,403);
		
		if(!empty($_POST['title']) AND !empty($_POST['article']))
		{
			if(empty($_POST['tags']))$_POST['tags'] = '';
			
			$core->get('article.new',array('title'=>$_POST['title'],'article'=>$_POST['article'],'tags'=>$_POST['tags'],'sid'=>$_SESSION['sid']));
			$data = $core->answer_decode;
			
		}
		
		$template = $twig->loadTemplate('articles/new');
		
		echo $template->render($core->render());
	}
	elseif($act=='confirm')
	{
		$template = $twig->loadTemplate('articles/confirm');
		
		echo $template->render($core->render());
	}
	else
		display($core,$twig,404);
}
else
{
	display($core,$twig,404);
}
?>