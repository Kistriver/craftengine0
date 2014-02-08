<?php
namespace CRAFTEngine\plugins\users;
class load
{
	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function OnEnable()
	{
		$uc = $this->core->plugin->initPl('users','core');

		foreach($uc->user->getPropertiesList() as $p)
		{
			$uc->user->$p->install();
		}
	}

	public function OnDisable()
	{

	}

	public function registerPluginEvent($id,$plugin,$addInfo)
	{
		switch($id.'_'.$plugin)
		{
			case 'upload_complete_core':
				if($addInfo['type']=='users_user_avatar')
				{

				}
				break;
		}

		return $addInfo;
	}
}
?>