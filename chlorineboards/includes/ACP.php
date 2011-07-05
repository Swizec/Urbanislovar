<?php

/**
*     defines the acp class
*     @file                acp.php
*     @see acp
*/
/**
* this is the ACP, yay
*     @class		   acp
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.2
*     @since        30th March 2006
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
// file_list :: list of ACP files
// classes :: list of classes

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'ACP', $vars, $visible ) );
// end class creation

class acp extends ACP_def
{
	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function ACP( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $template;
		
		$this->debug = $debug;
		
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP' );
		
		$this->file_list = array();
		
		// so the page wouldn't by accident end up getting cached
		define( 'DONT_CACHE', TRUE );
		
		// the compiler causes a nasty bug that prevents the panels from showing
		$template->compiler = 0;
	}
	/**
	* the main function that chooses what acp module to run and creates the whole interface
	*/
	function show_acp( )
	{
		global $errors, $userdata, $Cl_root_path, $cache, $template, $basic_gui;
		
		// first the utmost important check
		if ( $userdata[ 'user_level' ] != ADMIN )
		{
			$errors->report_error( $this->lang[ 'No_admin' ], CRITICAL_ERROR );
		}
		
		// now scan for the files
		if ( !$this->file_list = $cache->pull( 'ACP_file_list' ) )
		{ // not in cache
			$dir = $Cl_root_path . 'includes/ACP';
			$d = dir( $dir );
			while( FALSE !== ( $entry = $d->read() ) )
			{
				if ( substr( $entry, 0, 4 ) != 'ACP_' || substr( $entry, -strlen( phpEx ) ) != phpEx )
				{ // not ACP file
					continue;
				}
				$this->file_list[] = $entry;
			}
			$d->close();
			// store it
			$cache->push( 'ACP_file_list', $this->file_list, TRUE );
		}
		
		// now include and instantiate the classes
		if ( is_array( $this->file_list ) )
		{
			foreach ( $this->file_list as $file )
			{
				$dir = $Cl_root_path . 'includes/ACP/';
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
				'ACP' => 'ACP/index' . tplEx
			) );
			
			// this will have to set up the file for inclusion
			$this->classes[ $submode ]->show_panel();
			
			// vars
			$template->assign_block_vars( 'acppanel', '', array(
				'PANEL' => $submode,
			) );
			$template->assign_switch( 'acppanel', TRUE );
			
			// add it to the output
			$basic_gui->add_file( 'ACP' );
			
			// don't know exactly what happened or will happen but to be
			// on the safe side and to save the panels themselves some work
			// we empty the page cache here
			$basic_gui->remove_cached_pages();
		}
	}
	/**
	* a very simple "acp module" that just displays the index
	*/
	function show_index()
	{
		global $template, $basic_gui;
		
		// fire up the template
		$template->assign_files( array(
			'ACP' => 'ACP/index' . tplEx
		) );
		
		// vars
		$template->assign_block_vars( 'ACPindex', '', array(
			'HELLO' => $this->lang[ 'Hello' ],
		) );
		$template->assign_switch( 'ACPindex', TRUE );
		
		// add it to the output
		$basic_gui->add_file( 'ACP' );
	}
	
	
	//
	// End of ACP class
	//
}


?>