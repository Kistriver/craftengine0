<?php
namespace CRAFTEngine\plugins\users;
interface signupUserInterface
{
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
	 * @param $idnew
	 * @return boolean
	 */
	public function register($id,$idnew);
}