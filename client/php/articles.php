<?php
include_once(dirname(__FILE__).'/../system/core/include.php');

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
	$post['tags'] = implode(', ',$post['tags']);	
	$post['post_time'] = date('d-m-Y H:i',$post['post_time']);
	
	$posts[] = $post;
}
$core->render ['posts'] = $posts;
echo $template->render($core->render());
?>