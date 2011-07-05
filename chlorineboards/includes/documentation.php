<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:             documentation.php                       //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       06th November 2005                      //
//     version:               0.3.2                              //
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
// for managing documentation and stuff
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
// lang :: the language,doh

// class creation
$vars = array( 'debug', 'gui', 'lang' );
$visible = array( 'private', 'private', 'private' );
eval( Varloader::createclass( 'doc', $vars, $visible ) );
// end class creation

class Documentation extends doc_def
{
	
	// constructor
	function Documentation( $debug = FALSE )
	{
		global $Cl_root_path, $cache, $lang_loader, $Sajax;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/documentation_gui' . phpEx );
		$this->gui = new Documentation_gui( );
		// fetch the lang
		$this->lang = $lang_loader->get_lang( 'documentation' );
		$this->gui->lang = $this->lang; // shove it over to the gui too
		$Sajax->add2export( 'Documentation->editarticle', '$article_id' );
		$Sajax->add2export( 'Documentation->editarticle2', '$article_id, $title, $text' );
	}
	
	function show_doc()
	{
		global $security, $basic_gui, $db, $errors, $userdata, $basic_lang, $mod_loader;
		
		// first read the mode eh
		$mode = ( isset( $_GET[ SUBMODE_URL ] ) ) ? strval( $security->parsevar( $_GET[ SUBMODE_URL ], REM_SLASHES ) ) : 'index';
		
		// at some times reading from the db is needed (when displaying the list of articles)
		if ( $mode == 'admin' || $mode == 'user' || $mode == 'dev' )
		{
			// because we removed the slashes earlier we put 'em back
			$sect = $security->parsevar( $mode, ADD_SLASHES );
			// read from the db
			$sql = "SELECT d.doc_title, d.doc_id, d.doc_time, u.username AS doc_user FROM " . DOCS_TABLE . " d LEFT JOIN " . USERS_TABLE . " u ON d.doc_user=u.user_id WHERE doc_sect='$sect' ORDER BY doc_time DESC";
			if ( !$res = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not query data', GENERAL_ERROR );
			}
			// fetch list
			$list = $db->sql_fetchrowset( $res );
		}
		
		$basic_gui->set_title( $basic_lang[ 'Documentation' ] );
		
		// do upon the mode
		switch ( $mode )
		{
			case 'index':
				$basic_gui->set_level( 1, 'doc' );
				$this->gui->index();
				break;
			case 'admin':
				$basic_gui->set_level( 2, 'doc', '0,0' );
				$this->gui->show_list( $list, $mode );
				break;
			case 'user':
				$basic_gui->set_level( 2, 'doc', '0,1' );
				$this->gui->show_list( $list, $mode );
				break;
			case 'dev':
				$basic_gui->set_level( 2, 'doc', '0,2' );
				$this->gui->show_list( $list, $mode );
				break;
			case 'show':
				$id = ( isset( $_GET[ 'w' ] ) ) ? intval( $_GET[ 'w' ] ) : 0;
				// read it
				$sql = "SELECT d.doc_text, d.doc_title, d.doc_sect, d.doc_time, d.doc_id, d.doc_user AS user_id, u.username AS doc_user FROM " . DOCS_TABLE . " d LEFT JOIN ". USERS_TABLE . " u ON d.doc_user=u.user_id WHERE d.doc_id='$id'";
				if ( !$res = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Could not query data', GENERAL_ERROR );
				}
				$row = $db->sql_fetchrow( $res );
				// check if user can edit this
				$row[ 'user_edit' ] = ( $userdata[ 'user_id' ] == $row[ 'user_id' ] ) ? TRUE : FALSE;
				// to add to pagination we must decypher the section
				switch ( $row[ 'doc_sect' ] )
				{
					case 'admin':
						$sect = '0';
						break;
					case 'user':
						$sect = '1';
						break;
					case 'dev':
						$sect = '2';
						break;
				}
				// lets try this thingy
				$basic_gui->set_title( $row[ 'doc_title' ] );
				// this will be the custom thingo
				$custom = array( array( 'title' => $row[ 'doc_title' ], 'URL' => $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=show&w=' . $row[ 'doc_id' ] ) ) );
				$basic_gui->set_level( 2, 'doc', '0,' . $sect, $custom );
				$this->gui->show( $row );
				break;
			case 'add':
				// need to be logged in
				if ( !( $userdata[ 'user_level' ] >= ADMIN ) )
				{
					$errors->report_error( sprintf( $basic_lang[ 'Need_login' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
				}
			
				// to add to pagination we must decypher the section
				$sec = ( isset( $_GET[ 'w' ] ) ) ? strval( $security->parsevar( $_GET[ 'w' ], REM_SLASHES ) ) : '';
				switch ( $sec )
				{
					case 'admin':
						$sect = '0';
						break;
					case 'user':
						$sect = '1';
						break;
					case 'dev':
						$sect = '2';
						break;
				}
				
				// get editor
				$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
				$mod_loader->port_vars( array( 'name' => 'text', 'quickpost' => FALSE, 'def_text' => '' ) );
				$mod_loader->execute_modules( 0, 'show_editor' );
				$edit = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
				
				$basic_gui->set_level( 3, 'doc', '0,' . $sect );
				$this->gui->add( $sec, $edit );
				break;
			case 'add2':
				// need to be logged in
				if ( !( $userdata[ 'user_level' ] >= ADMIN ) )
				{
					$errors->report_error( sprintf( $basic_lang[ 'Need_login' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
				}
				
				// check for correctness
				if ( !isset( $_POST[ 'addart' ] ) )
				{
					$errors->report_error( 'Wrongly submited form', CRITICAL_ERROR );
				}else
				{
					// prepare the vars
					$author = $userdata[ 'user_id' ];
					$title = ( isset( $_POST[ 'title' ] ) ) ? strval( $_POST[ 'title' ] ) : '';
					$text = ( isset( $_POST[ 'text' ] ) ) ? strval( $_POST[ 'text' ] ) : '';
					$sect = ( isset( $_GET[ 'w' ] ) ) ? strval( $_GET[ 'w' ] ) : '';
					$time = time();
					
					// check if something is missing
					if ( empty( $title ) || empty( $text ) || empty( $sect ) )
					{
						$errors->report_error( $this->lang[ 'Err_missing' ], GENERAL_ERROR );
					}
					// insert it :)
					$sql = "INSERT INTO " . DOCS_TABLE . " VALUES( '', '$time', '$sect', '$author', '$title', '$text' )";
					if ( !$db->sql_query( $sql ) )
					{
						$errors->report_error( $this->lang[ 'Err_noinsert' ], GENERAL_ERROR );
					}else
					{
						$errors->report_error( sprintf( $this->lang[ 'Inserted' ], $security->append_sid( '?' . MODE_URL . '=doc' ) ), MESSAGE );
					}
				}
				break;
		}
	}
	
	function editarticle( $article_id )
	{
		global $errors, $db, $security, $userdata, $basic_lang;
		
		// fetch data from database
		$sql = "SELECT * FROM " . DOCS_TABLE . " WHERE doc_id='$article_id' LIMIT 1";
		if ( !$res = $db->sql_query( $sql ) )
		{
			return array( $article_id, $errors->return_error( 'Could not query data', CRITICAL_ERROR ) );
		}
		$row = $db->sql_fetchrow( $res );
		
		// now check if the users match
		if ( $userdata[ 'user_id' ] != $row[ 'doc_user' ] )
		{
			return array( $article_id, sprintf( $errors->return_error( $this->lang[ 'edit_erruser' ], '?' . MODE_URL . '=doc' ) ), GENERAL_ERROR );
		}
		
		// now return the fixed gui :)
		$ret = '<span class="gen">';
		$ret .= $this->lang[ 'Title' ] . ': <input type="text" id="title" value="' . $row[ 'doc_title' ] . '" maxlength="255" size="80"><br/>';
		$ret .= $this->lang[ 'Text' ] . ':<br/><textarea id="text" cols="100" rows="30">' . $row[ 'doc_text' ] . '</textarea><br/>';
		$ret .= '<input type="submit" value="' . $basic_lang[ 'Submit' ] . '" onclick="editarticle2( ' . $article_id . ' );">';
		$ret .= '</span>';
		
		return array( $article_id, $ret );
	}
	
	function editarticle2( $article_id, $title, $text )
	{
		global $errors, $db, $security, $userdata, $basic_lang, $plug_clcode;
		
		// fetch data from database
		$sql = "SELECT * FROM " . DOCS_TABLE . " WHERE doc_id='$article_id' LIMIT 1";
		if ( !$res = $db->sql_query( $sql ) )
		{
			return array( $article_id, $errors->return_error( 'Could not query data', CRITICAL_ERROR ) );
		}
		$row = $db->sql_fetchrow( $res );
		
		// now check if the users match
		if ( $userdata[ 'user_id' ] != $row[ 'doc_user' ] )
		{
			return array( $article_id, sprintf( $errors->return_error( $this->lang[ 'edit_erruser' ], '?' . MODE_URL . '=doc' ) ), GENERAL_ERROR );
		}
		
		// now we can parse the input data
		// we need to addslashes and force it to do so because this has been fetched straight
		// from the form and not through $_POST
// 		$title = $security->parsevar( $title, ADD_SLASHES, TRUE );
// 		$text = $security->parsevar( $text, ADD_SLASHES );
		
		// now we can insert it
		$sql = "UPDATE " . DOCS_TABLE . " SET doc_title='$title', doc_text='$text' WHERE doc_id='$article_id' LIMIT 1";
		if ( !$res = $db->sql_query( $sql ) )
		{
			return array( $article_id, $errors->return_error( 'Could not update article', CRITICAL_ERROR ) );
		}
		
		// return the new thingo
		return array( $article_id, '<span class="gen">' . $plug_clcode->parse( $text ) . '</span>' );
	}
	
	//
	// End of Doc class
	//
}


?>