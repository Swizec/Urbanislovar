<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:              forums_mod.php                         //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       05th February 2006                      //
//     version:               0.1.0                              //
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
// this module deals with forums and stuff
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
// cat_list :: array with categories
// forum_list :: array with forums
// field_hash :: array of field names
// thread_list :: array with threads
// post_list :: array with posts

// class creation
$vars = array( 'debug', 'gui', 'cat_list', 'forum_list' );
$visible = array( 'private', 'private', 'private', 'private' );
eval( Varloader::createclass( 'forums', $vars, $visible ) );
// end class creation

class forums extends forums_def
{
	
	// constructor
	function forums( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $cache;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/forums_gui' . phpEx );
		$this->gui = new forums_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'forums' );
		$this->gui->lang = $this->lang;
		
		// define some basic vars
		$this->cat_list = array();
		$this->forum_list = array();
		$this->thread_list = array();
		
		// define the field hash
		// this greatly simplifies things
		$this->field_hash = array();
		$this->field_hash[ 'category' ] = array( 
				'title' => 'cat_title',
				'order' => 'cat_order',
			);
		$this->field_hash[ 'forum' ] = array( 
				'title' => 'forum_title',
				'description' => 'forum_description',
				'posts' => 'forum_posts',
				'topics' => 'forum_topics',
				'path' => 'forum_path',
				'parent' => 'forum_parent',
				'order' => 'forum_order',
			);
		$this->field_hash[ 'thread' ] = array(
				'title' => 'thread_title',
				'description' => 'thread_description',
				'posts' => 'thread_posts',
				'topics' => 'thread_topics',
				'path' => 'thread_path',
				'parent' => 'thread_parent',
				'views' => 'thread_views',
			);
		$this->field_hash[ 'post' ] = array(
				'time' => 'post_time',
				'username' => 'post_username',
				'poster' => 'post_poster',
				'text' => 'post_text',
				'wysiwyg' => 'post_wysiwyg',
			);
			
