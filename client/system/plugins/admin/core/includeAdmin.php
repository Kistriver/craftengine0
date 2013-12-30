<?php
namespace CRAFTEngine\client\plugins\admin;
$adlo = 'CRAFTEngine\client\plugins\admin\\'.$core->plugins->getList()['admin']['loadClass'];
$adlo = new $adlo($core);

if($adlo->access()===false)$core->f->quit(403);