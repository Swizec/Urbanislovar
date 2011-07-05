<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:               news_gui.php                          //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       01st April 2006                         //
//     version:               0.2.0                              //
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
// gui for the news module
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
eval( Varloader::createclass( 'news_gui', $vars, $visible ) );
// end class creation

class news_gui extends news_gui_def
{
	function news_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'news' => 'news' . tplEx
		) );
	}
	
	function show( $news, $id_hash, $list, $URL, $pagi, $display = TRUE )
	{
		global $basic_gui, $template, $plug_clcode, $security, $userdata, $mod_loader, $board_config, $security;
		
		// vars and stuff
		$template->assign_block_vars( 'news', '', array(
			'LIST' => $list,
			
			'L_CATEGORIES' => $this->lang[ 'Categories' ],
			'L_NEXT' => $this->lang[ 'Next' ],
			'L_PREVIOUS' => $this->lang[ 'Previous' ],
			
			'U_NEXT' => $pagi[ 'unext' ],
			'U_PREVIOUS' => $pagi[ 'uprevious' ],
			
			'SHOWNEXT' => $pagi[ 'next' ],
			'SHOWPREV' => $pagi[ 'previous' ],
		) );
		$template->assign_switch( 'news', TRUE );
		
		// maybe somebody has something to say
		$mods = $mod_loader->getmodule( 'news_post', MOD_FETCH_MODE, NOT_ESSENTIAL );
		
		// loop for the news eh :P
		if ( count( $id_hash ) == 1 )
		{
			$single = TRUE;
		}else
		{
			$single = FALSE;
		}
		for ( $i = 0; $i < count( $id_hash ); $i++ )
		{
			$nws = $news[ $id_hash[ $i ] ];
			
			// execute additional stuff
			$mod_loader->port_vars( array( 'news_id' => $nws[ 'news_id' ], 'single' => $single ) );
			$mod_loader->execute_modules( 0, 'news_post' );
			$add = $mod_loader->get_vars( 'news_add' );
			
			$pbody = stripslashes( $nws[ 'news_preview' ] );
			$body = stripslashes( $nws[ 'news_text' ] );
			
			$preview = FALSE;
			if ( $board_config[ 'news_preview' ] && !isset( $_GET[ 'id' ] ) )
			{
				$uri = $security->append_sid( '?' . MODE_URL . '=news&id=' . $nws[ 'news_id' ] );;
				$preview = TRUE;
				$add = '';
				if ( strlen( $pbody ) == 0 )
				{
					$bod = strip_tags( $body );
					$body = substr( $bod, 0, $board_config[ 'news_preview_length' ] ) . '<a href="' . $uri . '">' . $this->lang[ 'More' ] . '...</a>';
				}else
				{
					$body = strip_tags( $pbody ) . '<a href="' . $uri . '">' . $this->lang[ 'More' ] . '...</a>';
				}
			}else
			{
				$body = $pbody . '<p></p>' . $body;
			}

			// category list
			$nocat = '<a href="' . $security->append_sid( '?' . MODE_URL . '=news&cat=0' ) . '">' . $this->lang[ 'Nocat' ] . '</a>';
			
			$template->assign_block_vars( 'newsrow', '', array(
// 				'BODY' => $plug_clcode->parse( stripslashes( $nws[ 'news_text' ] ), TRUE ),
				'BODY' => $body,
				'TITLE' => $nws[ 'news_title' ],
				'TIME' => date( $userdata[ 'user_timeformat' ], $nws[ 'news_time' ] ),
				'USER' => $nws[ 'username' ],
				'ADD' => $add,
				'SHOWADD' => ( $add == '' ) ? 0 : 1,
				'PREVIEW' => ( $preview ) ? 1 : 0,
				'CATEGORIES' => ( empty( $nws[ 'news_category' ] ) ) ? $nocat : implode( ', ', $nws[ 'news_category' ] ),
			) );
			$template->assign_switch( 'newsrow', TRUE );
		}
		
		// some other stuff
		if ( $display )
		{
			$basic_gui->set_title( $news[ $id_hash[ 0 ] ][ 'news_title' ] );
			$basic_gui->set_level( 1, 'news', '', array( array( 'URL' => $URL, 'title' => $news[ $id_hash[ 0 ] ][ 'news_title' ] ) ) );
			
			$basic_gui->add_file( 'news' );
		}else
		{
			return $template->justcompile( 'news' );
		}
	}


	//
	// End of news-gui class
	//
}


?>
