<?php

/**
*     defines the flexibase class
*     @file                flexibase.php
*     @see flexibase
*/
/**
* deals with public displaying of user profiles
*     @class		  flexibase
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since       31st January 2007
*     @package		     flexibase
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
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'flexibase', $vars, $visible ) );
// end class creation

class flexibase extends flexibase_def
{
	/**
	* constructor
	*/
	function flexibase( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax, $basic_gui;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/flexibase_gui' . phpEx );
		$this->gui = new flexibase_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'flexibase' );
		$this->gui->lang = $this->lang;
		
		$Sajax->add2export( 'flexibase->getthumb', '$image, $name' );
	}
	/**
	* deals with deciding what to display
	*/
	function display()
	{
		global $errors, $Cl_root_path;
		
		// decide what to display and run the appropriate function
		switch ( $_GET[ SUBMODE_URL ] )
		{
			case 'list':
				$this->listitems();
				break;
			case 'item':
				$this->showitem();
				break;
			case 'index':
				$this->showindex();
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], GENERAL_ERROR );
				break;
		}
	}
	/**
	* lists items
	*/
	function listitems()
	{
		global $errors, $db, $basic_gui;
		
		// remove all old display images
		foreach ( glob( $Cl_root_path . 'cache/flexibase_image*' ) as $file )
		{
			@unlink( $file );
		}
		
		$baseid = intval( $_GET[ 'baseid' ] );
		$showfrom = ( isset( $_GET[ 'showfrom' ] ) ) ?  intval( $_GET[ 'showfrom' ] ) : 0;
		$shownum = ( isset( $_GET[ 'shownum' ] ) ) ? intval( $_GET[ 'shownum' ] ) : 30;
		
		// get the fieldlist
		$sql = "SELECT name, description, fieldlist FROM " . FLEXIBASE_TABLE . " WHERE id='$baseid'";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
		}
		$base = $db->sql_fetchrow( $result );
		
		// read from db
		if ( isset( $_GET[ 'search' ] ) )
		{ // need to make the search adendum here
			print_R( explode( ':::', $_GET[ 'search' ] ) );
			$search = 'WHERE ';
			foreach ( explode( ':::', $_GET[ 'search' ] ) as $s )
			{
				if ( empty( $s ) )
				{
					continue;
				}
				// no need for this now
				$s = explode( '::', substr( $s, 6 ) );
				
				if ( $s[ 0 ] == '__' )
				{ // this needs all, so the rules are a bit different
					$search .= '( ';
					foreach ( explode( ':::', $base[ 'fieldlist' ] ) as $field )
					{
						$field = explode( '::', $field );
						if ( $field[ 1 ] != 'blob' )
						{
							$search .= $field[ 0 ] . ' LIKE \'%' . $s[ 1 ] . '%\' OR ';
						}
					}
					// remove the last or and add bracket
					$search = substr( $search, 0, -3 );;
					$search .= ' ) AND ';
				}else
				{ // quite simpler
					$search .= $s[ 0 ] . ' LIKE \'%' . $s[ 1 ] . '%\' AND ';
				}
			}
			// remove last AND
			$search = substr( $search, 0, -4 );
		}else
		{ // no search
			$search = '';
		}
		
		$sql = "SELECT * FROM " . FLEXIBASE_TABLE . "_$baseid  $search LIMIT $showfrom, $shownum";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
		}
		$items = $db->sql_fetchrowset( $result );
		
		// fetch the item count innefficiently
		$sql = "SELECT COUNT(*) AS valcount FROM " . FLEXIBASE_TABLE . "_$baseid";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
		}
		$valcount = $db->sql_fetchfield( 'valcount' );
		
		// pagination stuff
		$pag = array( array(  'URL' => '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=' . $baseid, 'title' => $base[ 'name' ] ) );
		$basic_gui->set_level( 1, 'flexibase', '', $pag );
		
		$this->gui->listitems( $items, $base, $baseid, array( $shownum, $showfrom, $valcount ) );
	}
	/**
	* shows single item
	*/
	function showitem()
	{
		global $errors, $db, $basic_gui;
		
		$baseid = intval( $_GET[ 'baseid' ] );
		$itemid = intval( $_GET[ 'itemid' ] );
		
		$sql = "SELECT * FROM " . FLEXIBASE_TABLE . "_$baseid WHERE id='$itemid' LIMIT 1";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
		}
		
		$item = $db->sql_fetchrow( $result );
		
		// get the fieldlist
		$sql = "SELECT name, fieldlist FROM " . FLEXIBASE_TABLE . " WHERE id='$baseid'";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
		}
		$base = $db->sql_fetchrow( $result );
		
		// pagination stuff
		$pag = array( array(  'URL' => '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=' . $baseid, 'title' => $base[ 'name' ] ) );
		$pag[] = array( 'URL' => '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=item&baseid=' . $baseid . '&itemid=' . $itemid, 'title' => $item[ $this->gui->getname( $base[ 'fieldlist' ] ) ] );
		$basic_gui->set_level( 1, 'flexibase', '', $pag );
		
		$this->gui->showitem( $item, $base, $baseid, $itemid );
	}
	/**
	* creates a thumbnail of something ya
	*/
	function getthumb( $name, $image )
	{
		global $Cl_root_path, $basic_gui;
		
		// use the function found on php.net to make a thumbnail
		$dir = $Cl_root_path . 'cache/';
		$this->resizejpeg( $dir, $dir, $image, 800, 600, 100, 100 );
	
		return array( $name, $basic_gui->get_URL() . '/cache/' . $image . '_thumb.jpg?' . EXECUTION_TIME );
	}
	/**
	* function resizejpeg:
	*
	*    creates a resized image based on the max width
	*    specified as well as generates a thumbnail from
	*    a rectangle cut from the middle of the image.
	*
	*    @dir    = directory image is stored in
	*    @newdir = directory new image will be stored in
	*    @img    = the image name
	*    @max_w  = the max width of the resized image
	*    @max_h  = the max height of the resized image
	*    @th_w  = the width of the thumbnail
	*    @th_h  = the height of the thumbnail
	*    @copyright kyle dot florence at gmail dot com
	*	16-Sep-2006 03:38
	*
	* changed to hopefully be multiextension
	*/
	function resizejpeg($dir, $newdir, $img, $max_w, $max_h, $th_w, $th_h)
	{
		// set destination directory
		if (!$newdir) $newdir = $dir;
		
		// get original images width and height
		list($or_w, $or_h, $or_t) = getimagesize($dir.$img);
		
		// decide which function to use for imagemaking
		switch ( $or_t )
		{
			case IMAGETYPE_GIF: $func = 'gif'; break;
			case IMAGETYPE_JPEG: $func = 'jpeg'; break;
			case IMAGETYPE_PNG: $func = 'png'; break;
			case IMAGETYPE_BMP: $func = 'bmp'; break;
			default; return FALSE;
		}
		
		
		// obtain the image's ratio
		$ratio = ($or_h / $or_w);
		
		// original image
		eval( "\$or_image = @imagecreatefrom$func(\$dir.\$img);" );
		
		// resize image?
		if ($or_w > $max_w || $or_h > $max_h) 
		{
			// resize by height, then width (height dominant)
			if ($max_h < $max_w) 
			{
				$rs_h = $max_h;
				$rs_w = $rs_h / $ratio;
			}
			// resize by width, then height (width dominant)
			else 
			{
				$rs_w = $max_w;
				$rs_h = $ratio * $rs_w;
			}
		
			// copy old image to new image
			$rs_image = imagecreatetruecolor($rs_w, $rs_h);
			imagecopyresampled($rs_image, $or_image, 0, 0, 0, 0, $rs_w, $rs_h, $or_w, $or_h);
		}
		// image requires no resizing
		else 
		{
			$rs_w = $or_w;
			$rs_h = $or_h;
		
			$rs_image = $or_image;
		}
		
		// generate resized image
		eval( "@image$func( \$rs_image, \$newdir.str_replace( '.', 'R.', \$img ), 100);" );
		
		$th_image = imagecreatetruecolor($th_w, $th_h);
		
		// cut out a rectangle from the resized image and store in thumbnail
		$new_w = (($rs_w / 2) - ($th_w / 2));
		$new_h = (($rs_h / 2) - ($th_h / 2));
		
		imagecopyresized($th_image, $rs_image, 0, 0, $new_w, $new_h, $rs_w, $rs_h, $rs_w, $rs_h);
		
		// generate thumbnail
		eval( "@image$func( \$th_image, \$newdir.\$img.'_thumb.jpg', 100);" );
		
		return true;
	}
	/**
	* shows the index
	*/
	function showindex()
	{
		global $db, $lang_loader;
		
		// get the fieldlist
		$lang = $lang_loader->board_lang;
		
		$sql = "SELECT id, name, description FROM " . FLEXIBASE_TABLE . " WHERE language='$lang'";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
		}
		$bases = $db->sql_fetchrowset( $result );
		
		$this->gui->showindex( $bases );
	}
	
	
	//
	// End of flexibase class
	//
}

?>