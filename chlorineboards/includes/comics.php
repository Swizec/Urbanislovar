<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:                comics.php                           //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        25th March 2006                        //
//     version:               0.5.2                              //
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
// module for uploading and showing comics
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
// comics_list :: list of comics
// comics_hash :: just a simple hashing thingy ^^

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'comics', $vars, $visible ) );
// end class creation

class comics extends comics_def
{
	// constructor
	function comics( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $cache;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/comics_gui' . phpEx );
		$this->gui = new comics_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'comics' );
		$this->gui->lang = $this->lang;
		
		$this->comics_hash = array();
		
		// fetch the comics list from cache
		if ( !$this->comics_list = $cache->pull( 'comics_list', ESSENTIAL ) )
		{
			// guess we need to read it from disk
			if ( !is_readable( $Cl_root_path . 'images/comics/' . $lang_loader->board_lang . '/' ) )
			{
				$this->comics_list = array();
				$this->comics_hash = array();
				return;
			}
			foreach ( $lang_loader->get_langlist() as $lang )
			{ // loop through all the languages
				if ( !is_dir(  $Cl_root_path . 'images/comics/' . $lang . '/' ) )
				{
					// skip, clearly there is nothing for this language
					continue;
				}
				$d = dir( $Cl_root_path . 'images/comics/' . $lang . '/' );
				while ( FALSE !== ( $entry = $d->read( ) ) )
				{
					if ( $entry == '.' || $entry == '..' )
					{
						continue;
					}
					// parse the name
					$comic = explode( '_', $entry );
					$comic[ 1 ] = explode( '.', $comic[ 1 ] );
					$this->comics_list[ $lang ][ $comic[ 0 ] ] = array( 'title' => $comic[ 1 ][ 0 ], 'file' => $entry );
					$this->comics_hash[ $lang ][] = $comic[ 0 ];
				}
				$d->close();
				// now sort the list
				sort( $this->comics_hash[ $lang ] );
			}
			// and put it on cache
			$cache->push( 'comics_list', $this->comics_list, TRUE, ESSENTIAL );
			$cache->push( 'comics_hash', $this->comics_hash, TRUE, ESSENTIAL );
		}elseif ( !$this->comics_hash = $cache->pull( 'comics_hash', ESSENTIAL ) )
		{ // the hash didn't make it
			foreach ( $lang_loader->get_langlist() as $lang )
			{ // loop through all the languages
				foreach ( $this->comics_list[ $lang ] as $k => $void )
				{
					$this->comics_hash[ $lang ][] = $k;
				}
				// now sort the list
				sort( $this->comics_hash[ $lang ] );
			}
			// and put it on cache
			$cache->push( 'comics_hash', $this->comics_hash, ESSENTIAL );
		}
		// fetch the next update
		if ( is_readable( $Cl_root_path . 'images/comics/new_update' ) )
		{
			include( $Cl_root_path . 'images/comics/new_update' );
			$this->gui->next_update = $next_update;
		}else
		{
			$this->gui->next_update = '';
		}
	}
	
	function display()
	{
		global $cache, $errors, $security, $Cl_root_path4template, $userdata, $lang_loader;
		
		$id = ( isset( $_GET[ 'comic' ] ) ) ? intval( $_GET[ 'comic' ] ) : count( $this->comics_hash[ $lang_loader->board_lang ] )-1;
		
		if ( $userdata[ 'user_level' ] != ADMIN )
		{ // don't worry if admin
			if ( isset( $this->comics_hash[ $lang_loader->board_lang ] ) )
			{
				if ( !array_key_exists( $id, $this->comics_hash[ $lang_loader->board_lang ] ) )
				{ // wtf, it isn't there
					$errors->report_error( $this->lang[ 'No_comic' ], GENERAL_ERROR );
				}
			}else
			{ // wtf, it isn't there
				$errors->report_error( $this->lang[ 'No_comic' ], GENERAL_ERROR );
			}
		}
		
		// set up some data
		$first = $security->append_sid( '?' . MODE_URL . '=show_comics&comic=0' );
		$prev = $security->append_sid( '?' . MODE_URL . '=show_comics&comic=' . ( ( $id > 0 ) ? $id-1 : 0 ) );
		$next = $security->append_sid( '?' . MODE_URL . '=show_comics&comic=' . ( ( $id < count( $this->comics_hash[ $lang_loader->board_lang ] )-1 ) ? $id+1 : $id ) );
		$last = $security->append_sid( '?' . MODE_URL . '=show_comics&comic=' . ( count( $this->comics_hash[ $lang_loader->board_lang ] )-1 ) );
		$current = $Cl_root_path4template . 'images/comics/' . $lang_loader->board_lang . '/' . $this->comics_list[ $lang_loader->board_lang ][ $this->comics_hash[ $lang_loader->board_lang ][ $id ] ][ 'file' ];
		
		// go to the gui part
		$this->gui->show_comics( $first, $prev, $next, $last, $current, $id, $this->comics_hash[ $lang_loader->board_lang ], $this->comics_list[ $lang_loader->board_lang ] );
	}
	
	function upload()
	{
		global $errors, $Cl_root_path, $cache, $board_config, $lang_loader;
		
		if ( empty( $_POST[ 'ul_submit' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// get the info
		$title = ( isset( $_POST[ 'ul_title' ] ) ) ? strval( $_POST[ 'ul_title' ] ) : '';
		$file = ( isset( $_FILES[ 'ul_file' ] ) ) ? $_FILES[ 'ul_file' ] : '';
		$language = ( isset( $_POST[ 'language' ] ) ) ? strval( $_POST[ 'language' ] ) : $board_config[ 'def_lang' ];
		$next = ( isset( $_POST[ 'ul_next' ] ) ) ? strval( $_POST[ 'ul_next' ] ) : '';
		
		if ( empty( $title ) || empty( $file ) )
		{ // need this :P
			$errors->report_error( $this->lang[ 'No_data' ], GENERAL_ERROR );
		}
		
		$dir = $Cl_root_path . 'images/comics';
		$dir2 = $dir . '/' . $language;
		
		//
		// deal with the uploaded file
		//
		// the dir setup
		if ( !is_dir( $dir ) )
		{ // top dir doesn't exist
			if ( !@mkdir( $dir, 0555 ) )
			{
				$errors->report_error( $this->lang[ 'No_mkdir' ], GENERAL_ERROR );
			}
		}
		if ( !is_dir( $dir2 ) )
		{ // specific dir doesn't exist
			if ( !is_writable( $dir ) )
			{ // not writable top dir
				if ( !@chmod( $dir, 0755 ) )
				{ // couldn't make writable top dir
					$errors->report_error( $this->lang[ 'No_chmod' ], GENERAL_ERROR );
				}
			}
			if ( !@mkdir( $dir2, 0755 ) )
			{ // couldn't create specific dir
				$errors->report_error( $this->lang[ 'No_mkdir' ], GENERAL_ERROR );
			}
		}elseif ( !is_writable( $dir2 ) )
		{ // not writable specific dir
			if ( !@chmod( $dir, 0755 ) )
			{ // couldn't make writable specific dir
				$errors->report_error( $this->lang[ 'No_chmod' ], GENERAL_ERROR );
			}
		}
		
		// check the meta
		if ( strpos( $file[ 'type' ], 'image' ) === FALSE )
		{ // not an image
			$errors->report_error( $this->lang[ 'No_image' ], GENERAL_ERROR );
		}
		// check the error message
		if ( $file[ 'error' ] > 0 )
		{
			$errors->report_error( $this->lang[ 'No_ul' ], GENERAL_ERROR );
		}
		// now put it where it belongs
		$source = $file[ 'tmp_name' ];
		$t = time();
		$dest = $dir2 . '/' . $t . '_' . $title . substr( strrchr( $file[ 'name' ], '.' ), 0 );
		if  ( !@move_uploaded_file( $source, $dest ) )
		{
			$errors->report_error( $this->lang[ 'No_ul' ], GENERAL_ERROR );
		}
		
		// quickly add this to cache
		$this->comics_list[ $language ][ $t ] = array( 'title' => $title, 'file' => $t . '_' . $title . substr( strrchr( $file[ 'name' ], '.' ), 0 ) );
		$this->comics_hash[ $language ][] = $t;
		$cache->delete( 'comics_list', $this->comics_list[ $lang ], TRUE, ESSENTIAL );
		$cache->delete( 'comics_hash', $this->comics_hash[ $lang ], TRUE, ESSENTIAL );
		
		// quickly jot down the next update
		$f = @fopen( $dir . '/new_update', 'wb' );
		@fwrite( $f, '<?php $next_update=\'' . $next . '\'; ?>' );
		@fclose( $f );
		
		// all went well
		$errors->report_error( $this->lang[ 'Done' ], MESSAGE );
		
		// rechmod the dir and file
		@chmod( $dest, 0444 );
		@chmod( $dir, 0555 );
		@chmod( $dir2, 0555 );
	}
	
	//
	// End of comics class
	//
}

?>