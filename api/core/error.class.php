<?php
namespace CRAFTEngine\core;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */
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
	
	//Добавление ошибки PHP
	public function errorPhp($code,$msg,$file,$line)
	{
		$file = str_replace('\\','/',$file);
		//FIXME: might replace other system/core and it will be epic fail
		$file_fr = str_replace('/system/core','',dirname(__FILE__));
		$file = str_replace($file_fr,'{{FRAMEWORK_ROOT}}',$file);

		if(!@$this->core->conf->system->core->debug)$this->error[] = $this->errorMake('engine',3);
		else $this->error[] = array('engine',3,array($code,$msg,$file,$line));

		$mysql = $this->core->mysql;
		//if(isset($this->core->mysql,$this->core->mail))
		if(@$this->core->conf->system->core->send_mail_report && $this->core->mysql->isConnect($mysql::DB_NAME))
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
		$debug = isset($this->core->conf->system->core->debug)?$this->core->conf->system->core->debug:true;
		$mr = isset($this->core->conf->system->core->send_mail_report)?$this->core->conf->system->core->send_mail_report:true;

		$error = error_get_last();
		if (isset($error))//Если фатальная ошибка, то обработка этой ошибки
			if($error['type'] == E_ERROR
			|| $error['type'] == E_PARSE
			|| $error['type'] == E_COMPILE_ERROR
			|| $error['type'] == E_CORE_ERROR)
			{
				ob_end_clean();

				if(empty($this->core->core_confs['api']['code']))header('HTTP/1.0 500');
				else header('HTTP/1.0 '.$this->core->core_confs['api']['code']);

				$error['file'] = str_replace('\\','/',$error['file']);
				$file_fr = str_replace('/system/core','',dirname(__FILE__));
				$error['file'] = str_replace($file_fr,'{{FRAMEWORK_ROOT}}',$error['file']);

				$mysql = $this->core->mysql;

				if(!$debug)$errA =
				array('error'=>'Unfortunately, there is an error there. But our team is working on elimination of this problem.');
				else $errA = array('error'=>"[$error[type]][$error[file]:$error[line]] $error[message]\r\n");
				echo json_encode($errA);
				if($this->core->conf->system->core->send_mail_report &&
				isset($this->core->mail))
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
		if(!preg_match("'^plugin_([a-zA-Z0-9-]*)_([a-zA-Z0-9-]*)$'is", $err_mod))
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
			list($pl, $mod) = explode(':',preg_replace("'^plugin_([a-zA-Z0-9-]*)_([a-zA-Z0-9-]*)$'is",'$1:$2', $err_mod));
			
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