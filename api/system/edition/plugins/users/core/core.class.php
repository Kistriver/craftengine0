<?php
namespace CRAFTEngine\plugins\users;

class core
{
	const SIGNUP_INVITE = 1;
	const SIGNUP_ADMIN = 2;
	const SIGNUP_MAIL = 4;
	const SIGNUP_MAX = 7;

	public function __construct($core)
	{
		$this->core = &$core;
	}

	/**
	 * Mode of registration
	 *
	 * @return int
	 */
	public function signupMode()
	{
		static $mode = 0;
		static $done = false;

		if(!$done)
		foreach($this->core->conf->plugins->users->user->signup_mode as $el)
		switch($el)
		{
			case 'admin':
				$mode += self::SIGNUP_ADMIN;
				break;

			case 'invite':
				$mode += self::SIGNUP_INVITE;
				break;

			case 'email':
				$mode += self::SIGNUP_MAIL;
				break;
		}

		$done = true;

		return $mode;
	}

	/**
	 * Is now registration mode include this parameter
	 *
	 * @param $is_mode
	 * @return boolean
	 */
	public function isSignupMode($is_mode)
	{
		if(substr(decbin($this->signupMode()),-1*strlen(decbin($is_mode)),1)==1)return true;
		else return false;
	}

	/**
	 * Register user in temporarily table
	 *
	 * @param array $p
	 * @return boolean
	 */
	public function signup($p=array())
	{
		$mode = $this->signupMode();
		$mode_in_db = self::SIGNUP_MAX - $this->signupMode();
		$u = $this->core->plugin->initPl('users','user');
		$prop = $u->getPropertiesList();

		foreach($prop as $pr)
		{
			if(!isset($p[$pr]))$p[$pr] = null;

			$value = $u->$pr->canSignup($p[$pr]);
			if($value===false)
			{
				return false;
			}
		}

		$qr = $this->core->mysql->query("INSERT INTO users_signup(mode) VALUE('$mode_in_db')");
		if(!$qr)return false;

		$qr = $this->core->mysql->query("SELECT LAST_INSERT_ID()");
		if(!$qr)return false;

		//$id = $this->core->mysql->fetch($qr);
		$id = $qr->fetch_array();
		$id = $id['LAST_INSERT_ID()'];

		foreach($prop as $pr)
		{
			$value = $u->$pr->signup($id,$p[$pr]);
			if($value===false)return false;
		}

		if($mode==0)$this->register($id);

		return true;
	}

	/**
	 * Register user in primary table
	 *
	 * @param $id
	 * @return boolean
	 */
	public function register($id)
	{
		$u = $this->core->plugin->initPl('users','user');
		$prop = $u->getPropertiesList();

		$qr = $this->core->mysql->query("INSERT INTO users(id) VALUE(NULL)");
		if(!$qr)return false;

		$qr = $this->core->mysql->query("SELECT LAST_INSERT_ID()");
		if(!$qr)return false;

		$idnew = $qr->fetch_array();
		$idnew = $idnew['LAST_INSERT_ID()'];

		foreach($prop as $pr)
		{
			$value = $u->$pr->register($id,$idnew);
			if($value===false)return false;
		}

		$this->core->mysql->query("DELETE FROM users_signup WHERE id='$id'");

		return true;
	}

	/**
	 * Change mode of user in temporarily table
	 *
	 * @param $id
	 * @param $mode_add
	 * @return boolean
	 */
	public function changeMode($id,$mode_add)
	{
		$qr = $this->core->mysql->query("SELECT * FROM users_signup WHERE id='$id'");
		if($this->core->mysql->rows($qr)==0)return false;
		$fr = $this->core->mysql->ауеср($qr);

		$mode =  $fr['mode'];

		if($this->isSignupMode($mode_add))return false;

		$mode += $mode_add;

		$qr = $this->core->mysql->query("UPDATE users_signup SET mode='$mode' WHERE id='$id'");
		if(!$qr)return false;

		if($mode==self::SIGNUP_MAX)
		{
			$this->register($id);
		}

		return true;
	}
}