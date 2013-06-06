<?php
session_start();

include_once(dirname(__FILE__).'/../core/core.class.php');
$core = new core();

/*$core->tpl->tpl('articles/instance');
$main = array(
	'TITLE'=>'Новости',
	'KEYWORDS'=>'KachalovCRAFT NET',
	'DESC'=>'KachalovCRAFT NET',
	'NAME'=>'KachalovCRAFT NET',
	'ERRORS'=>'',
);

	$post['article'] = 'sedsd';
	$post['tags'] = implode(', ',array(1, 2));
	$post['post_time'] = date('d-m-Y H:i');

$core->tpl->assign('post',$post);
$core->tpl->assign('MAIN',$main);
echo $core->tpl->render();

die();*/
/*$core->tpl->tpl('articles/instance');
$ex = array('olo'=>'=)', 'aza'=>'XD');
$core->tpl->assign('ex',$ex);
$ex_r = $core->tpl->render();

$core->tpl->tpl('main');
$core->tpl->assign('CONTENT',$ex_r);
echo $core->tpl->render();*/

$core->get('article','posts',array('page'=>'1','sid'=>session_id().'php'));
//$core->get('article','post',array('post_id'=>'1','user_id'=>'1','sid'=>session_id().'php'));
//$core->get('article','new',array('page'=>'1','sid'=>session_id().'php'));
//$_SESSION['sid']=$data['sid'];
$data = $core->answer_decode;
/*
echo "<pre>";
echo $core->url."\r\n";
echo $core->answer."\r\n";
print_r($core->answer_decode);
echo "</pre>";
die;*/
$content = '';
for($i=0;$i<sizeof($data['data']['posts']);$i++)
{
	$core->tpl->tpl('articles/instance');
	$post = $data['data']['posts'][$i];
	$post['article'] = str_replace("\r\n",'<br /> ',$post['article']);
	$post['article'] = str_replace('<br /> ',"<br />\r\n",$post['article']);
	$post['tags'] = implode(', ',$post['tags']);
	$post['post_time'] = date('d-m-Y H:i',$post['post_time']);
	
	$core->tpl->assign('post',$post);
	$content .= $core->tpl->render();
}

$errors = array();
foreach($data['errors'] as $er)
{
	if(is_array($er))
	$errors[] = "[$er[0]] $er[1]";
	else
	$errors[] = $er;
}

$mod = array(
);
if(!$_SESSION['loggedin'])$login='/html/main/login';
else $login='/html/main/logined';
$core->tpl->tpl($login);
$core->tpl->assign('SESSION',$_SESSION);
$mod['LOGIN'] = $core->tpl->render();

$main = array(
	'TITLE'=>'Новости | KachalovCRAFT NET',
	'KEYWORDS'=>'KachalovCRAFT NET',
	'DESC'=>'KachalovCRAFT NET',
	'HEADER'=>'Новости',
	'ERRORS'=>implode('<br />',$errors),
);
$core->tpl->tpl('html/main/main');
$main['CONTENT'] = $content;
$core->tpl->assign('MAIN',$main);

$core->tpl->assign('MOD',$mod);
echo $core->tpl->render();
die;
?>

<html>
<body>
<pre>
<?php
echo $core->url."\r\n";
echo $core->answer."\r\n";
?>
</pre>
<div>
<?php
for($i=0;$i<sizeof($data['data']['posts']);$i++)
{
	$post = $data['data']['posts'][$i];
	$post['article'] = str_replace("\r\n",'<br /> ',$post['article']);
	$post['article'] = str_replace('<br /> ',"<br />\r\n",$post['article']);
	echo '<h1>'.$post['title'].'</h1>';
	echo '<div>'.$post['article'].'</div>';
	echo '<br />';
	echo '<div>Author: '.$post['author_login'].'</div>';
	echo '<div>Tags: ';
	echo implode('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$post['tags']);
	echo '</div>';
	echo '<div>Views: '.$post['post_views'].'</div>';
	echo '<div>Date: '.date('d-m-Y H:i',$post['post_time']).'</div>';
	echo '<hr />';
}
?>
</div>
</body>
</html>