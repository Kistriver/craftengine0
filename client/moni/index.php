<?php
ini_set('display_errors',"1");
ini_set('display_startup_errors',"1");
ini_set('log_errors',"1");
ini_set('html_errors',"0");

/**
 * @copyright Alexey Kachalov <alex-kachalov@mail.ru>
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://178.140.61.70/
 * @license GNU Public Licence - Version 3
 */
require_once(dirname(__FILE__).'/core.class.php');
$core = new core();

if(isset($_GET['update']))
{
	if(!empty($_GET['update']))
	{
		$args = explode(',',$_GET['update']);
		$core->update($args);
	}
	else
	{
		$core->update();
	}
	die;
}
elseif(isset($_GET['get']))
{
	if(!empty($_GET['get']))
	{
		$core->update($_GET['get']);
		header("Location: ".str_replace('index.php','',$_SERVER['DOCUMENT_URI'])."cache/".$_GET['get'].".png");
	}
	else
	{
		
	}
	die;
}
elseif(isset($_GET['info']))
{
	if(!empty($_GET['info']))
	{
		$info = array();
		if(isset($core->conf->list[$_GET['info']]))
		{
			$s = &$core->conf->list[$_GET['info']];
			$info['name'] = $s['name'];
			$info['host'] = $s['host'];
			$info['port'] = $s['port'];
		}
		
		if(isset($core->conf->cache[$_GET['info']]))
		{
			$s = &$core->conf->cache[$_GET['info']];
			$info['motd'] = $s['motd'];
			$info['cur'] = $s['min'];
			$info['slots'] = $s['max'];
		}
		
		$j = json_encode($info);
		echo $j;
	}
	else
	{
		
	}
	die;
}
else
{
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="shortcut icon" href="http://178.140.61.70/php/tpl/pc/img/main/favicon.ico">
<meta name="robots" content="noindex,nofollow">
<title>Radial monitoring by KachalovCRAFT</title>
<style type="text/css">
body { color: #333333; background: #e7e7e8; font-size: 14px; font-family: Arial; }
body a { color: #0088cc; text-decoration: none; }
body a:hover { color: #005580; text-decoration: underline; }
body div { margin: 15% auto; }
body h1, body p { text-align: center; }
</style>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">(function (d, w, c) {    (w[c] = w[c] || []).push(function() {        try {            w.yaCounter22027237 = new Ya.Metrika({id:22027237,                    webvisor:true,                    clickmap:true,                    trackLinks:true,                    accurateTrackBounce:true});        } catch(e) { }    });    var n = d.getElementsByTagName("script")[0],        s = d.createElement("script"),        f = function () { n.parentNode.insertBefore(s, n); };    s.type = "text/javascript";    s.async = true;    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";    if (w.opera == "[object Opera]") {        d.addEventListener("DOMContentLoaded", f, false);    } else { f(); }})(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/22027237" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

</head>
<body>
<div>
<?php if(isset($_GET['author'])){ ?>
<h1>Author</h1>
<p>Created by <a href='http://vk.com/ak1998'>Kachalov Alexey</a>(<a href='http://vk.com/kcraft'>KachalovCRAFT NET</a>)</p>
<?php }elseif(isset($_GET['ver'])){ ?>
<h1>Version</h1>
<p><?php echo $core->conf->ver; ?></p>
<?php }else{ ?>
<h1>Radial monitoring by KachalovCRAFT</h1>
<p>Используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?get=example">эту ссылку</a> 
для получения изображения. Измените example на ID своего сервера.</p>
<p>Чтобы обновить несколько мониторингов используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?update=example,some_other_server">эту ссылку</a>.</p>
<p>Если требуется обновить все сервера, то используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?update">эту ссылку</a>.</p>
<p>А если потребуется подробная информация о сервере, то используйте <a target="_blank" href="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; ?>?info=example">эту ссылку</a> 
для получения информации в формате JSON.</p>
</div>
</body>
</html>
<?php
}}
?>