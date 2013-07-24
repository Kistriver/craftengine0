<?php

DIE();

include_once(dirname(__FILE__)."/../system/core/core.class.php");
$core = new core();
set_time_limit(0);
$user = new user($core);

$names_m = array('Алексей','Игорь','Василий','Артемий','Максим','Денис','Илья','Владимир','Андрей','Валера','Антон');
$names_f = array('Юлия','Мария','Екатерина','Людмила','Татьяна','Ольга','Анна','Паталья','Полина','Александра');

$surname1 = array('Трале','Жлобо','Земле','Амперо','Клее','Штир');
$surname2 = array('плю','дно','кля','н');
$surname3 = array('ов','ев','ан','ян','ин');

$email1 = array('a','b','c','d','e','f','g','h','i','k','l','m','n','o','p','r','s','t','u');
$email2 = array("mail.ru",
				"gmail.com",
				"yandex.ru",
				"bk.ru",
				"inbox.ru",
				"list.ru",
				"live.ru",
				"hotmail.com",
				"yahoo.com");

for($m=0;$m<1000;$m++){

$sex = ceil(rand(0,1));

if($sex==0)
$name = $names_f[ceil(rand(0,sizeof($names_f)-1))];
else
$name = $names_m[ceil(rand(0,sizeof($names_m)-1))];

$surname = $surname1[ceil(rand(0,sizeof($surname1)-1))].$surname2[ceil(rand(0,sizeof($surname2)-1))].$surname3[ceil(rand(0,sizeof($surname3)-1))];
if($sex==0)$surname = $surname.'а';

$email = '';
for($i=0;$i<65;$i++)
{
	$email .= $email1[ceil(rand(0,sizeof($email1)-1))];
}

$email .= '@'.$email2[ceil(rand(0,sizeof($email2)-1))];

$pass = '';
for($i=0;$i<45;$i++)
{
	$pass .= $email1[ceil(rand(0,sizeof($email1)-1))];
}

$login = '';
for($i=0;$i<45;$i++)
{
	$login .= $email1[ceil(rand(0,sizeof($email1)-1))];
}

$day = rand(1,28);
$month = rand(1,12);
$year = rand(1972,2000);

$time = time();

$user->new_user(
					$name,
					$surname,
					$email,
					$pass,
					$login,
					$sex,
					$day,
					$month,
					$year,
					null,
					null,
					null,
					$time,
					null
				);
}

?>