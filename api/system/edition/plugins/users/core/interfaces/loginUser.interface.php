<?php
namespace CRAFTEngine\plugins\users;
interface loginUserInterface
{
	/**
	 * Can user login with this ID
	 *
	 * @param $id
	 * @return boolean
	 */
	public function canLogin($id);
}