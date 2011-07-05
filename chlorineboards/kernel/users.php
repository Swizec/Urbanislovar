<?php

/**
*     defines the users class
*     @file                users.php
*     @uses 
*     @see Users
*/
/**
* The User Module
*     @class		   Users
*     @author              DKing & swizec;
*     @contact          swizec@swizec.com & yaggles@gmail.com
*     @version               0.2.3
*     @since        10th July 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
*     @license http://opensource.org/licenses/gpl-license.php
*     @changes I just fixed indentation a bit, it was funky for me - swiz; and some spacing :); and cache updating to set_userdaa ^_^; added board config reading
* @filesource
* @uses
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
// users_list :: stored user data

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'users', $vars, $visible ) );
// end class creation

class Users extends users_def
{
	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function users( $debug = FALSE )
	{
		global $cache;

		$this->debug = $debug;
		
		// read from cache
		if ( !$this->users_list = $cache->pull( 'users_list' ) )
		{
			$this->users_list = array();
		}
	}
	/**
	* @uses Get the user's data (userid, username, etc.) from the database
	* @usage $users->get_userdata( 2 ); $userdata[ 'username' ] is, in this case, equal to user #2's user name..
	* @param integer $user_id
	* @returns mixed user data
	*/
	function get_userdata( $user_id )
	{
		global $db, $errors, $cache, $board_config;
		
		$errors->debug_info( $this->debug, 'Users', 'get_userdata', 'Getting ' . $user_id . '\'s information from the database.' );

		if ( !isset( $this->users_list[ $user_id ] ) )
		{
			$sql = 'SELECT * FROM ' . USERS_TABLE. ' WHERE user_id = \'' . $user_id . '\'';
			if( !$result = $db->sql_query( $sql ) )
			{
				$errors ->report_error( 'Could not fetch userdata.', GENERAL_ERROR, 'Users', 'get_userdata', __LINE__, ERROR_GUI );
			}
			$userdata = $db->sql_fetchrow( $result );
			
			// read the board config and do stuff
			if ( isset( $_GET[ 'skin' ] ) && !empty( $_GET[ 'skin' ] ) )
			{
				$userdata[ 'user_skn' ] = $_GET[ 'skin' ];
			}elseif ( !isset( $userdata[ 'user_skin' ] ) || empty( $userdata[ 'user_skin' ] ) )
			{
				$userdata[ 'user_skin' ] = $board_config[ 'def_template' ];
			}
			// put it into the main arry
			$this->users_list[ $user_id ] = $userdata;
			
			// renew cache
			$cache->push( 'users_list', $this->users_list, TRUE );
		}
		
		return $this->users_list[ $user_id ];
	}
	/**
	* @uses Set user information
	* @usage $db->set_userdata('DKing', array('field' => 'value', 'field2' => 'value2');
	* @param integer $user_id
	* @param mixed $fieldsvalues associative array of table values
	*/
	function set_userdata( $user_id, $fieldsvalues = array( ) )
	{
		global $errors, $db, $cache;
			
		$errors->debug_info($this->debug, 'Users', 'set_userdata', 'Setting ' . $username . '\'s info, located in the database.');
		
		$sql = '';
		foreach( $fieldsvalues AS $field => $value )
		{
			$sql .= $field . ' = \'' . $value . '\',';
		}
		$sql = substr( $sql, 0, -1 );
		$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $sql . ' WHERE user_id=\'' . $user_id . '\'';
		//Do the query as asked.
		if( !$result = $db->sql_query( $sql ) )
		{
			$errors ->report_error( 'Could not update userdata.', GENERAL_ERROR, 'Users', 'set_userdata', __LINE__, ERROR_GUI );
		}
		// update the cache
		// to save on time we jsut delete what is set ^^
		$cache->delete( 'users_list' );
	}
	/**
	* added by swizec, it sends emails :)
	* @param string $to
	* @param string $subject
	* @param string $message
	* @param string $from
	* @returns bool
	*/
	function clb_Mail($to, $subject="", $message="", $from="") {
		global $board_config, $basic_gui;

		if ( empty( $to ) ) 
		{
			return FALSE;
		}

		if ( empty( $from ) ) 
		{
			$from = $board_config[ 'admin_email' ];
		}

		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=\"" . $board_config[ 'site_charset' ] . "\"\r\n";
		$headers .= "From: {$from}\r\n";
		$headers .= "Reply-To: {$from}\r\n";
		$headers .= "X-Mailer: Server - " . $basic_gui->get_URL();

		// Send the mail
		if( !@mail( trim( $to ), $subject, $message, $headers ) ) 
		{
			return FALSE;
		}

		return TRUE;
	}
	
	//
	// End of Users class
	//
}
?>