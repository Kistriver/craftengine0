<?php
namespace CRAFTEngine\plugins\articles;
class load
{
	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function OnEnable()
	{
		$art = $this->core->plugin->initPl('articles','core');

		foreach($art->article->getPropertiesList() as $p)
		{
			$art->article->$p->install();
		}
	}

	public function OnDisable()
	{

	}

	public function registerPluginEvent($id,$plugin,$addInfo,$staticInfo=null)
	{
		switch($id.'_'.$plugin)
		{
			case 'canDo_users':
				if(isset($staticInfo['act']))
				switch($staticInfo['act'])
				{
					case 'articles_edit':
						if(isset($staticInfo['property']))
						switch($staticInfo['property'])
						{
							case 'author':
								$addInfo = false;
								break;

							case 'publish_time':
								$addInfo = false;
								break;

							case 'body':
							case 'tags':
							case 'title':
								$addInfo = true;
								break;

							case 'views':
								$addInfo = false;
								break;
						}

						if($addInfo==true)
						{
							$art = $this->core->plugin->initPl('articles','core');

							if(!in_array('rank',$art->users_core->user->getPropertiesList()))
							{
								if($this->core->conf->plugins->users->user->admin_ip==true)
								{
									if(in_array($_SERVER['REMOTE_ADDR'],$this->core->conf->system->core->admin_ip))
									{
										return true;
									}
									else
									{
										return false;
									}
								}

								$this->core->error->error('server',403);
								return false;
							}
							else
							{
								$ranks = $art->users_core->user->rank->getProperty($art->users_core->user->currentUser());
								$can = false;

								foreach($ranks as $r)
									if(in_array($r,$this->core->conf->plugins->articles->ranks->access['articles_edit']))
									{
										$can = true;
										break;
									}

								if($can)
								{
									return true;
								}

								$this->core->error->error('server',403);
								return false;
							}
						}
						break;
				}
				break;
		}

		return $addInfo;
	}
}
?>