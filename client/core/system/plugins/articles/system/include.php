<?php
$core->rules[] = array('^articles$','articles.php', array('act'=>'posts','page'=>'1'));
$core->rules[] = array('^articles/page-([0-9]*)$','articles.php', array('act'=>'posts','page'=>'$1'));
$core->rules[] = array('^articles/([0-9]*)/([0-9]*)$','articles.php', array('act'=>'post','user_id'=>'$1','post_id'=>'$2'));
$core->rules[] = array('^articles/confirm/page-([0-9]*)$','articles.php', array('act'=>'confirm','page'=>'$1'));
$core->rules[] = array('^articles/([a-z]*)$','articles.php', array('act'=>'$1'));
$core->rules[] = array('^articles/edit/([0-9]*)-([0-9]*)$','articles.php', array('act'=>'edit','user_id'=>'$1','post_id'=>'$2'));

?>