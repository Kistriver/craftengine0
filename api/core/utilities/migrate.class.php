<?php
namespace CRAFTEngine\core\utilities;
/**
 * @package core
 * @author Alexey Kachalov <alex-kachalov@mail.ru>
 * @access public
 * @see http://kcraft.su/
 */

class migrate
{
	protected $core;
	protected $patches = array();

	const DB_VER = 2014011520;

	public function __construct($core)
	{
		$this->core = &$core;
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

	}

	/**
	 * Return existed patches of module if some exists or boolean if has an error
	 *
	 * @param $module
	 * @return array|boolean
	 */
	public function patchesList($module)
	{

	}

	/**
	 * Return installed patches of module if some exists or boolean if has an error
	 *
	 * @param $module
	 * @return array|boolean
	 */
	public function patchesInstalled($module)
	{

	}

	/**
	 * Updateing module
	 *
	 * @param $module
	 * @return boolean
	 */
	public function update($module)
	{

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

	}

	/*public function setBase()
	{
		$query = file_get_contents(dirname(__FILE__).'/migrate/base.sql');
		$this->core->mysql->db['craftengine']->multi_query($query);
	}

	public function upgrade()
	{
		$info = $this->mysqlGetLatestVersion('system',self::DB_VER);

		$ver = $info['version'];
		$list = $this->patchesList('system',0,self::DB_VER);

		foreach($list as $l)
		{
			require_once(dirname(__FILE__).'/migrate/'.$l.'.class.php');
			$name = '\CRAFTEngine\core\utilities\migrate\patch_'.$l;
			$this->patches[$l] = new $name($this->core);
		}

		foreach($this->patches as $ver=>$cl)
		{
			$cl->upgrade();
		}
	}

	public function patchesList($el,$min_ver,$max_ver)
	{
		$el = $this->core->sanString($el);

		$list = file_get_contents(dirname(__FILE__).'/migrate/list');
		$list = explode("\n",$list);

		$list_updates = array();


		$installed = $this->mysqlGetVersions($el);

		foreach($list as $l)
		{
			if($l>$min_ver && $l<$max_ver)
			{
				if(!isset($installed[$l]))
				$list_updates[] = $l;
			}
		}

		sort($list_updates);

		return $list_updates;
	}

	public function mysqlGetLatestVersion($el,$ver)
	{
		$ver = (int)$ver;
		$el = $this->core->sanString($el);
		//$now = date("Y-m-d H:i:s");
		$q = $this->core->mysql->query("SELECT * FROM system_versions WHERE element='$el' ORDER BY version DESC LIMIT 0,1");
		if($this->core->mysql->rows($q)==0)
		{
			//$this->core->mysql->query("INSERT INTO system_versions(version,element,date) VALUE('$ver','$el','$now')");
			$this->addVersion($el,$ver);

			$CURVER = $ver;
		}
		else
		{
			$r = $this->core->mysql->fetch($q);
			$CURVER = $r['version'];
		}

		return array('version'=>$CURVER);
	}

	public function mysqlGetVersions($el)
	{
		$el = $this->core->sanString($el);
		$q = $this->core->mysql->query("SELECT * FROM system_versions WHERE element='$el' ORDER BY version");


		$return = array();
		for($i=0;$i<$this->core->mysql->rows($q);$i++)
		{
			$res = $this->core->mysql->fetch($q);

			$return[$res['version']] = array(
				'version'=>$res['version']
			);
		}

		return $return;

	}

	public function addVersion($el,$ver)
	{
		$ver = (int)$ver;
		$el = $this->core->sanString($el);
		$now = date("Y-m-d H:i:s");

		if($this->core->mysql->rows($this->core->mysql->query("SELECT * FROM system_versions WHERE element='$el' AND version='$ver' ORDER BY date DESC LIMIT 0,1"))!=0)
			return true;

		$st = $this->core->mysql->query("INSERT INTO system_versions(version,element,date) VALUE('$ver','$el','$now')");

		if($st) return true;
		else return false;
	}*/
}