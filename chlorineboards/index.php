<?php
/**
* Creates the environment for the modules to run in
* supposed to be the only file to ever get called by the browser
*     @file                index.php
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.7.4
*     @since        8th June 2005
*     @package		     ClB_base
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
* 
*/

/** 
* this is legal script execution
*/
define( 'RUNNING_CL', TRUE );

/**
* @global array so we can return the time it took to make the page
* @name $timing
*/
$mtime = explode( " ", microtime() ); 
$timing[ 'starttime' ] = $mtime[ 1 ] + $mtime[ 0 ];

// deal with the register globals hazard
$glob = @ini_get('register_globals');
if ( $glob == '1' || $glob == 'on' )
{
	// list of vars or arrays to be removed
	$to_unset = array( '_GET', '_POST', '_SERVER', '_COOKIE', '_SESSION', '_ENV', '_FILES', 'Cl_root_path', 'phpversion' );
	
	// session is conditionally set so we make it an array just in case :)
	if ( !isset( $_SESSION ) )
	{
		$_SESSION = array();
	}
	
	/**
	*   the recursive function that loops through nested arrays and stuff
	* @param mixed $arry array to parse
	*/
	function rec_remove( $arry )
	{
		while( list( $var ) = each( $arry ) )
		{
			if ( is_array( ${$var} ) )
			{
				rec_remove( $var );
			}else
			{
				global ${$var};
				unset( ${$var} );
			}
		}	
	}
	
	// do it
	foreach ( $to_unset as $arry )
	{
		if ( is_array( ${$arry} ) )
		{
			rec_remove( ${$arry} );
		}else
		{
			unset( ${$arry} );
		}
	}
}
unset( $glob );

/**
* @global string the root
*/
$Cl_root_path = './';
/**
* so the command phpversion() doesn't keep getting called
*/
define( 'PHPVER', phpversion() );
/**
* to be used instead of time() in order to save the function from being called too much
*/
define( 'EXECUTION_TIME', time() );

// set what errors to report
error_reporting( E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR | E_USER_WARNING );
//ini_set( 'display_errors', 'On' );
//error_reporting( E_ALL );

//error_reporting( E_ALL );
//ini_set( 'display_errors', '1' );
//ini_set( 'display_startup_errors', '1' );
ob_start(); // so we don't get nasty 'headers already sent' errors
// this was reporting errors on some servers
if ( function_exists( 'date_default_timezone_set' ) )
{
	date_default_timezone_set( 'GMT' );
}

// lets first include some of the stuff we need
/**
*  // the basic config
*/
include( $Cl_root_path . 'kernel/config/gen_config.php' ); 
/**
*  // this is later on used to make the classes
*/
include( $Cl_root_path . 'kernel/varloader' . phpEx ); 
/**
*  // the error handler
*/
include( $Cl_root_path . 'kernel/errors' . phpEx ); 
/**
*  // the cache frontend
*/
include( $Cl_root_path . 'kernel/cache' . phpEx ); 
/**
*  // the template engine
*/
include( $Cl_root_path . 'kernel/template' . phpEx ); 
/**
*  // the encryption engine
*/
include( $Cl_root_path . 'kernel/encryption' . phpEx ); 
/**
*  // database layer
*/
include( $Cl_root_path . 'kernel/database/db' . phpEx ); 
/**
*  // security stuff
*/
include( $Cl_root_path . 'kernel/security' . phpEx ); 
/**
*  // config managment
*/
include( $Cl_root_path . 'kernel/config' . phpEx ); 
/**
*  // language managment
*/
include( $Cl_root_path . 'kernel/lang' . phpEx ); 
/**
*  // module managment
*/
include( $Cl_root_path . 'kernel/module_loader' . phpEx ); 
/**
*  // basic gui thing
*/
include( $Cl_root_path . 'kernel/basic_gui' . phpEx ); 
/**
*  // ze ol' mighty ajax
*/
include( $Cl_root_path . 'kernel/Sajax' . phpEx ); 
/**
*  // user managment
*/
include( $Cl_root_path . 'kernel/users' . phpEx ); 
/**
*  // plug-in initiation
*/
include( $Cl_root_path . 'kernel/plugins' . phpEx ); 
/**
*  // archives related stuff, gets useful
*/
include( $Cl_root_path . 'kernel/archive' . phpEx );

