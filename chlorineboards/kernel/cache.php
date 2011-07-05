<?php

/**
*     defines the cache class
*     @file                cache.php
*     @see cache
*/
/**
* This is a frontend for memcached (and the diskcache) used by the Chlorine Boards
*     @class		   Diskcache
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.11.2
*     @since        17th May 2005
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

// vars explanation
// executed :: list of php's already included
// backend :: the backend (the thing directly dealing with the servers)
// con_id :: unique id of this connection, used to enable more boards to use the same server
// debug :: debug mode flag
// enabled :: flag to tell us if caching is wanted
// type :: what type of cache is used

// create this class
$vars = array( 'executed', 'backend', 'con_id', 'debug', 'enabled', 'variables' );
$visible = array( 'private', 'private', 'private', 'private', 'private', 'public' );
eval( Varloader::createclass( 'cache', $vars, $visible ) );
// end class creation

class cache extends cache_def
{	
	/**
	* this creates an object of this class
	* @usage $cache = new cache( FALSE );
	* @param bool $debug debugging on or off
	*/
	function cache( $debug = FALSE ) 
	{
		// get some global vars
		global $Cl_root_path, $errors;
		
		// no files executed yet
		$this->executed = array( );
		
		// save the debug flag
		$this->debug = $debug;
		
		/**
		* get cache specific configuration
		*/
		include( $Cl_root_path . 'kernel/config/cache_config' . phpEx );
		$this->con_id = $cache_config[ 'prefix' ];
		$this->enabled = $cache_config[ 'enabled' ];
		$this->type = $cache_config[ 'type' ];
		
		// create a new object of class memcached
		if ( $this->enabled && $cache_config[ 'type' ] == 'mem' )
		{
			/**
			* get the backend for memory cache
			*/
			include( $Cl_root_path . 'kernel/memcache' . phpEx );
			$errors->debug_info( $debug, 'Cache', 'constructor', 'Connecting to cache' );
 			$this->backend = new memcached( array( 
				'servers' => $cache_config[ 'ip' ], 
				'debug' => FALSE, 
				'compress_threshold' => $cache_config[ 'compress_treshold' ], 
				'persistant' => $cache_config[ 'persistant' ]
			) );
			// check if connected
			$this->backend->add( 'test', 'ClB cache connection check' );
			if ( !empty( $this->backend->_cache_sock ) )
			{
				$errors->debug_info( $debug, 'Cache', 'constructor', 'Cache connected' );
			}else
			{
				$errors->debug_info( $debug, 'Cache', 'constructor', 'Cache not connected using diskcache' );
				/**
				* get the backend for disk cache
				*/
				include( $Cl_root_path . 'kernel/diskcache' . phpEx );
				$this->backend = new Diskcache( $Cl_root_path . 'cache/' );
				$this->con_id = ''; // no need for this now
				$this->type = 'disk'; // this is what we use
			}
		}elseif( $this->enabled && $cache_config[ 'type' ] == 'disk' )
		{
			$errors->debug_info( $debug, 'Cache', 'constructor', 'Instantiating diskcache' );
			/**
			* get the backend for disk cache
			*/
			include( $Cl_root_path . 'kernel/diskcache' . phpEx );
			$this->backend = new Diskcache( $Cl_root_path . 'cache/' );
			$this->con_id = ''; // no need for this now
		}else
		{
			$errors->debug_info( $debug, 'Cache', 'constructor', '<b>Warning: </b>Cache disabled' );
		}
		
		// get the variables, what a cheap trick
		if ( !$this->variables = $this->pull( 'cache_stored_vars' ) )
		{
			$this->variables = array();
		}
	}	
	/**
	* used for reading a file, for instance a template one
	* @usage $file = $cache->get_file( 'file.tpl' );
	* @usage $files = $cache->get_file( array( 'file1.tpl', 'file2.txt' ) );
	* @param mixed $files string or array of string of filenames
	* @return mixed file(s) contents
	*/
	function get_file( $files ) 
	{
		global $errors;
		
		// just to be safe
		$content = '';
		$out = array( );
		
		// change to array if not already
		if ( !is_array( $files ) )
		{
			$files = array( $files );
		}
		
		// go through the list
		foreach( $files as $file )
		{
			// attach con_id to the mem key
			$key = $this->con_id . $file;
			// try to fetch from cache
			if ( $this->enabled )
			{
				$content = $this->backend->get( $key );
			}
			if ( !$content )
			{
				// debug info
				$errors->debug_info( $this->debug, 'Cache', 'get_file', 'file ' . $file . 'not cached, reading from disk' );
				// get from disk and add to cache
				if ( is_readable( $file ) )
				{
					if ( $content = @file_get_contents( $file ) )
					{
						// debug info
						$errors->debug_info( $this->debug, 'Cache', 'get_file', 'adding file ' . $file . 'to cache' );
						if ( $this->enabled )
						{
							$this->backend->add( $key, $content );
							$this->backend->add( 'time_' . $key, time( ) );
						}
					}else
					{
						// error if file couldn't be loaded
						$errors->report_error( 'File ' . $file . ' couldn\'t be opened: ' . $file, GENERAL_ERROR, 'Cache', 'get_file', __LINE__, ERROR_RAW );
					}
				}else
				{
					// error if file couldn't be loaded
					$errors->report_error( 'File ' . $file . ' couldn\'t be read: ' . $file, GENERAL_ERROR, 'Cache', 'get_file', __LINE__, ERROR_RAW );
				}
			}else
			{
				// debug info
				$errors->debug_info( $this->debug, 'Cache', 'get_file', 'file ' . $file . ' is cached, reading from cache' );
				// if not readable just use the cached version
				if ( is_readable( $file ) )
				{
					// get the time when it was added to the cache
					$time = $this->backend->get( 'time_' . $key );
				
					// get the time of last file modify
					$time2 = filemtime( $file );
					
					// if modified later than added to cache renew cache
					if ( $time2 > $time )
					{
						// debug info
						$errors->debug_info( $this->debug, 'Cache', 'get_file', 'file ' . $file . ' has changed, reading from disk and caching' );
						if ( $content = @file_get_contents( $file ) )
						{
							if ( $this->enabled )
							{
								$this->backend->add( $key, $content );
								$this->backend->add( 'time_' . $key, time( ) );
							}
						}else
						{
							$errors->debug_info( $this->debug, 'Cache', 'get_file', 'file ' . $file . ' was unreadable, using old version' );
						}
					}
				}
			}
		
			// give the user what they want
			$out[] = $content;
		}
		
		// return as string if only one output
		if ( count( $out ) == 1 ) $out = $out[ 0 ];
		
		return $out;
	}
	/**
	* used to put data on cache (transparently deals with replaces)
	* @usage $cache->push( 'var', 'data' );
	* @usage $cache->push( array( 'var1', 'var2' ), array( 'data1', 'data2' ), FALSE );
	* @param mixed $keys name or array of names for the stored value
	* @param mixed $data value or array of values to store
	* @param bool $array the value is an array?
	* @param bool $essential either of ESSENTIAL or NOT_ESSENTIAL
	* @param bool $dontstore mark it in the list of stored values
	*/
	function push( $keys, $datas, $array = FALSE, $essential = NOT_ESSENTIAL, $dontstore = FALSE )
	{
		global $errors;
		
		// we still need to add this, and when using diskcache because of an unknown bug
		if ( $essential == ESSENTIAL && !$this->enabled )
		{
			$errors->debug_info( $this->debug, 'Cache', 'push', 'adding variable ' . $keys . ' <-- ' . $datas . ' to diskcache' );
			$this->urgent_add( $keys, $datas );
			return;
		}
		
		// cache not enabled, leave
		if ( !$this->enabled )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'push', 'cache not enabled' );
			return;
		}
		// change to array if not already
		if ( !is_array( $keys ) )
		{
			$keys = array( $keys );
		}
		// enables us to push arrays to cache
		if ( !$array )
		{
			if ( !is_array( $datas ) )
			{
				$datas = array( $datas );
			}
			// check if arguments are right
			if ( count( $keys ) != count( $datas ) )
			{
				// error if file couldn't be loaded
				$errors->report_error( 'Wrong parameter count', CRITICAL_ERROR, 'Cache', 'push', __LINE__, ERROR_RAW );
			}
		}else
		{
			$datas = array( $datas );
		}
	
		foreach ( $keys as $i => $key )
		{
			// add it to the variables list
			$this->variables[ $key ] = $key;
			
			// attach con_id to the mem key
			$key = $this->con_id . $key;
			
			$data = $datas[ $i ];
			
			// check if key is in use
			if ( $replaced = $this->backend->get( $key ) )
			{
				// debug info
				$errors->debug_info( $this->debug, 'replacing variable ' . $key . ' - ' . $replaced . ' with ' . $data, 'Cache', 'push' );
				// replace
				$this->backend->replace( $key, $data );
			}else
			{
				// debug info
				$errors->debug_info( $this->debug, 'Cache', 'push', 'adding variable ' . $key . ' <-- ' . $data );
				// normal add
				$this->backend->add( $key, $data );
			}
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'push', 'added or replaced variable ' . $key );
		}
		
		// now refresh it no cache
		if ( !$dontstore )
		{
			$this->push( 'cache_stored_vars', $this->variables, TRUE, NOT_ESSENTIAL, TRUE );
		}
	}
	/**
	* used to get data from the cache
	* @usage $var = $cache->pull( 'var' );
	* @usage $vars = $cache->pull( array( 'var1', 'var2' ) );
	* @param mixed $keys name or array of names of the stored value
	* @param bool $essential either of ESSENTIAL or NOT_ESSENTIAL
	* @return mixed the value from cache
	*/
	function pull( $keys, $essential = NOT_ESSENTIAL )
	{
		global $errors;
		
		// we still need this and when using diskcache from an unknown bug
		if ( $essential == ESSENTIAL && !$this->enabled )
		{
			$errors->debug_info( $this->debug, 'Cache', 'pull', 'reading variable ' . $keys . ' from diskcache' );
			$data = $this->urgent_get( $keys );
			return $data;
		}
		
		// cache not enabled, leave
		if ( !$this->enabled )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'pull', 'cache not enabled' );
			return;
		}
		$data = array( );
		
		// change to array if not already
		if ( !is_array( $keys ) )
		{
			$keys = array( $keys );
		}
		
		foreach ( $keys as $key )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'pull', 'reading variable ' . $key );
			// attach con_id to the mem key
			$key = $this->con_id . $key;
			
			$data[] = $this->backend->get( $key );
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'pull', 'read variable ' . $key . ' <-- ' . end( $data ) );
		}
		
		// return as string if only one output
		if ( count( $data ) == 1 ) $data = $data[ 0 ];
		
		return $data;
	}
	/**
	* used for decrimenting values in cache
	* @usage $var = $cache->decr( 'var', '1' );
	* @usage $var = $cache->decr( array( 'var1', 'var2' ), array( '1', '3' ) );
	* @param mixed $keys name or array of names of the stored value
	* @param mixed $amts amount or array of amounts to decrease for
	*/
	function decr ( $keys, $amts=1 )
	{
		global $errors;
		
		// cache not enabled, leave
		if ( !$this->enabled )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'decr', 'cache not enabled' );
			return;
		}
		// to be safe
		$out = array( );
	
		// lets be able to decrease more at once
		if ( !is_array( $keys ) )
		{
			$keys = array( $keys );
		}
		if ( !is_array( $amts ) )
		{
			$datas = array( $datas );
		}
		// check if arguments are right
		if ( count( $amts ) != 0 && ( count( $keys ) != count( $amts ) ) )
		{
			// error if file couldn't be loaded
			$errors->report_error( 'Wrong parameter count', CRITICAL_ERROR, 'Cache', 'decr', __LINE__, ERROR_RAW );
		}
		
		// go through the keys and decrease the values
		foreach ( $keys as $i => $key )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'decr', 'decreasing variable ' . $key . ' for ' . $amts[ $i ] . ' from ' . $this->backend->get( $key ) );
			// attach con_id to the mem key
			$key = $this->con_id . $key;
		
			$amt = $amts[ $i ];
			$out[] = $this->backend->decr( $key, $amt );
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'decr', 'decreased variable ' . $key . ' <-- ' . $this->backend->get( $key ) );
		}
		
		return $out;
	}
	/**
	* used for incrementing values in cache
	* @usage $var = $cache->incr( 'var', '1' );
	* @usage $var = $cache->incr( array( 'var1', 'var2' ), array( '1', '3' ) );
	* @param mixed $keys name or array of names of the stored value
	* @param mixed $amts amount or array of amounts to increase for
	*/
	function incr ( $keys, $amts = 1 )
	{
		global $errors;
		
		// cache not enabled, leave
		if ( !$this->enabled )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'incr', 'cache not enabled' );
			return;
		}
		// to be safe
		$out = array( );
	
		// lets be able to increase more at once
		if ( !is_array( $keys ) )
		{
			$keys = array( $keys );
		}
		if ( !is_array( $amts ) )
		{
			$datas = array( $datas );
		}
		// check if arguments are right
		if ( count( $amts ) != 0 && ( count( $keys ) != count( $amts ) ) )
		{
			// error if file couldn't be loaded
			$errors->report_error( 'Wrong parameter count', CRITICAL_ERROR, 'Cache', 'incr', __LINE__, ERROR_RAW );
		}
		
		// go through the keys and increase the values
		foreach ( $keys as $i => $key )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'incr', 'increasing variable ' . $key . ' for ' . $amts[ $i ] . ' from ' . $this->backend->get( $key ) );
			// attach con_id to the mem key
			$key = $this->con_id . $key;
		
			$amt = $amts[ $i ];
			$out[] = $this->backend->incr( $key, $amt );
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'inccr', 'increased variable ' . $key . ' <-- ' . $this->backend->get( $key ) );
		}
		
		return $out;
	}
	/**
	* used to delete values from cache optionaly a single index
	* @usage $cache->delete( 'var', 1 );
	* @usage $cache->delete( array( 'var1', 'var2' ), array( 1, 2 ) );
	* @param mixed $keys name or array of names of the stored value
	* @param mixed $index index or array of indexes to remove
	*/
	function delete( $keys, $index = -1 )
	{
		global $errors;
		
		// cache not enabled, leave
		if ( !$this->enabled )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'delete', 'cache not enabled' );
			return;
		}
		// change to array if not already
		if ( !is_array( $keys ) )
		{
			$keys = array( $keys );
		}
		if ( !is_array( $index ) )
		{
			$inx = $index;
			$index = array( $index );
		}else
		{
			$inx = 0;
		}
		// check if arguments are right
		if ( $inx != -1 )
		{
			if ( count( $keys ) != 0 && ( count( $keys ) != count( $index ) ) )
			{
				// error if file couldn't be loaded
				$errors->report_error( 'Wrong parameter count', CRITICAL_ERROR, 'Cache', 'incr', __LINE__, ERROR_RAW );
			}
		}
			
		// go through the keys and delete vars
		foreach ( $keys as $i => $key )
		{	
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'delete', 'deleting variable ' . $key . ' at index ' . $index[ $i ] );
			// attach con_id to the mem key
			$key = $this->con_id . $key;
			
			// determine the index (so we support calls without the index defined)
			if ( count( $index ) > 1 )
			{
				$inx = $index[ $i ];
			}else
			{
				$inx = $index[ 0 ];
			}
			
			// do it
			if ( $inx == -1 )
			{ // delete whole
				$this->backend->delete( $key );
				// remove it from the list
				unset( $this->variables[ $key ] );
			}else
			{ // delete an index
				$data = $this->backend->get( $key );
				unset( $data[ $inx ] );
				$data = $this->backend->replace( $key, $data );
			}
			// debug info
			$errors->debug_info( $this->debug, 'Cache', 'delete', 'variable ' . $key . ' deleted' );
		}
		
		// now refresh it on cache
		$this->push( 'cache_stored_vars', $this->variables, TRUE, NOT_ESSENTIAL, TRUE );
	}
	/**
	* used to add data to diskcache without the backend
	* @usage $this->urgent_add( $key, $data  )
	* @access private
	* @param string $key name of value
	* @param mixed $data the value to store
	*/
	function urgent_add( $key, $data )
	{
		global $Cl_root_path;
		
		$file = $Cl_root_path . 'cache/' . $key . '.chc';
		
		// open file, write file, close file
		$f = @fopen( $file, 'wb' );
		@fwrite( $f, serialize( $data ) );
		@fclose( $f );
	}
	/**
	* used to read data from diskcache without the backend
	* @usage $this->urgent_get( $key )
	* @access private
	* @param string $key name of value
	* @return mixed the value
	*/
	function urgent_get( $key )
	{
		global $Cl_root_path;
	
		// if file exist return it's contents otherwise nothing
		if ( is_file( $Cl_root_path . 'cache/' . $key . '.chc' ) )
		{
			$dat = unserialize( file_get_contents( $Cl_root_path . 'cache/' . $key . '.chc' ) );
		}else
		{
			$dat = array();
		}
		return $dat;
	}
	
	//
	// End class cache
	//
}
?>