<?php

/**
*     defines the UCP_uProfile class
*     @file                UCP_uProfile.php
*     @see UCP_uProfile
*/
/**
* UCP advanced settings :P
*     @class		   UCP_uProfile
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.1
*     @since        15th December 2006
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
eval( Varloader::createclass( 'UCP_uProfile', $vars, $visible ) );
// end class creation

class UCP_uProfile extends UCP_uProfile_def
{
	/**
	* constructor
	*/
	function UCP_uProfile(  )
	{
		global $Cl_root_path, $lang_loader, $basic_gui, $security;
			
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'UCP_uProfile' );
		
		// sidebar stuff :P
		$URL1 = $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_uProfile&s=base' );
		$URL2 = $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_uProfile&s=signature' );
		$URL3 = $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_uProfile&s=extra' );
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $URL1 . '">' . $this->lang[ 'Sidebar_base' ] . '</a><br /><a href="' . $URL2 . '">' . $this->lang[ 'Sidebar_signature' ] . '</a><br /><a href="' . $URL3 . '">' . $this->lang[ 'Sidebar_extra' ] . '</a></span>' );
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
			'UCP_uProfile' => 'UCP/uProfile' . tplEx
		) );
		
		// act upon it
		switch( $s )
		{
			default:
				case 'base':
					$this->base();
					break;
				case 'base2':
					$this->set_base();
					break;
				case 'signature':
					$this->signature();
					break;
				case 'signature2':
					$this->signature2();
					break;
				case 'extra':
					$this->extra();
					break;
				case 'extra2':
					$this->extra2();
					break;
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* administration of the basic profile
	*/
	function base()
	{
		global $template, $board_config, $security, $Cl_root_path, $userdata, $basic_gui;
		
		// get the default avy
		if ( !empty( $userdata[ 'user_avatar' ] ) )
		{
			$avy = $userdata[ 'user_avatar' ];
		}else
		{
			$avy = $basic_gui->get_URL() . '/images/' . $board_config[ 'uProfile_avydef' ];
		}
		
		// make the birthdate controls
		$day = date( 'd', $userdata[ 'user_birth' ] );
		$month = date( 'm', $userdata[ 'user_birth' ] );
		$year = date( 'Y', $userdata[ 'user_birth' ] );
		$days = '<select name="birthday">';
		for( $i = 1; $i < 32; $i++ )
		{
			$days .= '<option value="' . $i . '" ';
			if ( $i == $day )
			{
				$days .= 'selected ';
			}
			$days .= '>' . $i . '</option>';
		}
		$days .= '</select>';
		$months = '<select name="birthmonth">';
		for ( $i = 1; $i < 13; $i++ )
		{
			$months .= '<option value="' . $i . '" ';
			if ( $i == $month )
			{
				$months .= 'selected';
			}
			$months .= '>' . $this->lang[ 'month' . $i ] . '</option>';
		}
		$months .= '</select>';
		
		// make the sites list
		$sites = '';
		$s = explode( ':::', $userdata[ 'user_sites' ] );
		for ( $i = 0; $i < $board_config[ 'uProfile_contactsites' ]; $i++ )
		{
			if ( !isset( $s[ $i ] ) )
			{
				$s[ $i ] = '';
			}
			$sites .= '<b>#' . ($i+1) . ': </b><input type="text" value="' . $s[ $i ] . '" name="contactsite[' . $i . ']" /><br />';
		}
		// make the im list
		$im = '';
		$p = explode( ':::', $userdata[ 'user_im' ] );
		$ims = array();
		foreach ( $p as $k )
		{
			$m = explode( ':', $k );
			$ims[ $m[ 0 ] ] = $m[ 1 ];
		}
		foreach ( explode( ' ', $board_config[ 'uProfile_contactim' ] ) as $p )
		{
			$im .= '<b>' . $p . ': </b><input type="text" value="' . $ims[ $p ] . '" name="contactim_' . $p . '" /><br />';
		}
		
		$template->assign_block_vars( 'base', '', array(
			'L_TITLE' => $this->lang[ 'base_title' ],
			'L_EXPLAIN' => $this->lang[ 'base_explain' ],
			'L_AVY' => $this->lang[ 'base_avy' ],
			'L_AVYEXPLAIN' => $this->lang[ 'base_avyexplain' ],
			'L_AVYIMAGE' => $this->lang[ 'base_avyimage' ],
			'L_AVYUL' =>$this->lang[ 'base_avyul' ],
			'L_AVYURL' => $this->lang[ 'base_avyurl' ],
			'L_AVYCONSTRAINT' => sprintf( $this->lang[ 'base_avyconstraint' ], $board_config[ 'uProfile_avywidth' ], $board_config[ 'uProfile_avyheight' ], $board_config[ 'uProfile_avysize' ] ),
			'L_AVYDISABLE' => $this->lang[ 'base_avydisable' ],
			'L_AVYREMOVE' => $this->lang[ 'base_avyremove' ],
			
			'L_INFO' => $this->lang[ 'base_info' ],
			'L_INFOEXPLAIN' => $this->lang[ 'base_infoexplain' ],
			'L_INFOLOCATION' => $this->lang[ 'base_infolocation' ],
			'L_INFOBIRTH' => $this->lang[ 'base_infobirth' ],
			'L_INFOSHOWAGE' => $this->lang[ 'base_infoshowage' ],
			
			'L_CONTACT' => $this->lang[ 'base_contact' ],
			'L_CONTACTEXPLAIN' => $this->lang[ 'base_contactexplain' ],
			'L_CONTACTEMAIL' => $this->lang[ 'base_contactemail' ],
			'L_CONTACTSITES' => $this->lang[ 'base_contactsites' ],
			'L_CONTACTIM' =>$this->lang[ 'base_contactim' ],
			
			'L_PUBLIC' => $this->lang[ 'base_public' ],
			
			'U_AVYIMAGE' => $avy,
			'U_PUBLIC' => $security->append_sid( '?' . MODE_URL . '=uProfile_norm&uid=' .  $userdata[ 'user_id' ] ),
			
			'S_AVYURL' =>$userdata[ 'user_avatar' ],
			'S_INFOLOCATION' => $userdata[ 'user_location' ],
			'S_INFODAYS' => $days,
			'S_INFOMONTHS' => $months,
			'S_INFOYEAR' => $year,
			'S_INFOSHOWAGE' => ( $userdata[ 'user_showage' ] ) ? 'checked' : '',
			'S_CONTACTEMAIL' => $userdata[ 'user_publicmail' ],
			'S_CONTACTSITES' =>$sites,
			'S_CONTACTIM' => $im,
			'S_ACTION' =>$security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_uProfile&s=base2' ),
			
			'USEAVY' => ( $board_config[ 'uProfile_avyuse' ] ) ? 1 : 0,
			'USEUL' => ( $board_config[ 'uProfile_avyul' ] ) ? 1 : 0,
			'USEURL' => ( $board_config[ 'uProfile_avyrem' ] ) ? 1 : 0,
			'USELOCATION' => ( $board_config[ 'uProfile_infolocation' ] ) ? 1 : 0,
			'USEBIRTH' => ( $board_config[ 'uProfile_infobirth' ] ) ? 1 : 0,
			'USEEMAIL' =>( $board_config[ 'uProfile_contactemail' ] ) ? 1 : 0,
			'USESITES' =>( $board_config[ 'uProfile_contactsites' ] != 0 ) ? 1 : 0,
			'USEIM' =>( $board_config[ 'uProfile_contactim' ] != '' ) ? 1 : 0,
		) );
		
		$template->assign_switch( 'base', TRUE );
	}
	/**
	* sets the changes for the basic profile
	*/
	function set_base()
	{
		global $errors, $Cl_root_path, $users, $userdata, $basic_gui;;
		
		if ( !isset( $_POST[ 'ilikeprofiles' ] ) )
		{ // basic check
			$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
		}
		
		$toset = array();
		
		if ( $_FILES[ 'avyul' ][ 'error' ] == 4 )
		{ // nothing was ULed, just use the URL
			if ( $_POST[ 'avyurl' ] != $userdata[ 'user_avatar' ] )
			{
				if ( $this->checkimage( $_POST[ 'avyurl' ] ) )
				{ // size stuff fine
					$toset[ 'user_avatar' ] = $_POST[ 'avyurl' ];
					$newavy = TRUE;
				}else
				{
					$errors->report_error( $this->lang[ 'base_avysize' ], GENERAL_ERROR );
				}
			}
		}elseif ( $_FILES[ 'avyul' ][ 'error' ] != 0 )
		{
			$errors->report_error( $this->lang[ 'base_avyulfail' ], GENERAL_ERROR );
		}else
		{
			$dir = $Cl_root_path . 'images/avatars';
			$file = explode( '.', $_FILES[ 'avyul' ][ 'name' ] );
			$file = $dir . '/' . $file[ 0 ] . '_' . time() . '.' . $file[ 1 ];
			if ( !is_writable( $dir ) )
			{
				@chmod( $dir, 0744 );
				$modded = TRUE;
			}
			// upload file
			if ( is_uploaded_file( $_FILES[ 'avyul' ][ 'tmp_name' ] ) )
			{
				if ( move_uploaded_file( $_FILES[ 'avyul' ][ 'tmp_name' ], $file ) )
				{
					// check size stuff
					if ( $this->checkimage( $basic_gui->get_URL() . '/' . $file, $_FILES[ 'avyul' ][ 'size' ] ) )
					{
						$toset[ 'user_avatar' ] = $basic_gui->get_URL() . '/' . $file;
						$newavy = TRUE;
					}else
					{ // remove upload and give error
						unlink( $file );
						$errors->report_error( $this->lang[ 'base_avysize' ], GENERAL_ERROR );
					}
				}
			}else
			{
				$errors->report_error( $this->lang[ 'base_avyulfail' ], GENERAL_ERROR );
			}
			if ( $modded )
			{
				@chmod( $dir, 0544 );
			}
		}
		
		if ( $_POST[ 'avyremove' ] )
		{ // avatar has to go bye bye
			$newavy = TRUE;
			$toset[ 'user_avatar' ] = '';
		}
		
		if ( $newavy )
		{ // remove the old avatar if applicable
			$U = $basic_gui->get_URL();
			if ( substr( $userdata[ 'user_avatar' ], 0, strlen( $U ) ) == $U )
			{ // was local
				if ( is_file( str_replace( $U . '/', $Cl_root_path, $userdata[ 'user_avatar' ] ) ) )
				{
					unlink( str_replace( $U . '/', $Cl_root_path, $userdata[ 'user_avatar' ] ) );
				}
			}
		}
		
		// go through the rest of the fields and add them to the array
		$fields = array( 'infolocation' => 'user_location', 'infoshowage' => 'user_showage', 'contactemail' => 'user_publicmail' );
		foreach ( $fields as $k1 => $k2 )
		{
			$set = $_POST[ $k1 ];
			if ( $_POST[ $k1 ] == 'on' )
			{ // usually means this is a checkbox
				$set = 1;
			}elseif ( !isset( $_POST[ $k1 ] ) )
			{ // usually means an unchecked checkbox
				$set = 0;
			}
			$toset[ $k2 ] = $set;
		}
		
		// deal with the time
		$toset[ 'user_birth' ] = mktime( 0, 0, 0, $_POST[ 'birthmonth' ], $_POST[ 'birthday' ], $_POST[ 'birthyear' ] );
		
		// deal with webbies
		$toset[ 'user_sites' ] =( isset( $_POST[ 'contactsite' ] ) ) ?  implode( ':::', $_POST[ 'contactsite' ] ) : '';
		
		// deal with im
		$im = array();
		foreach ( $_POST as $k => $void )
		{ // search for im stuff
			if ( substr( $k, 0, 10 ) == 'contactim_' )
			{
				$m = explode( '_', $k );
				$im[] = $m[ 1 ] . ':' . $_POST[ $k ];
			}
		}
		$toset[ 'user_im' ] = implode( ':::', $im );
		
		// make all the changes
		$users->set_userdata( $userdata[ 'user_id' ], $toset );
		
		// guess it worked
		$errors->report_error( $this->lang[ 'base_done' ], MESSAGE );
	}
	/**
	* this function checks a possible avatar image for size constraints
	*/
	function checkimage( $URL, $bytes = 0 )
	{
		global $board_config, $errors;
		
		if ( !$size = getimagesize( $URL ) )
		{ // couldn't get image information
			return FALSE;
		}
		
		// now first check file size
		if ( $bytes == 0 )
		{ // filesize unknown, get it
			$bytes = $this->remotefsize( $URL );
		}
		if ( $bytes > $board_config[ 'uProfile_avysize' ]*1024 )
		{
			return FALSE;
		}
		// now for the width and height
		if ( $size[ 0 ] > $board_config[ 'uProfile_avywidth' ] || $size[ 1 ] > $board_config[ 'uProfile_avyheight' ] )
		{
			return FALSE;
		}
		
		return TRUE;
	}
	/**
	* function fetches remote filesize
	* Josh Finlay <josh at glamourcastle dot com>
	* 12-Nov-2006 09:22
	* I (swizec) just made the syntax more readable
	*/
	function remotefsize( $url )
	{
		$sch = parse_url($url, PHP_URL_SCHEME);
		if (($sch != "http") && ($sch != "https") && ($sch != "ftp") && ($sch != "ftps"))
		{
			return FALSE;
		}
		if (($sch == "http") || ($sch == "https")) {
			$headers = get_headers($url, 1);
			if ((!array_key_exists("Content-Length", $headers)))
			{
				return FALSE;
			}
			return $headers["Content-Length"];
		}
		if (($sch == "ftp") || ($sch == "ftps"))
		{
			$server = parse_url($url, PHP_URL_HOST);
			$port = parse_url($url, PHP_URL_PORT);
			$path = parse_url($url, PHP_URL_PATH);
			$user = parse_url($url, PHP_URL_USER);
			$pass = parse_url($url, PHP_URL_PASS);
			if ((!$server) || (!$path))
			{
				return FALSE;
			}
			if (!$port)
			{
				$port = 21;
			}
			if (!$user)
			{
				$user = "anonymous";
			}
			if (!$pass)
			{
				$pass = "phpos@";
			}
			switch ($sch)
			{
				case "ftp":
					$ftpid = ftp_connect($server, $port);
					break;
				case "ftps":
					$ftpid = ftp_ssl_connect($server, $port);
					break;
			}
			if (!$ftpid)
			{
				return FALSE;
			}
			$login = ftp_login($ftpid, $user, $pass);
			if (!$login)
			{
				return FALSE;
			}
			$ftpsize = ftp_size($ftpid, $path);
			ftp_close($ftpid);
			if ($ftpsize == -1)
			{
				return FALSE;
			}
			return $ftpsize;
		}
	}
	/**
	* displays the signature stuff
	*/
	function signature()
	{
		global $template, $mod_loader, $userdata, $security;
		
		// get editor first
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'sig1', 'quickpost' => FALSE, 'def_text' => stripslashes( $userdata[ 'user_signature' ] ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$sig1 = $mod_loader->get_vars( array( 'editor_HTML' ) );
		
		$template->assign_block_vars( 'signature', '', array(
			'L_TITLE' => $this->lang[ 'signature_title' ],
			'L_EXPLAIN' => $this->lang[ 'signature_explain' ],
			
			'S_SIG1' => $sig1[ 'editor_HTML' ],
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_uProfile&s=signature2' ),
		) );
		
		$template->assign_switch( 'signature', TRUE );
	}
	/**
	* changes the signature as needed
	*/
	function signature2()
	{
		global $errors, $users, $userdata;
		
		if ( !isset( $_POST[ 'ilikesigs' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
		}
		
		$toset = array();
		
		// add to the set array
		$toset[ 'user_signature' ] = str_replace( '&nbsp;', ' ', $_POST[ 'sig1' ] );
		
		// make all the changes
		$users->set_userdata( $userdata[ 'user_id' ], $toset );
		
		// guess it worked
		$errors->report_error( $this->lang[ 'signature_done' ], MESSAGE );
	}
	/**
	* changing the extra stuff eh
	*/
	function extra()
	{
		global $template, $mod_loader, $userdata, $board_config, $security, $errors;
		
		// make the fieldlist
		$fields = '';
		$types = array( 
				'mini_text' => '&nbsp;<input type="text" maxlength="10" name="%s" value="%s" />',
				'short_text' => '&nbsp;<input type="text" maxlength="50" name="%s" value="%s" />',
				'text' => '&nbsp;<input type="text" maxlength="255" name="%s" value="%s" />',
				'number' => '&nbsp;<input type="text" name="%s" value="%s" />',
				'float' => '&nbsp;<input type="text" name="%s" value="%s" />',
			);
		if ( $board_config[ 'uProfile_extrafields' ] == '' )
		{
			$errors->report_error( $this->lang[ 'extra_none' ], MESSAGE );
		}
		foreach ( explode( ':::', $board_config[ 'uProfile_extrafields' ] ) as $field )
		{
			$field = explode( ':', $field );
			
			$fields .= '<b>' . str_replace( '_', ' ', $field[ 0 ] ) . ':</b>';
			if ( $field[ 1 ] != 'long_text' )
			{
				$fields .= sprintf( $types[ $field[ 1 ] ], $field[ 0 ], $userdata[ 'user_' . $field[ 0 ] ] ) . '<br />';
			}else
			{
				// get editor first
				$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
				$mod_loader->port_vars( array( 'name' => $field[ 0 ], 'quickpost' => TRUE, 'def_text' => stripslashes( $userdata[ 'user_' . $field[ 0 ] ] ) ) );
				$mod_loader->execute_modules( 0, 'show_editor' );
				$f = $mod_loader->get_vars( array( 'editor_HTML' ) );
				$fields .= '<br /><div style="width: 70%;">' . $f[ 'editor_HTML' ] . '</div>';
			}
		}
		
		$template->assign_block_vars( 'extra', '', array(
			'L_TITLE' => $this->lang[ 'extra_title' ],
			'L_EXPLAIN' => $this->lang[ 'extra_explain' ],
			
			'S_FIELDS' => $fields,
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_uProfile&s=extra2' ),
		) );
		
		$template->assign_switch( 'extra', TRUE );
	}
	/**
	* commit the extra stuff
	*/
	function extra2()
	{
		global $users, $errors, $board_config, $userdata;
		
		$toset = array();
		
		foreach ( explode( ':::', $board_config[ 'uProfile_extrafields' ] ) as $field )
		{
			$field = explode( ':', $field );
			
			if ( isset( $_POST[ $field[ 0 ] ] ) )
			{ // it's been set, add it to the array
				$toset[ 'user_' . $field[ 0 ] ] = str_replace( '&nbsp;', ' ', $_POST[ $field[ 0 ] ] );
			}
		}
		
		// make the changes
		$users->set_userdata( $userdata[ 'user_id' ], $toset );
		
		// guess it worked
		$errors->report_error( $this->lang[ 'extra_done' ], MESSAGE );
	}
	//
	// End of UCP_uProfile class
	//
}


?>