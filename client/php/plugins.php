<?php
include_once(dirname(__FILE__).'/../system/core/include.php');

if(!$_SESSION['loggedin'])display($core, $twig, 403);
if($_SESSION['rank'][0]!=1)display($core, $twig, 403);

if(!empty($_POST['state']) AND !empty($_POST['name']))
{
	$state = $_POST['state']=='ON'?'on':'off';
	$core->get('plugin.'.$state,array('name'=>$_POST['name'],'sid'=>$_SESSION['sid']));
}

$core->get('plugin.list',array('sid'=>$_SESSION['sid']));
$data = $core->answer_decode;

if(sizeof($data['errors'])==0)
{
	foreach($data['data']['included'] as $f=>$c)
	foreach($data['data']['all'] as $fa=>&$ca)
	if($f==$fa AND $c['name']==$ca['name'])
	$ca['_included_'] = true;
	
	$core->render['plugins'] = $data['data']['all'];
	//$core->render['pluginsWorked'] = $data['data']['included'];
}

$template = $twig->loadTemplate('plugins/main');
echo $template->render($core->render());
?>