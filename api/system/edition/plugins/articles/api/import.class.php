<?php
namespace CRAFTEngine\api\articles;
class import extends \CRAFTEngine\core\api
{
	private $users_core;

	public function init()
	{
	   #$this->functions['act']='function';
		$this->functions['do']='doImport';

		$this->articles_core = $this->core->plugin->initPl('articles','core');
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
							'DROP TABLE IF EXISTS `articles`'=>'DROP TABLE IF EXISTS `system_tmp_articles`',
							'CREATE TABLE IF NOT EXISTS `articles` ('=>'CREATE TABLE IF NOT EXISTS `system_tmp_articles` (',
							'INSERT INTO `articles` ('=>'INSERT INTO `system_tmp_articles` (',


							'DROP TABLE `articles`'=>'DROP TABLE `system_tmp_articles`',
							'CREATE TABLE `articles` ('=>'CREATE TABLE `system_tmp_articles` (',
						);

						$dump = str_replace(array_keys($replace),$replace,$dump_original);

						$result = $this->core->mysql->db['craftengine']->multi_query($dump);
						while($this->core->mysql->db['craftengine']->more_results())
						{
							$this->core->mysql->db['craftengine']->next_result();
						}
						if(!$result)return array(false);

						$qrM = $this->core->mysql->query("SELECT * FROM system_tmp_articles");
						if(!$qrM)return array(false);

						$u = &$this->articles_core->article;
						$prop = $u->getPropertiesList();

						$id_replace = array();
						for($i=0;$i<$this->core->mysql->rows($qrM);$i++)
						{
							$commit = true;
							$this->core->mysql->query("START TRANSACTION");
							$qr = $this->core->mysql->query("INSERT INTO articles(id) VALUE(NULL)");
							if(!$qr)return array(false);

							$qr = $this->core->mysql->query("SELECT LAST_INSERT_ID()");
							if(!$qr)return array(false);

							$id = $qr->fetch_array();
							$id = $id['LAST_INSERT_ID()'];

							$fr = $this->core->mysql->fetch($qrM);

							foreach($fr as $name=>$el)
							{
								switch($name)
								{
									case 'id':
										$id_replace[$el] = $id;
										break;

									case 'user':
										if(in_array('author',$prop))
										{
											$u->author->setProperty($id,$el);
										}
										break;

									case 'title':
										if(in_array('title',$prop))
										{
											$u->title->setProperty($id,$el);
										}
										break;

									case 'article':
										if(in_array('body',$prop))
										{
											$u->body->setProperty($id,$el);
										}
										break;

									case 'time':
										if(in_array('publish_time',$prop))
										{
											$eln = date('Y-m-d H:i:s',$el);
											$u->publish_time->setProperty($id,$eln);
										}
										break;

									case 'times':
										if(in_array('views',$prop))
										{
											$u->views->setProperty($id,$el);
										}
										break;

									case 'tags':
										if(in_array('tags',$prop))
										{
											$u->tags->setProperty($id,$el);
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

						$qr = $this->core->mysql->query("DROP TABLE system_tmp_articles");

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