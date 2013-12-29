<?php
namespace CRAFTEngine\client\core;
class widget
{
	public $list = array();
	public $order = array();

	public function __construct($core)
	{
		$this->core = $core;

		$this->list = $this->widgetsList();

		$wid = (array)$this->core->conf->get('widgets');
		if(sizeof($wid['order'])!=0)
		{
			foreach($wid['order'] as &$w)
			{
				if(isset($this->list[$w]))
				{
					$this->order[$w] = $this->list[$w];

					if(!empty($this->list[$w]['loadClass']))
					require_once($this->core->core_confs['root'].'widgets/core/'.$w.'/'.$this->list[$w]['loadClass'].'.class.php');
				}
			}
		}
	}

	public function makeEvent($id,$wid,$addInfo)
	{
		foreach($this->list as $plug=>$pages)
		{
			$fun = '\CRAFTEngine\client\plugins\\'.$wid.'\RegisterWidgetEvent';
			if(function_exists($fun))
				$addInfo = $fun($id,$wid,$addInfo);
		}

		return $addInfo;
	}

	public function widgetsList()
	{
		$themes = array();
		$root = $this->core->core_confs['root'].'widgets/';
		$dir = opendir($root);
		while($folders = readdir($dir))
		{
			if($folders!="." && $folders!=".." && is_dir($root.$folders))
				if(file_exists($root.$folders.'/main.json'))
					if(is_dir($root.$folders.'/core'))
						if(is_dir($root.$folders.'/tpl'))
						{
							$main = $this->core->conf->get('../widgets/'.$folders.'/main');

							if($main!==null)
							{
								$active = false;
								if(in_array($folders,$this->core->conf->get('widgets')->active))
									$active = true;

								$themes[$folders] = array(
									'author'=>isset($main->author)?$main->author:'',
									'web'=>isset($main->web)?$main->web:'',
									'title'=>isset($main->title)?$main->title:'',
									'description'=>isset($main->description)?$main->description:'',
									'loadClass'=>isset($main->loadClass)?$main->loadClass:'',
									'folder'=>$folders,
									'active'=>$active,
								);
							}
						}
		}
		closedir($dir);
		return $themes;
	}

	public function on($widget)
	{
		$wid = (array)$this->core->conf->get('widgets');

		$exists = false;
		foreach($this->list as &$l)
		{
			if($l['folder']==$widget && $l['active']==false)
			{
				$exists = true;
				$l['active'] = true;
				$this->order[$l['folder']] = $l;
				$wid['active'][] = $widget;
				$wid['order'][] = $widget;
			}
		}
		if(!$exists)return false;

		if(!$this->core->conf->set('widgets',$wid))return false;

		return true;
	}

	public function off($widget)
	{
		$wid = (array)$this->core->conf->get('widgets');
		$wid_active = array();
		$wid_order = array();

		$exists = false;
		foreach($this->list as &$l)
		{
			if($l['folder']==$widget && $l['active']==true)
			{
				$exists = true;
				$l['active'] = false;
				unset($this->order[$l['folder']]);
				foreach($wid['active'] as &$w)
					if($w!=$widget)
						$wid_active[] = $w;
				foreach($wid['order'] as &$w)
					if($w!=$widget)
						$wid_order[] = $w;
			}
		}
		if(!$exists)return false;

		$wid['active'] = $wid_active;
		$wid['order'] = $wid_order;

		if(!$this->core->conf->set('widgets',$wid))return false;

		return true;
	}

	public function up($widget)
	{
		$wid = (array)$this->core->conf->get('widgets');

		$order = array();

		if(isset($this->list[$widget]))
		{
			for($i=0;$i<sizeof($wid['order']);$i++)
			{
				if($wid['order'][$i]==$widget && isset($wid['order'][$i-1]))
				{
					$order[] = $widget;
					$order[] = $wid['order'][$i-1];
				}
				else
				{
					if(isset($wid['order'][$i+1]))
					{
						if($wid['order'][$i+1]!=$widget)
						{
							$order[] = $wid['order'][$i];
						}
					}
					else
					{
						$order[] = $wid['order'][$i];
					}
				}
			}
		}

		$wid['order'] = $order;

		if(!$this->core->conf->set('widgets',$wid))return false;

		$this->order = array();
		foreach($wid['order'] as &$w)
		{
			if(isset($this->list[$w]))
			{
				$this->order[$w] = $this->list[$w];
			}
		}

		return true;
	}

	public function down($widget)
	{
		$wid = (array)$this->core->conf->get('widgets');

		$order = array();

		if(isset($this->list[$widget]))
		{
			for($i=0;$i<sizeof($wid['order']);$i++)
			{
				if($wid['order'][$i]==$widget && isset($wid['order'][$i+1]))
				{
					$order[] = $wid['order'][$i+1];
					$order[] = $widget;
				}
				else
				{
					if(isset($wid['order'][$i-1]))
					{
						if($wid['order'][$i-1]!=$widget)
						{
							$order[] = $wid['order'][$i];
						}
					}
					else
					{
						$order[] = $wid['order'][$i];
					}
				}
			}
		}

		$wid['order'] = $order;

		if(!$this->core->conf->set('widgets',$wid))return false;

		$this->order = array();
		foreach($wid['order'] as &$w)
		{
			if(isset($this->list[$w]))
			{
				$this->order[$w] = $this->list[$w];
			}
		}

		return true;
	}
}