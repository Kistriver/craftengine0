<?php
namespace CRAFTEngine\plugins\articles;
interface articleInterface
{
	public function __construct($core);

	/**
	 * Install module addition if is needed
	 *
	 * @return boolean
	 */
	public function install();

	/**
	 * Get value of property with ID
	 *
	 * @param $id
	 * @return mixed
	 */
	public function getProperty($id);

	/**
	 * Get id of article by value
	 *
	 * @param $value
	 * @return boolean|string|int
	 */
	public function getPropertyByValue($value);

	/**
	 * Set value of property with ID
	 *
	 * @param $id
	 * @param $value
	 * @return boolean
	 */
	public function setProperty($id,$value);

	/**
	 * Can we set property with this value
	 *
	 * @param $id
	 * @param $value
	 * @return boolean
	 */
	public function validateProperty($value,$id=null);

	/**
	 * Can user UID get value of property
	 *
	 * @param $id
	 * @param $uid
	 * @return boolean
	 */
	public function canGetProperty($id,$uid=null);

	/**
	 * Can user UID set value of property
	 *
	 * @param $id
	 * @param $uid
	 * @return boolean
	 */
	public function canSetProperty($id,$uid);
}