		// read some stuff from hash
		$this->cat_list = $cache->pull( 'cat_list' );
		$this->forum_list = $cache->pull( 'forum_list' );
		$this->thread_list = $cache->pull( 'thread_list' );
	}
	
	// the basic function that then decides what to do
	function display( )
	{
		global $Cl_root_path, $errors, $basic_gui, $db, $cache;
		
		// get the submode
		$mode = ( isset( $_GET[ SUBMODE_URL ] ) ) ? strval( $_GET[ SUBMODE_URL ] ) : 'main';
		$cid = ( isset( $_GET[ 'c' ] ) ) ? intval( $_GET[ 'c' ] ) : '';
		$fid = ( isset( $_GET[ 'f' ] ) ) ? intval( $_GET[ 'f' ] ) : 0;
		$tid = ( isset( $_GET[ 't' ] ) ) ? intval( $_GET[ 't' ] ) : '';
		
		switch ( $mode )
		{
			case 'main':
				$basic_gui->set_level( 1, 'forums' );
				$this->show_cats( 0, $cid );
				break;
			case 'showforum':
				// just get the path for the pagination
				if ( !$path = $this->forum_list[ $fid ][ '_self_' ][ 'path' ] )
				{ // wasn't in cache
					// read it from the sql
					$sql = "SELECT forum_path FROM " . FORUMS_TABLE . " WHERE forum_id='$fid' LIMIT 1";
					$res = $db->sql_query( $sql );
					$path = $db->sql_fetchrow( $res );
					$path = $path[ 'forum_path' ];
					
					// add to global
					$this->forum_list[ $fid ][ '_self_' ][ 'path' ] = $path;
					$cache->push( 'forum_list', $this->forum_list, TRUE );
				}
				
				// now construct the path for the pagination
				$path = explode( '->', $path );
				$pag = array();
				foreach ( $path as $entry )
				{
					// separate the url and the title
					$entry = explode( ';:;', $entry );
					$pag[] = array( 'URL' => '?' . MODE_URL . '=forums&' . SUBMODE_URL . $entry[ 0 ], 'title' => $entry[ 1 ] );
				}
				
				$basic_gui->set_level( 1, 'forums', '', $pag );
				$this->show_cats( $fid );
				$this->show_threads( $fid );
				break;
			case 'showthread':
				// quickly raise the view count
				$db->sql_query( "UPDATE " . THREADS_TABLE . " SET thread_views=thread_views+1 WHERE thread_id='$tid'" );
				
				// just get the path for the pagination
				if ( !$path = $this->forum_list[ $fid ][ 'threads' ][ $tid ][ 'path' ] )
				{ // wasn't in cache
					// read it from the sql
					$sql = "SELECT thread_path FROM " . THREADS_TABLE . " WHERE thread_id='$tid' LIMIT 1";
					$res = $db->sql_query( $sql );
					$path = $db->sql_fetchrow( $res );
					$path = $path[ 'thread_path' ];
					
					// add to global
					$this->forum_list[ $fid ][ 'threads' ][ $tid ][ 'path' ] = $path;
					$p = $this->forum_list[ $fid ][ 'threads' ][ $tid ][ 'parent' ];
					$this->thread_list[ $fid ][ $p ][ $tid ][ '_self_' ][ 'path' ] = $path;
					$cache->push( 'forum_list', $this->forum_list, TRUE );
					$cache->push( 'thread_list', $this->thread_list, TRUE );
				}
				
				// now construct the path for the pagination
				$path = explode( '->', $path );
				$pag = array();
				foreach ( $path as $entry )
				{
					// separate the url and the title
					$entry = explode( ';:;', $entry );
					$pag[] = array( 'URL' => '?' . MODE_URL . '=forums&' . SUBMODE_URL . $entry[ 0 ], 'title' => $entry[ 1 ] );
				}
				
				$basic_gui->set_level( 1, 'forums', '', $pag );
				$this->show_threads( $fid, $tid );
				$this->show_posts( $fid, $tid );
				break;
			case 'post_reply':
				$this->post_reply();
				break;
			default:
				$errors->report_error( $this->lang[ 'No_mode' ], CRITICAL_ERROR );
				break;
		}
		
		// will finish off the print
		$this->gui->finish();
	}
	
	// displays the categories
	function show_cats( $parent, $cat = '' )
	{
		global $db, $cache, $errors;
		
		// read the categories data
		if ( !isset( $this->cat_list[ $parent ] ) )
		{ // not in cache
			if ( empty( $cat ) )
			{
				$sql = "SELECT c.*, f.*, f2.forum_title AS subforum_title, f2.forum_id AS subforum_id FROM " . CATEGORY_TABLE . " c LEFT JOIN " . FORUMS_TABLE . " f ON c.cat_id=f.forum_cat LEFT JOIN " . FORUMS_TABLE . " f2 ON f2.forum_parent=f.forum_id WHERE c.cat_parent='$parent' ORDER BY c.cat_order, f.forum_order";
			}else
			{
				$sql = "SELECT c.*, f.*, f2.forum_title AS subforum_title, f2.forum_id AS subforum_id FROM " . CATEGORY_TABLE . " c LEFT JOIN " . FORUMS_TABLE . " f ON c.cat_id=f.forum_cat LEFT JOIN " . FORUMS_TABLE . " f2 ON f2.forum_parent=f.forum_id WHERE c.cat_id='$cat'  ORDER BY c.cat_order, f.forum_order";
			}
			
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not fetch categories data', CRITICAL_ERROR );
			}
			// parse the data
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				// set the category itself
				$id = $row[ 'cat_id' ];
				foreach ( $this->field_hash[ 'category' ] as $here => $sql )
				{
					$this->cat_list[ $parent ][ $id ][ '_self_' ][ $here ] = $row[ $sql ];
				}
				
				// its forums
				if ( $fid = $row[ 'forum_id' ] ) 
				{ // only if needed
					// the basic hash, the rest of the data comes later
					$this->forum_list[ $fid ][ '_self_' ][ 'cat' ] = $id;
					$this->forum_list[ $fid ][ '_self_' ][ 'parent' ] = $parent;
					foreach ( $this->field_hash[ 'forum' ] as $here => $sql )
					{
						$this->forum_list[ $fid ][ '_self_' ][ $here ] = $row[ $sql ];
					}
					// do the subforums
					if ( !empty( $row[ 'subforum_id' ] ) )
					{ // only if needed
						if ( !isset( $this->forum_list[ $fid ][ 'subs' ] ) )
						{ // set subforums if not yet
							$this->forum_list[ $fid ][ 'subs' ] = array();
						}
						$this->forum_list[ $fid ][ 'subs' ][] = array( 'id' => $row[ 'subforum_id' ], 'title' => $row[ 'subforum_title' ] );
					}
					$this->cat_list[ $parent ][ $id ][ 'forums' ][ $fid ] = &$this->forum_list[ $fid ];
				}
			}
			
			// store it
			$cache->push( 'cat_list', $this->cat_list, TRUE );
			$cache->push( 'forum_list', $this->forum_list, TRUE );
		}
		
		// now print it to the gui
		$this->gui->show_cats( $this->cat_list[ $parent ] );
	}
	
	// displays the threads
	function show_threads( $forum, $parent = 0 )
	{
		global $db, $cache, $errors;
		
		// read the threads data
		if ( !isset( $this->thread_list[ $forum ][ $parent ] ) )
		{ // not in cache
			$sql = "SELECT t.* FROM " . THREADS_TABLE . " t WHERE t.thread_forum='$forum' AND t.thread_parent='$parent'";
			
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not fetch threads data', CRITICAL_ERROR );
			}
			
			// parse these things now ;)
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				// add thread data to list and so
				$tid = $row[ 'thread_id' ];
				foreach ( $this->field_hash[ 'thread' ] as $here => $sql )
				{
					$this->forum_list[ $forum ][ 'threads' ][ $tid ][ $here ] = $row[ $sql ];
					$this->thread_list[ $forum ][ $parent ][ $tid ][ '_self_' ][ $here ] = $row[ $sql ];
				}
				
				// some more basic stuff
				$this->thread_list[ $forum ][ $parent ][ $tid ][ '_self_' ][ 'forum' ] = $forum;
			}
			// update cache
			$cache->push( 'forum_list', $this->forum_list, TRUE );
			$cache->push( 'thread_list', $this->thread_list, TRUE );
		}
		
		// now print it to the gui
		$this->gui->show_threads( $this->thread_list[ $forum ][ $parent ], $parent );
	}
	
	// displays the posts
	function show_posts( $fid, $tid )
	{
		global $db, $cache, $errors, $mod_loader, $basic_lang;
		
		// read the posts data
		if ( !isset( $this->post_list[ $tid ] ) )
		{ // not in cache
			$sql = "SELECT p.*, u.username AS u_name, u.user_id, u.user_level FROM " . POSTS_TABLE . " p LEFT JOIN " . USERS_TABLE . " u ON p.post_poster=u.user_id WHERE p.post_thread = '$tid' ORDER BY p.post_time ASC";
			
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not fetch posts data', CRITICAL_ERROR );
			}
			
			// parse it :P
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				// add post data to the list
				$pid = $row[ 'post_id' ];
				foreach ( $this->field_hash[ 'post' ] as $here => $sql )
				{
					$this->post_list[ $tid ][ $pid ][ '_self_' ][ $here ] = $row[ $sql ];
				}
				// set the user thing
				if ( $row[ 'user_level' ] == GUEST )
				{
					$this->post_list[ $tid ][ $pid ][ '_self_' ][ 'user' ] = ( empty( $row[ 'post_username' ] ) ) ? $basic_lang[ 'Guest' ] : $row[ 'post_username' ];
				}else
				{
					$this->post_list[ $tid ][ $pid ][ '_self_' ][ 'user' ] = $row[ 'u_name' ];
				}
			}
			$this->thread_list[ $tid ][ 'posts' ] = $pid;
			// update cache
			$cache->push( 'thread_list', $this->thread_list, TRUE );
			$cache->push( 'post_list', $this->post_list, TRUE );
		}
		// now print it to the gui
		$this->gui->show_posts( $this->post_list[ $tid ] );
		
		// the quickreply too
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => TRUE ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$quickreply = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		$this->gui->show_quickreply( $fid, $tid, $quickreply );
	}
	
	// posts a reply and stuff
	function post_reply()
	{
		global $errors, $plug_clcode, $userdata, $db, $security;
		
		// basic security
		if ( !isset( $_POST[ 'reply_post' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// parse the data
		$text = ( isset( $_POST[ 'editor1' ] ) ) ? $_POST[ 'editor1' ] : '';
		$wysiwyg = ( isset( $_POST[ 'wysiwyg' ] ) ) ? intval( $_POST[ 'wysiwyg' ] ) : 0;
		$username = ( isset( $_POST[ 'username' ] ) ) ? strval( $_POST[ 'username' ] ) : '';
		$mode = ( isset( $_POST[ 'post_mode' ] ) ) ? strval( $_POST[ 'post_mode' ] ) : '';
		$fid = ( isset( $_GET[ 'f' ] ) ) ? intval( $_GET[ 'f' ] ) : 0;
		$tid = ( isset( $_GET[ 't' ] ) ) ? intval( $_GET[ 't' ] ) : 0;
		
		// some extra stuff
		$time = time();
		$uid = $userdata[ 'user_id' ];
		
		// decide what to do according to the mode
		switch( $mode )
		{
			case 'reply': // a reply was made
				// construct the queries
				$sql1 = "INSERT INTO " . POSTS_TABLE . " ( post_forum, post_thread, post_poster, post_username, post_wysiwyg, post_time, post_text ) VALUES ( '$fid', '$tid', '$uid', '$username', '$wysiwyg', '$time', '$text' )"; // post insertion
				$sql2 = "SELECT t.thread_path FROM " . THREADS_TABLE . " t WHERE t.thread_id='$tid' LIMIT 1";
				
				if ( !$db->sql_query( $sql1 ) )
				{
					$errors->report_error( 'Could not insert post data', CRITICAL_ERROR );
				}
				// get the new post id
				$pid = $db->sql_nextid();
				
				if ( !$res = $db->sql_query( $sql2 ) )
				{
					$errors->report_error( 'Could not insert post data', CRITICAL_ERROR );
				}
				
				// now parse the path so to update all relevant threads and forums
				$row = $db->sql_fetchrow();
				$path = $row[ 'thread_path' ];
				$path = explode( '->', $path );
				$forums = array();
				$threads = array();
				foreach ( $path as $pa )
				{
					$pa = explode( ';:;', $pa );
					$pa = explode( '&', $pa[ 0 ] );
					foreach ( $pa as $smth )
					{
						$smth = explode( '=', $smth );
						if ( empty( $smth[ 0 ] ) )
						{
							continue;
						}
						switch ( $smth[ 0 ] )
						{
							case 'f':
								if ( !in_array( $smth[ 1 ], $forums ) )
								{
									$forums[] = $smth[ 1 ];
								}
								break;
							case 't':
								if ( !in_array( $smth[ 1 ], $threads ) )
								{
									$threads[] = $smth[ 1 ];
								}
								break;
						}
					}
				}
				
				// now construct the sql
				$adds = array(); 
				$adds[ 't' ] = array(); $adds[ 't' ][ 'f' ] = array(); $adds[ 't' ][ 's' ] = array(); $adds[ 't' ][ 't' ] = array();
				$adds[ 'f' ] = array(); $adds[ 'f' ][ 'f' ] = array(); $adds[ 'f' ][ 's' ] = array(); $adds[ 'f' ][ 't' ] = array();
				foreach ( $threads as $i => $t )
				{
					$adds[ 't' ][ 'f' ][] = THREADS_TABLE . " t$t ";
					$adds[ 't' ][ 's' ][] = " t$t.thread_posts=t$t.thread_posts+1";
					$adds[ 't' ][ 's' ][] = " t$t.thread_lastpost='$pid'";
					$adds[ 't' ][ 't' ][] = " t$t.thread_id='$tid'";
				}
				foreach ( $forums as $i => $f )
				{
					$adds[ 'f' ][ 'f' ][] = FORUMS_TABLE . " f$f ";
					$adds[ 'f' ][ 's' ][] = " f$f.forum_posts=f$f.forum_posts+1";
					$adds[ 'f' ][ 's' ][] = " f$f.forum_lastpost='$pid'";
					$adds[ 'f' ][ 't' ][] = " f$f.forum_id='$fid'";
				}
				
				$adds[ 't' ][ 'f' ] = implode( ', ', $adds[ 't' ][ 'f' ] );
				$adds[ 't' ][ 's' ] = implode( ', ', $adds[ 't' ][ 's' ] );
				$adds[ 't' ][ 't' ] = implode( ' AND ', $adds[ 't' ][ 't' ] );
				$adds[ 'f' ][ 'f' ] = implode( ', ', $adds[ 'f' ][ 'f' ] );
				$adds[ 'f' ][ 's' ] = implode( ', ', $adds[ 'f' ][ 's' ] );
				$adds[ 'f' ][ 't' ] = implode( ' AND ', $adds[ 'f' ][ 't' ] );
				
				$sql = "UPDATE " . $adds[ 't' ][ 'f' ] . ", " . $adds[ 'f' ][ 'f' ] . " SET " . $adds[ 't' ][ 's' ] . ", " . $adds[ 'f' ][ 's' ] . " WHERE " . $adds[ 't' ][ 't' ] . " AND " . $adds[ 'f' ][ 't' ];
				if ( !$db->sql_query( $sql ) )
				{
					$errors->report_error( 'Could not insert post data', CRITICAL_ERROR );
				}
				
				$errors->report_error( sprintf( $this->lang[ 'Post_done' ], '<a href="' . $security->append_sid( '?' . MODE_URL . '=forums&' . SUBMODE_URL . '=showthread&t=1&f=1' ) . '">', '</a>', '<a href="' . $security->append_sid( '?' . MODE_URL . '=forums' ) . '">', '</a>' ), MESSAGE );
				break;
			default: // there was no mode, wtf
				$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
				break;
		}
	}
	
	//
	// End of forums class
	//
}


?>