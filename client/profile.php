<?php
require_once(dirname(__FILE__).'/system/include.php');
if(!$_SESSION['loggedin'])$core->f->quit(403);


$core->f->show('profile/main');
?>