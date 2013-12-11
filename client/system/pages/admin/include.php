<?php
$cc = $core->conf->get('core');
if(!in_array($_SERVER['REMOTE_ADDR'],$cc->core->admin_ip))$core->f->quit(403);
//allow only for admins