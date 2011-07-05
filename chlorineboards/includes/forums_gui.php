<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:               forums_gui.php                        //
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
// gui for the forums module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation

// class creation
$vars = array( );
$visible = array( );
eval( Varloader::createclass( 'forums_gui', $vars, $visible ) );
// end class creation

class forums_gui extends forums_gui_def
{
	function forums_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'forums' => 'forums' . tplEx
		) );
	}
	
	function show_cats( $cats )
	{
		global $template, $basic_gui, $security, $plug_clcode;
		
// 		print_R( $cats );
		
		// some "global" vars
		$template->assign_block_vars( 'cats', '', array(
			'L_NOFORUMS' => $this->lang[ 'No_forums' ],
		 ) );
		
		// assign variables
		if ( is_array( $cats ) )
		{
			foreach ( $cats as $cat_id => $cat )
			{
				// some type saving
				$forums = $cat[ 'forums' ];
// 				print_R( $forums );die();
				$cat = $cat[ '_self_' ];
			
				// define some basicer variables
				$template->assign_block_vars( 'catrow', '', array(
					'CAT' => '',
					'FORUMS' => count( $forums ),
					'L_STATE' => $this->lang[ 'Forum_state' ],
					'L_TITLE' => $this->lang[ 'Forum_title' ],
					'L_TOPICS' => $this->lang[ 'Forum_threads' ],
					'L_POSTS' => $this->lang[ 'Forum_posts' ],
					'L_LASTPOST' => $this->lang[ 'Forum_lastpost' ],
				) );
				// some more specific ones
				$template->assign_var_levels( 'catrow', 'CAT', array(
					'ID' => $cat_id,
					'TITLE' => $cat[ 'title' ],
				) );
			
				// show the row
				$template->assign_switch( 'catrow', TRUE );
			
				// now loop through the needed forums
				if ( is_array( $forums ) )
				{ // only if needed
					foreach ( $forums as $fid => $forum )
					{
						// define some basic variables
						$template->assign_block_vars( 'catrow.forumrow', '', array(
							'FORUM' => '',
						) );
						// some more specific ones
						$template->assign_var_levels( 'catrow.forumrow', 'FORUM', array(
							'TITLE' => $forum[ '_self_' ][ 'title' ],
							'URL' => $security->append_sid( '?' . MODE_URL . '=forums&' . SUBMODE_URL . '=showforum&f=' . $fid ),
							'DESCRIPTION' => $plug_clcode->parse( $forum[ '_self_' ][ 'description' ] ),
							'TOPICS' => $forum[ '_self_' ][ 'topics' ],
							'POSTS' => $forum[ '_self_' ][ 'posts' ],
						) );
						// show the row
						$template->assign_switch( 'catrow.forumrow', TRUE );
						// sub forums
// 						echo "BU<br>";
// 						if ( !empty( $forum[ 'subs' ] ) )
// 						{
// 							for( $i = 0; $i < count( $forum[ 'subs' ] ); $i++ )
// 							{
// 								echo "BE<br>";
// 								$sub = $forum[ 'subs' ][ $i ];
// 								$template->assign_block_vars( 'catrow.forumrow.subforumrow', '', array(
// 									'TITLE' => $sub[ 'title' ],
// 									'URL' => $security->append_sid( '?' . MODE_URL . '=forums&' . SUBMODE_URL . '=showforum&f=' . $sub[ 'id' ] ),
// 								) );
// 								// make it seen
// 								$template->assign_switch( 'catrow.forumrow.subforumrow', TRUE );
// 							}
// 						}
					}
				}
			}
		}
		
		if ( count( $cats ) > 0 )
		{
			// make it visible
			$template->assign_switch( 'cats', TRUE );
		}
	}
	
	function show_threads( $threads, $parent = 0 )
	{
		global $template, $basic_gui, $security, $plug_clcode, $lang_loader;
		
// 		print_R( $threads );
		
		// some "global" vars
		$template->assign_block_vars( 'threads', '', array(
			'L_NOTHREADS' => $this->lang[ 'No_threads' ],
			'L_THREADS' => $this->lang[ 'Threads' ],
			'L_SUBTHREADS' => $this->lang[ 'Subthreads' ],
			'L_STATE' => $this->lang[ 'Thread_state' ],
			'L_TITLE' => $this->lang[ 'Thread_title' ],
			'L_POSTS' => $this->lang[ 'Thread_posts' ],
			'L_VIEWS' => $this->lang[ 'Thread_views' ],
			'L_LASTPOST' => $this->lang[ 'Thread_lastpost' ],
			
			'THREADS' => count( $threads ),
			'PARENT' => $parent,
		 ) );
		
		// some images
		$template->assign_var_levels( '', 'IMG', array(
				'NEW_THREAD' => 'template/YAT/images/' . $lang_loader->board_lang . '/new_thread.gif',
			) );
		
		if ( is_array( $threads ) )
		{
			foreach ( $threads as $tid => $thread )
			{
				// type saving
				$thread = $thread[ '_self_' ];
				
				// some basic variables
				$template->assign_block_vars( 'threadrow', '', array(
					'THREAD' => '',
				) );
				// a tad more specific
				$template->assign_var_levels( 'threadrow', 'THREAD', array(
					'TITLE' => $thread[ 'title' ],
					'DESCRIPTION' => $plug_clcode->parse( $thread[ 'description' ] ),
					'URL' => $security->append_sid( '?' . MODE_URL . '=forums&' . SUBMODE_URL . '=showthread&t=' . $tid . '&f=' . $thread[ 'forum' ] ),
					'POSTS' => $thread[ 'posts' ],
					'VIEWS' => $thread[ 'views' ],
				) );
				// make visible
				$template->assign_switch( 'threadrow', TRUE );
			}
		}
		
		if ( count( $threads ) > 0 || $parent == 0 )
		{
			// make it visible
			$template->assign_switch( 'threads', TRUE );
		}
	}
	
	function show_posts( $posts )
	{
		global $template, $basic_gui, $plug_clcode, $userdata;
		
		// globalish vars
		// some "global" vars
		$template->assign_block_vars( 'posts', '', array(
			'L_NOPOSTS' => $this->lang[ 'No_posts' ],
			'L_POSTS' => $this->lang[ 'Posts' ],
			'L_AUTHOR' => $this->lang[ 'Author' ],
			'L_TIME' => $this->lang[ 'Post_time' ],
			'L_BODY' => $this->lang[ 'Post_body' ],
			
			'POSTS' => count( $posts ),
		) );
		 
// 		print_R( $posts );
		
		if ( is_array( $posts ) )
		{
			foreach ( $posts as $pid => $post )
			{
				// type saving
				$post = $post[ '_self_' ];
				
				// some basic variables
				$template->assign_block_vars( 'postrow', '', array(
					'POST' => '',
				) );
				// a tad more specific
				$text = ( $post[ 'wysiwyg' ] ) ? $plug_clcode->parse( $post[ 'text' ], TRUE ) : $plug_clcode->parse( $post[ 'text' ] );
				$template->assign_var_levels( 'postrow', 'POST', array(
					'USER' => $post[ 'user' ],
					'TEXT' => stripslashes( $text ),
					'TIME' => date( $userdata[ 'user_timeformat' ], $post[ 'time' ] ),
				) );
				// make visible
				$template->assign_switch( 'postrow', TRUE );
			}
		}
		// make it visible
		$template->assign_switch( 'posts', TRUE );
	}
	
	function show_quickreply( $fid, $tid, $quickreply )
	{
		global $template, $basic_gui, $security, $userdata;
		
		$template->assign_block_vars( 'quickreply', '', array(
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=forums&' . SUBMODE_URL . '=post_reply&t=' . $tid . '&f=' . $fid ),
			'S_QUICKREPLY' => $quickreply[ 'editor_HTML' ],
			'S_WYSIWYG' => $quickreply[ 'editor_WYSIWYG' ],
			'S_MODE' => 'reply',
			
			'L_USERNAME' => $this->lang[ 'Username' ],
		) );
		$template->assign_switch( 'quickreply', TRUE );
		if ( $userdata[ 'user_level' ] == GUEST )
		{
		$template->assign_switch( 'quickreply.username', TRUE );
		}
	}
	
	function finish()
	{
		global $basic_gui;
		
		// add to output
		$basic_gui->add_file( 'forums');
	}


	//
	// End of forums-gui class
	//
}


?>