<?php
include_once(dirname(__FILE__)."/system/core/core.class.php");
echo '<pre>'."\r\n";
$core = new core();
session_start();
//echo $core->sanString($_GET['c']);echo"\r\n";
//print_r($api);
//print_r($core);
//$core->mysql->connect('social');
//$core->mysql->query("SELECT * FROM blocklog_blocks",'social');
//while($row = $core->mysql->fetch())print_r($row);
//echo $core->mysql->rows();
//$core->mysql->query("SELECT * FROM articles",'social');
//for($i=0;$i<mysql_num_rows($core->mysql->result);$i++)print_r(mysql_fetch_array($core->mysql->result));
//$core->mail->add_waiting_list('alex-kachalov@mail.ru','004',array('alex-kachalov@mail.ru','test'));
//$core->mail->send('alex-kachalov@mail.ru','004',array('alex-kachalov@mail.ru','test'));

$core->plugin('user');
//print_r($core);
$u = new user($core);
//echo $u->change_user('1',array('pass','qwerty'),'password')?'true':'false';
//$u->get_user(1);
//print_r($u->password_md5('pass', false, $u->time_reg, $u->salt));
//print_r($core->conf->ranks);
//echo $u->get_user('1','id')?'Y':'N';
/*$t =$core->runtime();
$u->signup(
	'name',
	'surname',
	$core->sanString("'\"\\/<>"),
	'lols',
	'login',
	'1',
	'01',
	'05',
	'1998',
	'0'
	);
echo $core->runtime()-$t;*/
$core->plugin('rank');
$r = new rank($core);
//echo $r->init(1, 'example')?'y':'n';
//print_r($r);

echo 'errors: '; print_r($core->error->error)."\r\n";
echo 'runtime: '.$core->runtime()."\r\n";
echo 'version: '.$core->conf->version."\r\n";
echo '</pre>';
//require null;
?>