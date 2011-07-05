<?php

/**
*     defines the diskcache class
*     @file                diskcache.php
*     @see Diskcache
*/
/**
* This is a cache backend used when memcached is not available or not desired
*     @class		   Diskcache
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0;
*     @since        11th July 2005
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
// path :: path to the cache

// create this class
$vars = array( 'path' );
$visible = array( 'private' );
eval( Varloader::createclass( 'diskcache', $vars, $visible ) );
// end class creation

class Diskcache extends diskcache_def
{

	/**
	* constructor
	* @param string $path path to the cache
	*/
	function Diskcache( $path )
	{
		$this->path = $path;
	}
	/**
	* used for adding variables to cache
	* @param string $key name of variable
	* @param mixed $value value to store
	*/
	function add( $key, $value )
	{
		$key = base64_encode( $key );
		// if it's an array serialize it
		if ( is_array( $value ) )
		{
			$value = 'Array' . serialize( $value );
		}
		// if the thing is unwritable just return
		if ( !is_writable( $this->path ) )
		{
			return FALSE;
		}
		// open file, write file, close file
		$f = fopen( $this->path . $key . '.chc', 'wb' );
		if ( fwrite( $f, $value ) )
		{
			return TRUE;
		}else
		{
			return FALSE;
		}
		fclose( $f );
	}
	/**
	* used for getting variables
	* @param string $key name of value ot fetch
	* @return mixed fetched value
	*/
	function get( $key )
	{
		$key = base64_encode( $key );
		// if unreadible jut leave
		if ( !is_readable( $this->path . $key . '.chc' ) )
		{
			return '';
		}
		// read it
		$data = file_get_contents( $this->path . $key . '.chc' );
		// chech if it's an array
		if ( substr( $data, 0, 5 ) == 'Array' )
		{ // aye
			return unserialize( substr( $data, 5 ) );
		}else
		{ // nay
			return $data;
		}
	}
	/**
	* used for replacing of variables
	* @param string $key name of variable
	* @param mixed $data new value
	*/
	function replace( $key, $data )
	{
		$key = base64_encode( $key );
		// just call the add, here it's the same thing
		$this->add( $key, $data );
	}
	/**
	* used for decreasing of integer values on cache
	* @param string $key name of variable
	* @param integer $amt amount to decrease
	*/
	function decr( $key, $amt )
	{
		$key = base64_encode( $key );
		// get value
		$data = intval( $this->get( $key ) );
		// set value
		$data -= $amt; 
		// write value
		$this->add( $key, $data );
	}
	/**
	* used for increasing of integer values on cache
	* @param string $key name of variable
	* @param integer $amt amount to increase
	*/
	function incr( $key, $amt )
	{
		$key = base64_encode( $key );
		// get value
		$data = intval( $this->get( $key ) );
		// set value
		$data += $amt; 
		// write value
		$this->add( $key, $data );
	}
	/**
	* used for deleting of variables
	* @param string $key name of variable
	*/
	function delete( $key )
	{
		$key = base64_encode( $key );
		// delete it
		unlink( $this->path . $key . '.chc' );
	}

	//
	// End of Diskcache class
	//
}

?>