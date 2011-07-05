<?php

/**
*     defines the uProfile class
*     @file                uProfile.php
*     @see uProfile
*/
/**
* deals with public displaying of user profiles
*     @class		  uProfile
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        17th December 2006
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

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'uProfile', $vars, $visible ) );
// end class creation

class uProfile extends uProfile_def
{
	/**
	* constructor
	*/
	function uProfile( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax, $basic_gui;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/uProfile_gui' . phpEx );
		$this->gui = new uProfile_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'uProfile' );
		$this->gui->lang = $this->lang;
	}
	/**
	* deals with displaying the normal profile
	*/
	function display_normal( )
	{
		global $errors, $users, $board_config, $basic_gui;
		
		if ( !isset( $_GET[ 'uid' ] ) || empty( $_GET[ 'uid' ] ) )
		{
			$errors->report_error( $this->lang[ 'nouid' ], GENERAL_ERROR );
		}
		
		// fetch user data
		$uid = $_GET[ 'uid' ];
		$user = $users->get_userdata( $uid );
		
		// calculate age
		$user[ 'user_age' ] = floor( ( EXECUTION_TIME - $user[ 'user_birth' ] ) / ( 86400*356 ) );
		if ( empty( $user[ 'user_avatar' ] ) )
		{ // fix the avvy
			$user[ 'user_avatar' ] = $basic_gui->get_URL() . '/images/' . $board_config[ 'uProfile_avydef' ];
		}
		
		// calculate some "permissions"
		$user[ 'showage' ] = ( $board_config[ 'uProfile_inforbirth' ] + $user[ 'user_showage' ] ) / 2;
		$user[ 'showlocation' ] = $board_config[ 'uProfile_infolocation' ];
		$user[ 'showavy' ] = $board_config[ 'uProfile_avyuse' ];
		$user[ 'showmail' ] = $board_config[ 'uProfile_contactemail' ];
		$user[ 'showim' ] = ( empty( $board_config[ 'uProfile_contactim' ] ) ) ? 0 : 1;
		$user[ 'showsite' ] = ( $board_config[ 'uProfile_contactsites' ] != 0 ) ? 1 : 0;
	
		// call out the gui
		$this->gui->normal( $uid, $user );
	}
	
	//
	// End of filebrowser class
	//
}

?>