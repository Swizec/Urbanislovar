<?php

/**
*     defines the language class
*     @file                lang.php
*     @see Lang
*/
/**
* Language manager
*     @class		   Lang
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.4.1
*     @since        24th June 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
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
// entries :: entries for all the modules
// board_lang :: stores the current value for the language
// lang_list :: stores the list of available languages

// class creation
$vars = array( 'debug', 'entries', 'board_lang' );
$visible = array( 'private', 'public', 'public' );
eval( Varloader::createclass( 'lang', $vars, $visible ) );
// end class creation

class Lang extends lang_def
{
	/**
	* constructor
	* Also sets the running language and fetches the needed things
	* @usage $lang_loader = new Lang( FALSE );
	* @param bool $debug debugging on or off
	*/
	function Lang( $debug = FALSE )
	{
		global $cache, $userdata, $Cl_root_path, $board_config;
		
		$this->debug = $debug;
		$this->lang_list = array();
		
		$this->entries = array();
		
		// set the running language
		if( isset( $_GET[ 'lang' ] ) && !empty( $_GET[ 'lang' ] ) && is_dir( $Cl_root_path . 'language/' . $_GET[ 'lang' ] ) )
		{ // uri lang setting superseeds all
			$this->board_lang = $_GET[ 'lang' ];
			
		}elseif ( ( isset( $userdata[ 'user_lang' ] ) && !empty( $userdata[ 'user_lang' ] ) ) || $_GET[ 'lang' ] == 'browser' )
		{ // user has lang set
			$this->board_lang = $userdata[ 'user_lang' ];
		}elseif( isset( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) && !empty( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) )
		{ // user has a lang set in the browser so lets try and use it eh
			// it seems that some browsers (opera atleast) return an "array" of these, so yay, FF too...
			$langs = explode( ',', $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] );
			// got through the list
			foreach ( $langs as $lang )
			{
				// remove the ;q=0.6 thingos
				$lang = preg_replace( '#;.*#', '', $lang );
				// try it
				if ( is_dir( $Cl_root_path . 'language/' . $lang ) )
				{
					
					$this->board_lang = $lang;
					$this->fetch_cache();
					return;
				}
				// check for en-us stuff where en will do just fine
				if ( strpos( $lang, '-' ) !== FALSE )
				{
					$lang = explode( '-', $lang );
					
					if ( is_dir( $Cl_root_path . 'language/' . $lang[ 0 ] ) )
					{
						$this->board_lang = $lang[ 0 ];
						$this->fetch_cache();
						return;
					}
				}
				// oh poo, there's nothing that can be done here anymore
				$this->board_lang = $board_config[ 'def_lang' ];
			}
		}else
		{ // nope, gotta use the def
			$this->board_lang = $board_config[ 'def_lang' ];
		}
		$this->fetch_cache();
	}
	/**
	* loads the lang needed for the module
	* @usage $lang_loader->load_lang( 'forums' );
	* @param string $module name of the class
	*/
	function load_lang( $module )
	{
		global $errors, $cache, $Cl_root_path, $board_config;
		
		$errors->debug_info( $this->debug, 'Lang', 'load_lang', 'Fetching entries for ' . $module );
		// get the contents of the file
		$file = $Cl_root_path . 'language/' . $this->board_lang . '/lang_' . $module . phpEx;
		// include it
		include( $file );
		
		// eval got all the entries into a lovely $lang array, load it up
		$this->entries[ $module ] = $lang;
		
		$errors->debug_info( $this->debug, 'Lang', 'load_lang', 'Refreshing cache' );
		// refresh cache
		$cache->push( 'language_entries_' . $this->board_lang, $this->entries, TRUE );
	}
	/**
	* returns the lang array for the module
	* @usage $lang = $lang_loader->get_lang( $module );
	* @param string $module name of the module
	* @param string $template whether to also set it up as template variables
	* @return mixed associative array of language entries for the particular module
	*/
	function get_lang( $module, $template = FALSE )
	{
		global $errors;
		
		// check if this is already loaded
		if ( isset( $this->entries[ $module ] ) )
		{ // it is, return
			$errors->debug_info( $this->debug, 'Lang', 'get_lang', 'Entries for ' . $module . ' already loaded, returning' );
			
			if ( $template )
			{ // set template vars
				$this->template_lang( $module );
			}
			
			return $this->entries[ $module ];
		}else
		{
			$errors->debug_info( $this->debug, 'Lang', 'get_lang', 'Entries for ' . $module . ' not loaded, fetching and returning' );
			// fetch
			$this->load_lang( $module );
			
			if ( $template )
			{ // set template vars
				$this->template_lang( $module );
			}
			
			// return
			return $this->entries[ $module ];
		}
	}
	/**
	* used to retrieve a string conjugated for the number
	* @usage {FORUM} => $lang_loader->get_numbered( 'basic', 'forum', 1, NONCOUNTED );
	* @param string $module the module this is needed for
	* @param string $entry name of the entry in the lang array
	* @param integer $num the number the string should be moulded for
	* @param string $how either of the constants NONCOUNTED or COUNTED; this is not implemented yet
	* @return string the correct language entry
	*/
	function get_numbered( $module, $entry, $num, $how = NONCOUNTED )
	{
		global $errors;
		
		$errors->debug_info( $this->debug, 'Lang', 'get_numbered', 'Fetching ' . $module . '-' . $entry . ' for ' . $num );
		
		// just get the last number of num as more is not usually needed
		$num = intval( substr( $num, -1, 1 ) );
		
		// parse the number a bit
		if ( $num == 4 )
		{
			$num = 3;
		}
		if ( $num > 5 )
		{
			$num = 5;
		}
		
		// fetch
		return $this->entries[ $module ][ $entry . $num ];
	}
	/**
	* used to change the language setting
	* @usage $lang_loader->set_lang( $lang );
	* @param string $lang wanted language
	*/
	function set_lang( $lang )
	{
		global $errors;
		
		$errors->debug_info( $this->debug, 'Lang', 'set_lang', 'Setting board language to ' . $lang );
		
		$this->board_lang = $lang;
	}
	/**
	* used to get the path for a language dependant image
	* @usage $img = $lang_loader->get_langimg( 'but_newthread.png' )
	* @param string $name image file name
	* @return string file path
	*/
	function get_langimg( $name )
	{
		global $template, $errors, $board_config;
		
		$file = $template->folder . 'images/' . $this->board_lang . '/' . $name;
		
		$errors->debug_info( $this->debug, 'Lang', 'get_langimg', 'Fetching image file for ' . $name );
		
		// return the file if readable otherwise the default lang's version
		if ( is_readable( $file ) )
		{
			return $file;
		}else
		{
			return $template->folder . 'images/' . $board_config[ 'def_lang' ] . '/' . $name;
		}
	}
	/**
	* used to fetch the list of available languages
	* @usage $lang_arry = $lang_loader->get_langlist()
	* @return mixed array of available languages
	*/
	function get_langlist()
	{
		global $Cl_root_path, $cache;
		
		if ( empty( $this->lang_list ) )
		{ // don't have it yet
			if ( !$this->lang_list = $cache->pull( 'language_list' ) )
			{ // wasn't in the cache, read from disk
				$d = dir( $Cl_root_path . 'language' );
				while( FALSE !== ( $entry = $d->read() ) )
				{
					if ( $entry == '.' || $entry == '..' )
					{
						continue;
					}
					if ( is_dir( $Cl_root_path . 'language/' . $entry ) && is_readable( $Cl_root_path . 'language/' . $entry ) )
					{ // language is usable, store it
						$this->lang_list[] = $entry;
					}
				}
				$d->close();
				// store it
				$cache->push( 'language_list', $this->lang_list, TRUE );
			}
		}
		
		return $this->lang_list;
	}
	/**
	* internal function for fetching lang entries from the cache
	* @access private
	*/
	function fetch_cache()
	{
		global $cache;
		
		// try to load up from cache
		if ( !$this->entries = $cache->pull( 'language_entries_' . $this->board_lang ) )
		{
			$this->entries = array();
		}
	}
	/**
	* loads up language entries into template variables
	* @param string $module what module to load up, entries MUST be available
	* @return bool false on failure
	*/
	function template_lang( $module )
	{
		global $template;
		
		if ( !isset( $this->entries[ $module ] ) )
		{
			return FALSE;
		}
		
		$lang = $template->value( 'LANG' );
		$lang = ( $lang == '' ) ? array() : $lang;
		
		$mod = explode( '_', $module );
		$this->_Rarrbuild( $lang, $mod );
		
		foreach ( $this->entries[ $module ] as $key => $val )
		{
			$k = array_merge( $mod, explode( '_', $key ) );
			$this->_Rarrbuild( $lang, $k, $val );
		}
		
		$template->assign_vars( array( 'LANG' => $lang ) );
	}
	/**
	* recursively builds arrays
	* @param mixed $keys
	* @return mixed
	*/
	function _Rarrbuild( &$arr, $keys, $val = FALSE )
	{
		$k = strtoupper( array_shift( $keys ) );
		if ( count( $keys ) > 0 )
		{
			if ( !isset( $arr[ $k ] )  )
			{
				$arr[ $k ] = array();
			}
			$this->_Rarrbuild( $arr[ $k ], $keys, $val );
		}elseif ( $val )
		{
			if ( !isset( $arr[ $k ] )  )
			{
				$arr[ $k ] = $val;
			}
		}else
		{
			if ( !isset( $arr[ $k ] )  )
			{
				$arr[ $k ] = array();
			}
		}
	}

	//
	// End of Config class
	//
}


?>