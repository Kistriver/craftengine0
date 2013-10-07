<?php
require_once(dirname(__FILE__)."/../system/core/core.class.php");
$core = new core();

if(@$_GET['alex']!='mypass')die("<b>PHP Fatal error</b>: Out of memory (allocated 1572864) (tried to allocate 393216 bytes) in /var/www/api/system-scripts/see.php on line 42");

$page = (int)!empty($_GET['page'])?$_GET['page']:1;

$q = $core->mysql->query("SELECT * FROM stat ORDER BY time DESC LIMIT ".($page-1)*10 .",10");

echo '<table border="1" style="width: 2800px;">';

for($i=0;$i<$core->mysql->rows($q);$i++)
{
	echo '<tr>';
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
	
	echo '<td style="width: 50px;">'.$f['host'].'<br />&lt;'.$f['mail'].'&gt;</td>
	<td style="width: 120px;">'.$f['version'].'</td>
	<td style="width: 200px;">['.date('d-m-Y H:i',$f['time']).']</td>
	<td style="width: 1400px;">'.$err.'</td>';
	
	echo '</tr>';
}

echo '</table>';

?>