// instantiate the error handler
$errors = new Errors( $admin_email, FALSE );

// this checks if the run is just after the summon
if ( !defined( 'CLB_INSTALLED' ) || CLB_INSTALLED == FALSE )
{
	// if it is then commence the installation thing
	include( $Cl_root_path . 'install' . phpEx );
	$install = new install( );
	$install->commence();
	exit; // don't need further execution after this do we
}

// db connection
initiate_db();

// instantiate cache
$cache = new cache( FALSE );

// get config
$config_class = new Config( FALSE ); // config engine
$board_config = $config_class->get_config();

// populates $_GET with all things that do not conform to URI shaping
$args = explode( 'index' . phpEx, $_SERVER[ 'REQUEST_URI' ] );
if ( count( $args ) == 1 )
{
	if ( substr( $args[ 0 ], 0, strlen( $board_config[ 'script_path' ] ) ) == $board_config[ 'script_path' ] )
	{
		$args[ 1 ] = substr( $args[ 0 ], strlen( $board_config[ 'script_path' ] ) );
	}
}
$args = explode( '/', $args[ 1 ] );
$Cl_root_path4template = ''; // this is needed because otherwise stuff doesn't work :)
if ( $args[ 0 ]{0} != '?' )
{ // SEO might be switched off and then this would produce errors
	foreach ( $args as $arg )
	{
		if ( empty( $arg ) )
		{
			continue;
		}
		$arg = explode( '=', $arg );
		if ( count( $arg ) < 2 )
		{ // not meant for this kind of reading apparently
			$Cl_root_path4template .= '../';
			continue;
		}
		$_GET[ $arg[ 0 ] ] = $arg[ 1 ];
		$Cl_root_path4template .= '../';
	}
	// this fixes the bug with the last character being /
	if ( $_SERVER[ 'REQUEST_URI' ]{strlen( $_SERVER[ 'REQUEST_URI' ] )-1} == '/' && strpos( $_SERVER[ 'REQUEST_URI' ], '=' ) !== FALSE )
	{
		$Cl_root_path4template .= '../';
	}
}

// this populates the $_GET with values from the URL with URI shaping
if ( @include( $Cl_root_path . 'kernel/config/URIconf.php' ) )
{ // URI shapinp
	// clean it up a bit for regexing
	$uri = preg_replace( '#^' . $board_config[ 'script_path' ] . '#i', '', $_SERVER[ 'REQUEST_URI' ] );
	$uri = preg_replace( '#^index' . phpEx . '.#i', '', $uri, -1, $isIndex );
	$uri = preg_replace( '#/(skin|lang|CLBSID)=[a-zA-Z0-9%]*#', '', $uri );
	
	if ( is_array( $URIconf ) )
	{
		foreach( $URIconf as $mode => $curr )
		{
			foreach ( $curr as  $cur )
			{
				if ( preg_match( $cur[ 0 ], $uri, $matches ) )
				{
					for ( $i = 1; $i < count( $matches ); $i++ )
					{
						if ( is_array( $cur[ $i ] ) )
						{ // static
							$k = array_keys( $cur[ $i ] );
							$_GET[ $k[ 0 ] ] = $cur[ $i ][ $k[ 0 ] ];
						}else
						{ // from URI
							$_GET[ $cur[ $i ] ] = $matches[ $i ];
						}
					}
					
					$_GET[ MODE_URL ] = $mode;
					$Cl_root_path4template = substr( $Cl_root_path4template, 3 );
					$Cl_root_path4template .= ( $isIndex > 0 ) ? '../' : '';
					break;
				}
			}
		}
	}
	
	
}

// first load the session and user class
$encryption = new Encryption( ); // encryption engine
$security = new Security( FALSE ); // security engine

$users = new Users( FALSE );

// make/update the current session and fetch user data

$security->newsession();

// instantiate some classes
if ( isset( $_GET[ 'skin' ] ) && !empty( $_GET[ 'skin' ] ) )
{ // temporarily set the skin to something else
	$userdata[ 'user_skin' ] = $_GET[ 'skin' ]; // the skin to use
}

$template = new Template( $cache, $Cl_root_path . 'template/' . $userdata[ 'user_skin' ] . '/', FALSE ); // template engine
$mod_loader = new Module_loader( FALSE ); // module managment
$lang_loader = new Lang( FALSE ); // language engine
$Sajax = new Sajax( FALSE, 'GET' ); // ajax
$basic_gui = new Basic_gui( ); // basic gui
$plugins = ( $enableplugins ) ? new Plugins( FALSE ) : ''; // plugins

