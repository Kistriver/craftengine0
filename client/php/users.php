<?php
include_once(dirname(__FILE__).'/../core/include.php');

$template = $twig->loadTemplate('users/main');
echo $template->render($core->render());
?>