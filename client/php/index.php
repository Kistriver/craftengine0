<?php
require_once(dirname(__FILE__).'/../system/core/include.php');

$template = $twig->loadTemplate('index/main');
echo $template->render($core->render());
?>