// enparse all globals for sql injection protection
$parsed = $security->parsevar( array( $_POST, $_GET, $_SESSION, $_SERVER, $_ENV, $_COOKIE, $_FILES ), ADD_SLASHES ); 
$_POST = $parsed[ 0 ];
$_GET = $parsed[ 1 ];
$_SESSION = $parsed[ 2 ];
$_SERVER = $parsed[ 3 ];
$_ENV = $parsed[ 4 ];
$_COOKIE = $parsed[ 5 ];
$_FILES = $parsed[ 6 ];

// basic language
$basic_lang = $lang_loader->get_lang( 'basic' ); // basic vars

// tell the basic gui to all set the back url
$basic_gui->set_back_URL();
// now tell the basic gui to define some global template stuff (needed the lang)
$basic_gui->defineglobal();

/*if ( !isset( $_GET[ 'AJAX_CALL' ] ) )
{
	print_R( $_SESSION );
}*/

// get the mode
$mode = ( !empty( $_GET[ MODE_URL ] ) ) ? strval( $_GET[ MODE_URL ] ) : 'index';

// now we try to output the page from cache
// all of that had to be loaded so that ajax and the likes might possibly work
// well at least what's of the default should
if ( $mode != 'ACP' && $mode != 'UCP' && $board_config[ 'pagecache_on' ] == TRUE )
{ // no caching of the ACP and UCP, for quite obvious reasons :)
	$pg = $cache->pull(  'cached_page_' . $lang_loader->board_lang . '_' . $_SERVER[ 'REQUEST_URI' ] . '_' . $userdata[ 'user_id' ] );
// 	echo 'cached_page_' . $lang_loader->board_lang . '_' . $basic_gui->get_URL() . '<br />';
	if ( !empty( $pg ) )
	{ // output it
		// check for the time first
		if ( EXECUTION_TIME-$pg[ 'time' ] < $board_config[ 'pagecache_time' ] )
		{
			// init sajax
			$Sajax->sajax_remote_uri = $Sajax->sajax_get_my_uri();
			$Sajax->sajax_init();
			$Sajax->sajax_export();
			$Sajax->sajax_handle_client_request();
			echo $pg[ 'content' ];
			$basic_gui->drag_list = $pg[ 'drag_list' ];
			// we'd like the footer to be updated
			$basic_gui->makefooter();
			ob_end_flush();
			exit; // no more further execution needed
		}
	}
}

//
// load up and execute the modules
//

// set the basic pagination level so that at least this one is always set
$basic_gui->set_level( 0, '' );

// $mode = 'forums';

// if we have the mode then we load that otherwise the module stored in the db
if ( $mode == 'pages' )
{ // this is for user added static pages so we try and keep it simple
	define( 'TESTING', FALSE );
	$basic_gui->static_page();
}elseif( $mode == 'plugtest' )
{ // this is meant for testing plugins during development
	if ( !empty( $_GET[ 'plug' ] ) && !empty( $_GET[ 'func' ] ) )
	{
		define( 'TESTING', TRUE );
		eval( '$' . $_GET[ 'plug' ] . '->' . $_GET[ 'func' ] . '( ' . $_GET[ 'args' ] . ' );' );
		exit();
	}
}elseif ( $mode != 'index' )
{
	define( 'TESTING', FALSE );
	$parent = 0; // index.php is the very top
	$mod_loader->getmodule( $mode, MOD_FETCH_MODE );
}else
{
	define( 'TESTING', FALSE );
	$module = $board_config[ 'main_module' ];
	$exp = tplEx;
	$handle = substr( $module, -strlen( $exp ) );
	
	
	if ( $handle == $exp )
	{ // if the main module is actually a template file just use it (static entry pages)
		$template->assign_files( array(
			$handle => $module
		) );
		$template->output( $handle );
		ob_end_flush();
		exit;
	}else
	{ // the module is actually a module so we do the module thing
		$mod_loader->getmodule( $module, MOD_FETCH_NAME );
	}
}

// execute needed functions
$mod_loader->execute_modules( $parent, $mode );

// make the page
$basic_gui->make_page();

?>