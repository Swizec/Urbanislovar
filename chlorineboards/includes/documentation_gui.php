<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:           documentation_gui.php                     //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       06th November 2005                      //
//     version:               0.6.2                              //
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
// gui for the documentation module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// lang :: figure it out, sheesh

// class creation
$vars = array( 'lang' );
$visible = array( 'private' );
eval( Varloader::createclass( 'documentation_gui', $vars, $visible ) );
// end class creation

class Documentation_gui extends documentation_gui_def
{
	function Documentation_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'documentation' => 'documentation' . tplEx
		) );
	}
	
	function index()
	{
		global $template, $basic_gui, $basic_lang, $security;
		
		// construct the list of sections
		$list = '<b><a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=admin' ) . '">' . $basic_lang[ 'Administrator' ]  . '</b></a><br />' . $this->lang[ 'Inx_admin' ] . '<br /><br /><br />';
		$list .= '<b><a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=user' ) . '">' . $basic_lang[ 'User' ] . '</b></a><br />' . $this->lang[ 'Inx_user' ] . '<br /><br /><br />';
		$list .= '<b><a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=dev' ) . '">' . $basic_lang[ 'Developer' ] . '</b></a><br />' . $this->lang[ 'Inx_dev' ] . '<br /><br /><br />';
		
		// teh vars
		$template->assign_block_vars( 'indextable', '', array(
			'L_TITLE' => $this->lang[ 'Inx_title' ],
			'L_TEXT' => $this->lang[ 'Inx_text' ],
			
			'LIST' => $list,
		) );
	
		// make it visible
		$template->assign_switch( 'indextable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'documentation');
	}
	
	function show_list( $list, $mode )
	{
		global $template, $basic_gui, $basic_lang, $userdata, $security;
		
		// make the left menu
		$menu = '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=admin' ) . '">' . $basic_lang[ 'Administrator' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=user' ) . '">' . $basic_lang[ 'User' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=dev' ) . '">' . $basic_lang[ 'Developer' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=add&w=' . $mode ) . '">' . $this->lang[ 'Add' ] . '</a><br />';
		
		// construct the general vars
		$template->assign_block_vars( 'listtable', '', array(
			'MENU' => $menu,
		) );
		
		// see if list needs showing
		if ( count( $list ) > 0 )
		{ // yes
			foreach ( $list as $entry )
			{
				// set vars
				$template->assign_block_vars( 'docrow', '', array(
						'USER' => $entry[ 'doc_user' ],
						'TITLE' => '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=show&w=' . $entry[ 'doc_id' ] ) . '">' . $entry[ 'doc_title' ] . '</a>',
						'TIME' => date( $userdata[ 'user_timeformat' ], $entry[ 'doc_time' ] ),
					) );
				
				// make visible
				$template->assign_switch( 'docrow', TRUE );
			}
		}else
		{ // show notice eh
			// assign vars
			$template->assign_block_vars( 'docrow', '', array(
					'L_NOTICE' => $this->lang[ 'No_doc' ],
					'SHOWNOTICE' => 'yes'
				) );
			// make visible
			$template->assign_switch( 'docrow', TRUE );
		}
		
		// make it visible
		$template->assign_switch( 'listtable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'documentation');
	}
	
	function show( $article )
	{
		global $template, $basic_gui, $basic_lang, $userdata, $plug_clcode, $security;
		
		// make the left menu
		$menu = '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=admin' ) . '">' . $basic_lang[ 'Administrator' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=user' ) . '">' . $basic_lang[ 'User' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=dev' ) . '">' . $basic_lang[ 'Developer' ] . '</a><br />';
		
		// construct the general vars
		$template->assign_block_vars( 'showtable', '', array(
			'MENU' => $menu,
		) );
		
		// construct the vars
		$template->assign_block_vars( 'showtable', 'last', array(
			'USER' => $article[ 'doc_user' ],
			'TITLE' => $article[ 'doc_title' ],
			'TIME' => date( $userdata[ 'user_timeformat' ], $article[ 'doc_time' ] ),
			'TEXT' => $plug_clcode->parse( $article[ 'doc_text' ], TRUE ),
			'ID' => $article[ 'doc_id' ],
			'USER_EDIT' => intval( $article[ 'user_edit' ] )
		) );
		
		// make it visible
		$template->assign_switch( 'showtable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'documentation');
	}
	
	function add( $sect, $editor )
	{
		global $template, $basic_gui, $basic_lang, $security;
		
		// make the left menu
		$menu = '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=admin' ) . '">' . $basic_lang[ 'Administrator' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=user' ) . '">' . $basic_lang[ 'User' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=dev' ) . '">' . $basic_lang[ 'Developer' ] . '</a><br />';
		
		// set the needed vars
		$template->assign_block_vars( 'addtable', '', array(
			'MENU' => $menu,
			
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=doc&' . SUBMODE_URL . '=add2&w=' . $sect ),
			'S_EDITOR' => $editor[ 'editor_HTML' ],
			
			'L_TITLE' => $this->lang[ 'Title' ],
			'L_TEXT' => $this->lang[ 'Text' ],
			'L_SUBMIT' => $basic_lang[ 'Submit' ],
			'L_RESET' => $basic_lang[ 'Reset' ],
			'L_GREET' => $this->lang[ 'Greet' ],
		) );
		
		// make it visible
		$template->assign_switch( 'addtable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'documentation');
	}

	//
	// End of documentation-gui class
	//
}


?>