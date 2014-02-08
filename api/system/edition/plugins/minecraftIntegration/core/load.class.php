<?php
namespace CRAFTEngine\plugins\minecraftIntegration;
class load
{
	public function __construct($core)
	{
		$this->core = &$core;


	}

	public function OnEnable()
	{
		$this->users_core = $this->core->plugin->initPl('users','core');
		$this->users_core->user->addProperty(dirname(__FILE__)."/../utilities/script/scripts/xLauncher/users/","minecraft_integration_session_id");
		$this->users_core->user->addProperty(dirname(__FILE__)."/../utilities/script/scripts/xLauncher/users/","minecraft_integration_server_id");
		$this->users_core->user->addProperty(dirname(__FILE__)."/../utilities/script/scripts/xLauncher/users/","minecraft_integration_hw_id");
		$this->users_core->user->addProperty(dirname(__FILE__)."/../utilities/script/scripts/xLauncher/users/","minecraft_integration_banned");

		$this->users_core->user->minecraft_integration_session_id->install();
		$this->users_core->user->minecraft_integration_server_id->install();
		$this->users_core->user->minecraft_integration_hw_id->install();
		$this->users_core->user->minecraft_integration_banned->install();
	}

	public function OnDisable()
	{

	}

	public function registerPluginEvent($id,$plugin,$addInfo)
	{
		switch($id.'_'.$plugin)
		{

		}

		return $addInfo;
	}
}
?>