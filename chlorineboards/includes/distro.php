<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:                distro.php                           //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:      04th November 2005                       //
//     version:               0.6.5                              //
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

//
// this module deals with everything that has to do with distributing clb modules
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// debug :: debug flag
// gui :: the gui subclass
// lang :: the language vars

// class creation
$vars = array( 'debug', 'gui', 'lang' );
$visible = array( 'private', 'private', 'private' );
eval( Varloader::createclass( 'distro', $vars, $visible ) );
// end class creation

class Distro extends distro_def
{
	
	// constructor
	function Distro( $debug = FALSE )
	{
		global $Cl_root_path, $cache, $lang_loader;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/distro_gui' . phpEx );
		$this->gui = new Distro_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'distro' );
		$this->gui->lang = $this->lang;
	}
	
	// this chooses what to run in order to produce the display
	function display()
	{
		global $security, $basic_gui, $db, $errors, $basic_lang, $userdata, $board_config;
		
		// this is for console DLs
		if ( isset( $_GET[ 'files' ] ) )
		{ // needed
			$file = strval( $_GET[ 'files' ] );
			// determine the type
			$type = str_replace( 'console.', '', $file );
			if ( $type == 'zip' )
			{
				$type = 'x-zip';
			}elseif ( $type = 'tar.gz' )
			{
				$type = 'x-tgz';
			}else
			{
				$type = 'x-tbz';
			}
			// output the file
			ob_clean();
			header( 'Content-type: application/' . $type );
			header ( 'Content-Disposition: attachment; filename="' . $file . '"' );
			echo file_get_contents( 'http://' . $board_config[ 'session_domain' ] . $board_config[ 'session_path' ] . 'files/' . $file );
			exit;
		}
		
		// read the submode first
		$mode = ( isset( $_GET[ SUBMODE_URL ] ) ) ? strval( $security->parsevar( $_GET[ SUBMODE_URL ], REM_SLASHES ) ) : 'index';
		
		// the page title
		$basic_gui->set_title( ( $mode != 'index' ) ? $basic_lang[ 'Distribution' ] : '' );
		
		// do as the mode commands
		switch ( $mode )
		{
			case 'index';
				$basic_gui->set_level( 1, 'distro' );
				$this->gui->display_index();
				break;
			case 'dl':
				$basic_gui->set_level( 2, 'distro' );
				$this->gui->display_dl();
				break;
			case 'getkey':
				// output the admin key
				ob_clean();
				header( 'Content-type: file/text' );
				header ( 'Content-Disposition: attachment; filename="mykey.dat"' );
				echo str_shuffle( 'aGedFDTkomzJyVjgHpqSZLAcBIUrCfvKsMbEOihwulWNRYnxPXtQ' ) . time();
				exit;
				break;
			case 'list':
				$basic_gui->set_level( 2, 'distro', '0,1' );
				// read the subsubmode (section to be precise)
				$sect = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : 'index';
				// do a bit of switching according to this
				switch ( $sect )
				{
					case 'index':
						$this->gui->listindex( );
						break;
					default:
						// read the list first eh
						$sql = "SELECT l.*, d.*, h.announce_methods, h.accept_methods, h.mod_methods, u.username AS author  FROM " . MODLIST_TABLE . " l LEFT JOIN " . DISTROMODULES_TABLE . " d ON l.callsign=d.callsign LEFT JOIN " . USERS_TABLE . " u ON u.user_id=l.author LEFT JOIN " . MODULES_HASH_TABLE . " h ON l.callsign=h.callsign WHERE l.section='$sect' ORDER BY l.time ASC";
						// query it
						if ( !$res = $db->sql_query( $sql ) )
						{
							$errors->report_error( 'Could not query list', CRITICAL_ERROR );
						}
						// create the list
						$list = array();
						while ( $row = $db->sql_fetchrow( $res ) )
						{
							// expand the verson
							if ( is_dir( $Cl_root_path . 'files/' . $row[ 'name' ] ) ) // some modules don't have a directory
							{ // there is one
								$row[ 'version' ] = array();
								if ( PHPVER >= 5.0 )
								{ // we can do this the simple way :)
									$row[ 'version' ] = scandir( $Cl_root_path . 'files/' . $row[ 'name' ] );
								}else
								{ // the old way :(
									$d = dir( $Cl_root_path . 'files/' . $row[ 'name' ] );
									while ( FALSE !== ( $entry = $d-> read() ) )
									{
										$row[ 'version' ][] = $entry;
									}
									$d->close();
								}
								// the first two are . and .. so we remove them
								$row[ 'version' ] = array_slice( $row[ 'version' ], 2 );
							}else
							{ // there isn't one
								$row[ 'version' ] = array( $row[ 'version' ] );
							}
							$list[] = $row;
						}
						$this->gui->display_list( $list, $sect );
						break;
				}
				break;
			case 'add':
				$basic_gui->set_level( 2, 'distro', '0,2' );
				
				// first check if the user can do this
				if ( !( $userdata[ 'user_level' ] >= ADMIN ) )
				{
					$errors->report_error( sprintf( $basic_lang[ 'Need_login' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
				}
				
				// generate the callsign list
				$sql = "SELECT callsign FROM " . DISTROMODULES_TABLE . " ORDER BY callsign ASC";
				// query it
				if ( !$res = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Could not query list', CRITICAL_ERROR );
				}
				$list = $db->sql_fetchrowset( $res );
				// read the section
				$sect = ( isset( $_GET[ 'w' ] ) ) ? $security->parsevar( $_GET[ 'w' ], REM_SLASHES ) : '';
				$this->gui->display_add( $list, $sect );
				break;
			case 'upmod':
				$basic_gui->set_level( 2, 'distro', '0,2' );
				// since this requiers fanciness we use a different function
				$this->upload();
				break;
		}
	}
	
	// chmods every parent leading to a file so the file can be chmodded (pretty sure it's needed)
	function chmodparent( $folder, $mode )
	{
		$fold = explode( '/', $folder );
		$folder = '';
		
		foreach ( $fold as $now )
		{
			$folder .= $now . '/';
			if ( $now == '.' || $now == '..' )
			{
				continue;
			}
			$change = '$good = @chmod( \'' . $folder . '\', ' . $mode . ' );';
			eval( $change );
			if ( !$good )
			{
				return FALSE;
			}
		}
		return TRUE;
	}
	
	// used to compare version numbers
	function verscmp( $v1, $v2 )
	{
		$v1 = explode( '.', $v1 );
		$v2 = explode( '.', $v2 );
		for ( $i = 0; $i < count( $v1 ); $i++ )
		{
			if ( $v1[ $i ] > $v2[ $i ] )
			{
				return 1;
			}elseif (  $v2[ $i ] < $v2[ $i ] )
			{
				return -1;
			}
		}
		return 0;
	}
	
	// this kinda only deals with the uploads of modules
	function upload( )
	{
		global $security, $errors, $Cl_root_path, $db, $basic_lang, $userdata;
		
		// first check if the user can do this
		if ( !( $userdata[ 'user_level' ] >= ADMIN ) )
		{
			$errors->report_error( sprintf( $basic_lang[ 'Need_login' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
		}
		
		// check if the form was submitted correctly
		if ( !isset( $_POST[ 'uploadmod' ] ) )
		{// not
			$errors->report_error( $this->lang[ 'add_errform' ], CRITICAL_ERROR );
		}
		
		// first we check the files to see if there are enough and whatnot
		$files = array( 'files', 'filemap', 'config', 'sql' ); // will simplify stuff
		$uploadedfiles = 0; // init counter
		foreach ( $files as $f )
		{
			if ( isset( $_FILES[ $f ] ) && $_FILES[ $f ][ 'error' ] != 4 && !empty( $_FILES[ $f ] ) )
			{ // file uploaded
				// now check if something was wrong
				if ( $_FILES[ $f ][ 'error' ] != 0 )
				{ // wrongness
					switch ( $_FILES[ $f ][ 'error' ] )
					{
						case 1: $errors->report_error( sprintf( $this->lang[ 'add_errsize1' ], $f ), GENERAL_ERROR ); break; // size in php.ini
						case 2: $errors->report_error( sprintf( $this->lang[ 'add_errsize2' ], $f ), GENERAL_ERROR ); break; // size in HTML
						case 3: $errors->report_error( sprintf( $this->lang[ 'add_errpart' ], $f ), GENERAL_ERROR ); break; // partial upload
						case 6: $errors->report_error( $this->lang[ 'add_errtmp' ], GENERAL_ERROR ); break; // no temp (php4.3.10 & 5.0.3)
						case 7: $errors->report_error( sprintf( $this->lang[ 'add_errwrt' ], $f ), GENERAL_ERROR ); break; // no write (php 5.1.0)
					}
				}else
				{ // we're coolish
					if ( is_uploaded_file( $_FILES[ $f ][ 'tmp_name' ] ) )
					{ // yep everything's fine except perhaps...
						// check the mime of the file
						switch ( $f )
						{
							case 'files':
								if ( $_FILES[ $f ][ 'type' ] != 'application/zip' )
								{
									$errors->report_error( sprintf( $this->lang[ 'add_errmime' ], $f, $_FILES[ $f ][ 'type' ], 'application/x-zip' ), GENERAL_ERROR );
								}
								break;
							case 'map':
							case 'config':
								if ( $_FILES[ $f ][ 'type' ] != 'text/plain' )
								{
									$errors->report_error( sprintf( $this->lang[ 'add_errmime' ], $f, $_FILES[ $f ][ 'type' ], 'text/plan' ), GENERAL_ERROR );
								}
								break;
							case 'sql':
								if ( $_FILES[ $f ][ 'type' ] != 'text/plain' && $_FILES[ $f ][ 'type' ] != 'application/octet-stream' )
								{
									$errors->report_error( sprintf( $this->lang[ 'add_errmime' ], $f, $_FILES[ $f ][ 'type' ], 'text/plan' ), GENERAL_ERROR );
								}
								break;
						}
						$checked[ 'files' ][ $f ] = $_FILES[ $f ]; // add to array of checked entries
						$uploadedfiles++;
					}else
					{ // no no no, not this
						$errors->report_error( sprintf( $this->lang[ 'add_hackerr' ], $f ), CRITICAL_ERROR );
					}
				}
			}
		}
		
		// now we can go through all the needed $_GETs and sanitise a bit
		$gets = array( 'name', 'version', 'description', 'callsign', 'spawn', 'request', 'fetch_restrict', 'useopts' ); // simplify
		foreach ( $gets as $g )
		{
			// check if set
			if ( isset( $_POST[ $g ] ) && !empty( $_POST[ $g ] ) )
			{ // is
				if ( $g != 'fetch_restrict' )
				{
					$checked[ 'vars' ][ $g ] = strval( $_POST[ $g ] ); // set the var eh
				}else
				{ // see opts is special :)
					$checked[ 'vars' ][ 'fetch_restrict' ] = intval( $_POST[ 'fetch_restrict' ] );
				}
			}else
			{ // nope
				// gotta see if it's essential
				switch ( $g )
				{
					case 'author':
					case 'name':
					case 'version':
					case 'description':
					case 'callsign':
						$errors->report_error( sprintf( $this->lang[ 'add_errinfo' ], $g ), GENERAL_ERROR );
						break;
					default:
						// again seeuse gets special treatment
						if ( $g == 'fetch_restrict' )
						{
							$checked[ 'vars' ][ 'fetch_restrict' ] = FALSE;
						}else
						{
							$checked[ 'vars' ][ $g ] = '';
						}
						break;
				}
			}
		}
		
		// set autho
		$checked[ 'vars' ][ 'author' ] = $userdata[ 'user_id' ];
		
		// some typing saviours
		$name = $checked[ 'vars' ][ 'name' ];
		$callsign = $checked[ 'vars' ][ 'callsign' ];
		$version = $checked[ 'vars' ][ 'version' ];
		
		// some more checks
		
		// check if the callsign is either unique or this is an update
		$sql = "SELECT callsign, version, name FROM " . DISTROMODULES_TABLE . " WHERE callsign='$callsign'";
		if ( !$res = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not query callsign', CRITICAL_ERROR );
		}
		if ( $db->sql_numrows( $res ) != 0 )
		{ // not unique
			$row = $db->sql_fetchrow( $res );
			if ( ( $this->verscmp( $version, $row[ 'version' ] ) <= 0 ) && ( $name == $row[ 'name' ] ) && ( $version != '0.0.0' && $row[ 'version' ] != '0.0.0' ) )
			{ // not an update, *gasp*
				$errors->report_error( sprintf( $this->lang[ 'add_errcall' ], $callsign ), CRITICAL_ERROR );
			}
			// some dirs need writability
			if ( $uploadedfiles != 0 )
			{ // they indeed do :)
				if ( is_dir( $Cl_root_path . 'files/' . $name ) )
				{ // dir exists and needs to be writable
					if ( !is_writable( $Cl_root_path . 'files/' . $name ) )
					{
						// chmod if possible
						if ( !$this->chmodparent( $Cl_root_path . 'files/' . $name, 0644 ) )
						{
							$errors->report_error( sprintf( $this->lang[ 'add_errperm' ], $Cl_root_path . 'files/' . $name ), GENERAL_ERROR );
						}
					}
				}else
				{ // dir doesn't exist so it might not have been needed before but is now :)
					if ( !is_writable( $Cl_root_path . 'files/' ) )
					{
						// chmod if possible
						if ( !$this->chmodparent( $Cl_root_path . 'files', 0644 ) )
						{
							$errors->report_error( sprintf( $this->lang[ 'add_errperm' ], $Cl_root_path . 'files' ), GENERAL_ERROR );
						}
						@mkdir( $Cl_root_path . 'files/' . $name ); // make the folder
					}else
					{
						@mkdir( $Cl_root_path . 'files/' . $name );
					}
				}
			}
			$unique = FALSE;
		}else
		{ // some dirs need creating
			// check if the root for the files is writable
			if ( !is_writable( $Cl_root_path . 'files/' ) )
			{
				// chmod if possible
				if ( !$this->chmodparent( $Cl_root_path . 'files', 0644 ) )
				{
					$errors->report_error( sprintf( $this->lang[ 'add_errperm' ], $Cl_root_path . 'files' ), GENERAL_ERROR );
				}
			}
			if ( !is_dir( $Cl_root_path . 'files/' . $name ) )
			{
				@mkdir( $Cl_root_path . 'files/' . $name ); // make the folder
			}
			$unique = TRUE;
		}
		
		// now that that's taken care of make the specific directory for this module
		if ( !is_dir( $Cl_root_path . 'files/' . $name . '/' . $version ) )
		{ // the check has to be made due to meta modules
			@mkdir( $Cl_root_path . 'files/' . $name . '/' . $version );
		}
		
		// now move all the files from temp to their destination
		if ( is_array( $checked[ 'files' ] ) )
		{
			foreach ( $checked[ 'files' ] as $i => $f )
			{
				// set the destination name
				$destination = $Cl_root_path . 'files/' . $name . '/' . $version . '/' . $i;
				switch ( $i )
				{
					case 'files': 
						$destination .= '.zip'; 
						break;
					case 'config':
					case 'filemap':
						$destination .= '.txt';
						break;
					case 'sql':
						$destination .= '.sql';
						break;
				}
				if ( !@move_uploaded_file( $f[ 'tmp_name' ], $destination ) )
				{
					// first we need to remove everything that has been done before this (so there isn't stuff left behind)
					foreach ( $checked[ 'files' ] as $fl => $void )
					{
						// build destionation (what to delete) name
						$destination = $Cl_root_path . 'files/' . $name . '/' . $version . '/' . $fl;
						switch ( $fl )
						{
							case 'files': 
								$destionation .= '.zip'; 
								break;
							case 'config':
							case 'filemap':
								$destination .= '.txt';
								break;
							case 'sql':
								$destination .= '.sql';
								break;
						}
						@unlink( $destination );
						if ( $fl == $i )
						{
							break;
						}
					}
					@rmdir( $Cl_root_path . 'files/' . $name . '/' . $version ); // remove the dir too
					
					// then chmod back to normal or delete the main dir if needed
					if ( $unique )
					{
						@rmdir( $Cl_root_path . 'files/' . $name ); // remove the dir
// 						@chmod( $Cl_root_path . 'files/', 0555 );
					}else
					{
// 						@chmod( $Cl_root_path . 'files/' . $name, 0555 );
					}
					
					// then produce the error
					$errors->report_error( sprintf( $this->lang[ 'add_errup' ], $i ), GENERAL_ERROR );
				}
			}
		}
		
		// now chmod all the files and the dirs back to normal
		if ( is_array( $checked[ 'files' ] ) )
		{
			foreach ( $checked[ 'files' ] as $i => $void )
			{
				switch ( $i )
				{
					case 'files': 
						$i .= '.zip'; 
						break;
					case 'config':
					case 'filemap':
						$i .= '.txt';
						break;
					case 'sql':
						$i .= '.sql';
						break;
				}
				@chmod( $Cl_root_path . 'files/' . $name . '/' . $version . '/' . $i, 0444 );
			}
		}
// 		@chmod( $Cl_root_path . 'files/' . $name . '/' . $version, 0555 );
// 		@chmod( $Cl_root_path . 'files/' . $name, 0555 );
// 		@chmod( $Cl_root_path . 'files/', 0555 );
// 		@chmod( $Cl_root_path, 0555 );
		
		// move on to sql changes
		
		// add two values
		$checked[ 'vars' ][ 'time' ] = time();
		$checked[ 'vars' ][ 'section' ] = strval( $_GET[ 'w' ] );
		
		//build the query
		$values = array();
		$values2 = array();
		
		// make a hash because this has to be in exactly the right order :)
		$hash = array( 'id', 'callsign', 'name', 'version', 'spawn', 'request', 'fetch_restrict', 'use_opts', 'summons' );
		$hash2 = array( 'id', 'callsign', 'author', 'description', 'time', 'section' );
		
		// the modhash thingies
		$announce = ( isset( $_POST[ 'announce_methods' ] ) ) ? strval( $_POST[ 'announce_methods' ] ) : '';
		$accept = ( isset( $_POST[ 'accept_methods' ] ) ) ? strval( $_POST[ 'accept_methods' ] ) : '';
		$methods = ( isset( $_POST[ 'mod_methods' ] ) ) ? strval( $_POST[ 'mod_methods' ] ) : '';
		
		if ( $unique )
		{ // fresh insert
			// build the values arrays
			foreach ( $hash as $n )
			{
				$values[] = '\'' . $checked[ 'vars' ][ $n ] . '\'';
			}
			foreach ( $hash2 as $n )
			{
				$values2[] = '\'' . $checked[ 'vars' ][ $n ] . '\'';
			}
		
			$values = implode( ',', $values );
			$values2 = implode( ',', $values2 );
			
			$sql = "INSERT INTO " . DISTROMODULES_TABLE . " VALUES( $values )";
			$sql2 = "INSERT INTO " . MODLIST_TABLE . " VALUES( $values2 )";
			$sql3 = "INSERT INTO " . MODULES_HASH_TABLE . " VALUES( '" . $checked[ 'vars' ][ 'callsign' ] . "', '$announce', '$accept', '$methods' )";
		}else
		{ // gotta update	
			// build the values arrays
			foreach ( $hash as $n )
			{
				// id, callsign, and summons must not get changed
				if ( $n == 'id' || $n == 'callsign' || $n == 'summons' )
				{
					continue;
				}
				$values[] = $n . ' = \''. $checked[ 'vars' ][ $n ] . '\'';
			}
			foreach ( $hash2 as $n )
			{
				// id, callsign, and summons must not get changed
				if ( $n == 'id' || $n == 'callsign' || $n == 'summons' )
				{
					continue;
				}
				$values2[] = $n . ' = \''. $checked[ 'vars' ][ $n ] . '\'';
			}
			
			$values = implode( ',', $values );
			$values2 = implode( ',', $values2 );
			
			$sql = "UPDATE " . DISTROMODULES_TABLE . " SET $values WHERE callsign = '" . $checked[ 'vars' ][ 'callsign' ] . "'";
			$sql2 = "UPDATE " . MODLIST_TABLE . " SET $values2 WHERE callsign = '" . $checked[ 'vars' ][ 'callsign' ] . "'";
			$sql3 = "UPDATE " . MODULES_HASH_TABLE . " SET announce_methods='$announce', accept_methods='$accept', mod_methods='$methods' WHERE callsign = '" . $checked[ 'vars' ][ 'callsign' ] . "'";
		}
		
		if ( !$db->sql_query( $sql ) || !$db->sql_query( $sql2 ) || !$db->sql_query( $sql3 ) )
		{
			$errors->report_error( 'Could not insert into database', CRITICAL_ERROR );
		}
		
		
		// tell the user all went well
		$errors->report_error( sprintf( $this->lang[ 'add_OK' ], $name, $version ), MESSAGE );
	}
	
	
	//
	// End of Distro class
	//
}


?>