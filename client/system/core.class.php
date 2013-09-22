<?php
require_once(dirname(__FILE__).'/functions.class.php');
require_once(dirname(__FILE__).'/conf.class.php');
require_once(dirname(__FILE__).'/api.class.php');
require_once(dirname(__FILE__).'/error.class.php');
require_once(dirname(__FILE__).'/libs/Twig/Autoloader.php');
require_once(dirname(__FILE__).'/plugin.class.php');

class core
{
	public $render = array();
	public $rules = array();
	
	public function __construct()
	{
		if(!empty($_GET['getinfo']))
		switch($_GET['getinfo'])
		{
			case 'author':
				die("<a href='http://vk.com/ak1998'>Alexey Kachalov</a> for <a href='http://kcraft.su/'>KachalovCRAFT NET</a>");
				break;
			case 'core':
				die("CRAFTEngine Client v1.0");
				break;
			case 'contact':
				die("alex-kachalov@mail.ru<br />http://kcraft.su/users/Kachalov<br />http://vk.com/ak1998");
				break;
			default:
				die("usage: ?getinfo=(author|core|contact)");
				break;
		}
		
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
		);
		
		try
		{
			Twig_Autoloader::register(true);
			$v = isset($cc->twig->ver)?$cc->twig->ver:'pc';
			$loader = new Twig_Loader_Filesystem(dirname(__FILE__).'/../tpl/'.$v);
			
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
			//die('Fatal Twig error: ' . $e->getMessage());
			$this->core->f->quit(500,'can\'t load Twig');
		}
		
		$this->plugins = new plugin($this);
		
		
		////========================REWRITE RULES ZONE========================////
		$this->rules[] = array(array('^index$','^$'),'index.php');
		
		$this->rules[] = array('^articles$','articles.php', array('act'=>'posts','page'=>'1'));
		$this->rules[] = array('^articles/page-([0-9]*)$','articles.php', array('act'=>'posts','page'=>'$1'));
		$this->rules[] = array('^articles/([0-9]*)/([0-9]*)$','articles.php', array('act'=>'post','user_id'=>'$1','post_id'=>'$2'));
		$this->rules[] = array('^articles/confirm/page-([0-9]*)$','articles.php', array('act'=>'confirm','page'=>'$1'));
		$this->rules[] = array('^articles/([a-z]*)$','articles.php', array('act'=>'$1'));
		
		$this->rules[] = array('^logout$','login.php', array('act'=>'logout'));
		$this->rules[] = array('^login$','login.php'/*, array('act'=>'login')*/);
		$this->rules[] = array('^login/restore$','login.php', array('act'=>'restore'));
		$this->rules[] = array('^login/confirm$','login.php', array('act'=>'confirm'));
		$this->rules[] = array('^login/confirm/([a-z0-9]*)$','login.php', array('act'=>'confirm','code'=>'$1'));
		
		$this->rules[] = array('^users$','users.php', array('act'=>'all','page'=>'1'));
		$this->rules[] = array('^users/page-([0-9]*)$','users.php', array('act'=>'all','page'=>'$1'));
		$this->rules[] = array('^users/id([0-9]*)$','users.php', array('act'=>'user','page'=>'$1'));
		$this->rules[] = array('^users/confirm$','users.php', array('act'=>'confirm','page'=>'1'));
		$this->rules[] = array('^users/confirm/page-([0-9]*)$','users.php', array('act'=>'confirm','page'=>'$1'));
		$this->rules[] = array('^users/([A-Za-z0-9_]*)$','users.php', array('act'=>'user','login'=>'$1'));
		
		$this->rules[] = array('^profile(|\/)$','profile.php', array('type'=>'main'));
		$this->rules[] = array('^profile/([A-Za-z_]*)$','profile.php', array('type'=>'$1'));
		
		$this->rules[] = array('^plugins$','plugins.php');
		
		$this->rules[] = array('^signup$','signup.php');
		
		$this->rules[] = array('^download$','download.php');
		////========================REWRITE RULES ZONE========================////
	}
}
?>