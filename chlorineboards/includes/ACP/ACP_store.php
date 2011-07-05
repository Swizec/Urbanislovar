<?php

/**
*     defines the ACP_store class
*     @file                ACP_store.php
*     @see ACP_store
*/
/**
* ACP panel for administration of the store
*     @class		   ACP_store
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        2nd March 2007
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
eval( Varloader::createclass( 'ACP_store', $vars, $visible ) );
// end class creation

class ACP_store extends ACP_store_def
{
	/**
	* constructor
	*/
	function ACP_store( $debug = FALSE )
	{
		global $Cl_root_path, $basic_gui, $lang_loader, $security;
		
		$this->debug = $debug;
	
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_store' );
		
		// make the two urls
		$url1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_store&s=manage' );
		$url2 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_store&s=categories' );

		// add to page
		$basic_gui->add2sidebar( 'right', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $url1 . '">' . $this->lang[ 'Side_manage' ] . '</a><br /><a href="' . $url2 . '">' . $this->lang[ 'Side_categories' ] . '</a></span>' );
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $template, $errors, $Cl_root_path;
		
		$template->assign_files( array(
			'ACP_store' => 'ACP/store' . tplEx
		) );
		
		// get the subsubmode
		$sub = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : '';
		
			
		switch( $sub )
		{
			case 'manage':
				$this->manage();
				break;
			case 'manage2':
				$this->manage2();
				break;
			case 'categories':
				$this->categories();
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* deals with managing shows and events
	*/
	function manage()
	{
		global $mod_loader, $template, $security, $db, $errors, $lang_loader, $Cl_root_path, $Cl_root_path4template, $board_config, $basic_gui;
		
		// fetch the item if this is an edit
		$item_id = ( $_GET[ 'id' ] ) ? intval( $_GET[ 'id' ] ) : 0;
		if ( $item_id != 0 )
		{ // 'tis an edit
			$sql = "SELECT *, im.image_thumbnail FROM " . STORE_ITEM_TABLE . " i LEFT JOIN " . STORE_IMAGES_TABLE . " im ON i.item_image=im.image_id WHERE i.item_id='$item_id' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not read from database', CRITICAL_ERROR );
			}
			$item = $db->sql_fetchrow( $result );
			// get the categories list ...
			$sql = "SELECT c.cat_name FROM " . STORE_CATEGORY_TABLE . " c, " . STORE_CAT2ITEM_TABLE . " c2i, " . STORE_ITEM_TABLE . " i WHERE c.cat_id=c2i.cat_id AND c2i.item_id=i.item_id AND i.item_id='$item_id' ";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not read from database', CRITICAL_ERROR );
			}
			$categories = '';
			while( $row = $db->sql_fetchrow( $result ) )
			{
				$categories .= $row[ 'cat_name' ] . ', ';
			}
			
			$thumbnail = '<img src="' . $Cl_root_path4template . '/' . $board_config[ 'store_imagepath' ] . '/' . $item[ 'image_thumbnail' ] . '" />';
		}else
		{ // set default values
			$item = array(
				'item_description' => '',
				'item_language' => '',
				'item_image' => '',
				'item_price' => '',
				'item_isdeleted' => 0,
			);
			$categories = '';
			$thumbnail = '';
		}
		
		// get the editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'description', 'quickpost' => FALSE, 'def_text' => stripslashes( $item[ 'item_description' ] ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$editor1 = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// create the list for choosing what to edit
		$list = '<select onchange="window.location.href = this.value"><option>' . $this->lang[ 'Manage_toedit' ] . '</option>';
		$sql = "SELECT item_id, item_title, item_language FROM " . STORE_ITEM_TABLE . " ORDER BY item_title ASC, item_language ASC";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not read from database', CRITICAL_ERROR );
		}
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$uri = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_store&s=manage&id=' . $row[ 'item_id' ] );
			$list .= '<option value="' . $uri . '">' . $row[ 'item_language' ] . '::' . $row[ 'item_title' ] . '</option>';
		}
		$list .= '</select>';
		
		// construct the language selection list
		$langl = $lang_loader->get_langlist();
		$langs = '<select name="language">';
		for ( $i = 0; $i < count( $langl ); $i++ )
		{
			$langs .= ( $langl[ $i ] == $item[ 'item_language' ] ) ? '<option selected>' . $langl[ $i ] . '</option>' : '<option>' . $langl[ $i ] . '</option>';
		}
		$langs .= '</select>';
		
		// category selection
		$sql = "SELECT cat_name FROM " . STORE_CATEGORY_TABLE;
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not read from database', CRITICAL_ERROR );
		}
		$category_select = '<select onchange="document.getElementById( \'category\' ).value =document.getElementById( \'category\' ).value + this.value "><option></option>';
		while( $row = $db->sql_fetchrow( $result ) )
		{
			$category_select .= '<option value="' . $row[ 'cat_name' ] . ', ">' . $row[ 'cat_name' ] . '</option>';
		}
		$category_select .= '</select>';
		
		$template->assign_block_vars( 'manage', '', array(
			'L_TITLE' => $this->lang[ 'Manage_title' ],
			'L_EXPLAIN' => $this->lang[ 'Manage_explain' ],
			'L_DESCRIPTION' => $this->lang[ 'Manage_description' ],
			'L_TITLE2' => $this->lang[ 'Manage_title2' ],
			'L_LANGUAGE' => $this->lang[ 'Manage_language' ],
			'L_DELETE' => $this->lang[ 'Manage_delete' ],
			'L_CATEGORY' => $this->lang[ 'Manage_category' ],
			'L_TIME_FROM_STAMP' => $this->lang[ 'Manage_time_from_stamp' ],
			'L_PRICE' => $this->lang[ 'Manage_price' ],
			'L_THUMBNAIL' => $this->lang[ 'Manage_thumbnail' ],
			
			'S_EDITOR1' => $editor1[ 'editor_HTML' ],
			'S_LIST' => $list,
			'S_LANGS' => $langs,
			'S_TITLE' => $item[ 'item_title' ],
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_store&s=manage2&id=' . $item_id ),
			'S_DELETED' => ( $item[ 'item_isdeleted' ] ) ? 'checked' : '',
			'S_LOCATION' => $item[ 'item_location' ],
			'S_CATEGORY' => $categories,
			'S_CATEGORIES' => $category_select,
			'S_PRICE' => $item[ 'item_price' ],
			'S_THUMBNAIL' => $thumbnail,
		) );
		$template->assign_switch( 'manage', TRUE );
	}
	/**
	* the form was submited O.o
	*/
	function manage2()
	{
		global $errors, $db, $board_config, $Cl_root_path, $security;
		
		if ( !isset( $_POST[ 'submit_shows' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
		}
		
		$item_id = ( isset( $_GET[ 'id' ] ) ) ? intval( $_GET[ 'id' ] ) : 0;
		
		// prepare the data
		$fields = array( 'title', 'description', 'language', 'price' );
		$checks = array( 'isdeleted' );
		
		// construct the sql
		if ( $item_id == 0 )
		{
			$fieldnames = array();
			$fieldvalues = array();
			foreach ( $fields as $field )
			{
				$fieldnames[] = "item_$field";
				$fieldvalues[] = "'" . str_replace( '&nbsp;', ' ', $_POST[ $field ] ) . "'";
			}
			foreach ( $checks as $check )
			{
				$fieldnames[] = "item_$check";
				$val = ( isset( $_POST[ $check ] ) ) ? 1 : 0;
				$fieldvalues[]= "'$val'";
			}
			$fieldnames = implode( ',', $fieldnames );
			$fieldvalues = implode( ',', $fieldvalues );
			$sql = "INSERT INTO " . STORE_ITEM_TABLE . " ( $fieldnames )VALUES( $fieldvalues )";
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not write database', CRITICAL_ERROR );
			}
			$item_id = $db->sql_nextid();
			$newthumbnail = TRUE;
		}else
		{
			$values = array();
			foreach ( $fields as $field )
			{
				$values[] = "item_$field = '" . str_replace( '&nbsp;', ' ', $_POST[ $field ] ) . "'";
			}
			foreach ( $checks as $check )
			{
				$val = ( isset( $_POST[ $check ] ) ) ? 1 : 0;
				$values[]= "item_$check = '$val'";
			}
			$values = implode( ',', $values );
			$sql = "UPDATE " . STORE_ITEM_TABLE . " SET $values WHERE item_id='$item_id' LIMIT 1";
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not write database', CRITICAL_ERROR );
			}
			$newthumbnail = FALSE;
		}
		
		// do the category stuff
		if ( !empty( $_POST[ 'category' ] ) )
		{
			$categories = explode( ', ', $_POST[ 'category' ] );
			$cats = array();
			foreach ( $categories as $cat )
			{
				if ( $cat != '' )
				{
					$cats[] = "'$cat'";
				}
			}
			if ( count( $cats ) > 0 )
			{
				$cats = implode( ',', $cats );
				
				// some might already be set and we hate doubles so we get what's already there
				$sql = "SELECT cat_id FROM " . STORE_CAT2ITEM_TABLE . " WHERE item_id='$item_id'";
				if ( !$result = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
				}
				$alreadies = array();
				$keeps = array();
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$alreadies[ $row[ 'cat_id' ] ] = TRUE;
				}
				
				$insert = '';
				$sql = "SELECT cat_id FROM " . STORE_CATEGORY_TABLE . " WHERE cat_name IN ( $cats ) AND cat_language='" . $_POST[ 'language' ] . "'";
				if ( !$result = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
				}
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					if ( !isset( $alreadies[ $row[ 'cat_id' ] ] ) )
					{ // the connection isn't set yet
						$insert .= "( '" . $row[ 'cat_id' ] . "', '" . $item_id . "' ),";
					}else
					{ // some might end up missing from the list, we need to clean them
						$keeps[] = $row[ 'cat_id' ];
					}
				}
				$insert = substr( $insert, 0, -1 );
				if ( $insert != '' )
				{
					$sql = "INSERT INTO " . STORE_CAT2ITEM_TABLE . " ( cat_id, item_id )VALUES $insert";
					if ( !$db->sql_query( $sql ) )
					{
						$errors->report_error( 'Could not write database', CRITICAL_ERROR );
					}
				}
				// remove what was there before and isn't anymore
				$pairs = array();
				foreach ( $alreadies as $old_cat )
				{
					if ( !in_array( $old_cat, $keeps ) )
					{ // remove it
						$pairs[] = "( cat_id='$old_cat' AND item_id='$item_id' )";
					}
				}
				if ( count( $pairs ) > 0 )
				{
					$pairs = implode( 'OR', $pairs );
					$sql = "DELETE FROM " . STORE_CAT2ITEM_TABLE . " WHERE $pairs";
					$db->sql_query( $sql );
				}
			}
		}
		
		// do the thumbnail
		if ( $_FILES[ 'thumbnail' ][ 'error' ] != 4 )
		{
			if ( $_FILES[ 'thumbnail' ][ 'error' ] == 0 )
			{
				$dir = $Cl_root_path . '/' . $board_config[ 'store_imagepath' ];
				if ( !is_dir( $dir ) )
				{
					if ( !mkdir( $dir, 0700 ) )
					{
						$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
					}
				}
				if ( $newthumbnail )
				{ // create id for new thumbnail
					$sql = "INSERT INTO " . STORE_IMAGES_TABLE . " ( item_id )VALUES( '$item_id' )";
					if ( !$result = $db->sql_query( $sql ) )
					{
						$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
					}
					$thumb_id = $db->sql_nextid();
					$sql = "UPDATE " . STORE_ITEM_TABLE . " SET item_image='$thumb_id' WHERE item_id='$item_id' LIMIT 1";
					$db->sql_query( $sql );
				}else
				{ // remove old thumbnail
					$sql = "SELECT item_image FROM " . STORE_ITEM_TABLE . " WHERE item_id='$item_id' LIMIT 1";
					if ( !$result = $db->sql_query( $sql ) )
					{
						$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
					}
					$row = $db->sql_fetchrow( $result );
					//$thumb = $row[ 'image_thumbnail' ];
					$thumb_id = $row[ 'item_image' ];
					//@unlink( $dir . '/' . $thumb );
				}
				if ( !$image = $this->processimage( $_FILES[ 'thumbnail' ][ 'tmp_name' ], $thumb_id, $dir, $_FILES[ 'thumbnail' ][ 'type' ] ) )
				{
					$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
				}
				$thmb = $image[ 0 ];
				$mini = $image[ 1 ];
				$img = $image[ 2 ];
				$sql = "UPDATE " . STORE_IMAGES_TABLE . " SET image_thumbnail='$thmb', image_mini='$mini', image_image='$img' WHERE image_id='$thumb_id' LIMIT 1";
				if ( !$db->sql_query( $sql ) )
				{
					$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
				}
			}
		}
		
		// all went well
		$back = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_store&s=manage&id=' . $item_id );
		$errors->report_error( sprintf( $this->lang[ 'Manage_success' ], $back ), MESSAGE );
	}
	/**
	* resizes and saves an image and its thumbnail
	*/
	function processimage( $file, $image_id, $dir, $type )
	{
		global $board_config;
		
		$info = getimagesize( $file );
		
		switch( $type )
		{
			case 'image/gif':
				$ext = 'gif';
				break;
			case 'image/jpeg':
				$ext = 'jpeg';
				break;
			case 'image/png':
				$ext = 'png';
				break;
			default:
				return FALSE;
		}
		
		// abstract the extension dependant function
		$createFunc = "imagecreatefrom" . $ext;
		$outputFunc = "image" . $ext;
		
		// calculate width and height
		$Width = $info[ 0 ];
		$Height = $info[ 1 ];
		
		$im = $createFunc( $file );
		$fullratio = $Width / $Height;
		$thumbratio = $board_config[ 'store_thumb_width' ] / $board_config[ 'store_thumb_height' ];
		
		//make the thumbnail
		if ( $fullratio >= $thumbratio )
		{ // resize to height and then crop	
			$this->_resizeHeight( $im, $Width, $Height, $board_config[ 'store_thumb_width' ], $board_config[ 'store_thumb_height' ], $dir . '/' . $image_id . '_thumbnail.' . $ext, $outputFunc );
		}else
		{ // resize to width and then crop
			$this->_resizeWidth( $im, $Width, $Height, $board_config[ 'store_thumb_width' ], $board_config[ 'store_thumb_height' ], $dir . '/' . $image_id . '_thumbnail.' . $ext, $outputFunc );
		}
		
		// make the mini image
		// there must be no crop in this case
		$this->_resizeWidth( $im, $Width, $Height, $board_config[ 'store_mini_width' ], $board_config[ 'store_mini_width' ], $dir . '/' . $image_id . '_mini.' . $ext, $outputFunc, TRUE );
		
		imagedestroy( $im );
		
		copy( $file, $dir . '/' . $image_id . '.' . $ext );
		// filenames
		$thmb = $image_id . '_thumbnail.' . $ext;
		$mini = $image_id . '_mini.' . $ext;
		$img = $image_id . '.' . $ext;
		
		return array( $thmb, $mini, $img, $small );
	}
	/**
	* resizes and stores an image
	*/
	function _resizeHeight( &$source, $Width, $Height, $nWidth, $tHeight, $path, $outputFunc, $nocrop=FALSE )
	{
		$tWidth = ceil( $Width / ( $Height / $tHeight ) );
		$temp = imagecreatetruecolor( $tWidth, $tHeight );
		$thumb = imagecreatetruecolor( $nWidth, $tHeight  );
		imagecopyresampled( $temp, $source, 0, 0, 0, 0, $tWidth, $tHeight, $Width, $Height ); // resize
		if ( !$nocrop )
		{
			imagecopy( $thumb, $temp, 0, 0, ( $tWidth / 2 )-( $nWidth / 2 ), 0, $nWidth, $tHeight ); // crop
			$outputFunc( $thumb, $path );
		}else
		{
			$outputFunc( $temp, $path );
		}
		imagedestroy( $temp );
		imagedestroy( $thumb );
	}
	/**
	* resizes and stores an image
	*/
	function _resizeWidth( &$source, $Width, $Height, $tWidth, $nHeight, $path, $outputFunc, $nocrop=FALSE )
	{
		$tHeight = ceil( $Height / ( $Width / $tWidth ) );
		$temp = imagecreatetruecolor( $tWidth, $tHeight );
		$thumb = imagecreatetruecolor( $tWidth, $nHeight );
		imagecopyresampled( $temp, $source, 0, 0, 0, 0, $tWidth, $tHeight, $Width, $Height ); // resize
		if ( !$nocrop )
		{
			imagecopy( $thumb, $temp, 0, 0, 0, ( $tHeight / 2 )-( $nHeight / 2 ), $tWidth, $nHeight ); // crop
			$outputFunc( $thumb, $path );
		}else
		{
			$outputFunc( $temp, $path );
		}
		imagedestroy( $temp );
		imagedestroy( $thumb );
	}
	/**
	* deals with managing categories
	*/
	function categories()
	{
		global $db, $errors, $template, $security, $lang_loader, $cache;
		
		// prepare some variables
		$id = ( isset( $_GET[ 'catid' ] ) ) ? intval( $_GET[ 'catid' ] ) : 0;
		$id = ( isset( $_POST[ 'catid' ] ) ) ? intval( $_POST[ 'catid' ] ) : $id;
		$name = '';
		$lang = '';
		$report = '';
		
		if ( isset( $_POST[ 'submitcat' ] ) )
		{
			$nam = $_POST[ 'name' ];
			$lan = $_POST[ 'language' ];
			if ( $id == 0 )
			{ // add it
				$sql = "INSERT INTO " . STORE_CATEGORY_TABLE . " ( cat_name, cat_language ) VALUES ( '$nam', '$lan' )";
			}else
			{ // edit it
				if ( isset( $_POST[ 'delete' ] ) )
				{ // delete it
					$sql = "DELETE FROM " . STORE_CATEGORY_TABLE . " WHERE cat_id='$id' LIMIT 1";
				}else
				{
					$sql = "UPDATE " . STORE_CATEGORY_TABLE . " SET cat_name='$nam', cat_language='$lan' WHERE cat_id='$id' LIMIT 1";
				}
			}
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not insert', CRITICAL_ERROR );
			}
			// deletin is much simpler than trying to update
			$cache->delete( 'news_categories' );
			$cache->delete( 'news_categories_list' );
			$report = $this->lang[ 'Cat_done' ];
		}
		
		// fetch all the categories
		$sql = "SELECT * FROM " . STORE_CATEGORY_TABLE . " ORDER BY cat_id";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Cannot read database', CRITICAL_ERROR );
		}
		
		// make the selectoin list
		$select = '<select name="catid" onchange="window.location.href=this.value;"><option value="0"></option>';
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$uri = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_store&s=categories&catid=' . $row[ 'cat_id' ] );
			$select .= '<option value="' . $uri . '">' . $row[ 'cat_language' ] . ' :: ' . $row[ 'cat_name' ] .  '</option>';
			if ( $row[ 'cat_id' ] == $id )
			{ // this one was selected for editation
				$name = $row[ 'cat_name' ];
				$lang = $row[ 'cat_language' ];
			}
		}
		$select .= '</select>';
		
		// construct the language selection list
		$langl = $lang_loader->get_langlist();
		$langs = '<select name="language">';
		for ( $i = 0; $i < count( $langl ); $i++ )
		{
			$langs .= ( $langl[ $i ] == $lang ) ? '<option selected>' . $langl[ $i ] . '</option>' : '<option>' . $langl[ $i ] . '</option>';
		}
		$langs .= '</select>';
		
		$template->assign_block_vars( 'categories', '', array(
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_store&s=categories' ),
			'S_ID' =>$id,
			'S_NAME' => $name,
			'S_NAMES' => $select,
			'S_LANGS' => $langs,
			
			'L_NAME' => $this->lang[ 'Cat_name' ],
			'L_LANG' => $this->lang[ 'Cat_lang' ],
			'L_TITLE' => $this->lang[ 'Cat_title'  ],
			'L_EXPLAIN' => $this->lang[ 'Cat_explain' ],
			'L_REPORT' => $report,
			'L_DELETE' => $this->lang[ 'Cat_delete' ],
			'L_CHOOSE' => $this->lang[ 'Cat_choose' ],
		) );
		$template->assign_switch( 'categories', TRUE );
	}
	
	//
	// End of ACP_store class
	//
}

?>