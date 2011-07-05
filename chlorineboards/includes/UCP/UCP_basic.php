<?php

/**
*     defines the UCP_basic class
*     @file                UCP_basic.php
*     @see UCP_basic
*/
/**
* UCP panel for changing password, email etc
*     @class		   UCP_basic
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.1
*     @since        7th Decemberl 2006
*     @package		     ClB_base
*     @subpackage	     ClB_UCP
*     @license http://opensource.org/licenses/gpl-license.php
*     @filesource
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
eval( Varloader::createclass( 'UCP_basic', $vars, $visible ) );
// end class creation

class UCP_basic extends UCP_basic_def
{
	/**
	* constructor
	*/
	function UCP_basic( $debug = FALSE )
	{
		global $Cl_root_path, $basic_gui, $lang_loader, $security;
		
		$this->debug = $debug;
	
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'UCP_basic' );
		
		// make the url
		$url1 = $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_basic&s=pass' );
		$url2 = $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_basic&s=lang' );
		$url3 = $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_basic&s=skin' );
		
		// add to page
		// add to page
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $url1 . '">' . $this->lang[ 'Sidebar_pass' ] . '</a><br /><a href="' . $url2 . '">' . $this->lang[ 'Sidebar_lang' ] . '</a><br /><a href="' . $url3 . '">' . $this->lang[ 'Sidebar_skin' ] . '</a><br /></span>' );
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $template, $errors, $Cl_root_path;
		
		$template->assign_files( array(
			'UCP_basic' => 'UCP/basic' . tplEx
		) );
		
		// get the subsubmode
		$sub = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : 'add';
		
			
		switch( $sub )
		{
			case 'lang':
				$this->setting( 'lang' );
				break;
			case 'lang2':
				$this->set_setting( 'lang' );
				break;
			case 'skin':
				$this->setting( 'skin' );
				break;
			case 'skin2':
				$this->set_setting( 'skin' );
				break;
			case 'pass':
				$this->password();
				break;
			case 'pass2':
				$this->set_password();
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* shows a panel to change either language or skin
	*/
	function setting( $what )
	{
		global $template, $errors, $Cl_root_path, $security, $lang_loader, $userdata, $basic_gui;
		
		$template->assign_files( array(
			'UCP_setting' => 'UCP/setting' . tplEx
		) );
		
		if ( $what == 'lang' )
		{
			// get and set up list of langauges
			$langs = $lang_loader->get_langlist();
			foreach ( $langs as $i => $lang )
			{
				$name = ( isset( $this->lang[ $lang ] ) ) ? $this->lang[ $lang ] : $lang;
				$sel = ( $userdata[ 'user_lang' ] == $lang ) ? 'selected' : '';
				$langs[ $i ] = '<option value="' . $lang . '" ' . $sel . '>' . $name . '</option>';
			}
			$sel = ( !$userdata[ 'user_lang' ] ) ? 'selected' : '';
			$langs[] = '<option value="browser" ' . $sel . '>' . $this->lang[ 'lang_browser' ] . '</option>';
			$setting = '<select name="language" onchange="changeprev( this.value, \'lang\' ); return false">' . implode( "\n", $langs ) . '</select>';
		}elseif( $what == 'skin' )
		{
			// get list of skins
			$skins = array();
			$d = dir( $Cl_root_path . '/template' );
			while ( FALSE !== ( $entry = $d->read() ) ) 
			{
// 				echo $Cl_root_path . 'template/' . $entry . "<br />";
				if ( $entry != '.' && $entry != '..' && is_dir( $Cl_root_path . 'template/' . $entry ) )
				{
					$sel = ( $userdata[ 'user_skin' ] == $entry ) ? 'selected' : '';
					$skins[] = '<option value="' . $entry . '" ' . $sel . '>' . $entry . '</option>';
				}
// 				print_R( $skins );
				$setting = '<select name="skin" onchange="changeprev( this.value, \'skin\' ); return false">' . implode( "\n", $skins ) . '</select>';
			}
			$d->close();
		}
		
		$template->assign_block_vars( 'setting', '', array(
				'L_TITLE' => $this->lang[ $what . '_title' ],
				'L_EXPLAIN' => $this->lang[ $what . '_explain' ],
				'L_NAME' => $this->lang[ $what . '_name' ],
				'L_SELECT' => $this->lang[ $what . '_select' ],
				
				'S_SETTING' => $setting,
				'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_basic&s=' . $what . '2' ),
				
				'U_PREVIEW' => $basic_gui->get_URL()
			) );
			
		$template->assign_switch( 'setting', TRUE );
	}
	/**
	* commits the change of language or skin
	*/
	function set_setting( $what )
	{
		global $errors, $Cl_root_path, $users, $userdata;
	
		if ( !isset( $_POST[ 'settingdone' ] ) )
		{ // a basic check
			$errors->report_error( GENERAL_ERROR, $this->lang[ 'Wrong_form' ] );
		}
		
		if ( $what == 'lang' )
		{
			$lang = $_POST[ 'language' ];
			if ( is_dir( $Cl_root_path . 'language/' . $lang ) )
			{
				$set = ( $lang != 'browser' ) ? $lang : '';
				$users->set_userdata( $userdata[ 'user_id' ], array( 'user_lang' => $set ) );
				$errors->report_error( sprintf( $this->lang[ 'lang_done' ],  ( isset( $this->lang[ $lang ] ) ) ? $this->lang[ $lang ] : $lang ), MESSAGE );
			}
			$errors->report_error( sprintf( $this->lang[ 'lang_fuck' ], ( isset( $this->lang[ $lang ] ) ) ? $this->lang[ $lang ] : $lang ), GENERAL_ERROR );
		}elseif ( $what == 'skin' )
		{
			$skin = $_POST[ 'skin' ];
			if ( is_dir( $Cl_root_path . 'template/' . $skin ) )
			{
				$users->set_userdata( $userdata[ 'user_id' ], array( 'user_skin' => $skin ) );
				$errors->report_error( sprintf( $this->lang[ 'skin_done' ],  ( isset( $this->lang[ $skin ] ) ) ? $this->lang[ $skin ] : $skin ), MESSAGE );
			}
			$errors->report_error( sprintf( $this->lang[ 'skin_fuck' ], ( isset( $this->lang[ $skin ] ) ) ? $this->lang[ $skin ] : $skin ), GENERAL_ERROR );
		}
	}
	/**
	* shows the gui to change password or e-mail
	*/
	function password()
	{
		global $template, $security, $userdata;
		
		$template->assign_block_vars( 'password', '', array(
			'L_TITLE' => $this->lang[ 'password_title' ],
			'L_EXPLAIN' => $this->lang[ 'password_explain' ],
			'L_OLDPASS' => $this->lang[ 'password_oldpass' ],
			'L_NEWPASS' => $this->lang[ 'password_newpass' ],
			'L_PASSWORD' => $this->lang[ 'password_password' ],
			'L_PASSWORD2' => $this->lang[ 'password_password2' ],
			'L_NEWMAIL' => $this->lang[ 'password_newmail' ],
			'L_OLDMAIL' => $this->lang[ 'password_oldmail' ],
			'L_MAILTO' => $this->lang[ 'password_mailto' ],
			
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=UCP&' . SUBMODE_URL . '=UCP_basic&s=pass2' ),
			'S_OLDMAIL' => $userdata[ 'user_email' ],
		) );
		
		$template->assign_switch( 'password', TRUE );
	}
	/**
	* changes the password or e-mail according to submitted data
	*/
	function set_password()
	{
		global $errors, $encryption, $security, $userdata, $db, $users;
		
		if ( !isset( $_POST[ 'changy' ] ) )
		{ // a basic check
			$errors->report_error( GENERAL_ERROR, $this->lang[ 'Wrong_form' ] );
		}
		
		// now check the password (copied over from login.php)
		//  encrypt the password we got
		$password = $encryption->encrypt( $security->make_key( $userdata[ 'user_email' ] ), $_POST[ 'old_pass' ], 30 );
		
		// check the password
		if ( $password != addslashes( $userdata[ 'password' ] ) )
		{
			$errors->report_error( $this->lang[ 'password_errpassword' ], GENERAL_ERROR );
		}
		
		// now we check what's changed
		if ( !empty( $_POST[ 'new_pass1' ] ) && !empty( $_POST[ 'new_pass2' ] ) )
		{ // apparently this is a password change
			$pass1 = $_POST[ 'new_pass1' ];
			$pass2 = $_POST[ 'new_pass2' ];
			// see if they match
			if ( $pass1 != $pass2 )
			{
				$errors->report_error( $this->lang[ 'password_nomatch' ], GENERAL_ERROR );
			}
			// encrypt the password
			$password = $encryption->encrypt( $security->make_key( $userdata[ 'user_email' ] ), $_POST[ 'new_pass1' ], 30 );
			$userdata[ 'password' ] = $password;
			// and shove it into the database
			$users->set_userdata( $userdata[ 'user_id' ], array( 'password' => $password ) );
			// notify by email
			$this->sendmail();
			$errors->report_error( $this->lang[ 'password_passchanged' ], MESSAGE );
		}elseif( !empty( $_POST[ 'new_mail' ] ) )
		{ // change the email
			$mail = $_POST[ 'new_mail' ];
			// the password has to be first decrypted
			$password = $encryption->decrypt( $security->make_key( $userdata[ 'user_email' ] ), $userdata[ 'password' ] );
			// and then encrypted with the new key
			$password = $encryption->encrypt( $security->make_key( $mail ), $password, 30 );
			$userdata[ 'password' ] = $password;
			$userdata[ 'user_email' ] = $mail;
			// now insert the two
			$users->set_userdata( $userdata[ 'user_id' ], array( 'password' => $password, 'user_email' => $mail ) );
			// notify by email
			$this->sendmail();
			$errors->report_error( $this->lang[ 'password_mailchanged' ], MESSAGE );
		}
		
		// if we got here then something is somewhat wrong
		$errors->report_error( $this->lang[ 'password_nochange' ], MESSAGE );
	}
	/**
	* this function tries sending an e-mail upon changing password or email
	*/
	function sendmail()
	{
		global $userdata, $board_config, $users, $encryption, $security;
		
		//decrypt password for showing
		$password = $encryption->decrypt( $security->make_key( $userdata[ 'user_email' ] ), $userdata[ 'password' ] );
		// parse the body
		$date = date( $userdata[ 'user_timeformat' ] );
		$subject = sprintf( $this->lang[ 'password_mailsubj' ], $board_config[ 'sitename' ] );
		$URL = 'http://' . $board_config[ 'session_domain' ] . $board_config[ 'session_path' ];
		$body = sprintf( $this->lang[ 'password_mailbody' ], $date, $board_config[ 'sitename' ], $userdata[ 'username'], $password );
		
		// send the email
		$users->clb_Mail( $userdata[ 'user_mail' ], $subject, $body, $board_config[ 'admin_email' ] );
	}
	//
	// End of UCP_basic class
	//
}

?>