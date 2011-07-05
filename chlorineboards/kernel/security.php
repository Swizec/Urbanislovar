<?php

/**
*     defines the security class
*     @file                security.php
*
*     @see Security
*/
/**
* manages all security related things
*     @class		   Security
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.5.19
*     @since        8th June 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
* 
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// debug :: debug flag
// sess_hash :: list of sessions

// class creation
$vars = array( 'debug' );
$visible = array( 'private');
eval( Varloader::createclass( 'security', $vars, $visible ) );
// end class creation

class Security extends security_def
{

	/**
	* creates the class
	* @usage $security = new security( FALSE );
	* @param bool $debug debugging on or off
	*/
	function Security( $debug = FALSE )
	{
		$this->debug = $debug;
	}
	
	/**
	* adds new user to sessions table, creates the session and manages the refreshing of it
	* @usage $security->newsession();
	*/
	function newsession( )
	{
		global $db, $encryption, $board_config, $errors, $userdata, $users;
		
		// get data
		$time = time(); // current time
		$ip = $_SERVER[ 'REMOTE_ADDR' ]; // user ip
		
		// encrypt the ip
		// don't use an added key so this can be easily decrypted
		$ipe = $encryption->encrypt( $this->make_key( '' ), $ip, 15 );
		
		// set the config
		if ( $board_config[ 'session_method' ] == SES_METHOD_COOKIE )
		{ // cookies
			session_set_cookie_params( $board_config[ 'session_expire' ], $board_config[ 'session_path' ], $board_config[ 'session_domain' ], $board_config[ 'session_secure' ] );
			// let the session know we want want cookies
			ini_set( 'session.use_cookies', 1 );
		}elseif( $board_config[ 'session_method' ] == SES_METHOD_COOKIELESS )
		{ // cookieless
			// let the session know we don't want cookies
			ini_set( 'session.use_cookies', 0 );
		}
		// we need not to transfer the session in any way possible
		ini_set( 'session.use_trans_sid', 0 );
		// give our session a snazzy name :)
		ini_set( 'session.name', 'CLBSID' );
		
		// start session
		ob_clean();
		session_start();
		
		// see what ip the database holds for this session
		$sid = session_id();
		$sql = "SELECT ip FROM " . SESSIONS_TABLE . " WHERE id='$sid' LIMIT 1";
		if ( $result = $db->sql_query( $sql ) )
		{
			$ip = $db->sql_fetchfield( 'ip' );
		}
		
		// if ips don't match make new session
		// if sessions don't match we have pretty much the same problem
		if ( ( $ip != stripslashes( $ipe ) ) || ( $_SESSION[ 'sid' ] != session_id() ) )
		{
			// remove the old session from db as well
			$sid1 = session_id();
			$sid2 = $_SESSION[ 'sid' ];
			$sql = "DELETE FROM " . SESSIONS_TABLE . " WHERE id='$sid1' OR id='$sid2'";
			$db->sql_query( $sql );
			session_regenerate_id( TRUE );
		}
		
		$errors->debug_info( $this->debug, 'Security', 'newsession', 'Output just got cleaned for session_start' );
		
		// get the sid
		$sid = session_id();
		$oldsid = 0;
		// get info from the session
		$userdata = $_SESSION;
			
		// check if we need to set a cookie (login requested it)
		if ( ( isset( $_SESSION[ 'setcookie' ] ) && $_SESSION[ 'setcookie' ] == 1 ) || isset( $_SESSION[ 'autolog' ] ) && $_SESSION[ 'autolog' ] == 1 )
		{
			$userdata[ 'setcookie' ] = 0;
			ob_clean();
			// time()+12096000 expire in 20 weeks, which should be far enough :P
			setcookie( 'CLBAutolog', $sid, time()+12096000, $board_config[ 'session_path' ], $board_config[ 'session_domain' ], $board_config[ 'session_secure' ] );
		}
		// check if the session is rather empty
		if ( empty( $userdata ) )
		{ // try to autolog
			if ( isset( $_COOKIE[ 'CLBAutolog' ] ) )
			{ // autolog will do fine :)
				$oldsid = $_COOKIE[ 'CLBAutolog' ];
// 				@session_destroy(); // kill the session
				ob_clean();
				session_start(); // make new one
				$sid = session_id();
				setcookie( 'CLBAutolog', $sid, time()+12096000, $board_config[ 'session_path' ], $board_config[ 'session_domain' ], $board_config[ 'session_secure' ] ); // tell the cookie about the new sid
				
				// fetch user data from the db
				$sql = "SELECT time_start, time_lastactive, user_id, autolog FROM " . SESSIONS_TABLE . " WHERE id='$oldsid' LIMIT 1";
				if ( $res = $db->sql_query( $sql ) )
				{
					$userdata = $db->sql_fetchrow( $res );
				}
			}
		}
		
		// set userdata array
		$userdata[ 'user_id' ] = ( isset( $userdata[ 'user_id' ] ) ) ? $userdata[ 'user_id' ] : 1;
		$user = $users->get_userdata( $userdata[ 'user_id' ] );
		// $user now contains something we don't need
		unset( $user[ 'user_id' ] );
		// add some stuff to the userdata array
		$userdata[ 'time_lastactive' ] = $time;
		$userdata[ 'time_start' ] = ( !isset( $userdata[ 'time_start' ] ) ) ? $time : $userdata[ 'time_start' ];
		$userdata[ 'autolog' ] = ( !isset( $userdata[ 'autolog' ] ) ) ? 0 : $userdata[ 'autolog' ];
		$userdata[ 'ip' ] = $ipe;
		$userdata[ 'sid' ] = $sid;
		if ( !isset( $userdata[ 'logged_in' ] ) )
		{
			$userdata[ 'logged_in' ] = ( $user[ 'user_level' ] != GUEST ) ? TRUE : FALSE;
		}
		// put the userdata and user array togethers
		$userdata = array_merge( $userdata, $user );
		
		// make it a global
		$GLOBALS[ 'userdata' ] = $userdata;
		
		// easier to insert
		$time_start = $userdata[ 'time_start' ];
		$time_lastactive = $userdata[ 'time_lastactive' ];
		$uid = $userdata[ 'user_id' ];
		$autolog = $userdata[ 'autolog' ];
		
		// clean the db
		$errors->debug_info( $this->debug, 'Security', 'newsession', 'Cleaning the db' );
		$sql = "DELETE FROM " . SESSIONS_TABLE . " WHERE (($time-time_lastactive)>" . $board_config[ 'session_expire' ] . " AND autolog=0) OR id='$sid' OR id='$oldsid'";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Expired sessions could not be deleted', CRITICAL_ERROR_ERROR, 'Security', 'newsession', __LINE__ );
		}
		
		// insert session into the db
		$sql = "INSERT INTO " . SESSIONS_TABLE . " VALUES ( '$sid', '$time_start', '$time_lastactive', '$ipe', '$uid', '$autolog' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not insert session data into the database', CRITICAL_ERROR );
		}
		
		// set the session data again, but first exclude some things that shouldn't go into session
		$u = $userdata;
		$out = array( 'ip', 'logged_in', 'password', 'username', 'user_email', 'user_level', 'autolog' );
		for ( $i = 0; $i < count( $out ); $i++ )
		{
			unset( $u[ $out[ $i ] ] );
		}
		$_SESSION = $u;
		
		return strip_tags( SID );
	}
	
	/**
	* determine if IP is bot
	* @access private
	* @param bool $load need to freshly load the list or not
	* @returns bool is it a bot or not
	*/
	function IPbot( $load )
	{
		global $cache, $basic_gui;
		
		// check if the constant is already set
		if ( !defined( 'IPISBOT' ) )
		{
			if ( $load || !$list = $cache->pull( 'botlist_list', ESSENTIAL ) )
			{ // we need to load it anew
				// get the list
				$list = '';
				$chck = TRUE;
				if ( !ini_get( 'allow_url_fopen' ) )
				{
					if ( !@ini_set( 'allow_url_fopen', 1 ) )
					{
						$chck = FALSE;
					}else
					{
						$chck = TRUE;
					}
				}
				if ( $chck )
				{
					$list .= @file_get_contents( 'http://www.iplists.com/nw/google.txt' );
					$list .= @file_get_contents( 'http://www.iplists.com/nw/inktomi.txt' );
					$list .= @file_get_contents( 'http://www.iplists.com/nw/lycos.txt' );
					$list .= @file_get_contents( 'http://www.iplists.com/nw/msn.txt' );
					$list .= @file_get_contents( 'http://www.iplists.com/nw/altavista.txt' );
					$list .= @file_get_contents( 'http://www.iplists.com/nw/wisenut.txt' );
					$list .= @file_get_contents( 'http://www.iplists.com/nw/askjeeves.txt' );
					$list .= @file_get_contents( 'http://www.iplists.com/nw/misc.txt' );
					$list .= @file_get_contents( 'http://www.iplists.com/nw/non_engines.txt' );
					
					// clean comments from it
					$list = preg_replace( '#^\#.*?$#m', '', $list );
					// explode it
					$list = explode( "\n", $basic_gui->gennuline( $list ) );
				}
				
				// store the list
				$cache->push( 'botlist_list', $list, TRUE, ESSENTIAL );
			}else
			{ // just fetch it from cache
				// it actually got pulled in the if there
			}
			// user IP
			$ip = $_SERVER[ 'REMOTE_ADDR' ];
			// check
			if ( is_array( $list ) )
			{
				if ( in_array( $ip, $list ) )
				{ // it is
					define( 'IPISBOT', TRUE );
					return TRUE;
				}else
				{// not
					define( 'IPISBOT', FALSE );
					return FALSE;
				}
			}else
			{ // not
				define( 'IPISBOT', FALSE );
				return FALSE;
			}
		}else
		{
			return IPISBOT;
		}
	}
	
	/**
	* adds the session id to the end of an url
	* and fixes the URL up so it's more SEOised
	* @usage $url = $security->append_sid( $url, $sid );
	* @param string $url the URL to be parsed
	* @param bool $shaping whether this URI is meant to be shaped or not
	* @returns string the parsed URL
	*/
	function append_sid( $url, $shaping = FALSE )
	{
		global $board_config, $cache, $basic_gui;
		
		// is this an absolute URL
		if ( strpos( $url, '://' ) !== FALSE )
		{
			return $url;
		}
		
		// if the URL doesn't have index.php then we add it
		if ( strpos( $url, 'index' . phpEx ) === FALSE && !$shaping  )
		{
			$url = ( strpos( $url, '?' ) !== FALSE ) ? str_replace( '?', 'index' . phpEx . '?', $url ) : 'index' . phpEx . $url;
		}
		// if it is a relative URL we fix that too
		$uri = $basic_gui->get_URL();
		if ( strpos( $url, $uri ) === FALSE )
		{
			$url = ( !$shaping ) ? substr( $url, strpos( $url, 'index' . phpEx ) ) : $url;
			$url = $uri . '/' . $url;
		}
		// this can sometimes occur
		//$url = str_replace( '//index', '/index', $url );
		
		// we add the skin and lang parameters if they have been set so they're easier to use
		if ( isset( $_GET[ 'skin' ] ) && strpos( $url, 'skin=' ) === FALSE )
		{
			$url .= ( strpos( $url, '?' ) !== FALSE ) ? '&skin=' . $_GET[ 'skin' ] : '?skin=' . $_GET[ 'skin' ];
		}
		if ( isset( $_GET[ 'lang' ] ) && strpos( $url,'lang=' ) === FALSE )
		{
			$url .= ( strpos( $url, '?' ) !== FALSE ) ? '&lang=' . $_GET[ 'lang' ] : '?lang=' . $_GET[ 'lang' ];
		}

		$sid = SID;
		if ( empty( $sid ) )
		{
			if ( $board_config[ 'use_SEO' ] )
			{ // some servers don't like this type of argument passing
				return str_replace( '&', '/', str_replace( '?', '/', $url ) );
			}else
			{
				return $url;
			}
		}
		
		if ( $board_config[ 'nosid4bots' ] )
		{ // try to check if the user is a known bot, then don't add a SID
			// fetch list
			// first try the cache
			if ( !$time = $cache->pull( 'botlist_time', ESSENTIAL ) )
			{
				$isbot = $this->IPbot( TRUE );
				// save time
				$cache->push( 'botlist_time', time(), FALSE, ESSENTIAL );
			}elseif ( time() - $time > 1209600 )
			{ // refresh at least every 14 days
				$isbot = $this->IPbot( TRUE );
				// save time
				$cache->push( 'botlist_time', time(), FALSE, ESSENTIAL );
			}else
			{
				$isbot = $this->IPbot( FALSE );
			}
			
		}
		
		if ( !$isbot )
		{
			if ( strpos( $url, '?' ) !== FALSE )
			{ // aye
				$url .= '&' . strip_tags( SID );
			}else
			{ // nay
				$url .= '?' . strip_tags( SID );
			}
		}
		if ( $board_config[ 'use_SEO' ] )
		{ // some servers don't like this type of argument passing
			return str_replace( array( '?', '&', '//', ':/' ), array( '/', '/', '/', '://' ), $url );
		}else
		{
			return $url;
		}
	}
	
	/**
	* returns an encryption key from the given arguments
	* @usage $key = $security->make_key( 'arg1' );
	* @param string $args array or single string of strings to be incorporated into the key
	* @returns string the key
	*/
	function make_key( $args = '' )
	{
		global $board_config, $cache;
		
		$key = '';
		
		// the bunch of silly stuff here is there to make the key as long as possible
		// which then makes encryption alot harder to break
		// the arguments are used so that even though everyone can see this function 
		// the key cannot be guessed as it changes with every encryption
		// key with no arguments is about 196 bit long, safe enough
		
		// since all the functions take time we take the prefix from the cache
		$prefix = $cache->pull( 'encryptkeyprefix' );
		if ( empty( $prefix ) )
		{
			// make it
			$prefix = $board_config[ 'key_prefix' ] . sha1( $board_config[ 'key_prefix' ] ) . $board_config[ 'session_domain' ];
			// write it
			$cache->push( 'encryptkeyprefix', $prefix, FALSE );
		}
		
		// if $args is an array implode it
		if ( is_array( $args ) )
		{
			$key = implode( ';', $args );
		}else
		{
			$key = $args;
		}
		
		// make the key
		$key = $prefix . base64_encode( $key );
		
		return $key;
	}
	
	/**
	* used for slashing nested arrys
	* @access private
	* @param mixed $var the array
	* @param string $method add or remove slashes
	* @return mixed the parsed array
	*/
	function parserecarry( &$var, $method )
	{
		$queue = array()
		;
		foreach ( $var as $ii => $v )
		{
			if ( !is_array( $v ) )
			{ // not array
				if ( $method == ADD_SLASHES )
				{
					$var[ $ii ] = addslashes( $var[ $ii ] );
				}elseif ( $method == REM_SLASHES )
				{
					$var[ $ii ] = stripslashes( $var[ $ii ] );
				}
			}else
			{ // add to queue
				$queue[] = $ii;
			}
		}
		// go through the gueue
		for ( $i = 0; $i < count( $queue ); $i++ )
		{
			$this->parserecarry( $var[ $queue[ $i ] ], $method );
		}
	}
	
	/**
	* performs operations to prevent sql injections
	* @usage $ary = $security->parsevar( array( $var1, $var2 ), ADD_SLASHES );
	* @param mixed $vars the array of vars to be parsed, can be a single string
	* @param string $method what exactly to do
	* @param bool $force don't care about magic quotes and stuff
	*/
	function parsevar( $vars, $method = REM_SLASHES, $force = FALSE )
	{
		// check if enparsing is needed
		if ( get_magic_quotes_gpc( ) && $method == ADD_SLASHES && !$force )
		{
			return $vars;
		}
		
		if ( !is_array( $vars ) )
		{
			// not an array of vars
			if ( !is_array( $vars ) )
			{
				// var itself isn't an array
				if ( $method == ADD_SLASHES )
				{
					$vars = addslashes( $vars );
				}elseif ( $method == REM_SLASHES )
				{
					$vars = stripslashes( $vars );
				}
			}
		}else
		{
			// an array of variables
			foreach ( $vars as $i => $var )
			{
				if ( !is_array( $var ) )
				{
					// var itself isn't an array
					if ( $method == ADD_SLASHES )
					{
						$vars[ $i ] = addslashes( $vars[ $i ] );
					}elseif ( $method == REM_SLASHES )
					{
						$vars[ $i ] = stripslashes( $vars[ $i ] );
					}
				}else
				{
					// var itself is an array
					$this->parserecarry( $vars[ $i ], $method );
				}
			}
		}
		
		return $vars;
	}

	//
	// End security class
	//
}

?>