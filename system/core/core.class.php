<?php
class core
{
	/*public	$conf,
			$error,
			$mysql,
			$menu,
			$mail;*/
	public	$runtime;
	
	public function __construct()
	{
		$this->runtime = microtime();
		ob_start();
		
		//ignore_user_abort(1);
		
		$includes = array(	'conf',	
							'error',
							'mysql',
							//'menu',
							'mail',
						);
		
		for($i=0;$i<sizeof($includes);$i++)
		{
			include_once(dirname(__FILE__)."/". $includes[$i] .".class.php");
			
			$this->$includes[$i] = new $includes[$i]($this);
		}
		
		/*include_once(dirname(__FILE__)."/conf.class.php");
		include_once(dirname(__FILE__)."/error.class.php");
		include_once(dirname(__FILE__)."/mysql.class.php");
		include_once(dirname(__FILE__)."/menu.class.php");	//!!!Надобность модуля под вопросом!!!//
		include_once(dirname(__FILE__)."/mail.class.php");*/
		
		/*$this->conf = new conf($this);
		$this->error = new error($this);
		$this->mysql = new mysql($this);
		$this->menu = new menu($this);
		$this->mail = new mail($this);*/
		
		if(isset($_SESSION['id']) AND isset($_SESSION['login']))
		$_SESSION['loggedin'] = true;
		else
		$_SESSION['loggedin'] = false;
		
		for($i=0;$i<sizeof($this->conf->db);$i++)
		{
			$this->mysql->connect($this->conf->db[$i][0], 
								  $this->conf->db[$i][1], 
								  $this->conf->db[$i][2], 
								  $this->conf->db[$i][3], 
								  $this->conf->db[$i][4]);
		}
		
		set_error_handler(array($this->error,'error_php'));
		register_shutdown_function(array($this->error, 'fatal_error_php'));
	}
	
	public function runtime()
	{
		return microtime() - $this->runtime;
	}
}
?>