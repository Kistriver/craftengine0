<?php
namespace CRAFTEngine\client\plugins\articles;
if(!defined('CE_HUB'))die('403');

//$core->f->msg('error','test');

if(!empty($_GET['act']))
{
	$act = $_GET['act'];

	if(sizeof($_POST)!=0)
	{
		if(!empty($_POST['msg']) && (new \CRAFTEngine\client\plugins\users\load($core))->loggedin())
		{
			$data = $core->api->get('articles/comment/new',array('article'=>$_GET['id'],'value'=>$_POST['msg']));
		}
	}
	
	if($act=='post' AND !empty($_GET['id']))
	{
		$core->render['SYS']['NOHEADER'] = true;
		$core->render['SYS']['NOMAINBORDER'] = true;

		$core->api->get('articles/article/post',array('id'=>$_GET['id']));
		$data = $core->api->answer_decode;
		
		if(isset($data['data'][0]))
		if($data['data'][0]===false)
		$core->f->quit(404);
		
		$post = $core->f->sanString($data['data'][1]);
		//$post['tags'] = implode(', ',$post['tags']);	
		//$post['post_time'] = date('d-m-Y H:i',$post['post_time']);
		$post['body'] = str_replace("\n",'<br /> ',$post['body']);
		$post['body'] = str_replace('<br /> ',"<br />\r\n",$post['body']);


		$b = array('[b]','[/b]','[i]','[/i]','[s]','[/s]','[u]','[/u]','[url]','[/url]');
		$a = array('<b>','</b>','<i>','</i>','<s>','</s>','<u>','</u>','<a href="','">Link</a>');
		$post['body'] = str_replace($b,$a,$post['body']);

		$post['body'] = preg_replace("'^(.*)\[craftcut(|=(.*))\](.*)$'is","$1$4",$post['body']);

		$core->render['post'] = $post;



		$data = $core->api->get('articles/comment/get',array('article'=>$_GET['id']));

		if(isset($data['data'][0]))
		if($data['data'][0]!==false)
		foreach($data['data'] as $d)
		{
			$d = $core->f->sanString($d);
			$d['value'] = str_replace("\n",'<br />',$d['value']);
			$core->render['comments'][] = $d;
		}

		
		$core->f->show('articles/post','articles');
	}
	elseif($act=='posts')
	{
		$core->render['SYS']['NOHEADER'] = true;
		$core->render['SYS']['NOMAINBORDER'] = true;

		$core->api->get('articles/article/posts',array('page'=>$_GET['page']));
		
		$data = $core->api->answer_decode;
		
		$content = '';
		$tags = array();
		$desc = '';
		
		$posts = array();
		
		if($data['data'][0]!==false)
		for($i=0;$i<sizeof($data['data'][1]);$i++)
		{
			//$template = $twig->loadTemplate('articles/main');
			$post = $core->f->sanString($data['data'][1][$i]);

			if(!isset($post['body']))$post['body']='';
			if(!isset($post['tags']))$post['tags']=array();


			$post['body'] = str_replace("\n",'<br /> ',$post['body']);
			$post['body'] = str_replace('<br /> ',"<br />\r\n",$post['body']);
			$desc = mb_substr($post['body'], 0, 150, 'UTF-8');
			$desc = str_replace("<br />\r\n",' ',$desc);
			foreach($post['tags'] as $t)$tags[trim($t)] = trim($t);
			//$post['tags'] = implode(', ',$post['tags']);	
			//$post['post_time'] = date('d-m-Y H:i',$post['post_time']);

			$b = array('[b]','[/b]','[i]','[/i]','[s]','[/s]','[u]','[/u]','[url]','[/url]');
			$a = array('<b>','</b>','<i>','</i>','<s>','</s>','<u>','</u>','<a href="','">Link</a>');
			$post['body'] = str_replace($b,$a,$post['body']);

			$post['body'] = preg_replace("'^(.*)\[craftcut(|=(.*))\](.*)$'is","$1$3",$post['body']);

			$posts[] = $post;
		}
		$core->render['type'] = 'default';
		$core->render['posts'] = $posts;
		$core->f->show('articles/main','articles');
	}
	elseif($act=='new')
	{
		if(!(new \CRAFTEngine\client\plugins\users\load($core))->loggedin())$core->f->quit(404);
		
		if(!empty($_POST['title']) AND !empty($_POST['article']))
		{
			if(empty($_POST['tags']))$_POST['tags'] = '';

			$tags = explode(',',$_POST['tags']);
			foreach($tags as &$t)$t = trim($t);
			$tags = implode(',',$tags);
			
			$core->api->get('articles/article/new',array('title'=>$_POST['title'],'body'=>$_POST['article'],'tags'=>$tags));
			$data = $core->api->answer_decode;
		}
		
		$core->f->show('articles/new','articles');
	}
	elseif($act=='edit')
	{
		if(!(new \CRAFTEngine\client\plugins\users\load($core))->loggedin())$core->f->quit(404);
		
		$err_up = false;
		if(!empty($_POST['title']) AND !empty($_POST['article']))
		{
			if(empty($_POST['tags']))$_POST['tags'] = '';

			$tags = explode(',',$_POST['tags']);
			foreach($tags as &$t)$t = trim($t);
			$tags = implode(',',$tags);
			
			$core->api->get('articles/article/edit',array('id'=>$_GET['id'],'title'=>$_POST['title'],'article'=>$_POST['article'],'tags'=>$tags));
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
			$core->api->get('articles/article/post',array('id'=>$_GET['id']));
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