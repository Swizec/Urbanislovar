<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:               distro_gui.php                        //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:      04th November 2005                       //
//     version:               0.6.10                              //
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
// gui for the distro module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// lang :: the language vars

// class creation
$vars = array( 'lang' );
$visible = array( 'private' );
eval( Varloader::createclass( 'distro_gui', $vars, $visible ) );
// end class creation

class distro_gui extends distro_gui_def
{
	function distro_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'distro' => 'distro' . tplEx
		) );
	}
	
	function display_index()
	{
		global $template, $basic_gui;
		
		// some vars
		$template->assign_block_vars( 'indextable', '', array(
				'TITLE' => $this->lang[ 'Inx_title' ],
				'WELCOME' => $this->lang[ 'Inx_welcome' ],
				'FEATURES' => $this->lang[ 'Inx_features' ],
				'FEATURELIST' => $this->lang[ 'Inx_featurelist' ],
				'NOTE' => $this->lang[ 'Inx_note' ]
			) );
		
		// make it visible
		$template->assign_switch( 'indextable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'distro' );
	}
	
	function display_dl()
	{
		global $template, $basic_gui, $Cl_root_path, $board_config, $security;
		
		// general vars
		$template->assign_block_vars( 'dltable', '', array(
			'L_ZIP' => $this->lang[ 'dl_zip' ],
			'L_GZ' => $this->lang[ 'dl_gz' ],
			'L_BZ2' => $this->lang[ 'dl_bz2' ],
			'L_VERSION' => $this->lang[ 'Version' ],
			'L_TITLE' => $this->lang[ 'dl_title' ],
			'L_DATE' => sprintf( $this->lang[ 'dl_date' ] , $board_config[ 'Last_console_ver' ], $board_config[ 'Last_console_ver_date' ] ),
			'L_EXPL' => $this->lang[ 'dl_explanation' ],
			'L_INSTR' => $this->lang[ 'dl_instr' ],
			'L_INSTRTEXT' => sprintf( $this->lang[ 'dl_instrtext' ], $security->append_sid( '?' . MODE_URL . '=doc' ) ),
			'L_LICENSE' => $this->lang[ 'dl_license' ],
			'L_KEY' => $this->lang[ 'dl_key' ],
			'L_KEY_TITLE' => $this->lang[ 'dl_key_title' ],
			'L_DISCLAIMER1' => $this->lang[ 'dl_discl1' ],
			'L_DISCLAIMER2' => $this->lang[ 'dl_discl2' ],
			
			'U_KEY' => 'http://' . $board_config[ 'session_domain' ] . $board_config[ 'session_path' ] . '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=getkey',
			'U_LICENSE' => 'http://opensource.org/licenses/gpl-license.php',
			'U_ZIP' => $security->append_sid( '?' . MODE_URL . '=distro&files=console.zip' ),
			'U_GZ' => $security->append_sid( '?' . MODE_URL . '=distro&files=console.tar.gz' ),
			'U_BZ2' => $security->append_sid( '?' . MODE_URL . '=distro&files=console.tar.bz2' ),
		) );
		
		// something for the image array
		$template->assign_var_levels( '', 'IMG', array(
			'DL_ZIP' => $template->folder . 'images/zip.png',
			'DL_GZ' => $template->folder . 'images/gz.png',
			'DL_BZ2' => $template->folder . 'images/bz2.png',
		) );
		
		// tootlips :)
		$template->assign_var_levels( 'dltable', 'TOOLS', array(
			'ZIP' => $basic_gui->make_tooltip( $this->lang[ 'tool_zip' ], 'buttontip' ),
			'GZ' => $basic_gui->make_tooltip( $this->lang[ 'tool_gz' ], 'buttontip' ),
			'BZ2' => $basic_gui->make_tooltip( $this->lang[ 'tool_bz2' ], 'buttontip' ),
		) );
		
		// make it visible
		$template->assign_switch( 'dltable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'distro');
	}
	
	function listindex()
	{
		global $template, $basic_gui, $security;
		
		// teh vars, we know the drill
		$template->assign_block_vars( 'listinxtable', '', array(
			'T_FUNCTION' => $this->lang[ 'title_funct' ],
			'T_COSMETIC' => $this->lang[ 'title_cosm' ],
			'T_BASIC' => $this->lang[ 'title_basic' ],
			'T_LANG' => $this->lang[ 'title_lang' ],
			
			'D_FUNCTION' => $this->lang[ 'desc_funct' ],
			'D_COSMETIC' => $this->lang[ 'desc_cosm' ],
			'D_BASIC' => $this->lang[ 'desc_basic' ],
			'D_LANG' => $this->lang[ 'desc_lang' ],
			
			'U_FUNCTION' => $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=list&s=func' ),
			'U_COSMETIC' => $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=list&s=cosm' ),
			'U_BASIC' => $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=list&s=basic' ),
			'U_LANG' => $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=list&s=lang' ),
		) );
		
		// make it visible
		$template->assign_switch( 'listinxtable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'distro');
	}
	
	function display_list( $list, $sect )
	{
		global $template, $basic_gui, $basic_lang, $userdata, $Cl_root_path, $board_config, $security, $plug_clcode;
		
		// first construct the menu
		$menu = '<a href="' . $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=list&s=func' ) . '">' . $this->lang[ 'title_funct' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=list&s=cosm' ) . '">' . $this->lang[ 'title_cosm' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=list&s=basic' ) . '">' . $this->lang[ 'title_basic' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=list&s=lang' ) . '">' . $this->lang[ 'title_lang' ] . '</a><br />';
		$menu .= '<a href="' . $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=add&w=' . $sect ) . '">' . $this->lang[ 'title_add' ] . '</a><br />';
		
		// assign the basic vars 'n' stuff
		$template->assign_block_vars( 'listtable', '', array(
			'MENU' => $menu,
			
			'L_CALLSIGN' => $this->lang[ 'Callsign' ],
			'L_AUTHOR' => $this->lang[ 'Author' ],
			'L_DESCRIPTION' => $this->lang[ 'Description' ],
			'L_TIME' => $this->lang[ 'Time' ],
			'L_VERSION' => $this->lang[ 'Version' ],
			'L_SPAWN' => $this->lang[ 'Spawn' ],
			'L_REQUEST' => $this->lang[ 'Request' ],
			'L_FETCH' => $this->lang[ 'Fetch' ],
			'L_USEOPTS' => $this->lang[ 'Useopts' ],
			'L_SUMMONS' => $this->lang[ 'Summons' ],
		) );
		
		// now to happily make the array output
		if ( count( $list ) > 0 )
		{
			// saves typing for popups
			foreach ( $list as $entry )
			{
				// make the viewsource thingo
				// first construct the contents	
				$version = $entry[ 'version' ][ count( $entry[ 'version' ] )-1 ];
				
				$map = ( @is_file( $Cl_root_path . 'files/' . $entry[ 'name' ] . '/' . $version . '/filemap.txt' ) ) ? @file_get_contents( $Cl_root_path . 'files/' . $entry[ 'name' ] . '/' . $version . '/filemap.txt' ) : $this->lang[ 'No_map' ];
				$map = htmlspecialchars( $map );
				
				$sql = ( @is_file( $Cl_root_path . 'files/' . $entry[ 'name' ] . '/' . $version . '/sql.sql' ) ) ? @file_get_contents( $Cl_root_path . 'files/' . $entry[ 'name' ] . '/' . $version . '/sql.sql' ) : $this->lang[ 'No_sql' ];
				$sql = htmlspecialchars( $sql );
				
				$config = ( @is_file( $Cl_root_path . 'files/' . $entry[ 'name' ] . '/' . $version . '/config.txt' ) ) ? @file_get_contents( $Cl_root_path . 'files/' . $entry[ 'name' ] . '/' . $version . '/config.txt' ) : $this->lang[ 'No_config' ];
				$config = htmlspecialchars( $config );
				
				$methods = '<b>' . $this->lang[ 'Announce' ] . ': </b>' . htmlspecialchars( $entry[ 'announce_methods' ] ) . '<br />';
				$methods .= '<b>' . $this->lang[ 'Accept' ] . ': </b>' . htmlspecialchars( $entry[ 'accept_methods' ] ) . '<br />';
				$methods .= '<b>' . $this->lang[ 'Methods' ] . ': </b>' . htmlspecialchars( $entry[ 'mod_methods' ] ) . '<br />';
				$methods = '<div>' . $methods . '</div>';
				
				// then form the menu
				$sourcemenu = '<a href="#" ' . $basic_gui->add_pop( $map, 250, 250, 400, 300 ) . '>' . $this->lang[ 'Map' ] . '</a> | ';
				$sourcemenu .= '<a href="#" ' . $basic_gui->add_pop( $sql, 250, 250, 400, 300 ) . '>' . $this->lang[ 'Sql' ] . '</a> | ';
				$sourcemenu .= '<a href="#" ' . $basic_gui->add_pop( $config, 250, 250, 400, 300 ) . '>' . $this->lang[ 'Config' ] . '</a> | ';
				$sourcemenu .= '<a href="' . $basic_gui->get_URL() . '/files/' . $entry[ 'name' ] . '" target="_blank">' . $this->lang[ 'Source' ] . '</a> | ';
				$sourcemenu .= '<a href="#" ' . $basic_gui->add_pop( $methods, 250, 250, 400, 300 ) . '>' . $this->lang[ 'Methods' ] . '</a>';
				
				// create the versions list
				$verlist = '<select>';
				arsort( $entry[ 'version' ] );// sort it prettier
				foreach ( $entry[ 'version' ] as $ver )
				{
					$verlist .= '<option>' . $ver . '</option>';
				}
				$verlist .= '</select>';
				
				// vars
				$template->assign_block_vars( 'modrow', '', array(
					'TITLE' => $entry[ 'name' ],
					'CALLSIGN' => $entry[ 'callsign' ],
					'AUTHOR' => $entry[ 'author' ],
					'DESCRIPTION' => $plug_clcode->parse( $entry[ 'description' ], TRUE ),
					'TIME' => date( $userdata[ 'user_timeformat' ], $entry[ 'time' ] ),
					'VERSION' => $verlist,
					'SPAWN' => ( !empty( $entry[ 'spawn' ] ) ) ? $entry[ 'spawn' ] : $this->lang[ 'None'],
					'REQUEST' => ( !empty( $entry[ 'request' ] ) ) ? $entry[ 'request' ] : $this->lang[ 'None'],
					'FETCH' => ( $entry[ 'fetch_restrict' ] ) ? $basic_lang[ 'Yes' ] : $basic_lang[ 'No' ],
					'USEOPTS' => ( !empty( $entry[ 'use_opts' ] ) ) ? $entry[ 'use_opts' ] : $this->lang[ 'None'],
					'SUMMONS' => $entry[ 'summons' ],
					'SOURCE' => $sourcemenu
				) );
			
				// visible
				$template->assign_switch( 'modrow', TRUE );
			}
		}else
		{
			// vars
			$template->assign_block_vars( 'modrow', '', array(
				'TITLE' => $this->lang[ 'Nope' ],
				'L_NONE' => $this->lang[ 'Nope_txt' ],
				'NONE' => 'yes',
			) );
			
			// visible
			$template->assign_switch( 'modrow', TRUE );
		}
		
		// make it visible
		$template->assign_switch( 'listtable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'distro');
	}
	
	function display_add( $callist, $sect )
	{
		global $template, $basic_gui, $basic_lang, $security, $mod_loader;
		
		// get editor first
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'description', 'quickpost' => TRUE ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$news = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// assign the vars shall we
		$template->assign_block_vars( 'addtable', '', array(
			'L_WELCOME' => $this->lang[ 'add_welcome' ],
			'L_NAME' => $this->lang[ 'add_name' ],
			'L_VERSION' => $this->lang[ 'add_version' ],
			'L_DESCRIPTION' => $this->lang[ 'add_description' ],
			'L_CALLSIGN' => $this->lang[ 'add_callsign' ],
			'L_SPAWN' => $this->lang[ 'add_spawn' ],
			'L_REQUEST' => $this->lang[ 'add_request' ],
			'L_FETCH' => $this->lang[ 'add_fetch' ],
			'L_USEOPTS' => $this->lang[ 'add_useopts' ],
			'L_FILES' => $this->lang[ 'add_files' ],
			'L_MAP' => $this->lang[ 'add_map' ],
			'L_CONFIG' => $this->lang[ 'add_config' ],
			'L_SQL' => $this->lang[ 'add_sql' ],
			'L_SUBMIT' => $basic_lang[ 'Submit' ],
			'L_RESET' => $basic_lang[ 'Reset' ],
			'L_ANNOUNCE' => $this->lang[ 'add_announce' ],
			'L_ACCEPT' => $this->lang[ 'add_accept' ],
			'L_METHODS' => $this->lang[ 'add_methods' ],
			
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=distro&' . SUBMODE_URL . '=upmod&w=' . $sect ),
			'S_EDITOR' => $news[ 'editor_HTML' ],
		) );
		
		// the tooltips
		$template->assign_var_levels( 'addtable', 'TOOLS', array(
			'NAME' => $basic_gui->make_tooltip( $this->lang[ 'tools_name' ], 'buttontip' ),
			'VERSION' => $basic_gui->make_tooltip( $this->lang[ 'tools_version' ], 'buttontip' ),
			'DESCRIPTION' => $basic_gui->make_tooltip( $this->lang[ 'tools_description' ], 'buttontip' ),
			'CALLSIGN' => $basic_gui->make_tooltip( $this->lang[ 'tools_callsign' ], 'buttontip' ),
			'SPAWN' => $basic_gui->make_tooltip( $this->lang[ 'tools_spawn' ], 'buttontip' ),
			'REQUEST' => $basic_gui->make_tooltip( $this->lang[ 'tools_request' ], 'buttontip' ),
			'FETCH' => $basic_gui->make_tooltip( $this->lang[ 'tools_fetch' ], 'buttontip' ),
			'USEOPTS' => $basic_gui->make_tooltip( $this->lang[ 'tools_useopts' ], 'buttontip' ),
			'FILES' => $basic_gui->make_tooltip( $this->lang[ 'tools_files' ], 'buttontip' ),
			'MAP' => $basic_gui->make_tooltip( $this->lang[ 'tools_map' ], 'buttontip' ),
			'CONFIG' => $basic_gui->make_tooltip( $this->lang[ 'tools_config' ], 'buttontip' ),
			'SQL' => $basic_gui->make_tooltip( $this->lang[ 'tools_sql' ], 'buttontip' ),
			'ANNOUNCE' => $basic_gui->make_tooltip( $this->lang[ 'tools_announce' ], 'buttontip' ),
			'ACCEPT' => $basic_gui->make_tooltip( $this->lang[ 'tools_accept' ], 'buttontip' ),
			'METHODS' => $basic_gui->make_tooltip( $this->lang[ 'tools_methods' ], 'buttontip' ),
		) );
		
		// make the lists
		// headers
		$spawnlist = '<select onchange="addfromlist( \'spawnlist\', \'spawns\' )" id="spawnlist">';
		$requestlist = '<select onchange="addfromlist( \'requestlist\', \'requests\' )" id="requestlist">';
		$optslist = '<select onchange="addfromlist( \'useoptslist\', \'useopts\' )" id="useoptslist">';
		
		// bodies
		foreach ( $callist as $call )
		{
			$call = $call[ 'callsign' ];
			$spawnlist .= '<option>' . $call . '</option>';
			$requestlist .= '<option>' . $call . '</option>';
			$optslist .= '<option>' . $call . '</option>';
		}
		
		// footers
		$spawnlist .= '</select>';
		$requestlist .= '</select>';
		$optslist .= '</select>';
		
		// assign the lists
		$template->assign_var_levels( 'addtable', 'LISTS', array(
			'SPAWN' => $spawnlist,
			'REQUEST' => $requestlist,
			'USEOPTS' => $optslist
		) );
		
		// make it visible
		$template->assign_switch( 'addtable', TRUE );
		
		// add to output
		$basic_gui->add_file( 'distro');
	}

	//
	// End of distro-gui class
	//
}


?>