<?php
namespace CRAFTEngine\plugins\users;
interface userInterface
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
	public function validateProperty($id,$value);

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
	 * @param $value
	 * @return boolean
	 */
	//public function canSetProperty($id,$idfrom);

	/**
	 * Can user signup with this value of property
	 *
	 * @param $value
	 * @return boolean
	 */
	public function canSignup($value);

	/**
	 * Signup user with this value of property
	 *
	 * @param $id
	 * @param $value
	 * @return boolean
	 */
	public function signup($id,$value);

	/**
	 * Register new user
	 *
	 * @param $id
	 * @return boolean
	 */
	public function register($id,$idnew);
}