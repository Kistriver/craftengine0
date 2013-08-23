<?php
//Если установлена константа, то запросить список файлов и их хеш
//Скачать архив файлов
//Закрыть доступ на сайт
//Разархивировать
//Файлов, которых нет в списке удалить
//Которые есть и изменились заменить
//Открыть доступ на сайт
//Уведомить админа по мылу
//Отправить в статцентр инфу об обновлении

set_time_limit(0);
ignore_user_abort(1);

for ($i=0; $i < 1000000; $i++) { 
	md5(sha1(md5(sha1($i))));
}
/*
$data = time();
$data = base64_encode($data);
$data = unpack('c*',$data);
$data = serialize($data);
$data = base64_encode($data);

file_put_contents(dirname(__FILE__).'/../system/core/cache/LastUpdateRequest', $data);*/
?>