<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:             banners_gui.php                         //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:      29th November 2005                         //
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
// gui for the banners module
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
eval( Varloader::createclass( 'banners_gui', $vars, $visible ) );
// end class creation

class banners_gui extends banners_gui_def
{

	function display( $list )
	{
		global $template, $lang, $basic_gui, $lang_loader;
		
		// first load the lang
		$this->lang = $lang_loader->get_lang( 'banners' );
		
		// then fire up the template
		$template->assign_files( array(
			'banners' => 'banners' . tplEx
		) );
		
		// vars
		$template->assign_var_levels( '', 'BANS', array(
			'TITLE' => $this->lang[ 'title' ],
			'TEXT' => $this->lang[ 'text' ],
			'OUTPUT' => $this->lang[ 'output' ],
			'HTML' => $this->lang[ 'html' ],
			'BBCODE' => $this->lang[ 'bbcode' ],
			'WIKICODE' => $this->lang[ 'wikicode' ],
		) );
		
		// now do the loop
		if ( is_array( $list ) )
		{
			foreach ( $list as $ban )
			{
				// vars
				$template->assign_block_vars( 'banrow', '', array(
					'OUTPUT' => $ban[ 'ban_html' ],
					'HTML' => htmlspecialchars( $ban[ 'ban_html' ] ),
					'BBCODE' => htmlspecialchars( $ban[ 'ban_bbcode' ] ),
					'WIKICODE' => htmlspecialchars( $ban[ 'ban_wikicode' ] ),
				) );
				// visibility
				$template->assign_switch( 'banrow', TRUE );
			}
		}
		
		// add it to the output
		$basic_gui->add_file( 'banners' );
	}

	//
	// End of banners-gui class
	//
}


?>