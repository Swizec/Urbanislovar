<?php

/**
*     defines the Config class
*     @file                config.php
*     @see Config
*/
/**
* Manages all configuration things
*     @class		   Config
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.2.0
*     @since        8th June 2005
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

// class creation
$vars = array( 'debug' );
$visible = array( 'private' );
eval( Varloader::createclass( 'config', $vars, $visible ) );
// end class creation

class Config extends config_def
{
	/**
	* constructor
	* @usage $config_class = new Config( FALSE );
	* @param bool $debug debugging is on or off
	*/
	function Config( $debug = FALSE )
	{
		$this->debug = $debug;
	}
	/**
	* fetches config from the db
	* @usage $board_config = $config_class->get_config();
	* @return mixed associative config array
	*/
	function get_config( )
	{
		// get needed vars
		global $db, $db_data, $cache, $errors;
		
		// config empty
		$config = array();
		
		// get table prefix
		$prefix = $db_data[ 'table_prefix' ];
		
		$errors->debug_info( $this->debug, 'Config', 'get_config', 'Reading config from cache' );
		// try the cache for the config
		$configs = $cache->pull( 'board_config' );
	
		if ( empty( $configs ) )
		{
			$errors->debug_info( $this->debug, 'Config', 'get_config', 'Config was not in cache, reading from the db' );
			// get config from db
			$sql = 'SELECT * FROM ' . $prefix . 'config';
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not read the config', CRITICAL_ERROR, 'Config', 'get_config', __LINE__, ERROR_RAW );
			}
			$configs = $db->sql_fetchrowset( $result );
			// add to cache
			$cache->push( 'board_config', $configs, TRUE );
		}
		
		$errors->debug_info( $this->debug, 'Config', 'get_config', 'Making the defines and the config array' );
		// go through the fetched rows
		foreach ( $configs as $row )
		{
			$name = $row[ 'config_name' ]; // name
			$value = $row[ 'config_value' ]; // value
			$method = $row[ 'config_method' ]; // method of defining
			
			// parse $value against known variables
			$value = str_replace( '$prefix', $prefix, $value ); // table prefix
			
			// do as the method wishes
			if ( $method == 'define' )
			{ // variable needs to be defined
				define( $name, $value );
			}elseif( $method == 'normal' )
			{ // variable needs to be added to array
				$config[ $name ] = $value;
			}
		}
		
		// return config array
		return $config;
	}
	/**
	* adds a line to a config file or replaces a line in it
	* @param string $file file to edit
	* @param string $line line to add
	* @param string $replace if set the line will be replaced with this
	* @return bool TRUE on success, FALSE on failure
	* @usage $config_class->add_config( 'menu.cfg', 'exampleline' );
	*/
	function add_config( $file, $line, $replace = '' )
	{
		global $Cl_root_path;
		
		// prepare some vars
		$file = $Cl_root_path . 'kernel/config/' . $file;
		$modded = FALSE;
		
		if ( !is_writable( $file ) )
		{ // try making it readable
			if ( !@chmod( $file, 0644 ) )
			{ // couldn't
				return FALSE;
			}
			$modded = TRUE;
		}
		
		// read the file
		$contents = file_get_contents( $file );
		if ( empty( $replace ) )
		{ // not replaceing
			if ( strpos( $contents, $line ) === FALSE )
			{ // only add line if not already there
				$contents = str_replace( '?>', "$line\n\n?>", $contents );
			}else
			{ // why do anything?
				return TRUE;
			}
		}else
		{ // just replace eh
			$contents = str_replace( $line, $replace, $contents );
		}
		
		// write it all back
		if ( !$f = @fopen( $file, 'w' ) )
		{
			return FALSE;
		}
		if ( !@fwrite( $f, $contents ) )
		{
			return FALSE;
		}
		@fclose( $f );
		
		if ( $modded )
		{ // have to chmod back ya
			@chmod( $file, 0444 );
		}
		
		// cool, done
		return TRUE;
	}
	

	//
	// End of Config class
	//
}


?>