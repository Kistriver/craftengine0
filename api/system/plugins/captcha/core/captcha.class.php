<?php
class plugin_captcha_captcha
{
	public function __construct($core)
	{
		$this->core = $core;
		
		
	}
	
	public function generate($type)
	{
		$_SESSION['captcha'][$type]['was'] = isset($_SESSION['captcha'][$type]['new'])?
													$_SESSION['captcha'][$type]['new']:'';
		$_SESSION['captcha'][$type]['new'] = $this->generate_value($type);
	}
	
	public function generate_value($type)
	{
		$symbols = array(
		'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
		);
		shuffle($symbols);
		
		$c = array_rand($symbols, 6);
		
		$ca = '';
		foreach($c as $c)
		{
			$ca .= $symbols[$c];
		}
		
		return $ca;
	}
	
	public function check($value,$type)
	{
		$was = &$_SESSION['captcha'][$type]['was'];
		$new = &$_SESSION['captcha'][$type]['new'];
		
		if($was==$new or $new=='')
		{
			return false;
		}
		else
		{
			if($value==$new)
			{
				$was = $new;
				return true;
			}
			else
			{
				$was = $new;
				return false;
			}
		}
	}
	
	public function pict($type)
	{
		$width = 120; $height = 40;
		$font = dirname(__FILE__).'/../fonts/default.ttf';
		$fontsize = 14;
		$im = imagecreatetruecolor($width, $height);
		imagesavealpha($im, true);
		$bg = imagecolorallocatealpha($im, 255, 255, 255, 127);
		imagefill($im, 0, 0, $bg);
		
		for($i=0;$i<15;$i++)
		{
			imageline($im, (int)rand(0,$width/2), 
			(int)rand(0,$height/2), 
			(int)rand($width/2,$width), 
			(int)rand($height/2,$height), 
			(int)rand(0,255));
		}
		
		for ($i = 0; $i < strlen($_SESSION['captcha'][$type]['new']); $i++)
		{
			$x = ($width - 20) / strlen($_SESSION['captcha'][$type]['new']) * $i + 10;
			$x = rand($x, $x+4);
			$y = $height - ( ($height - $fontsize) / 2 );
			$curcolor = imagecolorallocate( $im, rand(0, 100), rand(0, 100), rand(0, 100) );
			$angle = rand(-25, 25);
			imagettftext($im, $fontsize, $angle, $x, $y, $curcolor, $font, $_SESSION['captcha'][$type]['new'][$i]);
		}
		
		$col = imagecolorallocatealpha($im, 0, 0, 0, 80);
		$size = 9;
		$x = 10;
		$y = $height-3;
		//imagettftext($im, $size, 0, $x, $y, $col, $font, 'Click to update');
		
		imagepng($im);
		imagedestroy($im);
	}
}
?>