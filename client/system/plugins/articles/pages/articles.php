<?php
namespace CRAFTEngine\client\plugins\articles;
if(!defined('CE_HUB'))die('403');

if(!empty($_GET['act']))
{
	$act = $_GET['act'];
	
	if($act=='post' AND !empty($_GET['user_id']) AND !empty($_GET['post_id']))
	{
		$core->api->get('article/article/post',array('post_id'=>$_GET['post_id'],'user_id'=>$_GET['user_id']));
		$data = $core->api->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		$core->f->quit(404);
		
		if($data['data']['status']!='publish')
		$core->f->quit(403);
		
		$post = $data['data'];
		//$post['tags'] = implode(', ',$post['tags']);	
		//$post['post_time'] = date('d-m-Y H:i',$post['post_time']);
		$post['article'] = str_replace("\n",'<br /> ',$post['article']);
		$post['article'] = str_replace('<br /> ',"<br />\r\n",$post['article']);


		$b = array('[b]','[/b]','[i]','[/i]','[s]','[/s]','[u]','[/u]','[url]','[/url]');
		$a = array('<b>','</b>','<i>','</i>','<s>','</s>','<u>','</u>','<a href="','">Link</a>');
		$post['article'] = str_replace($b,$a,$post['article']);

		$post['article'] = preg_replace("'^(.*)\[craftcut(|=(.*))\](.*)$'is","$1$4",$post['article']);

		$core->render['post'] = $post;
		
		$core->f->show('articles/post','articles');
	}
	elseif($act=='posts')
	{
		$core->api->get('article/article/posts',array('page'=>$_GET['page']));
		
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
			$post['article'] = str_replace("\n",'<br /> ',$post['article']);
			$post['article'] = str_replace('<br /> ',"<br />\r\n",$post['article']);
			$desc = mb_substr($post['article'], 0, 150, 'UTF-8');
			$desc = str_replace("<br />\r\n",' ',$desc);
			foreach($post['tags'] as $t)$tags[trim($t)] = trim($t);
			//$post['tags'] = implode(', ',$post['tags']);	
			//$post['post_time'] = date('d-m-Y H:i',$post['post_time']);

			$b = array('[b]','[/b]','[i]','[/i]','[s]','[/s]','[u]','[/u]','[url]','[/url]');
			$a = array('<b>','</b>','<i>','</i>','<s>','</s>','<u>','</u>','<a href="','">Link</a>');
			$post['article'] = str_replace($b,$a,$post['article']);

			$post['article'] = preg_replace("'^(.*)\[craftcut(|=(.*))\](.*)$'is","$1$3",$post['article']);

			$posts[] = $post;
		}
		$core->render['type'] = 'default';
		$core->render['posts'] = $posts;
		$core->f->show('articles/main','articles');
	}
	elseif($act=='new')
	{
		if(!$_SESSION['loggedin'])$core->f->quit(404);
		
		if(!empty($_POST['title']) AND !empty($_POST['article']))
		{
			if(empty($_POST['tags']))$_POST['tags'] = '';

			$tags = explode(',',$_POST['tags']);
			foreach($tags as &$t)$t = trim($t);
			$tags = implode(',',$tags);
			
			$core->api->get('article/article/new',array('title'=>$_POST['title'],'article'=>$_POST['article'],'tags'=>$tags));
			$data = $core->api->answer_decode;
			
		}
		
		$core->f->show('articles/new','articles');
	}
	elseif($act=='confirm')
	{
		if(!empty($_POST['vote']) AND !empty($_POST['id']))
		{
			if($_POST['vote']=='plus')$con = true;
			elseif($_POST['vote']=='minus')$con = false;
			else return;
			$core->api->get('article/article/confirm_new',array('id'=>$_POST['id'],'confirm'=>$con));
		}
		
		
		$page = (!empty($_GET['page']) AND $_GET['page']>0)?$_GET['page']:'1';
		$core->api->get('article/article/posts',array('page'=>$page,'type'=>'unpublished'));
		$data = $core->api->answer_decode;
		//print_r($data);
		
		if(isset($data['data'][0]))
		if($data['data'][0]==false)
		$core->f->quit(403);
		
		$core->render['type'] = 'unpublished';
		$core->render['posts'] = $data['data']['posts'];
		$core->render['pages'] = $data['data']['pages'];
		$core->render['page'] = $page;
		
		$core->f->show('articles/confirm','articles');
	}
	elseif($act=='edit')
	{
		if(!$_SESSION['loggedin'])$core->f->quit(404);
		
		$err_up = false;
		if(!empty($_POST['title']) AND !empty($_POST['article']))
		{
			if(empty($_POST['tags']))$_POST['tags'] = '';

			$tags = explode(',',$_POST['tags']);
			foreach($tags as &$t)$t = trim($t);
			$tags = implode(',',$tags);
			
			$core->api->get('article/article/edit',array('id'=>$_GET['post_id'],'title'=>$_POST['title'],'article'=>$_POST['article'],'tags'=>$tags));
			$data = $core->api->answer_decode;
			
			if(isset($data['data'][0]))
			if($data['data'][0]==false)
			$err_up = true;
			
			$post['title'] = $_POST['title'];
			$post['article'] = $_POST['article'];
			$post['tags'] = $_POST['tags'];
		}
		
		if($err_up==false)
		{
			$core->api->get('article/article/post',array('post_id'=>$_GET['post_id'],'user_id'=>$_GET['user_id']));
			$data = $core->api->answer_decode;
			
			if(isset($data['data'][0]))
			if($data['data'][0]==false)
			$core->f->quit(404);
			
			$post = $data['data'];
			
			$core->render['post'] = $post;
		}
		
		$core->f->show('articles/edit','articles');
	}
	else
		$core->f->quit(404);
}
else
{
	$core->f->quit(404);
}
?>