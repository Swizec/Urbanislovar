<?php

/**
*     defines the store class
*     @file                store.php
*     @see store
*/
/**
* deals with everything partaining to stores
*     @class		  store
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since       3rd March 2007
*     @package		     store
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
eval( Varloader::createclass( 'store', $vars, $visible ) );
// end class creation

class store extends store_def
{
	/**
	* constructor
	*/
	function store( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax, $basic_gui, $Sajax;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/store_gui' . phpEx );
		$this->gui = new store_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'store' );
		$this->gui->lang = $this->lang;
	}
	/**
	* all that is needed for all the various types of showing :)
	*/
	function show()
	{
		global $db, $errors, $lang_loader, $basic_gui, $board_config, $Sajax;
		
		$mode = ( isset( $_GET[ SUBMODE_URL ] ) ) ? $_GET[ SUBMODE_URL ] : 'front';
		$from = ( isset( $_GET[ 'page' ] ) ) ? intval( $_GET[ 'page' ] ) : 0;
		$from *= $board_config[ 'store_itemsPerPage' ];
		$many = $board_config[ 'store_itemsPerPage' ];
		$category = ( isset( $_GET[ 'cat' ] ) ) ? intval( $_GET[ 'cat' ] ) : 0;
		$mode = ( $category != 0 ) ? 'cat' : $mode;
		
		$count = 0;
		$items = array();
		
		if ( $mode != 'front' )
		{
			$sql = "SELECT i . * , im . * FROM " . STORE_ITEM_TABLE . " i LEFT JOIN " . STORE_IMAGES_TABLE . " im ON i.item_image = im.image_id INNER JOIN " . STORE_CAT2ITEM_TABLE . " c2i ON i.item_id = c2i.item_id WHERE c2i.cat_id = '$category' AND i.item_language = '" . $lang_loader->board_lang . "' AND i.item_isdeleted = '0' ORDER BY i.item_title ASC LIMIT $from, $many";
				
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Error fetching data', CRITICAL_ERROR );
			}
			$items = $db->sql_fetchrowset( $result );
		}
		
		$sql = "SELECT * FROM " . STORE_CATEGORY_TABLE . " WHERE cat_language='" . $lang_loader->board_lang . "'";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Error fetching data', CRITICAL_ERROR );
		}
		$categories = $db->sql_fetchrowset( $result );
		
		if ( $mode != 'front' )
		{
			$sql = "SELECT COUNT(*) as count FROM " . STORE_ITEM_TABLE . " i, " . STORE_CAT2ITEM_TABLE . " c2i WHERE i.item_id=c2i.item_id AND c2i.cat_id='$category' AND i.item_isdeleted='0' AND i.item_language='" . $lang_loader->board_lang . "'";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Error fetching data', CRITICAL_ERROR );
			}
			$count = $db->sql_fetchrow( $result );
			$count = $count[ 'count' ];
		}
		
		$basic_gui->add_JS( 'includes/store.js' );
		
		$Sajax->add2export( 'store->fetchdesc', '$id' );
		
		$this->gui->show( $items, $categories, $category, $from, $many, $count, $mode );
	}
	/**
	* fetches a description
	*/
	function fetchdesc( $id )
	{
		global $db;
		
		$sql = "SELECT item_description FROM " . STORE_ITEM_TABLE . " WHERE item_id='$id'";
		if ( !$db->sql_query( $sql ) )
		{
			return array( $id, 'Something went wrong' );
		}
		
		return array( $id, $db->sql_fetchfield( 'item_description' ) );
	}
	
	//
	// End of store class
	//
}

?>