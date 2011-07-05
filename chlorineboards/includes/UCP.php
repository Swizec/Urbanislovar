<?php

/**
*     defines the ucp class
*     @file                ucp.php
*     @see ucp
*/
/**
* this is the UCP, yay
* actually just a copy of the ACP with some minor changes
*     @class		   ucp
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        14th June 2006
*     @package		     ClB_base
*     @subpackage	     ClB_UCP
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
// file_list :: list of ACP files
// classes :: list of classes

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'UCP', $vars, $visible ) );
// end class creation

class ucp extends UCP_def
{
	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function UCP( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader;
		
		$this->debug = $debug;
		
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'UCP' );
		
		$this->file_list = array();
		
		// so the page wouldn't by accident end up getting cached
		define( 'DONT_CACHE', TRUE );
	}
	/**
	* the main function that chooses what ucp module to run and creates the whole interface
	*/
	function show_ucp( )
	{
		global $errors, $userdata, $Cl_root_path, $cache, $template, $basic_gui;
		
		// first the utmost important check (if rights are less than 0)
		if ( $userdata[ 'user_level' ] < ADMIN )
		{
			$errors->report_error( $this->lang[ 'No_admin' ], CRITICAL_ERROR );
		}
		
		// now scan for the files
		if ( !$this->file_list = $cache->pull( 'UCP_file_list' ) )
		{ // not in cache
			$dir = $Cl_root_path . 'includes/UCP';
			$d = dir( $dir );
			while( FALSE !== ( $entry = $d->read() ) )
			{
				if ( substr( $entry, 0, 4 ) != 'UCP_' || substr( $entry, -strlen( phpEx ) ) != phpEx )
				{ // not ACP file
					continue;
				}
				$this->file_list[] = $entry;
			}
			$d->close();
			// store it
			$cache->push( 'UCP_file_list', $this->file_list, TRUE );
		}
		
		// now include and instantiate the classes
		if ( is_array( $this->file_list ) )
		{
			foreach ( $this->file_list as $file )
			{
				$dir = $Cl_root_path . 'includes/UCP/';
				// include it
				include( $dir . $file );
				// instantiate
				$name = str_replace( phpEx, '', $file );
				$this->classes[ $name ] = new $name;
			}
		}
		
		// get the submode
		$submode = ( isset( $_GET[ SUBMODE_URL ] ) ) ? strval( $_GET[ SUBMODE_URL ] ) : '';
		
		if ( empty( $submode ) )
		{
			// the nice index ^^
			$this->show_index();
		}else
		{
			// start the thingy
			$template->assign_files( array(
				'UCP' => 'UCP/index' . tplEx
			) );
			
			// this will have to set up the file for inclusion
			$this->classes[ $submode ]->show_panel();
			
			// vars
			$template->assign_block_vars( 'ucppanel', '', array(
				'PANEL' => $submode,
			) );
			$template->assign_switch( 'ucppanel', TRUE );
			
			// add it to the output
			$basic_gui->add_file( 'UCP' );
			
			// don't know exactly what happened or will happen but to be
			// on the safe side and to save the panels themselves some work
			// we empty the page cache here
			$basic_gui->remove_cached_pages();
		}
	}
	/**
	* a very simple "ucp module" that just displays the index
	*/
	function show_index()
	{
		global $template, $basic_gui;
		
		// fire up the template
		$template->assign_files( array(
			'UCP' => 'UCP/index' . tplEx
		) );
		
		// vars
		$template->assign_block_vars( 'UCPindex', '', array(
			'HELLO' => $this->lang[ 'Hello' ],
		) );
		$template->assign_switch( 'UCPindex', TRUE );
		
		// add it to the output
		$basic_gui->add_file( 'UCP' );
	}
	
	
	//
	// End of UCP class
	//
}


?>