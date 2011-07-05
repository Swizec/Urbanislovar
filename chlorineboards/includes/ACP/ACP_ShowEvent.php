<?php

/**
*     defines the ACP_ShowEvent class
*     @file                ACP_ShowEvent.php
*     @see ACP_ShowEvent
*/
/**
* ACP panel for administration of the ShowEvent shows and events
*     @class		   ACP_ShowEvent
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.2.2
*     @since        10th April 2007
*     @package		     ShowEvent
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
eval( Varloader::createclass( 'ACP_ShowEvent', $vars, $visible ) );
// end class creation

class ACP_ShowEvent extends ACP_ShowEvent_def
{
	/**
	* constructor
	*/
	function ACP_ShowEvent( $debug = FALSE )
	{
		global $Cl_root_path, $basic_gui, $lang_loader, $security;
		
		$this->debug = $debug;
	
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_ShowEvent' );
		
		// make the two urls
		$url1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_ShowEvent&s=manage' );
		$url2 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_ShowEvent&s=categories' );
		$url3 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_ShowEvent&s=invites' );

		// add to page
		$basic_gui->add2sidebar( 'right', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $url1 . '">' . $this->lang[ 'Side_manage' ] . '</a><br /><a href="' . $url2 . '">' . $this->lang[ 'Side_categories' ] . '</a><br /><a href="' . $url3 . '">' . $this->lang[ 'Side_mails' ] . '</a></span>' );
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $template, $errors, $Cl_root_path;
		
		$template->assign_files( array(
			'ACP_ShowEvent' => 'ACP/ShowEvent' . tplEx
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
			case 'invites':
				$this->invites();
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
			$sql = "SELECT *, im.image_thumbnail FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . SHOWEVENT_IMAGES_TABLE . " im ON i.item_thumbnail=im.image_id WHERE i.item_id='$item_id' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not read from database', CRITICAL_ERROR );
			}
			$item = $db->sql_fetchrow( $result );
			// get the categories list ...
			$sql = "SELECT c.cat_name FROM " . SHOWEVENT_CATEGORY_TABLE . " c, " . SHOWEVENT_CAT2ITEM_TABLE . " c2i, " . SHOWEVENT_ITEM_TABLE . " i WHERE c.cat_id=c2i.cat_id AND c2i.item_id=i.item_id AND i.item_id='$item_id' ";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not read from database', CRITICAL_ERROR );
			}
			$categories = '';
			while( $row = $db->sql_fetchrow( $result ) )
			{
				$categories .= $row[ 'cat_name' ] . ', ';
			}
			
			$thumbnail = '<img src="' . $Cl_root_path4template . '/' . $board_config[ 'showevent_imagepath' ] . '/' . $item[ 'image_thumbnail' ] . '" />';
		}else
		{ // set default values
			$item = array(
				'item_title' => '',
				'item_subtitle' => '',
				'item_excerpt' => '',
				'item_description' => '',
				'item_language' => '',
				'item_thumbnail' => '',
				'item_location' => '',
				'item_time_from' => '',
				'item_time_to' => '',
				'item_time_duration' => '',
				'item_recurrence' => '',
				'item_price' => '',
				'item_isdeleted' => 0,
				'item_isevent' => 0,
				'item_time_to_stamp' => EXECUTION_TIME,
				'item_time_from_stamp' => EXECUTION_TIME,
				'item_schedule' => '',
				'item_additional' => '',
			);
			$categories = '';
			$thumbnail = '';
		}
		
		// get the editor, twice
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'excerpt', 'quickpost' => FALSE, 'def_text' => stripslashes( $item[ 'item_excerpt' ] ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$editor1 = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		$mod_loader->port_vars( array( 'name' => 'description', 'quickpost' => FALSE, 'def_text' => stripslashes( $item[ 'item_description' ] ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$editor2 = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// create the list for choosing what to edit
		$list = '<select onchange="window.location.href = this.value"><option>' . $this->lang[ 'Manage_toedit' ] . '</option>';
		$sql = "SELECT item_id, item_title, item_language, item_isevent FROM " . SHOWEVENT_ITEM_TABLE . " ORDER BY item_title ASC, item_language ASC, item_isevent ASC";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not read from database', CRITICAL_ERROR );
		}
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( !$row[ 'item_isevent' ])
			{
				$tag = '<b>';
				$closetag = '</b>';
				$what = $this->lang[ 'Manage_show' ];
			}else
			{
				$tag = '';
				$closetag = '';
				$what = $this->lang[ 'Manage_event' ];
			}
			if ( $row[ 'item_isdeleted' ] )
			{
				$tag = '<i>';
				$closetag = '</i>';
			}

			$uri = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_ShowEvent&s=manage&id=' . $row[ 'item_id' ] );
			$list .= '<option value="' . $uri . '">' . $tag . $what . '::' . $row[ 'item_language' ] . '::' . $row[ 'item_title' ] . $closetag . '</option>';
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
		
		// time selections
		$time_to_stamp = $this->timeselect( $item[ 'item_time_to_stamp' ], 'time_to_stamp' );
		$time_from_stamp = $this->timeselect( $item[ 'item_time_from_stamp' ], 'time_from_stamp' );
			
		// category selection
		$sql = "SELECT cat_name FROM " . SHOWEVENT_CATEGORY_TABLE;
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
		
		// gallery
		$sql = "SELECT * FROM " . SHOWEVENT_IMAGES_TABLE . " WHERE item_id='$item_id' ORDER BY image_id ASC";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not read from database', CRITICAL_ERROR );
		}
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$template->assign_block_vars( 'imagerow', '', array(
				'ID' => $row[ 'image_id' ],
				'U_IMAGE' => $basic_gui->get_URL() . '/' . $board_config[ 'showevent_imagepath' ] . '/' . $row[ 'image_thumbnail' ],
				'U_IMAGE2' => $basic_gui->get_URL() . '/' . $board_config[ 'showevent_imagepath' ] . '/' . $row[ 'image_image' ],
				'WIDTH' => $board_config[ 'showevent_thumb_width' ],
				'ADD' => 0
			) );
			$template->assign_switch( 'imagerow', TRUE );
		}
		// for adding
		$template->assign_block_vars( 'imagerow', '', array(
			'ID' => 'new',
			'WIDTH' => $board_config[ 'showevent_thumb_width' ],
			'HEIGHT' => $board_config[ 'showevent_thumb_height' ],
			'ADD' => 1
		) );
		$template->assign_switch( 'imagerow', TRUE );
		
		$template->assign_block_vars( 'manage', '', array(
			'L_TITLE' => $this->lang[ 'Manage_title' ],
			'L_EXPLAIN' => $this->lang[ 'Manage_explain' ],
			'L_DESCRIPTION' => $this->lang[ 'Manage_description' ],
			'L_EXCERPT' => $this->lang[ 'Manage_excerpt' ],
			'L_TITLE2' => $this->lang[ 'Manage_title2' ],
			'L_SUBTITLE' => $this->lang[ 'Manage_subtitle' ],
			'L_LANGUAGE' => $this->lang[ 'Manage_language' ],
			'L_ISEVENT' => $this->lang[ 'Manage_isevent' ],
			'L_DELETE' => $this->lang[ 'Manage_delete' ],
			'L_LOCATION' => $this->lang[ 'Manage_location' ],
			'L_CATEGORY' => $this->lang[ 'Manage_category' ],
			'L_TIME_TO' => $this->lang[ 'Manage_time_to' ],
			'L_TIME_FROM' => $this->lang[ 'Manage_time_from' ],
			'L_TIME_DURATION' => $this->lang[ 'Manage_time_duration' ],
			'L_TIME_TO_STAMP' => $this->lang[ 'Manage_time_to_stamp' ],
			'L_TIME_FROM_STAMP' => $this->lang[ 'Manage_time_from_stamp' ],
			'L_PRICE' => $this->lang[ 'Manage_price' ],
			'L_THUMBNAIL' => $this->lang[ 'Manage_thumbnail' ],
			'L_UPLOAD' => $this->lang[ 'Manage_upload' ],
			'L_GALLERY' => $this->lang[ 'Manage_gallery' ],
			'L_DELETE2' => $this->lang[ 'Manage_delete2' ],
			'L_ADDIMG' => $this->lang[ 'Manage_addimg' ],
			'L_SCHEDULE' => $this->lang[ 'Manage_schedule' ],
			'L_ADDITIONAL' => $this->lang[ 'Manage_additional' ],
			
			'S_EDITOR1' => $editor1[ 'editor_HTML' ],
			'S_EDITOR2' => $editor2[ 'editor_HTML' ],
			'S_LIST' => $list,
			'S_LANGS' => $langs,
			'S_TITLE' => htmlspecialchars( $item[ 'item_title' ] ),
			'S_SUBTITLE' => $item[ 'item_subtitle' ],
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_ShowEvent&s=manage2&id=' . $item_id ),
			'S_ISEVENT' => ( $item[ 'item_isevent' ] ) ? 'checked' : '',
			'S_DELETED' => ( $item[ 'item_isdeleted' ] ) ? 'checked' : '',
			'S_LOCATION' => $item[ 'item_location' ],
			'S_CATEGORY' => $categories,
			'S_CATEGORIES' => $category_select,
			'S_TIME_TO' => $item[ 'item_time_to' ],
			'S_TIME_FROM' => $item[ 'item_time_from' ],
			'S_TIME_DURATION' => $item[ 'item_time_duration' ],
			'S_TIME_TO_STAMP' => $time_to_stamp,
			'S_TIME_FROM_STAMP' => $time_from_stamp,
			'S_PRICE' => $item[ 'item_price' ],
			'S_THUMBNAIL' => $thumbnail,
			'S_SCHEDULE' => $item[ 'item_schedule' ],
			'S_ADDITIONAL' => $item[ 'item_additional' ],
		) );
		$template->assign_switch( 'manage', TRUE );
		
		// also need the JS
		$basic_gui->add_JS( 'includes/ShowEvent.js' );
	}
	/**
	* creates the simplified selections for time
	*/
	function timeselect( $time, $prefix )
	{
		$day = date( 'j', $time );
		$month = date( 'n', $time );
		$year = date( 'Y', $time );
		
		$which = ( strpos( $prefix, 'to' ) === FALSE ) ? 1 : 0;
		//echo "$prefix::$which::" . strpos( $prefix, 'to' ) . "<br />";
		//echo intval( strpos( $prefix, 'to' ) === FALSE );
		
		$days = '<select name="' . $prefix . '_day" id="' . $prefix . '_day"  onchange="timeSelChange( ' . $which . ' )">';
		for ( $i = 1; $i <= 31; $i++ )
		{
			$sel = ( $day == $i ) ? 'selected' : '';
			$days .= '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
		}
		$days .= '</select>';
		
		$months = '<select name="' . $prefix . '_month" id="' . $prefix . '_month" onchange="timeSelChange( ' . $which . ' )">';
		for ( $i = 0; $i < 12; $i++ )
		{
			$sel = ( $month-1 == $i ) ? 'selected' : '';
			$months .= '<option value="' . ($i+1) . '" ' . $sel . '>' . $this->lang[ 'Month_' . $i ] . '</option>';
		}
		$months .= '</select>';
		
		return $days . ' ' . $months . ' <input type="text" name="' . $prefix . '_year" id="' . $prefix . '_year" onchange="timeSelChange( ' . $which . ' )" value="' . $year . '" cols="5" />';
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
		$fields = array( 'title', 'subtitle', 'excerpt', 'description', 'time_from', 'time_to', 'time_duration', 'location', 'language', 'price', 'schedule', 'additional' );
		$checks = array( 'isdeleted', 'isevent' );
		$stamp_to = '23:59:59 ' . intval( $_POST[ 'time_to_stamp_day' ] ) . '.' . intval( $_POST[ 'time_to_stamp_month' ] ) . '.' . intval( $_POST[ 'time_to_stamp_year' ] );
		$stamp_to = strtotime( $stamp_to );
		$stamp_from = '00:00:00 ' . intval( $_POST[ 'time_from_stamp_day' ] ) . '.' . intval( $_POST[ 'time_from_stamp_month' ] ) . '.' . intval( $_POST[ 'time_from_stamp_year' ] );
		$stamp_from = strtotime( $stamp_from );
		//die( 'what' );
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
			$sql = "INSERT INTO " . SHOWEVENT_ITEM_TABLE . " ( $fieldnames, item_time_to_stamp, item_time_from_stamp )VALUES( $fieldvalues, '$stamp_to', '$stamp_from' )";
			//die( $sql );
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
			$sql = "UPDATE " . SHOWEVENT_ITEM_TABLE . " SET $values, item_time_to_stamp='$stamp_to', item_time_from_stamp='$stamp_from' WHERE item_id='$item_id' LIMIT 1";
			//die( $sql );
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not write database', CRITICAL_ERROR );
			}
			// test to see if the thumbnail was actually set
			$newthumbnail = FALSE;
		}
		//die( $item_id );
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
				$sql = "SELECT cat_id FROM " . SHOWEVENT_CAT2ITEM_TABLE . " WHERE item_id='$item_id'";
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
				$sql = "SELECT cat_id FROM " . SHOWEVENT_CATEGORY_TABLE . " WHERE cat_name IN ( $cats ) AND cat_language='" . $_POST[ 'language' ] . "'";
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
					$sql = "INSERT INTO " . SHOWEVENT_CAT2ITEM_TABLE . " ( cat_id, item_id )VALUES $insert";
					if ( !$db->sql_query( $sql ) )
					{
						$errors->report_error( 'Could not write database', CRITICAL_ERROR );
					}
				}
				// remove what was there before and isn't anymore
				$pairs = array();
				foreach ( $alreadies as $old_cat => $void )
				{
					if ( !in_array( $old_cat, $keeps ) )
					{ // remove it
						$pairs[] = "( cat_id='$old_cat' AND item_id='$item_id' )";
					}
				}
				if ( count( $pairs ) > 0 )
				{
					$pairs = implode( 'OR', $pairs );
					$sql = "DELETE FROM " . SHOWEVENT_CAT2ITEM_TABLE . " WHERE $pairs";
					$db->sql_query( $sql );
				}
			}
		}
		
		// do the thumbnail
		if ( $newthumbnail )
		{ // this must be done if the thumbnail wasn't quite uploaded
			$sql = "INSERT INTO " . SHOWEVENT_IMAGES_TABLE . " ( item_id )VALUES( '$item_id' )";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
			}
			$thumb_id = $db->sql_nextid();
			$sql = "UPDATE " . SHOWEVENT_ITEM_TABLE . " SET item_thumbnail='$thumb_id' WHERE item_id='$item_id' LIMIT 1";
			$db->sql_query( $sql );
		}
		if ( $_FILES[ 'thumbnail' ][ 'error' ] != 4 )
		{
			if ( $_FILES[ 'thumbnail' ][ 'error' ] == 0 )
			{
				$dir = $Cl_root_path . '/' . $board_config[ 'showevent_imagepath' ];
				if ( !is_dir( $dir ) )
				{
					if ( !mkdir( $dir, 0700 ) )
					{
						$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
					}
				}
				if ( !$newthumbnail )
				{ // remove old thumbnail
					//$sql = "SELECT im.image_thumbnail, im.image_id FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . SHOWEVENT_IMAGES_TABLE . " im ON i.item_thumbnail=im.image_id WHERE i.item_id='$item_id' LIMIT 1";
					$sql = "SELECT item_thumbnail FROM " . SHOWEVENT_ITEM_TABLE . " WHERE item_id='$item_id' LIMIT 1";
					if ( !$result = $db->sql_query( $sql ) )
					{
						$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
					}
					$row = $db->sql_fetchrow( $result );
					//$thumb = $row[ 'image_thumbnail' ];
					$thumb_id = $row[ 'item_thumbnail' ];
					//@unlink( $dir . '/' . $thumb );
				}
				if ( !$image = $this->processimage( $_FILES[ 'thumbnail' ][ 'tmp_name' ], $thumb_id, $dir, $_FILES[ 'thumbnail' ][ 'type' ] ) )
				{
					$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
				}
				$thmb = $image[ 0 ];
				$mini = $image[ 1 ];
				$img = $image[ 2 ];
				$small = $image[ 3 ];
				$sql = "UPDATE " . SHOWEVENT_IMAGES_TABLE . " SET image_thumbnail='$thmb', image_mini='$mini', image_image='$img', image_smallthumb='$small' WHERE image_id='$thumb_id' LIMIT 1";
				if ( !$db->sql_query( $sql ) )
				{
					$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
				}
			}
		}
		
		// now do the gallery
		$dir = $Cl_root_path . '/' . $board_config[ 'showevent_imagepath' ];
		$nowrite = FALSE;
		if ( !is_dir( $dir ) )
		{
			if ( !mkdir( $dir, 0700 ) )
			{
				$nowrite = TRUE;
			}
		}
		if ( !$nowrite )
		{
			if ( is_array( $_POST[ 'imagedelete' ] ) )
			{ // remove what wants removal
				$keys = array_keys( $_POST[ 'imagedelete' ] );
				foreach ( $keys as $key )
				{
					if ( $key == $thumb_id )
					{ // thumbnail must not be removed
						continue;
					}
					$sql = "DELETE FROM " . SHOWEVENT_IMAGES_TABLE . " WHERE image_id='$key' LIMIT 1";
					if ( $db->sql_query( $sql ) )
					{
						foreach ( glob( $dir . '/' . $key . '*' ) as $file )
						{
							@unlink( $file );
						}
					}
				}
			}
			foreach ( $_FILES[ 'imageupload' ][ 'error' ] as $key => $error )
			{
				if ( $error != 0 || $error == 4 )
				{ // either an error or no upload, disregard
					continue;
				}
				if ( $key == 'new' )
				{ // the blanky at the end
					$sql = "INSERT INTO " . SHOWEVENT_IMAGES_TABLE . " ( item_id )VALUES( '$item_id' )";
					if ( !$result = $db->sql_query( $sql ) )
					{
						continue;
					}
					$id = $db->sql_nextid();
				}else
				{
					$id = $key;
				}
				if ( !$image = $this->processimage( $_FILES[ 'imageupload' ][ 'tmp_name' ][ $key ], $id, $dir, $_FILES[ 'imageupload' ][ 'type' ][ $key ] ) )
				{ // something went wrong so disregard this
					continue;
				}
				$thmb = $image[ 0 ];
				$mini = $image[ 1 ];
				$img = $image[ 2 ];
				$small = $image[ 3 ];
				$sql = "UPDATE " . SHOWEVENT_IMAGES_TABLE . " SET image_thumbnail='$thmb', image_mini='$mini', image_image='$img', image_smallthumb='$small' WHERE image_id='$id' LIMIT 1";
				$db->sql_query( $sql );
			}
		}
		
		// all went well
		$back = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_ShowEvent&s=manage&id=' . $item_id );
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
		
		// an error was made here before where the decision on how to work was made upon
		// width and height where it should be made upon the ratio ... heh
		$im = $createFunc( $file );
		$fullratio = $Width / $Height;
		$thumbratio = $board_config[ 'showevent_thumb_width' ] / $board_config[ 'showevent_thumb_height' ];
		$smallthumbratio = $board_config[ 'showevent_smallthumb_width' ] / $board_config[ 'showevent_smallthumb_height' ];
		$miniratio = $board_config[ 'showevent_mini_width' ] / $board_config[ 'showevent_mini_height' ];
		
		//make the thumbnail
		if ( $fullratio >= $thumbratio )
		{ // resize to height and then crop	
			$this->_resizeHeight( $im, $Width, $Height, $board_config[ 'showevent_thumb_width' ], $board_config[ 'showevent_thumb_height' ], $dir . '/' . $image_id . '_thumbnail.' . $ext, $outputFunc );
		}else
		{ // resize to width and then crop
			$this->_resizeWidth( $im, $Width, $Height, $board_config[ 'showevent_thumb_width' ], $board_config[ 'showevent_thumb_height' ], $dir . '/' . $image_id . '_thumbnail.' . $ext, $outputFunc );
		}
		
		// make the small thumbnail
		if ( $fullratio >= $smallthumbratio )
		{ // resize to height and then crop
			$this->_resizeHeight( $im, $Width, $Height, $board_config[ 'showevent_smallthumb_width' ], $board_config[ 'showevent_smallthumb_height' ], $dir . '/' . $image_id . '_smallthumb.' . $ext, $outputFunc );
		}else
		{ // resize to width and then crop
			$this->_resizeWidth( $im, $Width, $Height, $board_config[ 'showevent_smallthumb_width' ], $board_config[ 'showevent_smallthumb_height' ], $dir . '/' . $image_id . '_smallthumb.' . $ext, $outputFunc );
		}
		
		// make the mini image
		if ( $fullratio >= $miniratio )
		{ // resize to height and then crop
			$this->_resizeHeight( $im, $Width, $Height, $board_config[ 'showevent_mini_width' ], $board_config[ 'showevent_mini_height' ], $dir . '/' . $image_id . '_mini.' . $ext, $outputFunc );
		}else
		{ // resize to width and then crop
			$this->_resizeWidth( $im, $Width, $Height, $board_config[ 'showevent_mini_width' ], $board_config[ 'showevent_mini_height' ], $dir . '/' . $image_id . '_mini.' . $ext, $outputFunc );
		}
		
		imagedestroy( $im );
		
		copy( $file, $dir . '/' . $image_id . '.' . $ext );
		// filenames
		$small = $image_id . '_smallthumb.' . $ext;
		$thmb = $image_id . '_thumbnail.' . $ext;
		$mini = $image_id . '_mini.' . $ext;
		$img = $image_id . '.' . $ext;
		
		
		return array( $thmb, $mini, $img, $small );
	}
	/**
	* resizes and stores an image
	*/
	function _resizeHeight( &$source, $Width, $Height, $nWidth, $tHeight, $path, $outputFunc )
	{
		$tWidth = ceil( $Width / ( $Height / $tHeight ) );
		$temp = imagecreatetruecolor( $tWidth, $tHeight );
		$thumb = imagecreatetruecolor( $nWidth, $tHeight  );
		imagecopyresampled( $temp, $source, 0, 0, 0, 0, $tWidth, $tHeight, $Width, $Height ); // resize
		imagecopy( $thumb, $temp, 0, 0, ( $tWidth / 2 )-( $nWidth / 2 ), 0, $nWidth, $tHeight ); // crop
		$outputFunc( $thumb, $path );
		imagedestroy( $temp );
		imagedestroy( $thumb );
	}
	/**
	* resizes and stores an image
	*/
	function _resizeWidth( &$source, $Width, $Height, $tWidth, $nHeight, $path, $outputFunc )
	{
		$tHeight = ceil( $Height / ( $Width / $tWidth ) );
		$temp = imagecreatetruecolor( $tWidth, $tHeight );
		$thumb = imagecreatetruecolor( $tWidth, $nHeight );
		imagecopyresampled( $temp, $source, 0, 0, 0, 0, $tWidth, $tHeight, $Width, $Height ); // resize
		imagecopy( $thumb, $temp, 0, 0, 0, ( $tHeight / 2 )-( $nHeight / 2 ), $tWidth, $nHeight ); // crop
		$outputFunc( $thumb, $path );
		imagedestroy( $temp );
		imagedestroy( $thumb );
	}
	/**
	* deals with managing categories
	*/
	function categories()
	{
		global $db, $errors, $template, $security, $lang_loader, $cache, $mod_loader, $board_config, $Cl_root_path, $Cl_root_path4template;
		
		// prepare some variables
		$id = ( isset( $_GET[ 'catid' ] ) ) ? intval( $_GET[ 'catid' ] ) : 0;
		$id = ( isset( $_POST[ 'catid' ] ) ) ? intval( $_POST[ 'catid' ] ) : $id;
		$name = '';
		$lang = '';
		$description = '';
		$report = '';
		$parent = 0;
		$thumb = '';
		
		if ( isset( $_POST[ 'submitcat' ] ) )
		{
			$nam = $_POST[ 'name' ];
			$lan = $_POST[ 'language' ];
			$desc = $_POST[ 'description' ];
			$par = $_POST[ 'parent' ];
			if ( $id == 0 )
			{ // add it
				$sql = "INSERT INTO " . SHOWEVENT_CATEGORY_TABLE . " ( cat_name, cat_language, cat_description, cat_parent ) VALUES ( '$nam', '$lan', '$desc', '$par' )";
				$next = TRUE;
			}else
			{ // edit it
				if ( isset( $_POST[ 'delete' ] ) )
				{ // delete it
					$sql = "DELETE FROM " . SHOWEVENT_CATEGORY_TABLE . " WHERE cat_id='$id' LIMIT 1";
					$next = FALSE;
				}else
				{
					$sql = "UPDATE " . SHOWEVENT_CATEGORY_TABLE . " SET cat_name='$nam', cat_language='$lan', cat_description='$desc', cat_parent='$par' WHERE cat_id='$id' LIMIT 1";
					$next = FALSE;
				}
			}
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not insert', CRITICAL_ERROR );
			}
			if ( $next )
			{
				$id = $db->sql_nextid();
			}
			
			if ( $_FILES[ 'image' ][ 'error' ] != 4 )
			{
				if ( $_FILES[ 'image' ][ 'error' ] == 0 )
				{
					$dir = $Cl_root_path . '/' . $board_config[ 'showevent_imagepath' ];
					if ( !is_dir( $dir ) )
					{
						if ( !mkdir( $dir, 0700 ) )
						{
							$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
						}
					}
					if ( !$image = $this->processimage( $_FILES[ 'image' ][ 'tmp_name' ], 'cat_' . $id, $dir, $_FILES[ 'image' ][ 'type' ] ) )
					{
						$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
					}
					$thmb = $image[ 0 ];
					$mini = $image[ 1 ];
					$img = $image[ 2 ];
					$small = $image[ 3 ];
					$sql = "UPDATE " . SHOWEVENT_CATEGORY_TABLE . " SET cat_image='$mini' WHERE cat_id='$id' LIMIT 1";
					if ( !$db->sql_query( $sql ) )
					{
						$errors->report_error( 'Error while inserting', CRITICAL_ERROR );
					}
				}
			}
			
			$report = $this->lang[ 'Cat_done' ];
		}
		
		// fetch all the categories
		$sql = "SELECT * FROM " . SHOWEVENT_CATEGORY_TABLE . " ORDER BY cat_id";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Cannot read database', CRITICAL_ERROR );
		}
		
		// make the selection list
		$select = '<select name="catid" onchange="window.location.href=this.value;"><option value="0"></option>';
		$Pselect = '<select name="parent"><option value="0"></option>';
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$uri = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_ShowEvent&s=categories&catid=' . $row[ 'cat_id' ] );
			$select .= '<option value="' . $uri . '">' . $row[ 'cat_language' ] . ' :: ' . $row[ 'cat_name' ] .  '</option>';
			$Pselect .= '<option value="' . $row[ 'cat_id' ] . '">' . $row[ 'cat_language' ] . ' :: ' . $row[ 'cat_name' ]. '</option>';
			if ( $row[ 'cat_id' ] == $id )
			{ // this one was selected for editation
				$name = $row[ 'cat_name' ];
				$lang = $row[ 'cat_language' ];
				$description = $row[ 'cat_description' ];
				$parent = $row[ 'cat_parent' ];
				$thumb = '<img src="' . $Cl_root_path4template . '/' . $board_config[ 'showevent_imagepath' ] . '/' . $row[ 'cat_image' ] . '" />';
			}
		}
		$select .= '</select>';
		$Pselect .= '</select>';
		
		$Pselect = str_replace( '<option value="' . $parent . '">', '<option value="' . $parent . '" selected>', $Pselect );
		
		// get editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'description', 'quickpost' => FALSE, 'def_text' => stripslashes( $description ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$editor = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// construct the language selection list
		$langl = $lang_loader->get_langlist();
		$langs = '<select name="language">';
		for ( $i = 0; $i < count( $langl ); $i++ )
		{
			$langs .= ( $langl[ $i ] == $lang ) ? '<option selected>' . $langl[ $i ] . '</option>' : '<option>' . $langl[ $i ] . '</option>';
		}
		$langs .= '</select>';
		
		$template->assign_block_vars( 'categories', '', array(
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_ShowEvent&s=categories' ),
			'S_ID' =>$id,
			'S_NAME' => $name,
			'S_NAMES' => $select,
			'S_LANGS' => $langs,
			'S_DESCRIPTION' => $editor[ 'editor_HTML' ],
			'S_PARENT' => $Pselect,
			'S_IMAGE' => $thumb,
			
			'L_NAME' => $this->lang[ 'Cat_name' ],
			'L_LANG' => $this->lang[ 'Cat_lang' ],
			'L_TITLE' => $this->lang[ 'Cat_title'  ],
			'L_EXPLAIN' => $this->lang[ 'Cat_explain' ],
			'L_REPORT' => $report,
			'L_DELETE' => $this->lang[ 'Cat_delete' ],
			'L_CHOOSE' => $this->lang[ 'Cat_choose' ],
			'L_DESCRIPTION' => $this->lang[ 'Cat_description' ],
			'L_PARENT' => $this->lang[ 'Cat_parent' ],
			'L_IMAGE' => $this->lang[ 'Cat_image' ],
		) );
		$template->assign_switch( 'categories', TRUE );
	}
	/**
	* deals with displaying what to send out and such
	*/
	function invites()
	{
		global $template, $db, $errors, $lang_loader, $Cl_root_path, $basic_gui, $Sajax;
		
		// first time should get a clean timestamp of tomorrow without hours and such
		$time1 = strtotime( date( 'Y-m-d', EXECUTION_TIME+60*60*24*1 ) );
		// second time should get just before midnight of the day after tomorrow (or by the new specification a whole 7 days)
		$time2 = $time1 + ( 60*60*24*7-1 );
		
		$sql = "SELECT m.*, i.item_title, i.item_time_from_stamp FROM " . SHOWEVENT_MAIL_TABLE . " m, " . SHOWEVENT_ITEM_TABLE . " i WHERE ( m.mail_item=i.item_id OR m.mail_all=1 ) AND i.item_time_from_stamp >= $time1 AND i.item_time_from_stamp <= $time2 ORDER BY m.mail_name ASC";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Failed reading from database', CRITICAL_ERROR );
		}
		
		if ( $db->sql_numrows() == 0 )
		{
			$none = TRUE;
		}else
		{
			$none = FALSE;		
			$todelete = array();
			$langs = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				if ( !$this->validateEmail( $row[ 'mail_mail' ], TRUE, FALSE ) )
				{ // not even a valid email so we discard it
					$todelete[] = $row[ 'mail_id' ];
					continue;
				}
				
				if ( !isset( $langs[ $row[ 'mail_lang' ] ] ) )
				{
					$langs[ $row[ 'mail_lang' ] ] = TRUE;
				}
				
				$template->assign_block_vars( 'mailrow', '', array(
					'ID' => $row[ 'mail_id' ],
					'NAME' => $row[ 'mail_name' ],
					'MAIL' => $row[ 'mail_mail' ],
					'ITEM' => $row[ 'item_title' ],
					'LANG' => $row[ 'mail_lang' ],
					'TIME' => $row[ 'item_time_from_stamp' ],
					'ALL' => $row[ 'mail_all' ],
				) );
				$template->assign_switch( 'mailrow', TRUE );
			}
			
			if ( !empty( $todelete ) )
			{ // some emails need discarding
				$sql = "DELETE FROM " . SHOWEVENT_MAIL_TABLE . " WHERE mail_id IN ( " . implode( ', ', $todelete ) . " )";
				$db->sql_query( $sql );
			}
			
			// fetch the messages
			foreach ( $langs as $lan => $void )
			{
				if ( $lan == $lang_loader->board_lang )
				{
					$msg = $this->lang[ 'Invites_message' ];
					$subject = $this->lang[ 'Invites_subject' ];
				}else
				{ // need to load the entries first
					include( $Cl_root_path . 'language/' . $lan . '/lang_ACP_ShowEvent' . phpEx );
					$msg = $lang[ 'Invites_message' ];
					$subject = $lang[ 'Invites_subject' ];
				}
				$template->assign_block_vars( 'messages', '', array(
					'LANGUAGE' => $lan,
					'MESSAGE' => $msg,
					'SUBJECT' => $subject,
				) );
				$template->assign_switch( 'messages', TRUE );
			}
		}
		
		$template->assign_block_vars( 'invites', '', array(
			'L_TITLE' => $this->lang[ 'Invites_title' ],
			'L_EXPLAIN' => $this->lang[ 'Invites_explain' ],
			'L_NAME' => $this->lang[ 'Invites_name' ],
			'L_MAIL' => $this->lang[ 'Invites_mail' ],
			'L_ITEM' => $this->lang[ 'Invites_item' ],
			'L_LANGUAGE' => $this->lang[ 'Invites_language' ],
			'L_SEND' => $this->lang[ 'Invites_send' ],
			'L_MESSAGES' => $this->lang[ 'Invites_messages' ],
			'L_SENDING' => $this->lang[ 'Invites_sending' ],
			'L_NONE' => $this->lang[ 'Invites_none' ],
			
			'NONE' => ( $none ) ? 1 : 0,
		) );
		$template->assign_switch( 'invites', TRUE );
		
		// also need the JS
		$basic_gui->add_JS( 'includes/ShowEvent.js' );
		
		// need this one too
		$Sajax->add2export( 'ACP->classes[ "ACP_ShowEvent" ]->sendmail', '$name, $mail, $item, $lang, $time, $subject, $msg, $all, $success, $fail, $id, $id2, $count' );
	}
	/**
	* confirms the email address is valid
	* @author John Coggeshall
	* http://www.tienhuis.nl/php-email-address-validation-with-verify-probe
	*/
	function validateEmail($email, $domainCheck = false, $verify = false, $return_errors=false) {
	    $debug = FALSE; // changed by swizec
	    if($debug) {echo "<pre>";}
	    # Check syntax with regex
	    if (preg_match('/^([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))$/', $email, $matches)) {
	        $user = $matches[1];
	        $domain = $matches[2];
	        # Check availability of DNS MX records
	        if ($domainCheck && function_exists('checkdnsrr')) {
	            # Construct array of available mailservers
	            if(getmxrr($domain, $mxhosts, $mxweight)) {
	                for($i=0;$i<count($mxhosts);$i++){
	                    $mxs[$mxhosts[$i]] = $mxweight[$i];
	                }
	                asort($mxs);
	                $mailers = array_keys($mxs);
	            } elseif(checkdnsrr($domain, 'A')) {
	                $mailers[0] = gethostbyname($domain);
	            } else {
	                $mailers=array();
	            }
	            $total = count($mailers);
	            # Query each mailserver
	            if($total > 0 && $verify) {
	                # Check if mailers accept mail
	                for($n=0; $n < $total; $n++) {
	                    # Check if socket can be opened
	                    if($debug) { echo "Checking server $mailers[$n]...\n";}
	                    $connect_timeout = 2;
	                    $errno = 0;
	                    $errstr = 0;
	                    $probe_address = 'postmaster@tienhuis.nl';
	                    # Try to open up socket
	                    if($sock = @fsockopen($mailers[$n], 25, $errno , $errstr, $connect_timeout)) {
	                        $response = fgets($sock);
	                        if($debug) {echo "Opening up socket to $mailers[$n]... Succes!\n";}
	                        stream_set_timeout($sock, 5);
	                        $meta = stream_get_meta_data($sock);
	                        if($debug) { echo "$mailers[$n] replied: $response\n";}
	                        $cmds = array(
	                            "HELO outkast.tienhuis.nl",  # Be sure to set this correctly!
	                            "MAIL FROM: <$probe_address>",
	                            "RCPT TO: <$email>",
	                            "QUIT",
	                        );
	                        # Hard error on connect -> break out
	                        if(!$meta['timed_out'] && !preg_match('/^2\d\d[ -]/', $response)) {
	                            $error = "Error: $mailers[$n] said: $response\n";
	                            break;
	                        }
	                        foreach($cmds as $cmd) {
	                            $before = microtime(true);
	                            fputs($sock, "$cmd\r\n");
	                            $response = fgets($sock, 4096);
	                            $t = 1000*(microtime(true)-$before);
	                            if($debug) {echo htmlentities("$cmd\n$response") . "(" . sprintf('%.2f', $t) . " ms)\n";}
	                            if(!$meta['timed_out'] && preg_match('/^5\d\d[ -]/', $response)) {
	                                $error = "Unverified address: $mailers[$n] said: $response";
	                                break 2;
	                            }
	                        }
	                        fclose($sock);
	                        if($debug) { echo "Succesful communication with $mailers[$n], no hard errors, assuming OK";}
	                        break;
	                    } elseif($n == $total-1) {
	                        $error = "None of the mailservers listed for $domain could be contacted";
	                    }
	                }
	            } elseif($total <= 0) {
	                $error = "No usable DNS records found for domain '$domain'";
	            }
	        }
	    } else {
	        $error = 'Address syntax not correct';
	    }
	    if($debug) { echo "</pre>";}
	    #echo "</pre>";
	    if($return_errors) {
	        # Give back details about the error(s).
	        # Return FALSE if there are no errors.
	        # Keep this in mind when using it like:
	        # if(checkEmail($addr)) {
	        # Because of this strange behaviour this
	        # is not default ;-)
	        if(isset($error)) return htmlentities($error); else return false;
	    } else {
	        # 'Old' behaviour, simple to understand
	        if(isset($error)) return false; else return true;
	    }
	}
	/**
	* sends an invite
	*/
	function sendmail( $name, $mail, $item, $lang, $time, $subject, $msg, $all, $success, $fail, $id, $id2, $count )
	{
		global $db, $users, $security, $basic_gui;
		
		// so we don't flood the email stuff we first wait a second
		sleep( 1 );
		
		// change the msg
		$msg = preg_replace( '#\#name\##i', $name, $msg );
		$msg = preg_replace( '#\#item\##i', $item, $msg );
		$msg = preg_replace( '#\#time\##i', date( 'd.m.Y', $time ), $msg );
		
		// if subscribed to all then add a little something to unsubscribe
		if ( $all )
		{
			$url = $basic_gui->get_URL() . '/index.php/mode=ShowEvent/submode=unsubscribe/id=' . $id;
			$msg .= "\r\n\r\n\r\n" . sprintf( $this->lang[ 'Invites_unsubscribe' ], $url );
		}
		
		// allow the same changes for subject
		$subject = preg_replace( '#<name>#i', $name, $subject );
		$subject = preg_replace( '#<item>#i', $item, $subject );
		$subject = preg_replace( '#<time>#i', date( 'd.m.Y', $time ), $subject );
		
		if ( !$users->ClB_Mail( $mail, $subject, $msg ) )
		{
			$fail++;
		}else
		{
			$success++;
			$id2 = $security->parsevar( $id2, ADD_SLASHES, TRUE );
			$db->sql_query( "DELETE FROM ". SHOWEVENT_MAIL_TABLE . " WHERE mail_id='$id2' AND mail_all<>1" );
		}
		
		$id++;
		$msg = sprintf( $this->lang[ 'Invites_status' ], $success, $count, $fail );
		
		return array( $success, $fail, $id, $count, $msg );
	}
	
	//
	// End of ACP_ShowEvent class
	//
}

?>