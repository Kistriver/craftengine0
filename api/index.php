<?php
if(isset($_GET['method']))
{
	header('Access-Control-Allow-Origin: *');
	header('Content-type: application/json; charset=utf-8');
	
	$m_f = $_GET['method'];
	if(!preg_match('/^[a-z_-]{1,25}\.[a-z0-9_-]{1,25}$/',$m_f))die('Method error: doesn\'t exists');
	$m_f = explode('.',$m_f);
	
	require_once(dirname(__FILE__)."/system/core/core.class.php");
	$core = new core();
	
	require_once(dirname(__FILE__)."/system/core/api.class.php");
	$api = new api($core,$m_f[0],$m_f[1]);
}
else
{
	//header('Content-type: text/html; charset=utf-8');
	?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="shortcut icon" href="http://178.140.61.70/php/tpl/pc/img/main/favicon.ico">
<meta name="robots" content="noindex,nofollow">
<meta http-equiv="refresh" content="30; url=http://178.140.61.70/help">
<title>CRAFTEngine API</title>
<style type="text/css">
body { color: #333333; background: #e7e7e8; font-size: 14px; font-family: Arial; }
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
<div>
<h1>API</h1>
<p>Чтобы воспользоваться <b>CRAFTEngine API</b> нужно прочитать <a href="http://178.140.61.70/help">документацию</a>.<br />
	Вы будете автоматически перенаправлены через <span id="time">30</span> секунд.</p>
</div>
</body>
</html><?php
}
?>