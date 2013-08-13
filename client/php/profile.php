<?php
require_once(dirname(__FILE__).'/../system/core/include.php');
if(!$_SESSION['loggedin'])display($core,$twig,403);


$template = $twig->loadTemplate('profile/main');
echo $template->render($core->render());
?>