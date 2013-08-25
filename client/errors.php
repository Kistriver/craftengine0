<?php
require_once(dirname(__FILE__).'/system/include.php');

if(!empty($_GET['code']))
{
	$core->f->quit($_GET['code']);
}
else
{
	$core->f->quit(404);
}
?>