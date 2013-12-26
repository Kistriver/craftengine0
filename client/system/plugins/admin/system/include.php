<?php
namespace CRAFTEngine\client\plugins\admin;

$core->plugins->newRule(array('preg'=>'^admin$','page'=>'index.php','plugin'=>'admin'));

$core->plugins->newRule(array('preg'=>'^admin/api/plugins$','page'=>'api/plugins_list.php','plugin'=>'admin'));
$core->plugins->newRule(array('preg'=>'^admin/api/plugins/edit/(.*)$','page'=>'api/plugins_config.php','get'=>array('plugin'=>'$1'),'plugin'=>'admin'));

$core->plugins->newRule(array('preg'=>'^admin/client/pages$','page'=>'client/pages.php','plugin'=>'admin'));
$core->plugins->newRule(array('preg'=>'^admin/client/settings$','page'=>'client/settings.php','plugin'=>'admin'));
$core->plugins->newRule(array('preg'=>'^admin/client/themes$','page'=>'client/themes.php','plugin'=>'admin'));