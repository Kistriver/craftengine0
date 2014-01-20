<?php
namespace CRAFTEngine\core\scripts;
set_time_limit(0);
ignore_user_abort(1);

class mail
{
	public function __construct($core)
	{
		$core->mail->getWaitingList(50);
	}
}