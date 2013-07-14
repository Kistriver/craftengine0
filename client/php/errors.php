<?php
include_once(dirname(__FILE__).'/../system/core/include.php');

if(!empty($_GET['code']))
{
	display($core,$twig,$_GET['code']);
}
else
{
	display($core,$twig,404);
}
?>