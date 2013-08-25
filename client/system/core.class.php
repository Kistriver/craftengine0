<?php
require_once(dirname(__FILE__).'/functions.class.php');
require_once(dirname(__FILE__).'/conf.class.php');
require_once(dirname(__FILE__).'/api.class.php');
require_once(dirname(__FILE__).'/error.class.php');
require_once(dirname(__FILE__).'/libs/Twig/Autoloader.php');

class core
{
	public $render = array();
	
	public function __construct()
	{
		$php_min = '5.3.0';
		if(version_compare(PHP_VERSION, $php_min) <= 0)
		{
			die('Your PHP version is: '.PHP_VERSION.'. But required version above: '.$php_min);
		}
		
		$this->f = new functions($this);
		$this->conf = new conf($this);
		$this->error = new error($this);
		$this->api = new api($this);
		
		$cc = $this->conf->get('core');
		$cc = json_decode($cc,false);
		if($cc==false)$this->f->quit(500,'can\'t load config');
		$this->api->url = $cc->api->url;
		
		$ccc = $cc->tpl;
		//TODO: Lower layout
		$this->render = array(
			'MAIN'=> array(
				'NAME'=>$ccc->client_name,
				'TITLE'=>$ccc->client_name,
				'KEYWORDS'=>$ccc->client_name.', CRAFTEngine, '.$ccc->client_keywords,
				'DESC'=>$ccc->client_desc,
				'HEADER'=>$ccc->client_name,
				'ROOT'=>$ccc->root,
				'ROOT_HTTP'=>$ccc->root_http,
				'HOST'=>$ccc->client_host,
				'V'=>$cc->twig->ver,
				'ERRORS',
				'SUCCESS',
				'INFO',
			),
		);print_r($this->error->error());
		
		try
		{
			Twig_Autoloader::register(true);
			$loader = new Twig_Loader_Filesystem(dirname(__FILE__).'/../tpl/'.$cc->twig->ver);
			
			if($cc->twig->cache==true)
			$t_cache = dirname(__FILE__).'/tmp';
			else
			$t_cache = false;
			
			$twig = new Twig_Environment
			(
				$loader,
				array(
					'cache'=>$t_cache,
					'auto_reload'=>$cc->twig->reload,
					'autoescape'=>$cc->twig->escape
				)
			);
			$this->twig = $twig;
		}
		catch (Exception $e)
		{
			die('Fatal Twig error: ' . $e->getMessage());
		}
	}
}
?>