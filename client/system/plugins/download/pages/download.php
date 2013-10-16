<?php
if(!defined('CE_HUB'))die('403');

$editions = array();
$editions[] = array('name'=>'Standart Edition','info'=>'---','desc'=>'Ничего лишнего. Только ядро и клиент.',
					'status'=>'danger','time'=>'01.10.13 18:00','update'=>'Release','link'=>'');

$editions[] = array('name'=>'Minecraft Edition','info'=>'API: 0.1.6_alpha<br /> Client: v1.4 <br />Size: ~1.9MB',
					'desc'=>'Плагины: users, articles. Модифицированный под плагины клиент. Интергация плагина users с Bukkit плагином AuthMe. ',
					'status'=>'success','time'=>'13.10.13 20.25','update'=>'Update 9','link'=>'/me/latest.zip');

$core->render['editions'] = $editions;
$core->f->show('download/main','download');
?>