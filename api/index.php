<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
<p>Чтобы воспользоваться <b>CRAFTEngine APIv5</b> нужно прочитать <a href="http://api.kcraft.su/help.php">документацию</a>.<br />
	Вы будете автоматически перенаправлены через <span id="time">30</span> секунд.</p>
</div>
</body>
</html>