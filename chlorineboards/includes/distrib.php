<?php
///////////////////////////////////////////////////////////////////
//                                                               //
//     file:                distrib.php                          //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        5th August 2005                        //
//     version:              0.11.2                              //
//                                                               //
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
//                                                               //
// This program is free software; you can redistribute it        //
// and/or modify it under the terms of the GNU General Public    //
// License as published by the Free Software Foundation;         //
// either version 2 of the License, or (at your option)          //
// any later version.                                            //
//                                                               //
///////////////////////////////////////////////////////////////////

// the distributor for ClB
// this is a bit unpreety because it wasn't always a class :)

// this is all put here beause it would be useless to do the session stuff and all that from the index
// (this is an index excerpt to be precise)

// legal script execution
define( 'RUNNING_CL', TRUE );

// deal with the register globals hazard (not sure I need this, but better be safe than sorry eh
if ( @ini_get('register_globals') == '1' || strtolower( @ini_get( 'register_globals' ) ) == 'on' )
{
	// list of vars or arrays to be removed
	$to_unset = array( '_GET', '_POST', '_SERVER', '_COOKIE', '_SESSION', '_ENV', '_FILES', 'Cl_root_path', 'phpversion' );
	
	// session is conditionally set so we make it an array just in case :)
	if ( !isset( $_SESSION ) )
	{
		$_SESSION = array();
	}
	
	// the recursive function that loops through nested arrays and stuff
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

$Cl_root_path = './../'; // the root
define( 'PHPVER', phpversion() ); // so the command doesn't keep getting called

error_reporting( E_ALL ^ E_NOTICE );

// lets first include some of the stuff we need
include( $Cl_root_path . 'kernel/config/gen_config.php' ); // the basic config
include( $Cl_root_path . 'kernel/varloader' . phpEx ); // this is later on used to make the classes
include( $Cl_root_path . 'kernel/errors' . phpEx ); // the error handler
include( $Cl_root_path . 'kernel/cache' . phpEx ); // the cache frontend
include( $Cl_root_path . 'kernel/database/db' . phpEx ); // database layer
include( $Cl_root_path . 'kernel/security' . phpEx ); // security stuff
include( $Cl_root_path . 'kernel/config' . phpEx ); // config managment

// instantiate the error handler
$errors = new Errors( 'swizec@swizec.com', FALSE );

// db connection
initiate_db();

// instantiate cache
$cache = new cache( FALSE );

// get config
$config_class = new Config( FALSE ); // config engine
$board_config = $config_class->get_config(); // this is mostly for the constants

$security = new Security( FALSE ); // security engine

// enparse all globals for sql injection protection
$parsed = $security->parsevar( array( $_POST, $_GET, $_SESSION, $_SERVER, $_ENV, $_COOKIE, $_FILES ), ADD_SLASHES ); 
$_POST = $parsed[ 0 ];
$_GET = $parsed[ 1 ];
$_SESSION = $parsed[ 2 ];
$_SERVER = $parsed[ 3 ];
$_ENV = $parsed[ 4 ];
$_COOKIE = $parsed[ 5 ];
$_FILES = $parsed[ 6 ];

$modhash = array();

// commented out so we can make a new one
// function listfetch( $sql_what, &$list, $from = 'normal', $search = FALSE, $vercheck = FALSE )
// {
// 	global $db, $USE, $mode, $install, $modhash;
// 	
// 	if ( $vercheck )
// 	{
// 		$sql = "SELECT * FROM " . DISTROMODULES_TABLE	 . " WHERE $sql_what";
// 	}else
// 	{
// 		$sql = ( !$search ) ? "SELECT * FROM " . DISTROMODULES_TABLE	 . " WHERE callsign IN ( $sql_what )" : "SELECT * FROM " . DISTROMODULES_TABLE	 . " WHERE ( callsign LIKE $sql_what || name LIKE $sql_what )";
// 	}
// 	
// 	$res = $db->sql_query( $sql );
// 	
// 	while ( $row = $db->sql_fetchrow( $res ) )
// 	{
// 		if ( $row[ 'see_use' ] == '1' )
// 		{
// 			$sql_what = '';
// 			foreach ( explode( ';', $row[ 'use_opts' ] ) as $opt )
// 			{
// 				if ( in_array( $opt, $USE ) )
// 				{
// 					$sql_what .= ( !$search ) ? $opt . '\', \'' : $opt . '%\', \'%';
// 				}
// 			}
// 			if ( empty( $sql_what ) )
// 			{
// 				if ( !is_dir( $Cl_root_path . 'files/' . $row[ 'name' ] ) )
// 				{
// 					continue;
// 				}
// 			}else
// 			{
// 				if ( !$search )
// 				{
// 					$sql_what = '\'' . substr( $sql_what, 0, -4 ) . '\'';
// 					listfetch( $sql_what, $list, $from );
// 				}
// 				if ( !is_dir( $Cl_root_path . 'files/' . $row[ 'name' ] ) )
// 				{
// 					continue;
// 				}if ( !is_dir( $Cl_root_path . 'files/' . $row[ 'name' ] ) )
// 				{
// 					continue;
// 				}
// 			}
// 		}
// 		if ( !empty( $row[ 'request' ] ) )
// 		{
// 			$sql_what = explode( ';', $row[ 'request' ] );
// 			$what = array();
// 			for ( $i = 0; $i < count( $sql_what ); $i++ )
// 			{
// 				$opt = $sql_what[ $i ];
// 				if ( strpos( $opt, '[' ) !== FALSE )
// 				{
// 					preg_match( '#(.*?)\[#', $opt, $name );
// 					preg_match( '#\[(.*?)\]#', $opt, $ver );
// 			
// 					$opt = 'callsign=\'' . $name[ 1 ] . '\' AND version=\'' . $ver[ 1 ] . '\'';
// 				
// 					if ( !$search )
// 					{
// 						listfetch( $opt, $list, 'request', FALSE, TRUE );
// 					}
// 				}else
// 				{
// 					$what[] = $opt;
// 				}
// 			}
// 			if ( !$search )
// 			{
// 				listfetch( '\'' . implode( '\', \'', $what ) . '\'', $list, 'request' );
// 			}
// 		}
// 		if ( $install[ $row[ 'id' ] ] == $row[ 'version' ] )
// 		{
// 			if ( $mode == 'u' || $mode == 'pu' || $mode == 'su' || $from != 'normal' )
// 			{
// 				continue;
// 			}
// 			$mod = ' [R]';
// 		}elseif( !isset( $install[ $row[ 'id' ] ] ) )
// 		{
// 			$mod = ' [N]';
// 		}elseif( $install[ $row[ 'id' ] ] < $row[ 'version' ] )
// 		{
// 			$mod = ' [U <- ' . $install[ $row[ 'id' ] ] . ']';
// 		}elseif( $install[ $row[ 'id' ] ] > $row[ 'version' ] )
// 		{
// 			$mod = ' [D <- ' . $install[ $row[ 'id' ] ] . ']';
// 		}else
// 		{
// 			if ( $mode == 'u' )
// 			{
// 				continue;
// 			}
// 			$mod = '';
// 		}
// 		$modid = to63base( $row[ 'id' ] );
// 		if ( in_array( $modid, $modhash ) )
// 		{
// 			continue;
// 		}
// 		$modhash[] = $modid;
// 		$list[] = ( $mode == 's' || $mode == 'p' || $mode == 'pu' || $mode == 'su' ) ? $row[ 'name' ] . ' [' . $row[ 'version' ] . ']' . $mod : '(' . $modid . ')' . $row[ 'name' ] . ' [' . $row[ 'version' ] . ']';
// 		
// 		if ( !empty( $row[ 'spawn' ] ) )
// 		{
// 			$sql_what = explode( ';', $row[ 'spawn' ] );
// 			$what = array();
// 			for ( $i = 0; $i < count( $sql_what ); $i++ )
// 			{
// 				$opt = $sql_what[ $i ];
// 				if ( strpos( $opt, '[' ) !== FALSE )
// 				{
// 					preg_match( '#(.*?)\[#', $opt, $name );
// 					preg_match( '#\[(.*?)\]#', $opt, $ver );
// 			
// 					$opt = 'callsign=\'' . $name[ 1 ] . '\' AND version=\'' . $ver[ 1 ] . '\'';
// 			
// 					if ( !$search )
// 					{
// 						listfetch( $opt, $list, 'spawn', FALSE, TRUE );
// 					}
// 				}else
// 				{
// 					$what[] = $opt;
// 				}
// 			}
// 			if ( !$search )
// 			{
// 				listfetch( '\'' . implode( '\', \'', $what ) . '\'', $list, 'spawn' );
// 			}
// 		}
// 	}
// 	
// }

// here's the new one, I feel sad, that was a lot of code
function listfetch( $sql_what, &$list, $from = 'normal', $search = FALSE, $vercheck = FALSE )
{
	global $db, $USE, $install, $mode, $modhash;
	
	if ( $vercheck )
	{ // special version was requested
		$sql = "SELECT * FROM " . DISTROMODULES_TABLE	 . " WHERE $sql_what";
	}else
	{ // wasn't, a different query is needed for search
		$sql = ( !$search ) ? "SELECT * FROM " . DISTROMODULES_TABLE . " WHERE callsign IN ( $sql_what )" : "SELECT d.*, l.description FROM " . DISTROMODULES_TABLE	 . " d LEFT JOIN " . MODLIST_TABLE . " l ON d.id=l.id WHERE ( d.callsign LIKE $sql_what || d.name LIKE $sql_what ";
		if ( $search )
		{
			$sql .= ( $mode == 'sd' ) ? " || description LIKE $sql_what )" : ')';
		}
	}
	
	if ( !$res = $db->sql_query( $sql ) )
	{
		$list[] = 'Sql Problem';
		return;
	}
	
	// now we loop through the result and do the stuff :D
	while ( $row = $db->sql_fetchrow( $res ) )
	{
		if ( !empty( $row[ 'use_opts' ] ) && !$search )
		{ // we need to check for USE stuff
			$sql_what = '';
			$opts = explode( ';', $row[ 'use_opts' ] );
			for ( $i = 0; $i < count( $opts ); $i++ )
			{ // make the needed part of the sql querry
				$opt = $opts[ $i ];
				if ( strpos( $opt, '[' ) )
				{ // specific version is requested
					// extract version and callsign
					preg_match( '#(.*?)\[#', $what, $name );
					preg_match( '#\[(.*?)\]#', $what, $ver );
			
					if ( in_array( $name[ 1 ], $USE ) )
					{ // only add it if needed
						$sql_what = 'callsign=\'' . $name[ 1 ] . '\' AND version=\'' . $ver[ 1 ] . '\'';
						// recursion
						listfetch( $sql_what, $list, 'USE', FALSE, TRUE );
					}
					continue;
				}
				if ( in_array( $opt, $USE ) )
				{ // add it if it's in USE
					$sql_what .= $opt . '\', \'';
				}
			}
			if ( !empty( $sql_what ) )
			{ // we got something
				listfetch( '\'' . substr( $sql_what, 0, -4 ) . '\'', $list, 'USE' );
			}
		}
		
		if ( !empty( $row[ 'request' ] ) && !$search )
		{ // we need to check for dependancies
			$sql_what = explode( ';', $row[ 'request' ] );
			$what = array();
			for ( $i = 0; $i < count( $sql_what ); $i++ )
			{ // loop through requests
				$opt = $sql_what[ $i ];
				if ( strpos( $opt, '[' ) !== FALSE )
				{ // need special version
					preg_match( '#(.*?)\[#', $opt, $name );
					preg_match( '#\[(.*?)\]#', $opt, $ver );
			
					$opt = 'callsign=\'' . $name[ 1 ] . '\' AND version>=\'' . $ver[ 1 ] . '\'';
				
					listfetch( $opt, $list, 'request', FALSE, TRUE );
				}else
				{
					$what[] = $opt;
				}
			}
			listfetch( '\'' . substr( implode( '\', \'', $what ), 0, -4 ) . '\'', $list, 'request' );
		}
		
		// here we check for what special info to add to the list
		// or if it needs to go into the list
		if ( $install[ $row[ 'id' ] ] == $row[ 'version' ] )
		{ // the versions are the same
			if ( strpos( $mode, 'u' ) === TRUE || $from != 'normal' )
			{ // only add if this is not an update and is not from a request or something
				continue;
			}
			$mod = '[color=yellow]R[/color]';
		}elseif( !isset( $install[ $row[ 'id' ] ] ) )
		{ // not installed before
			$mod = '[color=green]N[/color]';
		}elseif( $install[ $row[ 'id' ] ] < $row[ 'version' ] )
		{ // version is lower, so this is an upgrade
			$mod = '[color=#9966FF]U[/color] <- ' . $install[ $row[ 'id' ] ];
		}elseif( $install[ $row[ 'id' ] ] > $row[ 'version' ] )
		{ // version is higher so this is a downgrade
			$mod = '[color=#9900FF]D[/color] <- ' . $install[ $row[ 'id' ] ];
		}
		// fetch restriction thingy
		if ( $row[ 'fetch_restrict' ] )
		{
			$mod .= ' [b][color=red]F[/color][/b]';
		}
		
		// now we put it into the list
		$modid = to63base( $row[ 'id' ] ); // saves room
		if ( in_array( $modid, $modhash ) )
		{ // if the mod is already in the list go away
			continue;
		}
		
		if ( $row[ 'version' ] != '0.0.0' )
		{ // don't add meta modules
			if ( strpos( $mode, 'p' ) !== FALSE || strpos( $mode, 's' ) !== FALSE )
			{ // add the special jazz only if list will be printed
				$list[] = '[b]{noparse}' . $row[ 'name' ] . '{/noparse}[/b] &#91;[i]' . $row[ 'version' ] . '[/i]&#93; &#91;' . $mod . '&#93;';
				// add the description if a search
				if ( $search )
				{
					$list[] = $row[ 'description' ];
					$list[] = '';
				}
			}else
			{ // clean list, for usage
				$add = '(' . $modid . ')' . $row[ 'name' ] . ' [' . $row[ 'version' ] . ']';
				$list[] = ( $row[ 'fetch_restrict' ] ) ? $add . 'F' : $add;
			}
			$modhash[] = $modid; // add to hash of list
		}
		
		if ( !empty( $row[ 'spawn' ] ) && !$search )
		{ // we need to check for spawns
			$sql_what = explode( ';', $row[ 'spawn' ] );
			$what = array();
			for ( $i = 0; $i < count( $sql_what ); $i++ )
			{ // loop through spawns
				$opt = $sql_what[ $i ];
				if ( strpos( $opt, '[' ) !== FALSE )
				{ // need special version
					preg_match( '#(.*?)\[#', $opt, $name );
					preg_match( '#\[(.*?)\]#', $opt, $ver );
			
					$opt = 'callsign=\'' . $name[ 1 ] . '\' AND version>=\'' . $ver[ 1 ] . '\'';
					listfetch( $opt, $list, 'spawn', FALSE, TRUE );
				}else
				{
					$what[] = $opt;
				}
			}
			listfetch( '\'' . substr( implode( '\', \'', $what ), 0, -4 ) . '\'', $list, 'spawn' );
		}
	}
}

function listcfetch( &$list, $what, $pretend )
{
	global $db, $USE, $install, $mode, $modhash;
	
	$sql = "SELECT * FROM " . DISTROMODULES_TABLE . " WHERE callsign='$what' OR request LIKE '%$what%'";
	$res = $db->sql_query( $sql );
	
	while ( $row = $db->sql_fetchrow( $res ) )
	{
		if ( !in_array( $row[ 'callsign' ], $modhash ) && !in_array( $row[ 'id' ], $install ) )
		{
			$modhash[] = $row[ 'callsign' ];
			
			$cleans = explode( ';', $row[ 'request' ] );
			$cleans = array_merge( $cleans, explode( ';', $row[ 'spawn' ] ) );
			
			for ( $i = 0; $i < count( $cleans ); $i++ )
			{
				listcfetch( $list, $cleans[ $i ], $pretend );
			}
		}
		
		if ( $pretend )
		{
			$list[] = '[b]' . $row[ 'name' ] . '[/b]';
		}else
		{
			$list[] = '(' . to63base( $row[ 'id' ] ) . ')' . $row[ 'name' ];
		}
	}
}

function to63base( $num )
{
	$table = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'U', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'u', 'x', 'y', 'z' );
		
	$out = '';
	while ( $num != 0 )
	{
		$out = $table[ ( $num % 63 ) ] . $out;
		$num = floor( $num / 63 );
	}
	return $out;
}
	
