<?php
namespace CRAFTEngine\core\utilities\migrate;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */

class migrate
{
	protected $core;
	protected $params;
	protected $patches = array();

	const DB_VER = 2014011520;

	public function __construct($core)
	{
		$this->core = &$core;
	}

	public function construct($params)
	{
		$this->params = $params;

		if(isset($params['modules']))
		if(sizeof($params['modules'])!=0)
		foreach($params['modules'] as $m)
		{
			$this->update($m);
		}
	}

	/**
	 * Installing patch for module
	 *
	 * @param $module
	 * @param $version
	 * @return boolean
	 */
	public function setPatch($module,$version)
	{
		switch($module)
		{
			case 'system':
				$file = dirname(__FILE__)."/patches/$version.class.php";
				if(!is_readable($file))false;
				require_once($file);
				$name = '\CRAFTEngine\core\utilities\migrate\patch_'.$version;
				$patch = new $name($this->core);
				if($patch->set())return true;
				else return false;
				break;

			default:
				$file = $this->core->getParams()['root']."plugins/$module/utilities/migrate/patches/$version.class.php";
				if(!is_readable($file))false;
				require_once($file);
				$name = '\CRAFTEngine\plugins\\'.$module.'\utilities\migrate\patch_'.$version;
				$patch = new $name($this->core);
				if($patch->set())return true;
				else return false;
				break;
		}
	}

	/**
	 * Return existed patches of module if some exists or boolean if has an error
	 *
	 * @param $module
	 * @return array|boolean
	 */
	public function patchesList($module)
	{
		switch($module)
		{
			case 'system':
				$file = dirname(__FILE__)."/confs/list";
				if(!is_readable($file))return false;
				$list = $this->core->file->readAsArray('utilities/migrate/confs/list','system');
				break;

			default:
				$file = $this->core->getParams()['root']."plugins/$module/utilities/migrate/confs/list";
				if(!is_readable($file))return false;
				$list = $this->core->file->readAsArray("plugins/$module/utilities/migrate/confs/list",'edition');
				break;
		}

		sort($list);

		foreach($list as $patch)
		{
			if(!$this->setPatch($module,$patch))return false;
		}

		return $list;
	}

	/**
	 * Return installed patches of module if some exists or boolean if has an error
	 *
	 * @param $module
	 * @return array|boolean
	 */
	public function patchesInstalled($module)
	{
		$module = $this->core->sanString($module);

		if(in_array($module,array('system')) && $this->core->mysql->rows($this->core->mysql->query("SHOW TABLES LIKE 'system_versions'"))==0)
			return array();

		$qr = $this->core->mysql->query("SELECT * FROM system_versions WHERE element='$module' ORDER BY version");
		if(!$qr)return false;

		$list = array();
		for($i=0;$i<$this->core->mysql->rows($qr);$i++)
		{
			$rr = $this->core->mysql->fetch($qr);

			$list[] = $rr['version'];
		}

		return $list;
	}

	/**
	 * Updateing module
	 *
	 * @param $module
	 * @return boolean
	 */
	public function update($module)
	{
		$installed = $this->patchesInstalled($module);
		$exist = $this->patchesList($module);

		if($installed===false || $exist===false)return false;

		$list = array_diff($installed,$exist);

		foreach($list as $patch)
		{
			if(!in_array($patch,$exist))continue;

			$status = $this->setPatch($module,$patch);
			if(!$status)return false;
		}

		return true;
	}

	/**
	 * Mark version of module as installed
	 *
	 * @param $module
	 * @param $version
	 * @return boolean
	 */
	public function markAsInstalled($module,$version)
	{
		$version = intval($version);
		$module = $this->core->sanString($module);
		$now = date("Y-m-d H:i:s");

		if($this->core->mysql->rows($this->core->mysql->query("SELECT * FROM system_versions WHERE element='$module' AND version='$version' ORDER BY date DESC LIMIT 0,1"))!=0)
			return true;

		$st = $this->core->mysql->query("INSERT INTO system_versions(version,element,date) VALUE('$version','$module','$now')");

		if($st) return true;
		else return false;
	}
}