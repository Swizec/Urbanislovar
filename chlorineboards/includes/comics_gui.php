<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:              comics_gui.php                         //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       25th March 2006                         //
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
// gui for the comics module
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
eval( Varloader::createclass( 'comics_gui', $vars, $visible ) );
// end class creation

class comics_gui extends comics_gui_def
{
	function comics_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'comics' => 'comics' . tplEx
		) );
	}

	function show_comics( $first, $prev, $next, $last, $current, $id, $hash, $list )
	{
		global $template, $basic_gui, $userdata, $security, $lang_loader, $mod_loader;
		
		define( 'NO_SIDEBARS', TRUE );
		
		// check if admin and show the upload stuff eh :)
		if ( $userdata[ 'user_level' ] == ADMIN )
		{
			$langl = $lang_loader->get_langlist();
			// construct the language selection list
			$langs = '<select name="language">';
			for ( $i = 0; $i < count( $langl ); $langs .= '<option>' . $langl[ $i ] . '</option>',$i++ );
			$langs .= '</select>';
			$template->assign_block_vars( 'upload', '', array(
				'L_TITLE' => $this->lang[ 'Title' ],
				'L_FILE' => $this->lang[ 'File' ],
				'L_LANGS' => $this->lang[ 'Langs' ],
				'L_NEXT' => $this->lang[ 'Next_update' ],
				'LANGS' => $langs,
				'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=upload_comic' ),
			) );
			$template->assign_switch( 'upload', TRUE );
		}
		
		// create the drop-down
		$drop = '<select onchange="window.location.href = this.value;">';
		if ( is_array( $hash ) )
		{
			foreach ( $hash as $k => $kk )
			{
				$drop .= ( $k == $id ) ? '<option selected ' : '<option ';
				$URL = $basic_gui->get_URL . '?' . MODE_URL . '=show_comics&comic=' . $k;
				$drop .= 'value="' . $security->append_sid( $URL ) . '">' . $k . ' :: ' . $list[ $kk ][ 'title' ] . '</option>';
			}
		}
		$drop .= '</select>';
		
		// maybe somebody has something to say
		$mods = $mod_loader->getmodule( 'comic_show', MOD_FETCH_MODE, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'comic_id' => $id ) );
		$mod_loader->execute_modules( 0, 'comic_show' );
		$add = $mod_loader->get_vars( 'comic_add' );
		
		// the comic thingo
		$template->assign_block_vars( 'comics', '', array(
			'U_FIRST' => $first,
			'U_PREV' => $prev,
			'U_NEXT' => $next,
			'U_LAST' => $last,
			
			'COMIC' => $current,
			'TITLE' => $list[ $hash[ $id ] ][ 'title' ],
			'DROP' => $drop,
			'NEXT' => $this->lang[ 'Next_update' ] . ' ' .$this->next_update,
			'ADD' => $add,
			
			'L_FIRST' => $this->lang[ 'First' ],
			'L_PREV' => $this->lang[ 'Prev' ],
			'L_NEXT' => $this->lang[ 'Next' ],
			'L_LAST' => $this->lang[ 'Last' ]
		) );
		
		// title
		$basic_gui->set_title( $list[ $hash[ $id ] ][ 'title' ] );
		// so it appears nicely in the header
		$basic_gui->set_level( 1, 'comics', '', array( array( 'URL' => '?' . MODE_URL . '=show_comics&comic=' . $id, 'title' => $list[ $hash[ $id ] ][ 'title' ] ) ) );
		
		// make it visible
		$template->assign_switch( 'comics', TRUE );
		
		// add to output
		$basic_gui->add_file( 'comics');
		
		// just some test
// 		$basic_gui->add_drag( 'test1' );
// 		$basic_gui->add_drag( 'test2' );
		$basic_gui->add2sidebar( 'right', 'comics', 'lolawldlfasd' );
	}

	//
	// End of comics-gui class
	//
}


?>