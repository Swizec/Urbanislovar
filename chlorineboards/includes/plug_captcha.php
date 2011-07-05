<?php

/**
*     defines the plug_captcha class
*     @file                plug_captcha.php
*     @see plug_captcha
*/
/**
* this is captcha plugin
*     @class		   plug_captcha
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.6
*     @since        28th December 2006
*     @package		     plug_captcha
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// debug :: debug flag

// class creation
$vars = array( 'debug', 'wordlist' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'plug_captcha', $vars, $visible ) );
// end class creation

class Plug_captcha extends plug_captcha_def
{
	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function Plug_captcha( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $cache;
		
		// get the lang
		$this->lang = $lang_loader->get_lang( 'plug_captcha' );
	}
	/**
	* this is the logical captcha, simple stuff
	*/
	function logical( $width = 300, $height = 50 )
	{
		global $Cl_root_path;
		
		// there is a cap to height and width due to the background image
		if ( $width > 500 )
		{
			$width = 500;
		}
		if ( $height > 500 )
		{
			$height = 500;
		}
		
		// make the string
		$i = rand() % count ( $this->lang[ 'logic' ] );
		$w1 = $this->randomword();
		$w2 = $this->randomword();
		$string = sprintf( $this->lang[ 'logic' ][ $i ], $w1, $w2, $w2 );
		
		// make the image with it
		$im = imagecreatetruecolor( $width, $height );
		$text = imagecolorallocate( $im, rand()%255, rand()%255, rand()%255 );
		$black = imagecolorallocate( $im, 0, 0, 0 );
		// the background
		$imb = imagecreatefrompng( $Cl_root_path . 'includes/captcha/back.png' );
		imagecopy( $im, $imb, 0, 0, rand()%(500-$width), rand()%(500-$height), $width, $height );
		imagedestroy( $imb );
		// put on the text
		$len = strlen( $string );
		$font = $Cl_root_path . 'includes/captcha/tahoma.ttf';
		for ( $size = 40, $w = $width+1, $h = $height+1; ( $w >= $width-10 || $h >= $height-10 ) && $size >= 0; $size-- )
		{
			$data = imagettfbbox( $size, 0, $font, $string );
			$w = $data[ 2 ];
			$h = -$data[ 7 ];
		}
		$x = $width / 2  - $w / 2;
		$y = $height / 2 + $h / 2;
		imagettftext( $im, $size, 0, $x+1, $y+1, $white, $font, $string );
		imagettftext( $im, $size, 0, $x, $y, $text, $font, $string );
		
		if ( TESTING == TRUE )
		{ // for testing purposes of this plugin
			header("Content-type: image/png");
			imagepng( $im );
			imagedestroy( $im );
		}else
		{
			$t = $this->microtime_float();
			$file = $Cl_root_path . 'cache/captcha_' . $t . '.png';
			imagepng( $im, $file );
			imagedestroy( $im );
			return array( $file, $w1, $t );
		}
	}
	/**
	* this captcha fetches an image from google and shows it for recognition
	* allow_url_fopen is required for this to work
	*/
	function recognition( $rec = FALSE )
	{
		// using altavista instead of google because google produces the results via JS
		$URL = 'http://www.altavista.com/image/results?itag=ody&q=QUEST&mik=photo&mik=graphic&mip=all&mis=all&miwxh=all';
		
		// get the whole HTML
		$term = $this->randomword( FALSE );
		$html = file_get_contents( str_replace( 'QUEST', $term, $URL ) );
		
		// find the images
// 		<img alt="Go to fullsize image" class="thumbnail" src="http://re3.mm-a4.yimg.com/image/2862573555" width="130" height="120" border="0" title="thumbnail of Aussie_Koala.jpg" />
		preg_match_all( '<img.*?src="(.*?/image/.*?)".*?/>', $html, $matches );
		if ( count ( $matches[ 1 ] ) == 0 )
		{
			$matches = $this->recognition( TRUE );
		}
		if ( $rec )
		{
			return $matches;
		}
		
		$image = $matches[ 1 ][ rand()%count( $matches[ 1 ] ) ];
		
		if ( TESTING == TRUE )
		{ // for testing
			echo "<html><body><img src=\"$image\" /><br />$term</body></html>";
		}else
		{
			return array( $image, $term );
		}
	}
	/**
	* this is a run of the mill random captcha
	*/
	function random( $width = 100, $height = 25, $length = 7 )
	{
		global $Cl_root_path;
		
		// there is a cap to height and width due to the background image
		if ( $width > 500 )
		{
			$width = 500;
		}
		if ( $height > 500 )
		{
			$height = 500;
		}
		
		// make the string
		$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789$%#?+*';
		$string = substr( str_shuffle( $str ), rand()%(68 - $length ), $length );
		
		// make the image with it
		$im = imagecreatetruecolor( $width, $height );
		$text = imagecolorallocate( $im, rand()%255, rand()%255, rand()%255 );
		$white = imagecolorallocate( $im, 255, 255, 255 );
		// the background
		$imb = imagecreatefrompng( $Cl_root_path . 'includes/captcha/back1.png' );
		imagecopy( $im, $imb, 0, 0, rand()%(500-$width), rand()%(500-$height), $width, $height );
		imagedestroy( $imb );
		// put on the text
		$font = $Cl_root_path . 'includes/captcha/tahoma.ttf';
		for ( $size = 40, $w = $width+1, $h = $height+1; ( $w >= $width-10 || $h >= $height-10 ) && $size >= 0; $size-- )
		{
			$data = imagettfbbox( $size, 0, $font, $string );
			$w = $data[ 2 ];
			$h = -$data[ 7 ];
		}
		$x = $width / 2  - $w / 2;
		$y = $height / 2 + $h / 2;
		imagettftext( $im, $size, 0, $x+1, $y+1, $white, $font, $string );
		imagettftext( $im, $size, 0, $x, $y, $text, $font, $string );
		// imagestring( $im, $font, $x+1, $y+1, $string, $white );
		// imagestring( $im, $font, $x, $y, $string, $text );
		
		if ( TESTING )
		{ // for testing purposes of this plugin
			header("Content-type: image/png");
			imagepng( $im );
			imagedestroy( $im );
		}else
		{
			$t = $this->microtime_float() ;
			$file = $Cl_root_path . 'cache/captcha_' . $t . '.png';
			imagepng( $im, $file );
			imagedestroy( $im );
			return array( $file, $string, $t );
		}
	}
	/**
	* returns a random word
	*/
	function randomword( $large = TRUE )
	{
		global $lang_loader, $cache;
		
		if ( $large )
		{ // from the big list
			// get the wordlist(s)
			if ( !$list = $cache->pull( 'captcha_wordlist' ) )
			{
				include( $Cl_root_path . 'includes/captcha/wordlist' . phpEx );
				$cache->push( 'captcha_wordlist', $list, TRUE );
			}
			$this->wordlist_large = $list;
			$this->wordnum_large = count( $list );
			return $this->wordlist_large[ rand()%$this->wordnum_large ];
		}else
		{ // from the small list, this is also language sensitive
			if ( !$list = $cache->pull( 'captcha_wordlist_small' ) )
			{
				include( $Cl_root_path . 'includes/captcha/wordlist_small' . phpEx );
				$cache->push( 'captcha_wordlist_small', $list, TRUE );
			}
			$this->wordlist_small = $list;
			$this->wordnum_small = count( $list );
			return $this->wordlist_small[ rand()%$this->wordnum_small ];
		}
	}
	/**
	* makes pretty microtime
	*/
	function microtime_float()
	{
   		list($usec, $sec) = explode(" ", microtime());
   		return ((float)$usec + (float)$sec);
	}
	
	//
	// End of Plug_captcha class
	//
}


?>