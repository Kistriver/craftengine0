<?php
if(isset($_GET['method']))
{
	$start = microtime(true);
	header('Access-Control-Allow-Origin: *');
	header('Content-type: application/json; charset=utf-8');
	
	$m_f = $_GET['method'];
	if(!preg_match('/^[a-z_-]{1,25}\.[a-z0-9_-]{1,25}$/',$m_f))die('Method error: doesn\'t exists');
	$m_f = explode('.',$m_f);

	include_once(dirname(__FILE__)."/system/include.php");
	if(!isset($core_confs))
	$core_confs = array
	(
		'confs'=>array('root'=>''),
		'cache'=>array('root'=>''),
		'tpl'=>array('root'=>''),
		'plugins'=>array('root'=>''),
	);//Добавить такое и в system-scripts

	$core_confs['api'] = array('module'=>$m_f[0],'method'=>$m_f[1]);
	$core_confs['start_time'] = $start;

	require_once(dirname(__FILE__)."/system/core/core.class.php");
	$core = new core($core_confs);
	
	//require_once(dirname(__FILE__)."/system/core/api.class.php");
	//$api = new api($core,$m_f[0],$m_f[1]);
	$api = $core->api;
}
else
{
	//header('Content-type: text/html; charset=utf-8');
	?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="shortcut icon" href="http://kcraft.su/php/tpl/pc/img/main/favicon.ico">
<meta name="robots" content="noindex,nofollow">
<meta http-equiv="refresh" content="30; url=http://api.kcraft.su/help.php">
<title>CRAFTEngine API</title>
<style type="text/css">
body
{
	color: #333333; 
	background: #e7e7e8; 
	font-size: 14px; 
	font-family: Arial;
}
body a { color: #0088cc; text-decoration: none; }
body a:hover { color: #005580; text-decoration: underline; }
body div { margin: 15% auto; }
body h1, body p { text-align: center; }
</style>

<script language = 'javascript'>
left = 30;
  function startTime() {
    document.getElementById("time").innerHTML = left;
    left = left - 1;
    setTimeout(startTime, 1000);
  }
</script>


</head>
<body onLoad = 'startTime()'>
<img src="http://cs407316.vk.me/v407316634/8ee1/apRK3iePh4c.jpg" 
style="
position: fixed; 
opacity: 0.25;
top: 0px;
left: 0px;
overflow: hidden;
width: 100%;
height: 100%;
z-index: -1;
">
<div>
<h1>API</h1>
<p>Чтобы воспользоваться <b>CRAFTEngine API</b> нужно прочитать <a href="http://api.kcraft.su/help.php">документацию</a>.<br />
	Вы будете автоматически перенаправлены через <span id="time">30</span> секунд.</p>
</div>
</body>
</html><?php
}
?>
