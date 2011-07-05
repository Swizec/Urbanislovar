<?php

/**
*     defines the ACP_uProfile class
*     @file                ACP_uProfile.php
*     @see ACP_uProfile
*/
/**
* ACP advanced settings :P
*     @class		   ACP_uProfile
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        11th December 2006
*     @package		     uProfile
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
// gui :: the gui subclass
// forum_list :: array with forums

// class creation
$vars = array(  );
$visible = array(  );
eval( Varloader::createclass( 'ACP_uProfile', $vars, $visible ) );
// end class creation

class ACP_uProfile extends ACP_uProfile_def
{
	/**
	* constructor
	*/
	function ACP_uProfile(  )
	{
		global $Cl_root_path, $lang_loader, $basic_gui, $security;
			
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_uProfile' );
		
		// sidebar stuff :P
		$URL1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_uProfile&s=base' );
		$URL2 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_uProfile&s=extra' );
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $URL1 . '">' . $this->lang[ 'Sidebar_base' ] . '</a><br /><a href="' . $URL2 . '">' . $this->lang[ 'Sidebar_extra' ] . '</a></span>' );
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $errors, $template;
		
		// get the mode
		$s = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : '';
		
		// fire the template
		$template->assign_files( array(
			'ACP_uProfile' => 'ACP/uProfile' . tplEx
		) );
		
		// act upon it
		switch( $s )
		{
			case 'base':
				$this->base();
				break;
			case 'base2':
				$this->set_base();
				break;
			case 'extra':
				$this->extra();
				break;
			case 'extra2':
				$this->extra2();
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* administration of the basic profile
	*/
	function base()
	{
		global $template, $board_config, $security, $Cl_root_path;
		
		$template->assign_block_vars( 'base', '', array(
			'L_TITLE' => $this->lang[ 'base_title' ],
			'L_EXPLAIN' => $this->lang[ 'base_explain' ],
			'L_AVY' => $this->lang[ 'base_avy' ],
			'L_AVYEXPLAIN' => $this->lang[ 'base_avyexplain' ],
			'L_AVYUSE' => $this->lang[ 'base_avyuse' ],
			'L_AVYUL' => $this->lang[ 'base_avyul' ],
			'L_AVYREM' => $this->lang[ 'base_avyrem' ],
			'L_AVYHEIGHT' => $this->lang[ 'base_avyheight' ],
			'L_AVYWIDTH' => $this->lang[ 'base_avywidth' ],
			'L_AVYSIZE' => $this->lang[ 'base_avysize' ],
			'L_PIXEL' => $this->lang[ 'base_pixel' ],
			'L_KB' => $this->lang[ 'base_kb' ],
			'L_AVYDEFUL' => $this->lang[ 'base_avydeful' ],
			'L_AVYDEFAULT' => $this->lang[ 'base_avydefault' ],
			'L_INFO' => $this->lang[ 'base_info' ],
			'L_INFOEXPLAIN' => $this->lang[ 'base_infoexplain' ],
			'L_INFOLOCATION' => $this->lang[ 'base_infolocation' ],
			'L_INFOBIRTH' => $this->lang[ 'base_infobirth' ],
			'L_CONTACT' => $this->lang[ 'base_contact' ],
			'L_CONTACTEXPLAIN' =>$this->lang[ 'base_contactexplain' ],
			'L_CONTACTEMAIL' => $this->lang[ 'base_contactemail' ],
			'L_CONTACTIM' => $this->lang[ 'base_contactim' ],
			'L_CONTACTSITES' => $this->lang[ 'base_contactsites' ],
			
			'U_AVYDEFAULT' => $Cl_root_path . 'images/' . $board_config[ 'uProfile_avydef' ],
			
			'S_AVYUSE' => ( $board_config[ 'uProfile_avyuse' ] ) ? 'checked' : '',
			'S_AVYUL' => ( $board_config[ 'uProfile_avyul' ] ) ? 'checked' : '',
			'S_AVYREM' => ( $board_config[ 'uProfile_avyrem' ] ) ? 'checked' : '',
			'S_AVYHEIGHT' => $board_config[ 'uProfile_avyheight' ],
			'S_AVYWIDTH' => $board_config[ 'uProfile_avywidth' ],
			'S_AVYSIZE' => $board_config[ 'uProfile_avysize' ],
			'S_INFOLOCATION' => ( $board_config[ 'uProfile_infolocation' ] ) ? 'checked' : '',
			'S_INFOBIRTH' => ( $board_config[ 'uProfile_infobirth' ] ) ? 'checked' : '',
			'S_CONTACTEMAIL' => ( $board_config[ 'uProfile_contactemail' ] ) ? 'checked' : '',
			'S_CONTACTIM' => $board_config[ 'uProfile_contactim' ],
			'S_CONTACTSITES' => $board_config[ 'uProfile_contactsites' ],
			
			'S_ACTION' =>$security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_uProfile&s=base2' ),
		) );
		
		$template->assign_switch( 'base', TRUE );
	}
	/**
	* sets the changes for the basic profile
	*/
	function set_base()
	{
		global $db, $errors, $Cl_root_path, $db;
		
		if ( !isset( $_POST[ 'ilikeprofiles' ] ) )
		{ // basic check
			$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
		}
		
		// just go through posted fields and set them
		// if somebody made a boo-boo they should've known better
		foreach ( array_keys( $_POST ) as $k )
		{
			$val = $_POST[ $k ];
			$k = 'uProfile_' . $k;
			$sql = "UPDATE " . CONFIG_TABLE .  " SET config_value='$val' WHERE config_name='$k' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not insert into database', CRITICAL_ERROR );
			}
		}
		// checkboxes need different treatment, so we make an array of what is expected and use it
		$boxes = array( 'avyuse', 'avyul', 'avyrem', 'infolocation', 'infobirth', 'contactemail' );
		foreach ( $boxes as $k )
		{
			if ( !isset( $_POST[ $k ] ) )
			{
				$val = '0';
			}else
			{
				$val = '1';
			}
			$k = 'uProfile_' . $k;
			$sql = "UPDATE " . CONFIG_TABLE .  " SET config_value='$val' WHERE config_name='$k' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not insert into database', CRITICAL_ERROR );
			}
		}
		// now for the uploads
		$file = $_FILES[ 'defavy' ];
		if ( $file[ 'error' ] != 4 )
		{ // something was uploaded
			if ( $file[ 'error' ] != 0 )
			{ // there was an error
				$errors->report_error( $this->lang[ 'base_avyulfail' ], GENERAL_ERROR );
			}
			if ( !in_array( $file[ 'type' ], array( 'image/jpeg', 'image/gif', 'image/png' ) ) )
			{ // make sure it's an image
				$errors->report_error( $this->lang[ 'base_avyulfail' ], GENERAL_ERROR );
			}
			$name = explode( '/', $file[ 'type' ] );
			$name = 'defaultavy.' . $name[ 1 ];
			$dir = $Cl_root_path . 'images/';
			if ( is_uploaded_file( $file[ 'tmp_name' ] ) )
			{
				if ( !is_writable( $dir ) )
				{ // has to be writable
					@chmod( $dir, 0744 );
					$modded = TRUE;
				}else
				{
					$modded = FALSE;
				}
				// remove the old ones
				$dir = $Cl_root_path . 'images/';
				if ( is_readable( $dir . 'defaultavy.jpeg' ) )
				{
					$avydef = $dir . 'defaultavy.jpeg';
					@unlink( $avydef );
				}elseif( is_readable( $dir . 'defaultavy.png' ) )
				{
					$avydef = $dir . 'defaultavy.png';
					@unlink( $avydef );
				}elseif( is_readable( $dir . 'defaultavy.gif' ) )
				{
					$avydef = $dir . 'defaultavy.gif';
					@unlink( $avydef );
				}
				// end removing
				if ( !move_uploaded_file( $file[ 'tmp_name' ], $dir . $name ) )
				{
					$errors->report_error( $this->lang[ 'base_avyulfail' ], GENERAL_ERROR );
				}
				if ( $modded )
				{
					@chmod( $dir, 0544 );
				}
				// put it in config
				$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='$name' WHERE config_name='uProfile_avydef' LIMIT 1";
				$db->sql_query( $sql );
			}else
			{
				$errors->report_error( $this->lang[ 'base_avyulfail' ], GENERAL_ERROR );
			}
		}
		
		// guess it worked
		$errors->report_error( $this->lang[ 'base_done' ], MESSAGE );
	}
	/**
	* administration of extra profile stuff
	*/
	function extra()
	{
		global $template, $security, $board_config;
		
		// make the list of existant fields
		if ( $board_config[ 'uProfile_extrafields' ] == '' )
		{ // there is naught, we tell this
			$existant = $this->lang[ 'extra_none' ];
		}else
		{
			$fields = explode( ':::', $board_config[ 'uProfile_extrafields' ] );
			$existant = '';
			foreach ( $fields as $i => $field )
			{
				$field = explode( ':', $field );
				$existant .= '<b>' . $this->lang[ 'extra_addname' ] . ': </b><input type="text" value="' . str_replace( '_', ' ', $field[ 0 ] ) . '" name="existant_name_' . $i . '" />&nbsp;';
				$existant .= '<b>' . $this->lang[ 'extra_addtype' ] . ': </b>' . $this->typeselect( 'existant_type_' . $i, $field[ 1 ] ) . '&nbsp;';
				$sel = ( $field[ 2 ] ) ? 'checked' : '';
				$existant .= '<b>' . $this->lang[ 'extra_addpublic' ] . ': </b><input type="checkbox" value="1" name="existant_public_' . $i . '" ' . $sel . ' />&nbsp;';
				$existant .= '<b>' . $this->lang[ 'extra_adddel' ]. ': </b><input type="checkbox" value="1" name="existant_delete_' . $i . '" /><br />';
			}
		}
		
		$types = $this->typeselect( 'addtype', '' );
		
		$template->assign_block_vars( 'extra', '', array(
			'L_TITLE' => $this->lang[ 'extra_title' ],
			'L_EXPLAIN' => $this->lang[ 'extra_explain' ],
			'L_EXISTANT' => $this->lang[ 'extra_existant' ],
			'L_ADD' => $this->lang[ 'extra_add' ],
			'L_ADDEXPLAIN' => $this->lang[ 'extra_addexplain' ],
			'L_ADDNAME' => $this->lang[ 'extra_addname' ],
			'L_ADDTYPE' => $this->lang[ 'extra_addtype' ],
			'L_ADDPUBLIC' => $this->lang[ 'extra_addpublic' ],
			
			'S_EXISTANT' => $existant,
			'S_ADDTYPE' => $types,
			'S_ACTION' =>$security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_uProfile&s=extra2' ),
		) );
		
		$template->assign_switch( 'extra', TRUE );
	}
	/**
	* comitting changes for the extra stuff
	*/
	function extra2()
	{
		global $errors, $db, $board_config;
		
		if ( !isset( $_POST[ 'ilikeextrastuff' ] ) )
		{ // basic check
			$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
		}
		
		$types = array( 
				'mini_text' => 'VARCHAR ( 10 )',
				'short_text' => 'VARCHAR ( 50 )',
				'text' => 'VARCHAR ( 255 )',
				'long_text' => 'TEXT',
				'number' => 'INT',
				'float' => 'FLOAT'
			);
		
		// go through the fields and make changes as needed
		$fields = array();
		if ( $board_config[ 'uProfile_extrafields' ] != '' )
		{
			foreach( explode( ':::', $board_config[ 'uProfile_extrafields' ] ) as $i => $field )
			{
				$field = explode( ':', $field );
				$del = FALSE;
				$newname = ( $this->fieldexist( $_POST[ 'existant_name_' . $i ] ) ) ? $field[ 0 ] : str_replace( ' ', '_', $_POST[ 'existant_name_' . $i ] );
				if ( $_POST[ 'existant_delete_' . $i ] )
				{ // delete the poor bastard
					$del = TRUE;
					$sql = "ALTER TABLE " . USERS_TABLE . " DROP `user_" . $field[ 0 ] . "`";
					if ( !$result = $db->sql_query( $sql ) )
					{
						$errors->report_error( 'Cannot delete field', CRITICAL_ERROR );
					}
				}elseif ( $newname != $field[ 0 ] || $_POST[ 'existant_type_' . $i ] != $field[ 1 ] )
				{ // make the query to change stuff
					$sql =" ALTER TABLE " . USERS_TABLE . " CHANGE `user_" . $field[ 0 ] . "` `user_" . $newname . "` " . $types[ $_POST[ 'existant_type_' . $i ] ] . "  NOT NULL";
					if ( !$result = $db->sql_query( $sql ) )
					{
						$errors->report_error( 'Cannot change field', CRITICAL_ERROR );
					}
				}
				if ( !$del )
				{ // add it to the new list of fields
					$pub = ( isset( $_POST[ 'existant_public_' . $i ] ) ) ? '1' : '0';
					$fields[] = $newname . ':' . $_POST[ 'existant_type_' . $i ] . ':' . $pub;
				}
			}
			$fields = implode( ':::', $fields );
			$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='$fields' WHERE config_name='uProfile_extrafields'";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Cannot insert into config', CRITICAL_ERROR );
			}
			$board_config[ 'uProfile_extrafields' ] = $fields;
		}
		
		// take care of adding the field to the user table
		if ( $_POST[ 'addname' ] && $_POST[ 'addtype' ] && !$this->fieldexist( $_POST[ 'addname' ] ) )
		{ // both need to be set
			$sql = 'ALTER TABLE ' . USERS_TABLE . ' ADD `user_%s` %s NOT NULL';
			$sql = sprintf( $sql, str_replace( ' ', '_', $_POST[ 'addname' ] ), $types[ $_POST[ 'addtype' ] ] );
			// we now execute this, but if something fails further on we will remove it
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Cannot add field', CRITICAL_ERROR );
			}
			
			// now try adding it to the list as well
			$field = str_replace( ' ', '_', $_POST[ 'addname' ] ) . ':' . $_POST[ 'addtype' ] . ':';
			$field .= ( isset( $_POST[ 'addpublic' ] ) ) ? '1' : '0';
			$fields = $board_config[ 'uProfile_extrafields' ];
			$fields .= ( $fields == '' ) ? $field : ':::' . $field;
			$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='$fields' WHERE config_name='uProfile_extrafields'";
			if ( !$result = $db->sql_query( $sql ) )
			{
				// we quickly drop the previously added field
				$db->sql_query( 'ALTER TABLE ' . USERS_TABLE . ' DROP `user_' . str_replace( ' ', '_', $_POST[ 'addname' ] ) . '`' );
				$errors->report_error( 'Cannot add field', CRITICAL_ERROR );
			}
		}
		
		$errors->report_error( $this->lang[ 'extra_done' ], MESSAGE );
	}
	/**
	* makes a list of choosable types
	*/
	function typeselect( $name, $selected )
	{
		// make the type selection
		$types = array( 'mini_text', 'short_text', 'text', 'long_text', 'number', 'float' );
		for( $i = 0; $i < count( $types ); $i++ )
		{
			if ( $types[ $i ] == $selected )
			{
				$sel = 'selected';
			}else
			{
				$sel = '';
			}
			$types[ $i ] = '<option value="' . $types[ $i ] . '" ' . $sel . '>' . $this->lang[ 'extra_type_' . $types[ $i ] ] . '</option>';
		}
		$types = '<select name="' . $name . '"><option></option>' . implode( "\n", $types ) . '</select>';
		
		return $types;
	}
	/**
	* checks if a field already exists
	*/
	function fieldexist( $name )
	{
		global $board_config;
		
		$name = str_replace( ' ', '_', $name );
		
		foreach ( explode( ':::', $board_config[ 'uProfile_extrafields' ] ) as $field )
		{
			$field = explode( ':', $field );
			if ( $field[ 0 ] == $name )
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	//
	// End of ACP_uProfile class
	//
}


?>