<?php

/**
*     defines the ACP_advance class
*     @file                ACP_advance.php
*     @see ACP_advance
*/
/**
* ACP advanced settings :P
*     @class		   ACP_advance
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.2
*     @since        02nd April 2006
*     @package		     ClB_base
*     @subpackage	     ClB_ACP
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
eval( Varloader::createclass( 'ACP_advance', $vars, $visible ) );
// end class creation

class ACP_advance extends ACP_advance_def
{
	/**
	* constructor
	*/
	function ACP_advance(  )
	{
		global $Cl_root_path, $lang_loader, $basic_gui, $security;
			
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_advance' );
		
		// sidebar stuff :P
		$URL1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_advance&s=settings' );
		$URL2 = $security->append_sid( '?' . MODE_URL . '=filebrowser&' . SUBMODE_URL . '=wtf' );
		$URL3 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_advance&s=clearcache' );
		$URL4 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_advance&s=console' );
		$URL5 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_advance&s=key' );
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $URL1 . '">' . $this->lang[ 'Side_settings' ] . '</a><br /><a href="' . $URL2 . '">' . $this->lang[ 'Side_browser' ] . '</a><br /><a href="' . $URL3 . '">' . $this->lang[ 'Side_clear' ] . '</a><br /><a href="' . $URL4 . '">' . $this->lang[ 'Side_console' ] . '</a><br /><a href="' . $URL5 . '">' . $this->lang[ 'Side_key' ] . '</a></span>' );
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
			'ACP_advance' => 'ACP/advance' . tplEx
		) );
		
		// act upon it
		switch( $s )
		{
			case 'settings':
				$this->settings();
				break;
			case 'settings_real':
				$this->settings_real();
				break;
			case 'browser':
				$this->browser();
				break;
			case 'clearcache':
				$this->clearcache();
				break;
			case 'console':
				$this->console();
				break;
			case 'key':
				$this->key();
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* the settings panel
	*/
	function settings()
	{
		global $template, $board_config, $security;
		
		// teh template
		$template->assign_block_vars( 'settings', '', array(
			'L_TITLE' => $this->lang[ 'Sett_title' ],
			'L_EXPLAIN' => $this->lang[ 'Sett_explain' ],
			
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_advance&s=settings_real' )
		) );
		$template->assign_switch( 'settings', TRUE );
		
		// now go through the config and get the thingies
		foreach ( $board_config as $var => $val )
		{
			$template->assign_block_vars( 'confrow', '', array(
				'NAME' => $var,
				'VALUE' => $val,
				'TITLE' => ( isset( $this->lang[ $var ] ) ) ? $this->lang[ $var ] : $var,
			) );
			$template->assign_switch( 'confrow', TRUE );
		}
	}
	/**
	* performs the needed sql alterations for the changes to be made
	*/
	function settings_real()
	{
		global $errors, $db, $board_config, $cache;
		
		// basic check
		if ( !isset( $_POST[ 'submit_sett' ] ) )
		{
			$errrors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// this used to create a behemoth query that sometimes crashed servers
		// it has been decided that using several simpler queries is quicker
		foreach ( $board_config as $name => $value )
		{
			if ( isset( $_POST[ $name ] ) )
			{ // value was sent with the form
				$post = $_POST[ $name ];
				if ( $post != $value )
				{ // old and new values differ
					$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='$post' WHERE config_name='$name'";
					if ( !$db->sql_query( $sql ) )
					{
						$errors->report_error( 'Couldn\'t write to database', CRITICAL_ERROR );
					}
				}
			}
		}
		// got here, this means all went well
		// remove from cache
		$cache->delete( 'board_config' );
		
		$errors->report_error( $this->lang[ 'Sett_done' ], MESSAGE );
		return;
		
		// the old version remains for archival purposes for now and will be removed with time
		
		// now build the query
		// do the same table is done as many in order to use only one query
		$tables = array();
		$wheres = array();
		$sets = array();
		$i = 0;
		// loop
		foreach ( $board_config as $name => $void )
		{
			if ( isset( $_POST[ $name ] ) )
			{
				$post = $_POST[ $name ];
				$t = 'c' . $i;
				$tables[] = CONFIG_TABLE . ' ' . $t;
				$wheres[] = "$t.config_name='$name'";
				$sets[] = "$t.config_value='$post'";
				$i++;
			}
		}
		// make the query
		$sql = "UPDATE " . implode( ', ', $tables ) . " SET " . implode( ', ', $sets ) . " WHERE " . implode( ' AND ', $wheres );
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t write to database', CRITICAL_ERROR );
		}
		
		// remove from cache
		$cache->delete( 'board_config' );
		
		$errors->report_error( $this->lang[ 'Sett_done' ], MESSAGE );
	}
	/**
	* the file browser panel
	*/
	function browser()
	{
		global $template, $Sajax, $basic_gui, $mod_loader;
		
		// basic thingies for the template
		$template->assign_block_vars( 'browser', '', array(
			'L_TITLE' => $this->lang[ 'Browser_title' ],
			'L_EXPLAIN' => $this->lang[ 'Browser_explain' ],
		) );
		$template->assign_switch( 'browser', TRUE );
		
		// get the browser
		$mods = $mod_loader->getmodule( 'filebrowser', MOD_FETCH_NAME, NOT_ESSENTIAL );
// 		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => FALSE, 'def_text' => stripslashes( $content ) ) );
		$mod_loader->execute_modules( 0, 'filebrowser' );
// 		$browser = $mod_loader->get_vars( array( 'bla' ) );
		
// 		print_R( $browser );
		
		// the sajax stuff
// 		$GLOBALS[ 'acp_advance' ] = &$this;
// // 		print_R( $GLOBALS );die();
// 		$Sajax->add2export( 'ACP_advance->get_tree', '$folder' );
// 		
// 		// the JS
// 		$basic_gui->add_JS( 'includes/ACP/browser.js' );
	}
	/**
	* clears the cache
	*/
	function clearcache()
	{
		global $Cl_root_path, $errors, $cache;
		
		// remove the cache that is operated propelry
		// this will remove stuff done with diskcache and essential stuff too
		foreach( $cache->variables as $var )
		{
			$cache->delete( $var );
		}
		
		// this is here to empty the directory in case it was operated directly
		$d = dir( $Cl_root_path . 'cache/' );
		while ( FALSE !== ( $entry = $d->read( ) ) )
		{
			if ( $entry == '.' || $entry == '..' )
			{
				continue;
			}
			// remove it
			if ( !@unlink( $Cl_root_path . 'cache/' . $entry ) )
			{ // failed
				$errors->report_error( $this->lang[ 'No_clear' ], GENERAL_ERROR );
			}
		}
		
		$errors->report_error( $this->lang[ 'Cleared' ], MESSAGE );
	}
	
	/**
	* shows an iframe with the console neatly open inside, out of safety reasons the login still has to be performed
	*/
	function console()
	{
		global $Cl_root_path, $errors, $template, $basic_gui;
		
		// do a pre-check if the console exists
		if ( !is_readable( $Cl_root_path . 'console/' ) )
		{
			$errors->report_error( $this->lang[ 'No_console' ], CRITICAL_ERROR );
		}
		// now check for the mykey.dat file
		if ( !is_readable( $Cl_root_path . 'console/config/mykey.dat' ) )
		{
			$errors->report_error( $this->lang[ 'No_mykey' ], CRITICAL_ERROR );
		}
		
		// now show the thingy
		$template->assign_block_vars( 'console', '', array(
				'L_TITLE' => $this->lang[ 'Console_title' ],
				'L_EXPLAIN' => $this->lang[ 'Console_explain' ],
				'U_FRAME' => $basic_gui->get_URL() . '/console/'
			) );
		$template->assign_switch( 'console', TRUE );
	}
	
	/**
	* the interface for uploading and removing of mykey.dat
	*/
	function key()
	{
		global $Cl_root_path, $errors, $template, $security;
		
		$status = '';
		
		if ( isset( $_POST[ 'UL' ] ) )
		{ // an upload has happened
			$err = $_FILES[ 'key' ][ 'error' ];
			if ( $err != 0 )
			{
				$status = '<span style="color: red">' . $this->lang[ 'Key_err' . $err ] . '</span>';
			}else
			{
				if ( @move_uploaded_file( $_FILES[ 'key' ][ 'tmp_name' ], $Cl_root_path . 'console/config/mykey.dat' ) )
				{
					$status = '<span style="color: green">' . $this->lang[ 'Key_uploaded' ] . '</span>';
				}else
				{
					$status = '<span style="color: red">' . $this->lang[ 'Key_errx' ] . '</span>';
				}
			}
		}elseif ( isset( $_POST[ 'DL' ] ) )
		{
			// have to send the raw file to the user
			if ( is_readable( $Cl_root_path . 'console/config/mykey.dat' ) )
			{
				ob_clean();
				header( 'Content-type: file/text' );
				header ( 'Content-Disposition: attachment; filename="mykey.dat"' );
				echo @file_get_contents( $Cl_root_path . 'console/config/mykey.dat' );
				exit;
			}else
			{
				$status = '<span style="color: red">' . $this->lang[ 'Key_noread' ] . '</span>';
			}
		}elseif ( isset( $_POST[ 'RM' ] ) )
		{
			if ( !@file_exists( $Cl_root_path . 'console/config/mykey.dat' ) )
			{
				$status = '<span style="color: green">' . $this->lang[ 'Key_removed' ] . '</span>';
			}elseif ( @unlink( $Cl_root_path . 'console/config/mykey.dat' ) )
			{
				$status = '<span style="color: green">' . $this->lang[ 'Key_removed' ] . '</span>';
			}else
			{
				$status = '<span style="color: red">' . $this->lang[ 'Key_notremoved' ] . '</span>';
			}
		}
		
		
		// show it
		$template->assign_block_vars( 'key', '', array(
				'L_TITLE' => $this->lang[ 'Key_title' ],
				'L_EXPLAIN' => $this->lang[ 'Key_explain' ],
				'L_KEY' => $this->lang[ 'Key_ulfield' ],
				'L_UPLOAD' => $this->lang[ 'Key_upload' ],
				'L_REMOVE' => $this->lang[ 'Key_remove' ],
				'L_DOWNLOAD' => $this->lang[ 'Key_download' ],
				
				'S_FRAME_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_advance&s=key' ),
				
				'STATUS' => $status
			) );
		$template->assign_switch( 'key', TRUE );
	}
	
	//
	// End of ACP_advance class
	//
}


?>