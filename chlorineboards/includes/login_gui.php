<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:               login_gui.php                         //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:      08th December 2005                       //
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
// gui for the login module
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
eval( Varloader::createclass( 'login_gui', $vars, $visible ) );
// end class creation

class Login_gui extends login_gui_def
{
	function login_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'login' => 'login' . tplEx
		) );
	}
	
	function index()
	{
		global $template, $basic_gui, $plug_clcode, $security;
		
		// well we assign the vars eh
		$template->assign_block_vars( 'index', '', array(
			'L_HEAD' => $this->lang[ 'index_head' ],
			'L_LOGIN' => $this->lang[ 'index_login' ],
			'L_USERNAME' => $this->lang[ 'index_username' ],
			'L_PASSWORD' => $this->lang[ 'index_password' ],
			'L_WELCOME' => $plug_clcode->parse( $this->lang[ 'index_welcome' ] ),
			'L_AUTOLOG' => $this->lang[ 'index_autolog' ],
			'L_FORGOT' => $this->lang[ 'log_forgotpass' ],
			'L_REGISTER' => $this->lang[ 'log_register' ],
			
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=login' ),
			'S_FORGOT' => $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=forgot' ),
			'S_REGISTER' => $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=register' ),
		) );
		
		// the tooltips
		$template->assign_var_levels( 'index', 'TOOLS', array(
			'USERNAME' => $basic_gui->make_tooltip( $this->lang[ 'itool_username' ], 'buttontip' ),
			'PASSWORD' => $basic_gui->make_tooltip( $this->lang[ 'itool_password' ], 'buttontip' ),
		) );
	
		// make it visible
		$template->assign_switch( 'index', TRUE );
		
		// add to output
		$basic_gui->add_file( 'login' );
	}
	
	function forgot()
	{
		global $template, $basic_gui, $security;
		
		// the vars, as always
		$template->assign_block_vars( 'forgot', '', array(
			'L_HEAD' => $this->lang[ 'forgot_head' ],
			'L_WELCOME' => $this->lang[ 'forgot_welcome' ],
			'L_EMAIL' => $this->lang[ 'forgot_email' ],
			
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=fetchpass' ),
		) );
		
		// tooltips
		$template->assign_var_levels( 'forgot', 'TOOLS', array(
			'EMAIL' => $basic_gui->make_tooltip( $this->lang[ 'ftool_email' ], 'buttontip' ),
		) );
		
		// make it visible
		$template->assign_switch( 'forgot', TRUE );
		
		// add to output
		$basic_gui->add_file( 'login' );
	}
	
	function register()
	{
		global $template, $basic_gui, $security, $plug_captcha, $lang_loader, $Cl_root_path;
		
		// get the captcha
		if ( $lang_loader->board_lang == 'en' )
		{ // this one is cool. but very language sensitive
			$captcha = $plug_captcha->recognition();
		}else
		{
			$captcha = $plug_captcha->random( 200, 50 );
			$captcha[ 0 ] = $basic_gui->get_URL() . '/' . substr( $captcha[ 0 ], strlen( $Cl_root_path ) );
		}
		
		
		// the vars
		$template->assign_block_vars( 'register', '', array(
			'L_HEAD' => $this->lang[ 'reg_head' ],
			'L_WELCOME' => $this->lang[ 'reg_welcome' ],
			'L_USERNAME' => $this->lang[ 'reg_username' ],
			'L_EMAIL1' => $this->lang[ 'reg_email1' ],
			'L_EMAIL2' => $this->lang[ 'reg_email2' ],
			'L_PASS1' => $this->lang[ 'reg_pass1' ],
			'L_PASS2' => $this->lang[ 'reg_pass2' ],
			'L_CAPTCHA' => $this->lang[ 'reg_captcha' ],
// 			'L_CAPTCHAINFO' => $this->lang[ 'reg_captchainfo' ],
			
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=register2' ),
			'S_CAPTCHA' => $captcha[ 0 ],
			'S_CAPTCHA2' => md5( strtolower( $captcha[ 1 ] ) ),
		) );
		
		// the tooltips
		$template->assign_var_levels( 'register', 'TOOLS', array(
			'USERNAME' => $basic_gui->make_tooltip( $this->lang[ 'rtool_username' ], 'buttontip' ),
			'EMAIL1' => $basic_gui->make_tooltip( $this->lang[ 'rtool_email1' ], 'buttontip' ),
			'EMAIL2' => $basic_gui->make_tooltip( $this->lang[ 'rtool_email2' ], 'buttontip' ),
			'PASS1' => $basic_gui->make_tooltip( $this->lang[ 'rtool_pass1' ], 'buttontip' ),
			'PASS2' => $basic_gui->make_tooltip( $this->lang[ 'rtool_pass2' ], 'buttontip' ),
			'CAPTCHA' => $basic_gui->make_tooltip( $this->lang[ 'rtool_captcha' ], 'buttontip' ),
		) );
		
		// make it visible
		$template->assign_switch( 'register', TRUE );
		
		// add to output
		$basic_gui->add_file( 'login' );
	}


	//
	// End of login-gui class
	//
}


?>