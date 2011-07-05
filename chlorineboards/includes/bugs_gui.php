<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:               bugs_gui.php                          //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       24th November 2005                      //
//     version:               0.4.2                              //
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
// gui for the bugs module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation

// class creation
$vars = array( 'lang' );
$visible = array( 'private' );
eval( Varloader::createclass( 'bugs_gui', $vars, $visible ) );
// end class creation

class Bugs_gui extends bugs_gui_def
{
	function Bugs_gui()
	{
		global $template;
		
		// add the template and stuff so we don't have to do it constantly
		$template->assign_files( array(
			'bugs' => 'bugs' . tplEx
		) );
	}
	
	function index()
	{
		global $template, $basic_gui, $basic_lang, $security;
		
		// assign the vars
		$template->assign_block_vars( 'indextable', '', array(
			'TITLE' => $this->lang[ 'inx_title' ],
			'TEXT' => $this->lang[ 'inx_text' ],
			'REPORT' => $basic_lang[ 'Report bug' ],
			'READ' => $basic_lang[ 'Read bugs' ],
			'FEATURE' => $basic_lang[ 'Request feature' ],
			'MODULE' => $basic_lang[ 'Request module' ],
			'T_REPORT' => $this->lang[ 'inx_report' ],
			'T_READ' => $this->lang[ 'inx_read' ],
			'T_FEATURE' => $this->lang[ 'inx_feature' ],
			'T_MODULE' => $this->lang[ 'inx_module' ],
			'U_REPORT' => $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=rep' ),
			'U_READ' => $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=read' ),
			'U_FEATURE' => $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=reqf' ),
			'U_MODULE' => $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=reqm' ),
		) );
		
		// make it visible
		$template->assign_switch( 'indextable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'bugs' );
	}
	
	function report()
	{
		global $template, $basic_gui, $security;
		
		// vars
		$template->assign_block_vars( 'reporttable', '', array(
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=rep2' ),
			'L_TITLE' => $this->lang[ 'rep_title' ],
			'L_TEXT' => $this->lang[ 'rep_text' ],
// 			'L_AUTHOR' => $this->lang[ 'rep_author' ],
			'L_BTITLE' => $this->lang[ 'rep_btitle' ],
			'L_DESC' => $this->lang[ 'rep_desc' ],
		) );
		
		// tooltips
		$template->assign_var_levels( 'reporttable', 'TOOLS', array(
// 			'AUTHOR' => $basic_gui->make_tooltip( $this->lang[ 'rep_tauthor' ], 'buttontip' ),
			'TITLE' => $basic_gui->make_tooltip( $this->lang[ 'rep_ttitle' ], 'buttontip' ),
			'DESC' => $basic_gui->make_tooltip( $this->lang[ 'rep_tdesc' ], 'buttontip' ),
		) );
		
		// make it visible
		$template->assign_switch( 'reporttable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'bugs' );
	}
	
	function readbugs( $bugs )
	{
		global $template, $basic_gui, $userdata;
		
		// some more general vars
		$template->assign_block_vars( 'readtable', '', array(
			'TITLE' => $this->lang[ 'read_title' ],
			'TEXT' => $this->lang[ 'read_text' ],
		) );
		
		// set the bugrow
		if ( is_array( $bugs ) )
		{
			foreach( $bugs as $bug )
			{
				// vars
				$template->assign_block_vars( 'bugrow', '', array(
					'TITLE' => $bug[ 'bug_title' ],
					'TIME' => date( $userdata[ 'user_timeformat' ], $bug[ 'bug_time' ] ),
					'ID' => $bug[ 'bug_id' ],
				) );
				// visibility
				$template->assign_switch( 'bugrow', TRUE );
			}
		}
		
		// make it visible
		$template->assign_switch( 'readtable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'bugs' );
	}
	
	function showbug( $bug, $bugid )
	{
		global $template, $basic_gui, $basic_lang, $userdata, $plug_clcode;
		
		// clear the template because otherwise we get an old output	
		$template->clear();
		
		// reassing the bugs template
		$template->assign_files( array(
			'bugs' => 'bugs' . tplEx
		) );
		
		// vars
		$template->assign_block_vars( 'bugtable', '', array(
			'AUTHOR' => sprintf( $this->lang[ 'read_bugby' ], $bug[ 0 ][ 'bug_author' ] ),
			'TEXT' => $plug_clcode->parse( $bug[ 0 ][ 'bug_text' ] ),
			'ID' => $bugid,
			
			'L_AUTHOR' => $this->lang[ 'read_author' ],
			'L_REPLY' => $this->lang[ 'read_reply' ],
			'L_SUBMIT' => $basic_lang[ 'Submit' ],
			'L_RESET' => $basic_lang[ 'Reset' ],
		) );
		
		// reply row
		foreach ( $bug as $reply )
		{
			// check if needed at all
			if ( empty( $reply[ 'reply_id' ] ) )
			{
				continue;
			}
			// the vars
			$template->assign_block_vars( 'replyrow', '', array(
				'HEAD' => sprintf( $this->lang[ 'read_replyhead' ], $reply[ 'reply_author' ], date( $userdata[ 'user_timeformat' ], $reply[ 'reply_time' ] ) ),
				'TEXT' => $plug_clcode->parse( $reply[ 'reply_text' ] ),
			) );
			// the visibility
			$template->assign_switch( 'replyrow', TRUE );
		}
		
		// make it visible
		$template->assign_switch( 'bugtable', TRUE );
		
		// return compiled
		return $template->justcompile( 'bugs' );
		
	}
	
	function requests( $list, $mode )
	{
		global $template, $basic_gui, $userdata, $security;
				
		// first the basics
		$template->assign_block_vars( 'reqtable', '', array(
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=addreq&w=' . $mode ),
		
			'L_TITLE' => ( $mode == 'feature' ) ? $this->lang[ 'req_ftitle' ] : $this->lang[ 'req_mtitle' ],
			'L_TEXT' => ( $mode == 'feature' ) ? $this->lang[ 'req_ftext' ] : $this->lang[ 'req_mtext' ],
			'L_DESC' => $this->lang[ 'req_desc' ],
		) );
		
		// make the row
		if ( is_array( $list ) )
		{
			foreach ( $list as $req )
			{
				// vars
				$template->assign_block_vars( 'reqrow', '', array(
					'HEAD' => sprintf( $this->lang[ 'req_head' ], $req[ 'req_author' ], date( $userdata[ 'user_timeformat' ], $req[ 'req_time' ] ), ( $req[ 'req_solved' ] ) ? $this->lang[ 'req_solved' ] : $this->lang[ 'req_unsolved' ] ),
					'CLICK' => $basic_gui->add_pop( '<div class="bodyline">' . str_replace( "\n", '<br />', $basic_gui->gennuline( $req[ 'req_text' ] ) ) . '</div>', 300, 250, 400, 300 ),
				) );
				// visibility
				$template->assign_switch( 'reqrow', TRUE );
			}
		}
		
		// the tooltips
		$template->assign_var_levels( 'reqtable', 'TOOLS', array(
			'DESC' => $basic_gui->make_tooltip( $this->lang [ 'req_tdesc' ], 'buttontip' ),
		) );
		
		// make it visible
		$template->assign_switch( 'reqtable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'bugs' );
	}

	//
	// End of Bugs-gui class
	//
}


?>