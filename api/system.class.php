<?php
class api_system extends api
{
	public function init()
	{
		#$this->functions['function']='act';
		$this->functions['loggedin']='loggedin';
	}
	
	protected function loggedin()
	{
		if($_SESSION['loggedin'])
		{
			$ses = array(
				true,
				'nickname' => $_SESSION['nickname'],
				//'salt' => $_SESSION['salt'],
				//'pass' => $_SESSION['pass'],
				'email' => $_SESSION['email'],
				'id' => $_SESSION['id'],
				'login' => $_SESSION['login'],
				'rank' => $_SESSION['rank'],
				'rank_main' => $_SESSION['rank_main'],
			);
			
			return $this->json($ses);
		}
		else
		{
			return $this->json(array(false));
		}
	}
}