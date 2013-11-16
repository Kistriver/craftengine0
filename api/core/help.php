<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="shortcut icon" href="http://kcraft.su/php/tpl/pc/img/main/favicon.ico">
<meta name="robots" content="noindex,nofollow">
<title>CRAFTEngine API DOC</title>
</head>
<body>
<img src="http://cs407316.vk.me/v407316634/8ee1/apRK3iePh4c.jpg" 
style="
position: fixed; 
opacity: 0.08;
top: 0px;
left: 0px;
overflow: hidden;
width: 100%;
height: 100%;
z-index: -1;
">
<div>
<h1>Документация по APIv4</h1>
<hr />
<h2>Использование</h2>
<p>
Чтобы воспользоваться CRAFTEngine API нужно лишь знать, какие есть модули. Например,
чтобы получить список пользователей(плагин user), нужно обратиться так: <b>
?module=user.list&data={"page":"1"}&sid=example</b>, где users - название api модуля,
list - метод модуля, а data передаётся в формате json, либо с помощью GET, либо с помощью POST.
Всегда обязательным параметром в data является sid(session id), который и определяет Вас как
клиента. Получить sid можно из ответа любого запроса. Могут быть и другие обязательные параметры,
но это уже зависит от модуля(см. документацию плагина).
</p>
<h2>Написание плагина</h2>
<p>
Чтобы написать понадобится просмотреть весь код). Шутка, скоро выложу описание функций. 

</p>
</div>
</body>
</html>
