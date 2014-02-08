<?php
namespace CRAFTEngine\plugins\users;
interface userInterface extends signupUserInterface
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
	 * Get id of user by ID
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
	 * Can user idfrom get value of property
	 *
	 * @param $id
	 * @param $idfrom
	 * @return boolean
	 */
	public function canGetProperty($id,$idfrom);

	/**
	 * Can user idfrom set value of property
	 *
	 * @param $id
	 * @param $idfrom
	 * @return boolean
	 */
	public function canSetProperty($id,$idfrom);
}