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
}