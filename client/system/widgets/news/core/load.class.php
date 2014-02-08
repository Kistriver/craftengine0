<?php
namespace CRAFTEngine\client\widgets\news;
class load
{
	public function __construct($core)
	{
		$this->core = $core;
	}

	public function render()
	{
		$this->core->render['SYS']['WIDGETS'][] = array('news','main');

		$this->core->api->get('articles/article/posts',array('page'=>1));

		$data = $this->core->api->answer_decode;

		$content = '';
		$tags = array();
		$desc = '';

		$posts = array();

		if($data['data'][0]===true && sizeof($data['data'][1])!=0)
			for($i=0;$i<sizeof($data['data'][1]);$i++)
			{
				$post = $this->core->f->sanString($data['data'][1][$i]);

				if(!isset($post['article']))$post['article']='';
				if(!isset($post['tags']))$post['tags']=array();

				$post['article'] = str_replace("\n",'<br /> ',$post['article']);
				$post['article'] = str_replace('<br /> ',"<br />\r\n",$post['article']);
				$desc = mb_substr($post['article'], 0, 150, 'UTF-8');
				$desc = str_replace("<br />\r\n",' ',$desc);
				foreach($post['tags'] as $t)$tags[trim($t)] = trim($t);
				//$post['tags'] = implode(', ',$post['tags']);
				//$post['post_time'] = date('d-m-Y H:i',$post['post_time']);

				$b = array('[b]','[/b]','[i]','[/i]','[s]','[/s]','[u]','[/u]','[url]','[/url]');
				$a = array('<b>','</b>','<i>','</i>','<s>','</s>','<u>','</u>','<a href="','">Link</a>');
				$post['article'] = str_replace($b,$a,$post['article']);

				$post['article'] = preg_replace("'^(.*)\[craftcut(|=(.*))\](.*)$'is","$1$3",$post['article']);

				$posts[] = $post;
			}
		$this->core->render['WIDGETS']['news'] = $posts;
	}

	public function OnEnable()
	{

	}

	public function OnDisable()
	{

	}
}