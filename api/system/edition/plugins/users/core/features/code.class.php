<?php
namespace CRAFTEngine\plugins\users\features;
class code implements \CRAFTEngine\plugins\users\featureInterface
{
	public function __construct($core,$users_core)
	{
		$this->core = &$core;
		$this->users_core = &$users_core;
		$this->confs = &$this->core->conf->plugins->users;
	}

	/**
	 * Add code with type ans value
	 *
	 * @param $type
	 * @param $value
	 * @param $data
	 * @return bool
	 */
	public function addCode($type,$value,$data)
	{
		$value = $this->core->sanString($value);
		$type = $this->core->sanString($type);
		$data = $this->core->sanString(json_encode($data,JSON_UNESCAPED_UNICODE));
		$timestamp = time();

		$qr = $this->core->mysql->query("INSERT INTO users_code(type,value,data,time) VALUE('$type','$value','$data','$timestamp')");
		if($qr)return true;
		else return false;
	}

	/**
	 * Delete code with type and value
	 *
	 * @param $type
	 * @param $value
	 * @return bool
	 */
	public function removeCode($type,$value)
	{
		$qr = $this->core->mysql->query("DELETE FROM users_code WHERE type='$type' AND value='$value'");
		if($qr)return true;
		else return false;
	}

	/**
	 * Get code with type and value
	 *
	 * @param $type
	 * @param $value
	 * @return array|bool|mixed
	 */
	public function getCode($type,$value)
	{
		$value = $this->core->sanString($value);
		$type = $this->core->sanString($type);

		$qr = $this->core->mysql->query("SELECT * FROM users_code WHERE type='$type' AND value='$value'");
		if($this->core->mysql->rows($qr)==0)return false;

		$fr = $this->core->mysql->fetch($qr);
		return json_decode($fr['data'],true);
	}
}