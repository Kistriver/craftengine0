<?php
namespace CRAFTEngine\api\users;
class import extends \CRAFTEngine\core\api
{
	private $users_core;

	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['do']='doImport';

		$this->users_core = $this->core->plugin->initPl('users','core');
	}
	
	protected function doImport()
	{
		if(!in_array($_SERVER['REMOTE_ADDR'],$this->core->conf->system->core->admin_ip))
		{
			$this->core->error->error('server',403);
			return array(false);
		}

		$this->input('engine','version');
		$engine = str_replace('..','',$this->data['engine']);
		$version = str_replace('..','',$this->data['version']);

		$file = dirname(__FILE__).'/../confs/import/'.$engine.' '.$version.'.sql';
		if(!is_readable($file))return array(false);
		$dump_original = file_get_contents($file);

		set_time_limit(0);
		ignore_user_abort(1);

		switch($engine)
		{
			case 'craftengine':
				switch($version)
				{
					case '0.3.0':
						$replace = array(
							'DROP TABLE IF EXISTS `users`'=>'DROP TABLE IF EXISTS `system_tmp_users`',
							'CREATE TABLE IF NOT EXISTS `users` ('=>'CREATE TABLE IF NOT EXISTS `system_tmp_users` (',
							'INSERT INTO `users` ('=>'INSERT INTO `system_tmp_users` (',


							'DROP TABLE `users`'=>'DROP TABLE `system_tmp_users`',
							'CREATE TABLE `users` ('=>'CREATE TABLE `system_tmp_users` (',
						);

						$dump = str_replace(array_keys($replace),$replace,$dump_original);

						$result = $this->core->mysql->db['craftengine']->multi_query($dump);
						while($this->core->mysql->db['craftengine']->more_results())
						{
							$this->core->mysql->db['craftengine']->next_result();
						}
						if(!$result)return array(false);

						$qrM = $this->core->mysql->query("SELECT * FROM system_tmp_users");
						if(!$qrM)return array(false);

						$u = &$this->users_core->user;
						$prop = $u->getPropertiesList();

						$id_replace = array();
						for($i=0;$i<$this->core->mysql->rows($qrM);$i++)
						{
							$commit = true;
							$this->core->mysql->query("START TRANSACTION");
							$qr = $this->core->mysql->query("INSERT INTO users(id) VALUE(NULL)");
							if(!$qr)return array(false);

							$qr = $this->core->mysql->query("SELECT LAST_INSERT_ID()");
							if(!$qr)return array(false);

							$id= $qr->fetch_array();
							$id = $id['LAST_INSERT_ID()'];

							$fr = $this->core->mysql->fetch($qrM);

							foreach($fr as $name=>$el)
							{
								switch($name)
								{
									case 'id':
										$id_replace[$el] = $id;
										break;

									case 'name':
									case 'surname':
									case 'sex':
										if(in_array($name,$prop))
										{
											$u->$name->setProperty($id,$el);
										}
										break;

									case 'invite':
										if(in_array('invited',$prop))
										{
											$id_in_new = isset($id_replace[$el])?$id_replace[$el]:0;
											$this->core->mysql->query("UPDATE users SET invited='$id_in_new' WHERE id='$id'");
										}
										break;

									case 'password':
										if(in_array('password_salt',$prop))
										{
											$u->password_salt->setProperty($id,$u->password_salt->makeSalt());
										}
										break;

									case 'salt':
										continue;
										break;

									case 'rank':
										if(in_array('rank',$prop))
										{
											$u->rank->setProperty($id,array('user'));
										}
										break;

									case 'about':
										if(in_array('about',$prop))
										{
											$u->about->setProperty($id,$el);
										}
										break;

									case 'time_reg':
										if(in_array('time_signup',$prop))
										{
											$eln = date('Y-m-d H:i:s',$el);
											$u->time_signup->setProperty($id,$eln);
										}
										break;

									case 'login':
										if(in_array('login',$prop))
										{
											if(!$u->login->getPropertyByValue($el))
											{
												$u->login->setProperty($id,$el);
											}
											else
											{
												$commit = false;
											}
										}
										break;

									case 'email':
										if(in_array('email',$prop))
										{
											if(!$u->email->getPropertyByValue($el))
											{
												$u->email->setProperty($id,$el);
											}
											else
											{
												$commit = false;
											}
										}
										break;

									case 'birthday':
										if(in_array('birthday',$prop))
										{
											$da = explode(':',preg_replace("'^(\d{1,2})(\d{2})(\d{4})$'",'$1:$2:$3',$el));
											$el = array('day'=>$da[0],'month'=>$da[1],'year'=>$da[2]);
											$u->birthday->setProperty($id,$el);
										}
										break;
								}
							}

							if($commit)
							{
								$this->core->mysql->query("COMMIT");
							}
							else
							{
								$this->core->mysql->query("ROLLBACK");
							}
						}

						$qr = $this->core->mysql->query("DROP TABLE system_tmp_users");

						return array(true);
						break;

					default:
						return array(false);
						break;
				}
				break;

			default:
				return array(false);
				break;
		}

		return array(false);
	}
}
?>