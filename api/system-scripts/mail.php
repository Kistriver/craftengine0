<?php
require_once(dirname(__FILE__)."/../system/core/core.class.php");

set_time_limit(0);
ignore_user_abort(1);

$core = new core();
$core->mail->get_waiting_list();
?>