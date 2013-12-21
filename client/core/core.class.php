<?php
/*
 * TODO: редактирование конфигов
 * TODO: переделать меню
 */
require_once(dirname(__FILE__).'/libs/Twig/Autoloader.php');

class core
{
	public $render = array();
	public $rules = array();
	
	public function __construct($core_confs=array())
	{
		$this->core = &$this;

		$this->core_confs = $core_confs;
		if(empty($this->core_confs['root']))
		{
			header('HTTP/1.1 500');
			die("Missed core parameter 'root'");
		}//$this->core_confs['root'] = dirname(__FILE__).'/';

		$ver = 'v2.0';
		if(!empty($_GET['getinfo']))
		switch($_GET['getinfo'])
		{
			case 'author':
				die("<a href='http://vk.com/ak1998'>Alexey Kachalov</a> for <a href='http://kcraft.su/'>KachalovCRAFT NET</a>");
				break;
			case 'core':
				die("CRAFTEngine Client ".$ver);
				break;
			case 'contact':
				die("alex-kachalov@mail.ru<br />http://kcraft.su/users/Kachalov<br />http://vk.com/ak1998");
				break;
			case 'version':
				die($ver);
				break;
			default:
				die("usage: ?getinfo=(author|core|contact|version)");
				break;
		}
		
		$php_min = '5.4.0';
		if(version_compare(PHP_VERSION, $php_min) <= 0)
		{
			header('HTTP/1.1 500');
			die('Your PHP version is: '.PHP_VERSION.'. But required version above: '.$php_min);
		}
		
		/*$this->f = new functions($this);
		$this->conf = new conf($this);
		$this->error = new error($this);
		$this->api = new api($this);*/

		$inc = array(
			array('/functions.class.php','functions','f'),
			array('/conf.class.php','conf','conf'),
			array('/api.class.php','api','api'),
			array('/error.class.php','error','error'),
			array('/plugin.class.php','plugin','plugins'),
		);

		foreach($inc as $inc)
		{
			require_once(dirname(__FILE__).$inc[0]);
			$this->{$inc[2]} = new $inc[1]($this);
		}

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
				'THEME'=>$ccc->theme,
				'ERRORS',
				'SUCCESS',
				'INFO',
			),
			'SYS' => array(
				'CORE_VER'=>$ver,
				'BASE'=>'themes/'.$ccc->theme.'/tpl/base/',
				'BASE_TPL'=>$ccc->columns.'_column.twig',
				//'BASE'=>'tpl/base/'.$ccc->theme.'/',
				'PLUGIN'=>'plugins/',
				'TPL'=>'tpl/',
				'NOHEADER'=>false,
			),
		);
		
		try
		{
			Twig_Autoloader::register(true);
			$loader = new Twig_Loader_Filesystem($this->core_confs['root']);
			
			if($cc->twig->cache==true)
			$t_cache = $this->core_confs['root'].'tmp';
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

		if($this->core->conf->conf->core->core->tech==true)$this->core->f->quit(403,'Technical works');
		
		//$this->plugins = new plugin($this);
	}
}
?>