<?php
namespace CRAFTEngine\client\plugins\admin;
$cc = $core->conf->get('core');
$access = false;
$page = preg_replace("'^admin(/|)(.*?)$'i",'$2',$uri);

if(in_array($_SERVER['REMOTE_ADDR'],$cc->core->admin_ip))$access = true;

list($access,) = $core->plugins->makeEvent('admin_access','admin',array($access,$page));

if($access===false)$core->f->quit(403);