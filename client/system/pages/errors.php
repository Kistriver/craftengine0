<?php
if(!defined('CE_HUB'))die('403');

if(!empty($_GET['code']))
{
	$core->f->quit($_GET['code']);
}
else
{
	$core->f->quit(404);
}
?>