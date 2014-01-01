<?php
namespace CRAFTEngine\client\plugins\admin;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function rules()
	{
		$this->core->plugins->newRule(array('preg'=>'^admin$','page'=>'index.php','plugin'=>'admin'));

		$this->core->plugins->newRule(array('preg'=>'^admin/api/plugins$','page'=>'api/plugins_list.php','plugin'=>'admin'));
		$this->core->plugins->newRule(array('preg'=>'^admin/api/plugins/edit/(.*)$','page'=>'api/plugins_config.php','get'=>array('plugin'=>'$1'),'plugin'=>'admin'));

		$this->core->plugins->newRule(array('preg'=>'^admin/client/pages$','page'=>'client/pages.php','plugin'=>'admin'));
		$this->core->plugins->newRule(array('preg'=>'^admin/client/settings$','page'=>'client/settings.php','plugin'=>'admin'));
		$this->core->plugins->newRule(array('preg'=>'^admin/client/themes$','page'=>'client/themes.php','plugin'=>'admin'));
		$this->core->plugins->newRule(array('preg'=>'^admin/client/widgets$','page'=>'client/widgets.php','plugin'=>'admin'));
	}

	public function  RegisterPluginEvent($id,$plugin,$info)
	{
		switch($id.'_'.$plugin)
		{
			case 'render_widget_menu':
				//FIXME: After logout(plugins users) it stays in menu until get this page
				if($this->access()!==false)
				$info[] = array('Админка','admin');
				break;
		}
		return $info;
	}

	public function access()
	{
		$cc = $this->core->conf->get('core');
		//$access = false;
		$page = preg_replace("'^admin(/|)(.*?)$'i",'$2',$this->core->uri);

		//if(in_array($_SERVER['REMOTE_ADDR'],$cc->core->admin_ip))$access = true;
		$access = null;

		list($access,) = $this->core->plugins->makeEvent('admin_access','admin',array($access,$page));

		if($access===null)
		{
			if(in_array($_SERVER['REMOTE_ADDR'],$cc->core->admin_ip))$access = true;
			else $access = false;
		}


		return $access;
	}
}