function pot( $b, $p )
{
	$o = $b;
	if ( $p == 0 )
	{
		$o = 1;
	}elseif ( $p >= 1 )
	{
		for ( $p = $p; $p > 1; $p-- )
		{
			$o *= $b;
		}
	}else
	{
		for ( $p = $p; $p < -1; $p++ )
		{
			$o *= $b;
		}
		if ( ( $p % 2 ) == 1 )
		{
			$o = -$o;
		}else
		{
			$o = abs( $o );
		}
	}
	return $o;
}
	
function from63base( $num )
{
	$table = array( '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, 'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19,'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29, 'V' => 30, 'W' => 31, 'U' => 32, 'X' => 33, 'Y' => 34, 'Z' => 35, 'a' => 36, 'b' => 37, 'c' => 38, 'd' => 39, 'e' => 40, 'f' =>41, 'g' => 42, 'h' => 43, 'i' => 44, 'j' => 45, 'k' => 46, 'l' => 47, 'm' => 48, 'n' => 49, 'o' => 50, 'p' => 51, 'q' => 52, 'r' => 53, 's' => 54, 't' => 55, 'u' => 56, 'v' => 57, 'w' => 58, 'u' => 59, 'x' => 60, 'y' => 61, 'z' => 62 );
	
	$out = 0;
	$i = 0;
	$p = strlen( $num ) - 1;
	$num = strval( $num );
	while ( $i < strlen( $num ) )
	{
		$out += $table[ $num{ $i } ] * pot( 63, $p );
		$i++;
		$p--;
	}
	return $out;
}

function deparseurl( $data )
{
	$data = explode( '|', str_replace( '!!', ' ', $data ) );
	return $data;
}

error_reporting( E_ALL ); // notices are hard to avoid
// echo 'BU';

$mode = ( isset( $_GET[ 'm' ] ) ) ? strval( $security->parsevar( $_GET[ 'm' ], REM_SLASHES ) ) : '';

switch ( $mode )
{
	case 'conf': // config
		$output = @file_get_contents( $Cl_root_path . 'files/summon_config.php' );
		break;
	case 'p': // pretend
	case 's': // search
	case 'u': // update
	case 'pu': // pretend update
	case 'su': // search update
	case 'r': // real
	case 'sd': // searchdesc
		$what = ( isset( $_GET[ 'w' ] ) || empty( $_GET[ 'w' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'w' ], REM_SLASHES ) ) : array();
		$USE = ( isset( $_GET[ 'U' ] ) || empty( $_GET[ 'U' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'U' ], REM_SLASHES ) ) : array();
		$installed = ( isset( $_GET[ 'i' ] ) || empty( $_GET[ 'i' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'i' ], REM_SLASHES ) ) : array();
		$list = array();
		
// 		$list[] = $mode;
		
		if ( !empty( $installed ) )
		{
			foreach ( $installed as $inst )
			{
				if ( empty( $inst ) )
				{
					continue;
				}
				$inst = explode( '-', $inst );
				$install[ from63base( $inst[ 0 ] ) ] = $inst[ 1 ];
			}
		}
		
		$check = implode( '|', $what );
		$vercheck = FALSE;
		if ( strpos( $check, '[' ) !== FALSE )
		{
			foreach ( $what as $opt )
			{
				if ( strpos( $opt, '[' ) )
				{
					$what = $opt;
					$vercheck = TRUE;
					break;
				}
			}
		}
	
		if ( !$vercheck )
		{
			$sql_what = ( $mode != 's' && $mode != 'sd' ) ? '\'' . implode( '\', \'', $what ) . '\'' : '\'%' . $what[ 0 ] . '%\'';
		
			listfetch( $sql_what, $list, 'normal', ( $mode == 's' || $mode == 'sd' ) ? TRUE : FALSE );
		}else
		{
			preg_match( '#(.*?)\[#', $what, $name );
			preg_match( '#\[(.*?)\]#', $what, $ver );
			
			$sql_what = 'callsign=\'' . $name[ 1 ] . '\' AND version=\'' . $ver[ 1 ] . '\'';
			
			listfetch( $sql_what, $list, 'normal', FALSE, TRUE );
		}
		
		if ( empty( $list ) )
		{
			$list[] = 'Nothing found';
		}
		
		$output = implode( "\n", $list );
		break;
	case 'pc': // pretendclean
	case 'c': // clean
		$what = ( isset( $_GET[ 'w' ] ) || empty( $_GET[ 'w' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'w' ], REM_SLASHES ) ) : array();
		$USE = ( isset( $_GET[ 'U' ] ) || empty( $_GET[ 'U' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'U' ], REM_SLASHES ) ) : array();
		$installed = ( isset( $_GET[ 'i' ] ) || empty( $_GET[ 'i' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'i' ], REM_SLASHES ) ) : array();
		$list = array();
		
		if ( !empty( $installed ) )
		{
			foreach ( $installed as $inst )
			{
				if ( empty( $inst ) )
				{
					continue;
				}
				$inst = explode( '-', $inst );
				$install[ from63base( $inst[ 0 ] ) ] = $inst[ 1 ];
			}
		}
		
		$sql = "SELECT id FROM " . DISTROMODULES_TABLE	 . " WHERE callsign='" . $what[ 0 ] . "'";
		$res = $db->sql_query( $sql );
		$row = $db->sql_fetchrow( $res );
		if ( $db->sql_numrows( $res ) == 0 || !array_key_exists( $row[ 'id' ], $install ) )
		{
			$list[] = '[color=red][b]Module ' . $what[ 0 ] . ' not installed[/b][/color]';
		}else
		{
			listcfetch( $list, $what[ 0 ], ( $mode == 'pc' ) ? TRUE : FALSE );
		}
		
		if ( empty( $list ) )
		{
			$list[] = 'Nothing found';
		}
		
		$output = implode( "\n", $list );
		break;
	case 'm': // filemap
	case 'gz': // zip
		$what = ( isset( $_GET[ 'w' ] ) || empty( $_GET[ 'w' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'w' ], REM_SLASHES ) ) : '';
		$output = '';
		
		preg_match( '#(.*?) \[#', $what[ 0 ], $name );
		preg_match( '#\[(.*?)\]#', $what[ 0 ], $ver );
		
		if ( $mode == 'gz' )
		{
			$zip = @file_get_contents( $Cl_root_path . 'files/' . $name[ 1 ] . '/' . $ver[ 1 ] . '/files.zip' );
			$zip .= 'thisSeparatorLikelyWillNotColide' . md5_file( $Cl_root_path . 'files/' . $name[ 1 ] . '/' . $ver[ 1 ] . '/files.zip' );
		}
		
		$output = ( $mode == 'm' ) ? @file_get_contents( $Cl_root_path . 'files/' . $name[ 1 ] . '/' . $ver[ 1 ] . '/filemap.txt' ) : $zip;
		break;
	case 'gs': // sql
	case 'gc': // config map
		$what = ( isset( $_GET[ 'w' ] ) || empty( $_GET[ 'w' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'w' ], REM_SLASHES ) ) : '';
			
		preg_match( '#(.*?) \[#', $what[ 0 ], $name );
		preg_match( '#\[(.*?)\]#', $what[ 0 ], $ver );
		
		$output = ( $mode == 'gs' ) ? @file_get_contents( $Cl_root_path . 'files/' . $name[ 1 ] . '/' . $ver[ 1 ] . '/sql.sql' ) : @file_get_contents( $Cl_root_path . 'files/' . $name[ 1 ] . '/' . $ver[ 1 ] . '/config.txt' );
		
		// update the summon count
		if ( $mode == 'gs' )
		{
			$sql = "UPDATE " . DISTROMODULES_TABLE . " SET summons=summons+1 WHERE name='" . $name[ 1 ] . "' AND version='" . $ver[ 1 ] . "'";
			$db->sql_query( $sql );
		}
		
		if ( empty( $output ) && $mode == 'gs' )
		{
			$output = "\n";
		}
		break;
	case 'ms': // module sql
		$what = ( isset( $_GET[ 'w' ] ) || empty( $_GET[ 'w' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'w' ], REM_SLASHES ) ) : array();
		$installed = ( isset( $_GET[ 'i' ] ) || empty( $_GET[ 'i' ] ) ) ? deparseurl( $security->parsevar( $_GET[ 'i' ], REM_SLASHES ) ) : array();
		$output = '';
		
		$sql = "SELECT callsign FROM " . DISTROMODULES_TABLE	 . " WHERE id='" . from63base( $what[ 0 ] ) . "'";
		$res = $db->sql_query( $sql );
		$row = $db->sql_fetchrow( $res );
		$callsign = $row[ 'callsign' ];
		
		$sql = "SELECT * FROM " . MODULES_HASH_TABLE . " WHERE callsign='$callsign'";
		$res = $db->sql_query( $sql );
		$mod = $db->sql_fetchrow( $res );
		$mod[ 'announce_methods' ] = explode( ';', $mod[ 'announce_methods' ] );
		$mod[ 'accept_methods' ] = explode( ';', $mod[ 'accept_methods' ] );
		
		$ids = array();
		foreach ( $installed as $inst )
		{
			$inst = explode( '-', $inst );
			$id = from63base( $inst[ 0 ] );
			$ids[] = "id='$id'";
		}
		$ids = implode( ' OR ', $ids );
		$sql = "SELECT * FROM " . MODULES_HASH_TABLE . " WHERE $ids";
		$res = $db->sql_query( $sql );
		
		$queries = array();
		
		$hash[ 'ann' ] = array();
		$hash[ 'acc' ] = array();
		
		while ( $row = $db->sql_fetchrow( $res ) )
		{
			foreach ( $mod[ 'announce_methods' ] as $method )
			{
				if ( strpos( $row[ 'accept_methods' ], $method ) !== FALSE )
				{
					$name = $row[ 'callsign' ];
					$parent = '$old,?' . $mod[ 'callsign' ] . '?';
					$methods = '$old';
					
					if ( in_array( $mod[ 'callsign' ], $hash[ 'ann' ] ) )
					{
						continue;
					}
					
					$queries[] = "INSERT INTO ClB_modules ( mod_name, mod_parent, mod_methods ) VALUES ( $name, $parent, $methods );";
					
					$hash[ 'ann' ][] = $mod[ 'callsign' ];
				}
			}
			foreach ( $mod[ 'accept_methods' ] as $method )
			{
				if ( strpos( $row[ 'announce_methods' ], $method ) !==  FALSE )
				{
					$name = $mod[ 'callsign' ];
					$parent = '$old,?' . $row[ 'callsign' ] . '?';
					$methods = $mod[ 'mod_methods' ];
					
					if ( in_array( $mod[ 'callsign' ], $hash[ 'acc' ] ) )
					{
						continue;
					}
					
					$queries[] = "INSERT INTO ClB_modules ( mod_name, mod_parent, mod_methods ) VALUES ( $name, $parent, $methods );";
					
					$hash[ 'acc' ][] = $mod[ 'callsign' ];
				}
			}
		}
		$output = implode( "\n", $queries );
		if ( empty( $output ) )
		{
			$output = "\n";
		}
		break;
}

// print_R( $list );

ob_clean();
header( 'Content-type: file/text' );
header ( 'Content-Disposition: attachment; filename="output.txt"' );
echo $output;
exit;

?>