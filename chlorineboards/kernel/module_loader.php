<?php

/**
*     defines the Module_loader class
*     @file                module_loader.php
*     @see Module_loader
*/
/**
* manages the loading of modules
*     @class		   Module_loader
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.5.10
*     @since        18th June 2005
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
// modules :: list of loaded modules
// methods :: list of functions from modules
// module_hash :: list of al the data about the modules
// idhash :: quickly fetchable module ids and parents
// variables :: ported variables

// class creation
$vars = array( 'debug', 'modules', 'methods', 'module_hash' );
$visible = array( 'private', 'public', 'public', 'public' );
eval( Varloader::createclass( 'module_loader', $vars, $visible ) );
// end class creation

class Module_loader extends module_loader_def
{

	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function Module_loader( $debug = FALSE )
	{
		global $errors, $cache;
		
		// set the debug flag
		$this->debug = $debug;
		
		// try reading from cache
		$errors->debug_info( $debug, 'Module loader', 'constructor', 'Reading loaded modules from cache' );
			
		// if it wasn't read set it to null
		if ( !$arry = $cache->pull( 'modules_array' ) )
		{
			$this->modules = array();
			$this->methods = array();
			$this->module_hash = array();
			$this->idhash = array();
			$this->variables = array();
		}else
		{
			$this->modules = $arry[ 'modules' ];
			$this->methods = $arry[ 'methods' ];
			$this->module_hash = $arry[ 'module_hash' ];
			$this->idhash = $arry[ 'idhash' ];
			$this->variables = $arry[ 'variables' ];
		}
	}
	/**
	* retrieves a list of function calls from a file
	* @usage $methods = getmethods( $Cl_root_path . '/file.php', 'fileclass' );
	* @param string $file path to the module file
	*/
	function getmethods( $file )
	{
		global $cache, $errors, $phpversion;
		
		$errors->debug_info( $this->debug, 'Module loader', 'getmethods', 'Fetching methods from ' . $file );
		// get raw function definitions
		$code = $cache->get_file( $file );
		if ( empty( $code ) )
		{
			$errors->report_error( 'File could not be loaded', GENERAL_ERROR, 'Module loader', 'getmethods', __LINE__ );
			return FALSE;
		}
		preg_match_all( "/function (\S+)\(.*?\)/", $code, $matches );
		$funcnames = $matches[ 1 ];
		$funcs = $matches[ 0 ];
		// clean up
		while ( list( $key ) = each( $funcs ) )
		{
			$funcs[ $key ] = str_replace( 'function ', '', $funcs[ $key ] );
			$funcs[ $key ] = preg_replace( "/\s+=\s+([^?].*?)*([ ,\)])/", '$2', $funcs[ $key ] );
		}
		
		if ( count( $funcs ) == 0 || count( $funcnames ) == 0 )
		{
			$errors->debug_info( $this->debug, 'Module loader', 'getmethods', 'No methods in the file' );
			return FALSE;
		}else
		{
			// array_combine only works for php5 :(
			if ( $phpversion >= 5.0 )
			{
				return array_combine( $funcnames, $funcs );
			}else
			{
				// this is probably slower, but works for php4
				$out = array();
				foreach( $funcnames as $i => $name )
				{
					$out[ $name ] = $funcs[ $i ];
				}
				return $out;
			}
		}
		
	}
	/**
	* used to load up a module
	* @usage $mod_loader->getmodule( 'forums', MOD_FETCH_NAME, NOT_ESSENTIAL );
	* @usage $mod_loader->getmodule( 1, MOD_FETCH_ID, NOT_ESSENTIAL );
	* @usage $mod_loader->getmodule( 1, MOD_FETCH_PARENT, NOT_ESSENTIAL );
	* @param string $module name, id, parent or methods of the module; depending on the method
	* @param string $method either of the constants MOD_FETCH_NAME, MOD_FETCH_ID, MOD_FETCH_PARENT, MOD_FETCH_MODE
	* @param bool $essential either of the constants ESSENTIAL or NOT_ESSENTIAL
	* @param bool $debug debugging for this module is on or off
	* @return bool/integer true or false on success, on complete success the number of fetched modules
	*/
	function getmodule( $module, $method = MOD_FETCH_ID, $essential = ESSENTIAL, $debug = FALSE )
	{
		global $Cl_root_path, $db, $errors, $cache;
		
		$errors->debug_info( $this->debug, 'Module loader', 'getmodule', 'Loading module ' . $module );
		
		// don't bother if we already know it's already loaded
		if ( $method == MOD_FETCH_NAME && in_array( $module, array_keys( $this->modules ) ) )
		{
			$errors->debug_info( $this->debug, 'Module loader', 'getmodule', 'Module already loaded' );
			return TRUE;
		}
		// get module file
		switch ( $method )
		{
			// make sql statement
			case MOD_FETCH_NAME:
				$sql = "SELECT * FROM " . MODULES_TABLE . " WHERE mod_name='$module'";
			case MOD_FETCH_ID:
				$sql = "SELECT * FROM " . MODULES_TABLE . " WHERE mod_id='$module'";
			case MOD_FETCH_PARENT:
				$sql = "SELECT * FROM " . MODULES_TABLE . " WHERE mod_parent LIKE '%$module%'";
			case MOD_FETCH_MODE:
				$sql = "SELECT * FROM " . MODULES_TABLE . " WHERE mod_methods LIKE '%$module->%'";
			case MOD_FETCH_NAME:
			case MOD_FETCH_ID:
			case MOD_FETCH_PARENT:
			case MOD_FETCH_MODE:
				// act upon the statement
				$result = $db->sql_query( $sql );
				if ( !$result )
				{
					$errors->report_error( 'Unable to read module data', GENERAL_ERROR, 'Module loader', 'getmodule' );
				}
				// get the number of loaded modules
				$modulesnum = $db->sql_numrows( $result );
				if ( $modulesnum == 0 && $essential == ESSENTIAL)
				{
					$errors->report_error( 'Module ' . $module . ' not found with method ' . $method, GENERAL_ERROR, 'Module loader', 'getmodule' );
					return FALSE;
				}elseif( $modulesnum == 0 )
				{ // not essential and nothing found, don't bother with the rest
					return 0;
				}
				// put it in an array
				$module = array();
				$module = $db->sql_fetchrowset( $result );
				break;
		}
		
		// include file or files and instantiate the classes
		foreach( $module as $mod )
		{
			$name = $mod[ 'mod_name' ];
			// check if already loaded
			if ( in_array( $name, array_keys( $this->modules ) ) )
			{	
				continue;
			}
			$file = $Cl_root_path . 'includes/' . $name . phpEx;
			// check if file readable
			if ( is_readable( $file ) )
			{ // is
				include( $file );
			}else
			{ // isn't
				$errors->report_error( 'Module file not found', GENERAL_ERROR, 'Module loader', 'getmodule' );
			}
// 			echo '<b>' . $name . '</b>';
			$GLOBALS[ $name ] = new $name( $debug ); // make it globally available
// 			print_R( $GLOBALS[ $name ] );
		
			// add to the list of loaded modules
			$this->modules[ $name ] = ${$name};
			// get the functions
			$methods = $this->getmethods( $file );
			// add to methods list
			$this->methods[ $name ] = $methods;
			
			// add to hash
			$parents = explode( ';', $mod[ 'mod_parent' ] );
			foreach ( $parents as $p )
			{
				// mroe than one parent
				if ( strpos( $p, ',' ) !== FALSE )
				{ // aye
					$ps = explode( ',', $p );
					foreach ( $ps as $p )
					{
						$this->module_hash[ $p ][ $name ] = $mod;
					}
				}else
				{ // nay
					$this->module_hash[ $p ][ $name ] = $mod;
				}
			}
			// add to idhash
			$this->idhash[ $name ][ 'id' ] = $mod[ 'mod_id' ];
			$this->idhash[ $name ][ 'parent' ] = explode( ',', $mod[ 'mod_parent' ] );
		}	
		
		$errors->debug_info( $this->debug, 'Module loader', 'getmodule', 'Updating module cache' );
		
		// check if everything loaded well
		// for arrayed loading
		foreach ( $module as $mod )
		{
			$name = $mod[ 'mod_name' ];
			if ( !in_array( $name, array_keys( $this->modules ) ) )
			{
				$errors->debug_info( $this->debug, 'Module loader', 'getmodule', 'Module ' . $name . ' not loaded' );
				return FALSE;
			}
		}
		// update the cache
		$this->_store();
		
		$errors->debug_info( $this->debug, 'Module loader', 'getmodule', 'Module ' . $name . ' loaded fine' );
		// got here, everything must've been ok
		return $modulesnum;
	}
	/**
	* executes the methods each loaded module demands for a certain mode
	* @usage $mod_loader->execute_modules( $parent, 'index' );
	* @param integer $parent the parent id
	* @param string $mode the execution mode/event
	*/
	function execute_modules( $parent, $mode )
	{
		global $errors;
		
		$execute = array();
		
		$errors->debug_info( $this->debug, 'Module loader', 'execute_modules', 'Executing modules for module with id ' . $parent . ' on ' . $mode );
		
		// leave if no modules to execute
		if ( empty( $this->module_hash[ $parent ] ) )
		{
			$errors->debug_info( $this->debug, 'Module loader', 'execute_modules', 'No modules atm' );
			return;
		}
		
		// go through every module with the needed parent and add the function to the execute list
		$errors->debug_info( $this->debug, 'Module loader', 'execute_modules', 'Fetching modules' );
		foreach ( $this->module_hash[ $parent ] as $mod )
		{
			// determine what to execute
			preg_match_all( "#(;\s*|^)$mode\s*\->\s*([^?].*?);#m", $mod[ 'mod_methods' ], $matches );
			
			// add to execution list
			foreach ( $matches[ 2 ] as $match )
			{
				$execute[] = $mod[ 'mod_name' ] . '->' . $match;
			}
		}

		// execute the needed functions
		$exec = '';
		$errors->debug_info( $this->debug, 'Module loader', 'execute_modules', 'Creating the execution code blob' );
		foreach ( $execute as $funct )
		{
			// separate class name from the function
			$funct = explode( '->', $funct );
			$class = $funct[ 0 ];
			$func = $funct[ 1 ];
			
			// make function call
			if ( !isset( $this->methods[ $class ][ $func ] ) )
			{
				$errors->report_error( 'method ' . $func . ' not in module ' . $class, CRITICAL_ERROR, 'Module loader', 'execute_modules', __LINE__ );
			}else
			{
				// make the call
				$func = $this->methods[ $class ][ $func ];
				// get the vars list
				preg_match_all( '/(\$\S*)[ =,\)]/', $func, $vars );
				foreach ( $vars[ 1 ] as $var )
				{
					// remove the comma or something left over from the preg_match
					$find = array( ' ', '=', ',', '\\', ')' );
					$var = str_replace( $find, ' ', $var );
					if ( strpos( $var, ' ' ) !== FALSE )
					{
						$var = substr( $var, 0, -1 );
					}
					// replace variable with variable value
					$func = str_replace( $var, '$this->variables[ \'' . $var . '\' ]', $func );
				}
				// compile the call and add it to the list
				$call = 'global $' . $class . ';$' . $class . '->' . $func . ';' . "\n";
				$exec .= $call;
			}
		}
		// execute what we've built up
		$errors->debug_info( $this->debug, 'Module loader', 'execute_modules', 'Executing code' );
		eval( $exec );
	}
	/**
	* fetches the modules id (to be used as parent and stuff)
	* @usage $parent = $mod_loader->get_id( 'forums' );
	* @param string $module name of module
	* @return integer the id of the module
	*/
	function get_id( $module )
	{
		global $errors;
		
		$errors->debug_info( $this->debug, 'Module loader', 'get_id', 'Fetching id for ' . $module );
		
		// just return the id
		return $this->idhash[ $module ][ 'id' ];
	}
	/**
	* fetches the modules parent id
	* @usage $mod_parent = $mod_loader->get_parent( 'threads', 0 );
	* @param string $module name of module
	* @param integer $parent_num the sequental number of the parent
	* @return integer parent id
	*/
	function get_parent( $module, $parent_num = 0 )
	{
		global $errors;
		
		$errors->debug_info( $this->debug, 'Module loader', 'get_parent', 'Fetching parent ' . $parent_num . ' for ' . $module );
		
		// return the correct parent
		return $this->idhash[ $module ][ 'parent' ][ $parent_num ];
	}
	/**
	* used to port variables to modules
	* @usage $mod_loader->port_vars( array( 'var1' => 'va11', 'var2' => 'val2' ) );
	* @param mixed $vars associative array of variables
	*/
	function port_vars( $vars )
	{
		global $errors, $cache;
		
		$errors->debug_info( $this->debug, 'Module loader', 'port_vars', 'Porting variables' );
		
		// because we don't wan't values disappearing we loop through this
		foreach ( $vars as $name => $val )
		{
			$this->variables[ '$' . $name ] = $val;
			$errors->debug_info( $this->debug, 'Module loader', 'port_vars', "Porting variable $name with value $val" );
		}
		// update the cache
		$this->_store();
	}
	/**
	* used to get back the ported vars
	* @usage $var = $mod_loader->get_vars( 'var' );
	* @usage $var = $mod_loader->get_vars( array( 'var1', 'var2' ) );
	* @param mixed $vars array of variable names to fetch
	* @return mixed associative array of variables
	*/
	function get_vars( $vars )
	{
		global $errors;
		
		// null the arry
		$arry = array();
		
		if ( is_array( $vars ) )
		{
			// loop through and fetch the vars
			foreach( $vars as $name )
			{
				$arry[ $name ] = $this->variables[ '$' . $name ];
			}
		}else
		{
			// normal fetch
			$arry = $this->variables[ '$' . $vars ];
		}
		
		return $arry;
	}
	/**
	* used to store to cache
	*/
	function _store()
	{
		global $cache;
		
		$arry = array();
		$arry[ 'modules' ] = $this->modules;
		$arry[ 'methods' ] = $this->methods;
		$arry[ 'module_hash' ] = $this->module_hash;
		$arry[ 'idhash' ] = $this->idhash;
		$arry[ 'variables' ] = $this->variables;
		$cache->push( 'modules_array', $arry, TRUE );
	}

	//
	// End of Module_loader class
	//
}

?>