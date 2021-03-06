<?php
namespace CRAFTEngine\core;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */

ini_set('display_errors',"0");
ini_set('display_startup_errors',"0");
ini_set('log_errors',"1");
ini_set('html_errors',"0");
class error
{
	public			$error						= Array();//Массив ошибок
	
	protected		$core;//Ядро
	
	public function __construct($core)
	{
		$this->core = &$core;
		
		error_reporting(E_ALL);
		
		//Объявление обработчиков ошибок
		set_error_handler(array($this,'errorPhp'));
		register_shutdown_function(array($this, 'fatalErrorPhp'));
		
		//$this->core->timer->mark('error.class.php/__construct');
	}
	
	//Добавление ошибки
	public function error($err_mod,$err_no)
	{
		$err = $this->errorMake($err_mod,$err_no);
		if($err)$this->error[] = $err;
	}

	public function pathReplacer($path)
	{
		$path = str_replace('\\','/',$path);

		$before = array(
			dirname(__FILE__).'/',
			$this->core->functions->pathBuilder($this->core->getParams()['root'].'/'),
			$this->core->functions->pathBuilder(dirname(__FILE__).'/../'),
		);

		$after = array(
			'{{CORE_ROOT}}/',
			'{{EDITION_ROOT}}/',
			'{{SYSTEM_ROOT}}/',
		);

		return str_replace($before,$after,$path);
	}
	
	//Добавление ошибки PHP
	public function errorPhp($code,$msg,$file,$line)
	{
		$file = $this->pathReplacer($file);

		$mr = isset($this->core->conf->system->core->debug['mail-report'])?$this->core->conf->system->core->debug['mail-report']:true;
		$debug = isset($this->core->conf->system->core->debug['errors'])?$this->core->conf->system->core->debug['errors']:true;

		if(!$debug)$this->error[] = $this->errorMake('engine',3);
		else $this->error[] = array('engine',3,array($code,$msg,$file,$line));

		$mysql = $this->core->mysql;
		//if(isset($this->core->mysql,$this->core->mail))
		if($mr && $this->core->mysql->isConnect($mysql::DB_NAME))
		$this->core->mail->addWaitingList(
		$this->core->conf->system->core->admin_mail, 
		'001', 
		array(
			'code'=>$code,
			'msg'=>$msg,
			'file'=>$file,
			'line'=>$line,
			'time'=>time()
			)
		);

		$er_j = json_encode(array(
			'code'=>$code,
			'msg'=>$msg,
			'file'=>$file,
			'line'=>$line,
			'time'=>time()
		));
		$this->core->statCache('error',$er_j,true);
	}
	
	//Отлов завершения работы скрипта
	public function fatalErrorPhp()
	{
		$debug = isset($this->core->conf->system->core->debug['errors'])?$this->core->conf->system->core->debug['errors']:true;
		$mr = isset($this->core->conf->system->core->debug['mail-report'])?$this->core->conf->system->core->debug['mail-report']:true;

		$error = error_get_last();
		if (isset($error))//Если фатальная ошибка, то обработка этой ошибки
			if($error['type'] == E_ERROR
			|| $error['type'] == E_PARSE
			|| $error['type'] == E_COMPILE_ERROR
			|| $error['type'] == E_CORE_ERROR)
			{
				ob_end_clean();

				if(empty($this->core->getParams()['api']['code']))header('HTTP/1.0 500');
				else header('HTTP/1.0 '.$this->core->getParams()['api']['code']);

				$error['file'] = $this->pathReplacer($error['file']);

				$mysql = $this->core->mysql;

				if(!$debug)$errA =
				array('error'=>'Unfortunately, there is an error there. But our team is working on elimination of this problem.');
				else $errA = array('error'=>"[$error[type]][$error[file]:$error[line]] $error[message]\r\n");
				echo json_encode($errA);
				if($mr && isset($this->core->mail))
				if($this->core->mysql->isConnect($mysql::DB_NAME))
				$this->core->mail->addWaitingList(
				$this->core->conf->system->core->admin_mail, 
				'000', 
				array(
					'code'=>$error['type'],
					'msg'=>$error['message'],
					'file'=>$error['file'],
					'line'=>$error['line'],
					'time'=>time()
					)
				);

				$er_j = json_encode(array(
					'code'=>$error['type'],
					'msg'=>$error['message'],
					'file'=>$error['file'],
					'line'=>$error['line'],
					'time'=>time()
				));

				$this->core->statCache('error',$er_j,true);
			}
		ob_end_flush();
	}
	
	//Создание ошибки
	public function errorMake($err_mod,$err_no)
	{
		if(!preg_match("'^plugin_([a-zA-Z0-9-]*)_([a-zA-Z0-9-_]*)$'is", $err_mod))
		{
			if(!isset($this->core->conf->system->errors->$err_mod))
			return array('engine',1,'Error in creating error');
			
			if(!isset($this->core->conf->system->errors->{$err_mod}[$err_no]))
			return array('engine',2,'Error in creating error');
			
			$err = array($err_mod, $err_no, $this->core->conf->system->errors->{$err_mod}[$err_no]);
			return $err;
		}
		else
		{
			//list($pl, $mod) = explode(':',preg_replace("'^plugin_([a-zA-Z0-9-]*)_([a-zA-Z0-9-_]*)$'is",'$1:$2', $err_mod));
			preg_match("'^plugin_([a-zA-Z0-9-]*)_([a-zA-Z0-9-_]*)$'is", $err_mod, $matched);
			$pl = $matched[1];
			$mod = $matched[2];

			if(!isset($this->core->conf->plugins->{$pl}))
			return $this->errorMake('engine',1);
			
			if(!isset($this->core->conf->plugins->{$pl}->errors))
			return $this->errorMake('engine',1);
			
			if(!isset($this->core->conf->plugins->{$pl}->errors->$mod))
			return $this->error('engine',1);
			
			if(!isset($this->core->conf->plugins->{$pl}->errors->{$mod}[$err_no]))
			return $this->error('engine',2);
			
			$err = array("${pl}_$mod", $err_no, $this->core->conf->plugins->{$pl}->errors->{$mod}[$err_no]);
			return $err;
		}
	}
}