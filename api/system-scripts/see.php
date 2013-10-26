<?php
if(@$_GET['alex']!='mypass')die("<b>PHP Fatal error</b>: Out of memory (allocated 1572864) (tried to allocate 393216 bytes) in /var/www/api/system-scripts/see.php on line 42");

require_once(dirname(__FILE__)."/../system/core/core.class.php");
$core = new core();
?>
<!DOCTYPE html>
<html style="height: 100%;">
<!--Attention! If you have psychiatric disorder, don't watch the code below-->
<head>
	<!--Ослик реально бесит-->
	<!--[if IE]>
	<meta http-equiv="refresh" content="0;url=http://www.mozilla.org/ru/firefox/fx/" />
	<![endif]-->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="KachalovCRAFT NET, CRAFTEngine, KachalovCRAFT NET, Minecraft" />
	<meta name="description" content="Look at my site, my site is amazing! Give it a lick..." />
	<meta name="author" lang="ru" content="Алексей Качалов" />
	<meta name="document-state" content="Dynamic" />
	<link rel="image_src" href="/style/img/main/logo.png" />
	<!--/* INCLUDED CSS */-->
	<!--cosmo,cyborg,flatly,spacelab,united-->
	<link href="http://kcraft.su/style/bootstrap/css/cosmo.css" rel="stylesheet" media="screen">

	<!--/* INCLUDED JS */-->
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="http://kcraft.su/style/bootstrap/js/bootstrap.js"></script>
	<!-- Yandex.Metrika counter -->
	<script type="text/javascript">
		(function (d, w, c) {
			(w[c] = w[c] || []).push(function() {
				try {
					w.yaCounter20286013 = new Ya.Metrika({id:20286013,
						webvisor:true,
						clickmap:true,
						trackLinks:true,
						accurateTrackBounce:true});
				} catch(e) { }
			});

			var n = d.getElementsByTagName("script")[0],
				s = d.createElement("script"),
				f = function () { n.parentNode.insertBefore(s, n); };
			s.type = "text/javascript";
			s.async = true;
			s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

			if (w.opera == "[object Opera]") {
				d.addEventListener("DOMContentLoaded", f, false);
			} else { f(); }
		})(document, window, "yandex_metrika_callbacks");
	</script>
	<noscript><div><img src="//mc.yandex.ru/watch/20286013" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->

	<link rel="shortcut icon" href="http://kcraft.su/style/img/main/favicon.ico"/>
	<title>
		Error log | KachalovCRAFT NET</title>
</head>
<body style="height: 100%; margin: 0px; padding: 0px;">









<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="navbar-header">
	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</button>
	<a class="navbar-brand" href="http://kcraft.su/">KachalovCRAFT NET</a>
</div>

<div class="collapse navbar-collapse navbar-ex1-collapse">
	<ul class="nav navbar-nav navbar-right">
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">Меню <b class="caret"></b></a>
			<ul class="dropdown-menu">
				<li><a href="http://kcraft.su/">Главная</a></li>
				<li><a href="http://kcraft.su/articles">Новости</a></li>
				<li><a href="http://kcraft.su/users">Пользователи</a></li>
			</ul>
		</li>
	</ul>
</div>
</nav>


<br/><br/>














<?php

$page = (int)!empty($_GET['page'])?$_GET['page']:1;

$q = $core->mysql->query("SELECT * FROM stat ORDER BY time DESC LIMIT ".($page-1)*10 .",10");

echo 'Core errors: <pre>';
print_r($core->error->error);
echo '</pre>';

//echo '<table border="1" style="width: 2800px;">';

for($i=0;$i<$core->mysql->rows($q);$i++)
{
	//echo '<tr>';
	//echo '<div class="row" style="width: 100%; margin: 0px;">';
	$f = $core->mysql->fetch($q);
	/*$err = array();
	
	$err = explode("\r\n",$f['errors']);
	
	foreach($err as &$er)
	{
		$er = str_replace(PHP_EOL,'',$er);
		$er = preg_replace("'^([0-9-]{0,20})(.*)$'i","$2",$er);
		$er = preg_replace("'^\[([^\]]*)\](.*)$'i","[<b>$1</b>] $2",$er);
		$er = str_replace("{{FRAMEWORK_ROOT}}","<sup>framework_root</sup>",$er);
	}
	
	$err = implode("\r\n<br />",$err);*/
	
	$err = str_replace(PHP_EOL,"<br />",$f['errors']);

	$c = 0;
	foreach(explode('<br />',$err) as $er)
	{
		$col = '';
		$col1='rgb(250, 240, 240)';
		$col2='rgb(250, 250, 240)';
		if($c==0){$c=1;$col=$col2;}
		elseif($c==1){$c=0;$col=$col1;}
		$er = str_replace('&quot;','"',$er);
		$er_c = $er;
		$er = json_decode(trim($er),true);
		if(empty($er['msg']))
		{
			if(empty($er_c))continue;

			$er_c = str_replace("{{FRAMEWORK_ROOT}}/","/",$er_c);
			echo '<div class="row" style="width: 100%; margin: 0px; background-color: '.$col.';">';
			echo '<div class="col-md-2">'.$f['version'].' ['.
				date('d-m-Y H:i',$f['time']).']</div>';
			echo '<div class="col-md-10">'.$er_c.'</div>';
			echo '</div>';
			echo '<hr style="margin: 0px; border: 1px dashed;border-top: none;" />';
			continue;
		}
		$er['file'] = str_replace("{{FRAMEWORK_ROOT}}/","/",$er['file']);
		echo '<div class="row" style="width: 100%; margin: 0px; background-color: '.$col.';">';
		echo '<div class="col-md-2">'.$f['version'].' ['.
			date('d-m-Y H:i',$f['time']).']</div>';
		echo '<div class="col-md-5">'.$er['file'].':'.$er['line'].'</div>';
		echo '<div class="col-md-5">'.$er['msg'].'</div>';
		echo '</div>';
		echo '<hr style="margin: 0px; border: 1px dashed;border-top: none;" />';
	}
	if(!empty($err) || true==true)echo '<hr />';
	/*echo '<td style="width: 50px;">'.$f['host'].'<br />&lt;'.$f['mail'].'&gt;</td>
	<td style="width: 120px;">'.$f['version'].'</td>
	<td style="width: 200px;">['.date('d-m-Y H:i',$f['time']).']</td>
	<td style="width: 1400px;">'.$err.'</td>';*/

	/*cho '<div class="col-md-3">'.
		$f['host'].
		' ('.$f['version'].')<br />['.
		date('d-m-Y H:i',$f['time']).
		']</div>';
	echo '<div class="col-md-9">'.$err.'</div>';*/
	
	//echo '</tr>';
	//echo '</div>';
}

//echo '</table>';

?>


</body>
</html>
<!--My congratulations! It's the end of the website code! Get cookies from kitchen XD-->