<?php
include_once(dirname(__FILE__).'/../core/include.php');


if(isset($_POST['login']) and isset($_POST['pass']))
{
	//quick checking data
	//request to API
}


$core->tpl->tpl('signup/main');
$content = $core->tpl->render();

$main['TITLE'] = 'Регистрация | '.$main['NAME'];
$main['KEYWORDS'] = $main['NAME'].', signup, register';
$main['DESC'] = 'Регистрация';
$main['HEADER'] = 'Регистрация';

foreach ($core->errors as $er)
{
	if(is_array($er))$main['ERRORS'] .= "[$er[0]] $er[1]<br />\r\n";
	else $main['ERRORS'] .= "$er<br />\r\n";
}
//$main['ERRORS'] = implode("<br />\r\n", $core->errors);

$core->tpl->tpl('html/main/main');
$main['CONTENT'] = $content;
$core->tpl->assign('MAIN',$main);
$core->tpl->assign('MOD',$mod);
echo $core->tpl->render();
?>