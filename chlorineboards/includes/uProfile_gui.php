<?php

/**
*     defines the ACP_uProfile class
*     @file                uProfile_gui.php
*     @see uProfile_gui
*/
/**
* gui for the uProfile module
*     @class		  uProfile_gui
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since       17th December 2006
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

// class creation
$vars = array( );
$visible = array( );
eval( Varloader::createclass( 'uProfile_gui', $vars, $visible ) );
// end class creation

class uProfile_gui extends uProfile_gui_def
{
	function uProfile_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'uProfile' => 'uProfile' . tplEx
		) );
	}
	
	function normal( $uid, $user )
	{
		global $template, $security, $basic_gui, $board_config, $userdata;
		
		// make the IM list
		$im = '';
		foreach ( explode( ':::', $user[ 'user_im' ] ) as $m )
		{
			$m = explode( ':', $m );
			$im .= '<b>' . $m[ 0 ] . ':</b> ' . $m[ 1 ] . '<br />';
		}
		// make the sites list
		$site = '';
		$i = 1;
		foreach ( explode( ':::', $user[ 'user_sites' ] ) as $s )
		{
			$site .= '<a href="' . $s . '" target="_blank">#' . $i++ . '</a>&nbsp;&nbsp;';
		}
		// make the extras display
		$extras = '';
		if ( $board_config[ 'uProfile_extrafields' ] != '' )
		{
			foreach( explode( ':::', $board_config[ 'uProfile_extrafields' ] ) as $field )
			{
				$field = explode( ':', $field );
				// public/nonpublic
				if ( $field[ 2 ] != 1 && ( $userdata[ 'user_level' ] > MOD || $userdata[ 'user_level' ] < ADMIN ) )
				{
					continue;
				}
				if ( $field[ 1 ] != 'long_text' )
				{
					$extras .= '<b>' . str_replace( '_', ' ', $field[ 0 ] ) . ': </b>' . $user[ 'user_' . $field[ 0 ] ] . '<br />';
				}else
				{
					$extras .= '<b>' . str_replace( '_', ' ', $field[ 0 ] ) . ': </b><br />' . $user[ 'user_' . $field[ 0 ] ] . '<br /><br />';
				}
			}
		}
		
		$template->assign_block_vars( 'normal', '', array(
			'USER' => $user[ 'username' ],
			'LOCATION' => $user[ 'user_location' ],
			'AGE' => $user[ 'user_age' ],
			'EMAIL' => $user[ 'user_publicmail' ],
			'IM' => $im,
			'SITE' => $site,
			'SIGNATURE' => $user[ 'user_signature' ],
			'EXTRA' => $extras,
		
			'U_AVVY' => $user[ 'user_avatar' ],
			'U_USER' => $security->append_sid( '?' . MODE_URL . '=uProfile_norm&uid=' . $uid ),
			
			'L_AVVY' => $this->lang[ 'avvy' ],
			'L_LOCATION' => $this->lang[ 'location' ],
			'L_AGE' => $this->lang[ 'age' ],
			'L_EMAIL' => $this->lang[ 'email' ],
			'L_SITE' => $this->lang[ 'site' ],
			'L_SIGNATURE' => $this->lang[ 'signature' ],
			'L_EXTRA' => $this->lang[ 'extra' ],
			'L_NOEXTRA' => $this->lang[ 'noextra' ],
			
			'SHOWAGE' =>$user[ 'showage' ],
			'SHOWLOCATION' => $user[ 'showlocation' ],
			'SHOWAVY' => $user[ 'showavy' ],
			'SHOWMAIL' => $user[ 'showmail' ],
			'SHOWIM' => $user[ 'showim' ],
			'SHOWSITE' => $user[ 'showsite' ],
			'SHOWEXTRA' => ( $extras != '' ) ? 1 : 0,
		) );
		
		$template->assign_switch( 'normal', TRUE );
		
		$basic_gui->add_file( 'uProfile' );
	}

	//
	// End of uProfile-gui class
	//
}


?>