<?php
if(!defined('CE_HUB'))die('403');

$editions = array();
$editions[] = array('name'=>'Standart Edition','info'=>'---','desc'=>'Ничего лишнего. Только ядро и клиент.',
					'status'=>'danger','time'=>'01.10.13 18:00','update'=>'Release','link'=>'');

$editions[] = array('name'=>'Minecraft Edition','info'=>'API: 0.1.5_alpha<br /> Client: v1.4 <br />Size: ~1.9MB',
					'desc'=>'Плагины: users, articles. Модифицированный под плагины клиент. Интергация плагина users с Bukkit плагином AuthMe. ',
					'status'=>'success','time'=>'05.10.13 19.40','update'=>'Update 7','link'=>'/me/latest.zip');

$core->render['editions'] = $editions;
$core->f->show('download/main','download');
?>