<?php

/**
* Tag cloud creation class
*     @class		   TagCloud_engine
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        17th July 2007
* @filesource
*/

error_reporting( E_ALL ^ E_NOTICE ^ E_WARNING );
//error_reporting( E_ALL );
//error_reporting( 0 );
ob_start();

function Tagerrors( $errno, $errstr, $errfile, $errline )
{
	if ( $errno != E_WARNING && $errno != E_NOTICE && $errno != 0 && $errno != E_STRICT )
	{
		// tell the main process we're done
		echo $errstr . '<br />File:' . $errfile . '<br />Line:' . $errline;
		file_put_contents( PATH . '/cache/' . TIME . '/done.txt', 'done' );
		die();
	}
}

//set_error_handler( 'Tagerrors' );

class TagCloud_engine
{
	/**
	* constructor
	*/
	function TagCloud_engine( $source = '', $file = TRUE, $method = 'html',	$size = '300x150' )
	{
		$this->outWidth = '100%';
		$this->outHeight = 400;
		$this->outFont = 'Verdana';
		$this->outFontMax = 12;
		$this->outColour = '#000000';
		$this->margin = 5; // koliko procentov max stata da se pokaze
		$this->maxOut = 100; // ko je vec kot toliko besed se margin uposteva
		
		$this->size = $size;
		
		$this->outMethod = $method;
		
		include( PATH . '/includes/TagCloud/TagCloud_engine_en.php' );
		include( PATH . '/includes/TagCloud/TagCloud_engine_sl.php' );
		
		$this->en = new TagCloud_en( $this );
		$this->sl = new TagCloud_sl( $this );
	
		include( PATH . '/includes/TagCloud/detectLang.php' );
		$langDetect = new detectLang( PATH );
		
		include( $source );
		
		$this->specificFile = ( ID == 'none' ) ? FALSE : TRUE;
		if ( $this->specificFile )
		{
			$inputHash = array( ID => $inputHash[ ID ] );
		}
		
		foreach ( $inputHash as $i => $hash )
		{
			$file = $hash[ 'target' ];
			$file = PATH . PATH2 . '/' . $i . '.txt';
			echo "$file\n";
			if ( !is_readable( $file ) )
			{
				continue;
			}
			
			$this->statisticsHash = array();
			$this->statistics = array();
			$this->maxStat = 0;
			
			$source = file_get_contents( $file );
			//$source = $this->readFile( $inputHash[ $i ][ 'target' ], $inputHash[ $i ][ 'type' ], $contents, TIME, $inputHash[ $i ][ 'name' ] );
			
			mb_internal_encoding( mb_detect_encoding( $source ) );
			
			$language = $langDetect->detect( mb_substr( $source, 0, 5000 ) );
			$language = substr( $language, 0, 2 );
			
			$this->$language->initiate( $source, FALSE );
			$this->$language->main();
		
			$this->output( $i );
		}
		
		// tell the main process we're done
		file_put_contents( PATH . PATH2 . '/done.txt', 'done' );
	}
	/**
	* creates the output :)
	*/
	function output( $result_id )
	{
		echo "$result_id\n";
		switch ( $this->outMethod )
		{
			case 'image': 
				$this->showimage( $result_id );
				break;
			case 'html':
				header( 'Content-type: text/html; charset=UTF-8' );
				echo $this->showhtml();
				break;
			case 'justprint':
				header( 'Content-type: text/plain; charset=UTF-8' );
				//foreach ( $this->statisticsHash as $key )
				//{
				//	echo $key . ':::' . $this->statistics[ $key ][ 'count' ] . ':::'  . $this->statistics[ $key ][ 'output' ] . "\n";
				//}
				print_R( $this->statistics );
				break;
			default:
				header( 'Content-type: text/plain; charset=UTF-8' );
				print( 'By god man, use a real method!' );
		}
	}
	/**
	* orders the statistics via a hashing array
	*/
	function orderStats()
	{
		// make some vars
		$this->statisticsHash = array_keys( $this->statistics );
		// this is a nice trick that does what was a php based sorting algorithm ... those never seem to work well
		usort( $this->statisticsHash, array( 'TagCloud', 'cmpStat' ) );
	}
	/**
	* creates the cloud in html output form
	*/
	function showhtml()
	{
		$margin = round( $this->maxStat / 100 * $this->margin );
		$count = count( $this->statistics );
	
		$html = '<div style="width: ' . $this->outWidth . '; height: ' . $this->outHeight . '; font-family: ' . $this->outFont . ';">';
		$wordkeys= 'wordnum = ' . count( $this->statistics ) . ';';
		$i = 0;
		
		foreach ( $this->statisticsHash as $key )
		{
			if ( $this->statistics[ $key ][ 'count' ] < $margin && $count > $this->maxOut )
			{ // no need
				continue;
			}
			if ( $this->maxStat == 0 )
			{
				$this->maxStat = 1;
			}
			$size = round( $this->statistics[ $key ][ 'count' ] * $this->outFontMax / $this->maxStat, 2);
			
			$word = $this->statistics[ $key ][ 'output' ];
			$html .= '<p style="float:left; background: yellow; margin: 1px;" id="' . $i . '"><a style="color: ' . $this->outColour . '; font-size: ' . strval( $size ) . 'em;">' . $word . '</a></p>';
			$i++;
		}
		
		$html .= '</div>';
		
		$javascript = file_get_contents( 'javascript.js' );
		
		//$html = "<html>\n<head>\n<script language=\"JavaScript\" type=\"text/javascript\">\n$javascript\n$wordkeys\n</script>\n</head>\n<body onload=\"organise(); return false\">\n$html\n</body></html>";
		
		return $html;
	}
	/**
	* puts the result in an image
	*/
	function showimage( $result_id )
	{
		$sizeId = $this->getSize( $this->size );
		$size = explode( 'x', $this->size );
		$width = intval( $size[ 0 ] );
		$height = intval( $size[ 1 ] );
		
		$image = imagecreatetruecolor( $width, $height );
		imagesavealpha( $image, TRUE );
		$yellow = imagecolorallocate( $image, 255, 255, 0 );
		$black = imagecolorallocate( $image, 0, 0, 0 );
		$white = imagecolorallocatealpha( $image, 255, 255, 255, 127 );
		$shadow = imagecolorallocatealpha( $image, 0, 0, 0, 90 );
		$font = PATH . '/includes/TagCloud/lucida.ttf';
		$this->sizemultiplier = $sizeId;
		$this->rectangleTrash = array();
		$this->outputCap = 400;
		$this->revolveRate = 0;
		$this->endWords = array();
		$this->widthLimit = 30;
		$this->heightLimit = 10;
		$margin = 5;
		
		imagealphablending( $image, FALSE );
		imagefilledrectangle( $image, 0, 0, $width, $height, $white );
		imagealphablending( $image, TRUE );
		
		$this->countOutput = 0;
		
		$rectangle = array( 'x' => $margin, 'y' => $margin, 'w' => $width - $margin, 'h' => $height - $margin );
		$this->rectangleTrash[] = $rectangle;
			
		$this->putWord( $width, $height, $margin, $font );
		
		$this->drawImage( $image, $black, $yellow, $shadow, $font, $result_id );
		
		$add = ( $this->specificFile ) ? $this->size : '';
		$file = PATH2 . '/' . $add . 'image_' . $result_id . '.png';	
		imagepng( $image, PATH . $file, 9 );
		echo '<img src="' . TEMPLATE_PATH . $file . '" />';
		
		//echo $this->countOutput . '::' . count( $this->statisticsHash );
		//echo '<br />';
		//print_R( $this->rectangleTrash );
	}
	/**
	* draws the image once everything is calculated
	*/
	function drawImage( &$image, $black, $yellow, $shadow, $font, $result_id )
	{
		$output = array();
		for ( $i = count( $this->endWords )-1; $i >= 0; $i-- )
		{
			$word = $this->endWords[ $i ];
			$x = $word[ 'x' ];
			$y = $word[ 'y' ];
			$tw = $word[ 'tw' ];
			$th = $word[ 'th' ];
			$p1 = $word[ 'p1' ];
			$fontS = $word[ 'fontS' ];
			
			imagefilledrectangle( $image, $x+( $tw/5 ), $y-( $th/5 ), $x+( $tw/5 )+$tw, $y-( $th/5 )+$p1+$th, $shadow );
			imagefilledrectangle( $image, $x, $y-$th+$p1, $x+$tw, $y+$p1, $yellow );
			imagettftext( $image, $fontS, 0, $x, $y, $black, $font, $word[ 'output' ] );
			
			$output[] = array( 'x' => $x, 'y' => $y-$th+$p1, 'tw' => $tw, 'th' => $th, 'word' => $word[ 'output' ] );
		}
		
		$file = PATH . PATH2 . '/wordhash_' . $result_id . '.php';
		file_put_contents( $file, serialize( $output ) );
		
		$file = PATH . PATH2 . '/statistics_' . $result_id . '.php';
		file_put_contents( $file, serialize( $this->statistics ) );
		
		$hash = array();
		foreach ( $this->statistics as $k => $word )
		{
			$hash[ $word[ 'output' ] ] = $k;
		}
		
		$file = PATH . PATH2 . '/statisticshash_' . $result_id . '.php';
		file_put_contents( $file, serialize( $hash ) );
	}
	/**
	* puts a word on the image
	*/
	function putWord( $Width, $Height, $margin, $font )
	{
		if ( $this->countOutput > $this->outputCap )
		{
			return;
		}
		if ( $this->countOutput % 25 == 0 && $this->countOutput != 0 )
		{
			//$this->cleanUp();
		}
		
		$key = array_shift( $this->statisticsHash );
		$word = $this->statistics[ $key ];
		
		$fontS = ( ( ( $word[ 'count' ] / $this->maxStat ) ) * ( $Height / $Width ) )*$this->sizemultiplier;
		if ( $fontS < 10 )
		{
			$margin = 3;
		}
		if ( $fontS < 6 )
		{
			$fontS = 6;
			$margin = 2;
		}
	
		$position = imagettfbbox( $fontS, 0, $font, $word[ 'output' ] );
		$tw = $position[ 2 ];
		$th = -( $position[ 5 ] - $position[ 1 ] );
		
		$refuse = array();
		$found = FALSE;
		while( count( $this->rectangleTrash ) > 0 )
		{
			$rectangle = array_shift( $this->rectangleTrash );
			$width = $rectangle[ 'w' ];
			$height = $rectangle[ 'h' ];
			if ( $width-$margin >= $tw && $height-$margin >= $th )
			{ // the word fits, use the rectangle
				$found = TRUE;
				break;
			}else
			{
				$refuse[] = $rectangle;
			}
		}
		if ( !$found )
		{ // apparently there's no more space, die
			return;
		}
		$this->rectangleTrash = array_merge( $refuse, $this->rectangleTrash );

		$x = $rectangle[ 'x' ];
		$y = $rectangle[ 'y' ];
	
		$x += rand( $margin, $width - $tw - $margin );
		$y += rand( $th - $margin, $height - $position[ 1 ] - $margin );
	
		$this->endWords[] = array( 'output' => $word[ 'output' ], 'x' => $x, 'y' => $y, 'tw' => $tw, 'th' => $th, 'p1' => $position[ 1 ], 'fontS' => $fontS );
		$this->countOutput++;
		
		// add rectangles
		$x1 = $rectangle[ 'x' ];
		$y1 = $rectangle[ 'y' ];
		$w1 = $rectangle[ 'w' ];
		$h1 = $rectangle[ 'h' ];
		$yy = $y + $position[ 1 ];
		$add = array();
		// top row
		$add[] = array( 'x' => $x1, 'y' => $y1, 'w' => $width, 'h' => $yy - $th - $y1  );
		// left box
		$add[] = array( 'x' => $x1, 'y' => $y1 + ( $yy - $th - $y1 ), 'w' => $x - $x1, 'h' => $th );
		// bottom row
		$add[] = array( 'x' => $x1, 'y' => $yy, 'w' => $width, 'h' => $height - ( $yy - $y1 ) );
		// right box
		$add[] = array( 'x' => $x + $tw, 'y' => $y1 + ( $yy - $th - $y1 ), 'w' => $width - ( $x - $x1 ) - $tw, 'h' => $th );
		
		for ( $i = 0; $i < 4; $i++ )
		{
			if ( $add[ $i ][ 'w' ] >= $this->widthLimit && $add[ $i ][ 'h' ] >= $this->heightLimit )
			{
				$this->rectangleTrash[] = $add[ $i ];
			}
		}
		
		// now the trash needs sorting
		usort( $this->rectangleTrash, array( 'TagCloud_engine', 'trashSorter' ) );
		
		// recurse ftw
		$this->putWord( $Width, $Height, $margin, $font );
	}
	/**
	* sort function for the rectangle trash
	*/
	function trashSorter( $a, $b )
	{
		if ( $a[ 'w' ] > $b[ 'w' ] )
		{
			return -1;
		}
		if ( $a[ 'h' ] > $b[ 'h' ] )
		{
			return -1;
		}
		if ( $a[ 'w' ] < $b[ 'w' ] )
		{
			return 1;
		}
		if( $a[ 'h' ] < $b[ 'h' ] )
		{
			return 1;
		}
		return 0;
	}
	/**
	* function to produce bigger rectangles from leftovers
	*/
	function cleanUp()
	{
		$clusters = array();
		
		usort( $this->rectangleTrash, array( 'TagCloud_engine', 'sortY' ) );
		
		$y = 0;
		$h = 0;
		for ( $i = 0; $i < count( $this->rectangleTrash ); $i++ )
		{
			$rectangle = $this->rectangleTrash[ $i ];
			if ( abs( ( $y + $h ) - $rectangle[ 'y' ] ) > 5 || $i == 0 )
			{					
				$y = $rectangle[ 'y' ];
				$h = 0;
				$clusters[ $y ] = array();
			}
			$clusters[ $y ][] = $rectangle;
			$h += $rectangle[ 'h' ];
		}
			
		// now organise by x ...
		foreach( $clusters as $c => $cluster )
		{
			$replace = array();
			for ( $i = 1, $x = $cluster[ 0 ][ 'x' ], $replace[ $x ] = array( $cluster[ 0 ] ); $i < count( $cluster ); $i++ )
			{
				$r1 = $cluster[ $i-1 ];
				$r2 = $cluster[ $i ];
				if ( !( ( $r1[ 'x' ] >= $r2[ 'x' ] && $r1[ 'x' ] <= $r2[ 'x' ]+$r2[ 'w' ]-$this->widthLimit ) ||
					( $r2[ 'x' ] >= $r1[ 'x' ] && $r2[ 'x' ] <= $r1[ 'x' ]+$r1[ 'w' ]-$this->widthLimit ) ) )
				{
					$x = $r2[ 'x' ];
					$replace[ $x ] = array();
				}
				$replace[ $x ][] = $r2;
			}
			$clusters[ $c ] = $replace;
		}
		
		// hook 'em up
		$newTrash = array();
		foreach ( $clusters as $y => $cluster )
		{
			foreach ( $cluster as $x => $sub )
			{
				if ( count( $sub ) < 2 )
				{
					$newTrash[] = $sub[ 0 ];
					continue;
				}
				for ( $i = 0; $i < count( $sub )-1; $i++ )
				{
					if ( !( ( $r1[ 'x' ] >= $r2[ 'x' ] && $r1[ 'x' ] <= $r2[ 'x' ]+$r2[ 'w' ]-$this->widthLimit ) ||
						( $r2[ 'x' ] >= $r1[ 'x' ] && $r2[ 'x' ] <= $r1[ 'x' ]+$r1[ 'w' ]-$this->widthLimit ) ) )
					{
						// no match
						$newTrash[] = $r1;
						continue;
					}
					
					$r1 = $sub[ $i ];
					$r2 = $sub[ $i+1 ];
					if ( $r1[ 'x' ] > $r2[ 'x' ] )
					{
						$w = ( $r2[ 'w' ]-( $r1[ 'x' ]-$r2[ 'x' ] ) );
						$w1 = ( $r2[ 'x' ]+$r2[ 'w' ]-( $r1[ 'x' ]+$r1[ 'w' ] ) );
						$w -= ( $w1 > 0 ) ? $w1 : 0;
						
						$newTrash[] = array( 'x' => $r1[ 'x' ], 'y' => $r1[ 'y' ], 'w' => $w, 'h' => $r1[ 'h' ]+$r2[ 'h' ] );
						
						$r1[ 'x' ] += $w;
						$r1[ 'w' ] -= $w;
						$r2[ 'w' ] -= $w;
					}else
					{
						$w = ( $r1[ 'w' ]-( $r1[ 'x' ]-$r2[ 'x' ] ) );
						$w1 = ( $r1[ 'x' ]+$r1[ 'w' ]-( $r2[ 'x' ]+$r1[ 'w' ] ) );
						$w -= ( $w1 > 0 ) ? $w1 : 0;
						
						$newTrash[] = array( 'x' => $r2[ 'x' ], 'y' => $r1[ 'y' ], 'w' => $w, 'h' => $r1[ 'h' ]+$r2[ 'h' ] );
						
						$r2[ 'x' ] += $w;
						$r2[ 'w' ] -= $w;
						$r1[ 'w' ] -= $w;
					}
					
					if ( $r1[ 'w' ] >= $this->widthLimit )
					{
						$newTrash[] = $r1;
					}
				}
			}
		}
		
		//print_R( $clusters ); echo "<br />\n<br />\n";
		//print_R( $newTrash ); echo "<br />\n<br />\n<br />\n<br />\n";
		
		$this->rectangleTrash = $newTrash;
		
		usort( $this->rectangleTrash, array( 'TagCloud_engine', 'trashSorter' ) );
		
		return;
		
		$clusters = array();
		
		// cluster them up according to x position
		for ( $i = 0; $i < count( $this->rectangleTrash ); $i++ )
		{
			$r = $this->rectangleTrash[ $i ];
			if ( !isset( $clusters[ $r[ 'x' ] ] ) )
			{
				$clusters[ $r[ 'x' ] ] = array();
			}
			$clusters[ $r[ 'x' ] ][] = $r;
		}
		
		// go through the clusters and organise by height
		$clustersO = array();
		foreach ( $clusters as $x => $cluster )
		{
			$clustersO[ $x ] = array();
			$y = 0;
			$h = 0;
			$w = 9000;
			usort( $cluster, array( 'TagCloud_engine', 'sortY' ) );
			for ( $i = 0; $i < count( $cluster ); $i++ )
			{
				if ( abs( ( $y + $h ) - $cluster[ $i ][ 'y' ] ) > 2 )
				{
					if ( $i != 0 )
					{
						$cl = $clustersO[ $x ][ $y ];
						$arr = array();
						$clustersO[ $x ][ $y ] = array();
						$arr[ 'c' ] = $cl;
						$arr[ 'h' ] = $h;
						$arr[ 'w' ] = $w;
						$clustersO[ $x ][ $y ] = $arr;
					}
					
					$y = $cluster[ $i ][ 'y' ];
					$h = 0;
					$w = 9000;
					$clustersO[ $x ][ $y ] = array();
				}
				$clustersO[ $x ][ $y ][] = $cluster[ $i ];
				$h += $cluster[ $i ][ 'h' ];
				if ( $cluster[ $i ][ 'w' ] < $w )
				{
					$w = $cluster[ $i ][ 'w' ];
				}
			}
			
			$cl = $clustersO[ $x ][ $y ];
			$arr = array();
			$clustersO[ $x ][ $y ] = array();
			$arr[ 'c' ] = $cl;
			$arr[ 'h' ] = $h;
			$arr[ 'w' ] = $w;
			$clustersO[ $x ][ $y ] = $arr;
		}
		$clusters = $clustersO;
		
		// all nicely clustered up, now replace the rectangleTrash with this stuff
		$this->rectangleTrash = array();
		foreach ( $clusters as $x => $cluster )
		{
			foreach ( $cluster as $y => $subcluster )
			{
				$this->rectangleTrash[] = array( 'x' => $x, 'y' => $y, 'w' => $subcluster[ 'w' ], 'h' => $subcluster[ 'h' ] );
				$w = $subcluster[ 'w' ];
				for ( $i = 0; $i < count( $subcluster[ 'c' ] ); $i++ )
				{
					$sub = $subcluster[ 'c' ][ $i ];
					if ( $sub[ 'w' ] > $w + 50 )
					{
						$this->rectangleTrash[] = array( 'x' => $x + $w, 'y' => $sub[ 'y' ], 'w' => $sub[ 'w' ] - $w, 'h' => $sub[ 'h' ] );
					}
				}
			}
		}
		usort( $this->rectangleTrash, array( 'TagCloud_engine', 'trashSorter' ) );
		
		//print_R( $clusters );
		//echo '<br /><br /><br />' . "\n\n\n";
	}
	/**
	* sorts a cluster by y
	*/
	function sortY( $a, $b )
	{
		$a = $a[ 'y' ];
		$b = $b[ 'y' ];
		if ( $a == $b )
		{
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}
	/**
	* decodes built-in XML entities
	*/
	function xml_entity_decode( $source )
	{
		$what = array( '&quot;', '&amp;', '&apos;', '&lt;', '&gt;' );
		$with = array( '"', '&', "'", '<', '>' );
		return str_replace( $what, $with, $source );
	}
	/**
	* does everything needed to read obscure file formats
	*/
	function readFile( $target, $type, $contents, $time, $name )
	{
		$Cl_root_path = PATH . '/';
		
		if ( $type == 'text/plain' )
		{
			return $contents;
		}
		
		if ( $type != 'application/octet-stream' )
		{ // convert into odt
			$pyPath = 'C:\Program Files\OpenOffice.org 2.2\program\python'; // openoffice's python binary
			$abs = str_replace( 'TagCloud_engine.php', '', __FILE__ );
			
			$sPath = $target; // source
			$tPath = $target . '.odt'; // target
			$target = $target . '.odt';
			
			exec( "\"$pyPath\" ${abs}DocumentConverter.py $sPath $tPath", $result );
			
			if ( !empty( $result ) )
			{ // verily there was a problem converting
				print_R( $result );
				return;
			}
		}
		
		include_once( $Cl_root_path . 'includes/TagCloud/Zip.php' );
		//@unlink( $Cl_root_path . 'cache/' . $time . '/content.xml' ); // might've been something old there, kill it
		
		$Zip = new Archive_Zip( $target );
		$Zip->extract( array( 'add_path' => 'cache/' . $time . '/' . $name . '/', 'set_chmod' => '0666' ) );
		
		if ( !is_readable( $Cl_root_path . 'cache/' . $time . '/' . $name . '/content.xml' ) )
		{
			return;
		}
		$contents = file_get_contents( $Cl_root_path . 'cache/' . $time . '/' . $name . '/content.xml' );
		$contents = preg_replace( '#\<.*?\>#s', ' ', $contents );
		
		return $contents;
	}
	/**
	* converts size to inverted size id
	*/
	function getSize( $size )
	{
		switch ( $size )
		{
			case '200x200':
				$size = 15;
				break;
			case '400x250':
				$size = 30;
				break;
			case '500x500':
				$size = 20;
				break;
			case '600x400':
				$size = 50;
				break;
			case '1024x800':
			default;
				$size = 100;
		}
		
		return $size;
	}
	//
	// End of TagCloud class
	//
}

define( 'PATH', $argv[ 1 ] );
define( 'PATH2',  $argv[ 7 ] );
$file = $argv[ 2 ];
define( 'TIME', $argv[ 4 ] );
define( 'TEMPLATE_PATH', $argv[ 6 ] );
if ( isset( $argv[ 8 ] ) )
{
	define( 'ID', $argv[ 8 ] );
}else
{
	define( 'ID', 'none' );
}

$Tag = new TagCloud_engine( $file, TRUE, $argv[ 3 ], $argv[ 5 ] );

ob_flush();

?>