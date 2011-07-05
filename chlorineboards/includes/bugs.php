<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:                 bugs.php                            //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       24th November 2005                      //
//     version:               0.4.1                              //
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
// this is the ClB's official bug tracker
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
// forum_list :: array with forums

// class creation
$vars = array( 'debug', 'gui', 'lang' );
$visible = array( 'private', 'private', 'private' );
eval( Varloader::createclass( 'bugs', $vars, $visible ) );
// end class creation

class Bugs extends bugs_def
{
	
	// constructor
	function Bugs( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/bugs_gui' . phpEx );
		$this->gui = new Bugs_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'bugs' );
		$this->gui->lang = $this->lang;
		$Sajax->add2export( 'Bugs->showbug', '$bugid' );
		$Sajax->add2export( 'Bugs->addbugreply', '$bugid, $author, $reply' );
	}
	
	// takes care of choosing the correct thing to display
	function display()
	{
		global $security, $basic_gui, $errors, $basic_lang, $userdata;
		
		// read the sub mode
		$mode = ( isset( $_GET[ SUBMODE_URL ] ) ) ? strval( $_GET[ SUBMODE_URL ] ) : '';
		
		// the page title
		$basic_gui->set_title( $basic_lang[ 'Bugs' ] );
		
		// now do as it says
		switch ( $mode )
		{
			case '':
				$basic_gui->set_level( 1, 'bugs' );
				$this->gui->index();
				break;
			case 'rep':
				$basic_gui->set_level( 2, 'bugs' );
				if ( !( $userdata[ 'user_level' ] >= ADMIN ) )
				{ // user needs to be logged in
					$errors->report_error( sprintf( $basic_lang[ 'Need_login' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
				}
				$this->gui->report();
				break;
			case 'rep2':
				$this->addbug();
				break;
			case 'read':
				$basic_gui->set_level( 2, 'bugs', '0,1' );
				$this->readbugs();
				break;
			case 'reqf':
				$basic_gui->set_level( 2, 'bugs', '0,2' );
				$this->getreq( 'feature' );
				break;
			case 'reqm':
				$basic_gui->set_level( 2, 'bugs', '0,3' );
				$this->getreq( 'module' );
				break;
			case 'addreq':
				$this->addreq();
				break;
			default:
				$errors->report_error( $this->lang[ 'err_nomode' ], GENERAL_ERROR );
				break;
		}
	}
	
	// this "reports" a bug
	function addbug()
	{
		global $security, $errors, $db, $basic_lang, $userdata;
		
		// check if the user is logged in
		if ( !( $userdata[ 'user_level' ] >= ADMIN ) )
		{
			$errors->report_error( sprintf( $basic_lang[ 'Need_login' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
		}
		
		// check if the form is correctly submitted
		if ( !isset( $_POST[ 'repbug' ] ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'rep_errsubm' ], $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=rep' ) ), GENERAL_ERROR );
		}
		
		// set the vars
		$title = ( isset( $_POST[ 'title' ] ) ) ? strval( $_POST[ 'title' ] ) : '';
		$desc = ( isset( $_POST[ 'desc' ] ) ) ? strval( $_POST[ 'desc' ] ) : '';
		$author = $userdata[ 'user_id' ];
		
		// check if all is right
		if ( empty( $title ) || empty( $desc ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'rep_errempty' ], $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=rep' ) ), GENERAL_ERROR );
		}
		
		// add it to the db
		$sql = "INSERT INTO " . BUGS_TABLE . " VALUES( '', '" . time() . "', '$author', '$title', '$desc' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'rep_errquery' ], $security->append_sid( '?' . MODE_URL . '=bugs&' . SUBMODE_URL . '=rep' ) ), GENERAL_ERROR );
		}
		$errors->report_error( sprintf( $this->lang[ 'rep_done' ], $security->append_sid( '?' . MODE_URL . '=bugs' ) ), MESSAGE );
	}
	
	// this will fetch stuff from the bugs table
	function readbugs()
	{
		global $errors, $db;
		
		// query the stuff
		$sql = "SELECT * FROM " . BUGS_TABLE . " ORDER BY bug_time DESC";
		if ( !$res = $db->sql_query( $sql ) )
		{ // damn it
			$errors->report_error( $this->lang[ 'read_errquery' ], GENERAL_ERROR );
		}
		// put it in an arry
		$bugs = $db->sql_fetchrowset( $res );
		
		// pass it to the gui 'n' stuff
		$this->gui->readbugs( $bugs );
	}
	
	// this returns the display of the info from a bug report
	function showbug( $bugid )
	{
		global $db, $errors;
		
		// query it
		$sql = "SELECT b.bug_text, r.*, u1.username AS bug_author, u2.username AS reply_author FROM " . BUGS_TABLE . " b LEFT JOIN " . BUG_REPLIES . " r ON b.bug_id=r.bug_id LEFT JOIN " . USERS_TABLE . " u1 ON u1.user_id=b.bug_author LEFT JOIN " . USERS_TABLE. " u2 ON u2.user_id=r.reply_author WHERE b.bug_id='$bugid'";
		if ( !$res = $db->sql_query( $sql ) )
		{
			return array( $bugid, $errors->return_error( 'Could not query data', GENERAL_ERROR ) );
		}
		
		$bug = $db->sql_fetchrowset( $res );
		
		return array( $bugid, $this->gui->showbug( $bug, $bugid ) );
	}
	
	// this adds a reply to a bug
	function addbugreply( $bugid, $reply )
	{
		global $security, $db, $errors, $userdata, $basic_lang;
		
		// check for logged in
		if ( !( $userdata[ 'user_level' ] >= ADMIN ) )
		{
			return array( $bugid, $errors->return_error( sprintf( $basic_lang[ 'Need_login' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR ) );
		}
		
		// parse the reply
		// we need to addslashes and force it to do so because this has been fetched straight
		// from the form and not through $_POST
// 		$reply = $securtiy->parsevar( $reply, ADD_SLASHES, TRUE );
		
		$author = $userdata[ 'user_id' ];
		// query the thingo
		$sql = "INSERT INTO " . BUG_REPLIES . " VALUES( '', '$bugid', '" . time() . "', '$author', '$reply' )";
		if ( !$db->sql_query( $sql ) )
		{
			return array( $bugid, $errors->return_error( 'Could not query input', GENERAL_ERROR ) );
		}
	
		// so the bug gets refreshed :)
		return array( $bugid, $errors->return_error( sprintf( $this->lang[ 'read_replied' ], $security->append_sid( '?' . MODE_URL . '=bugs' ) ), MESSAGE ) );
	}
	
	// fetches the list of requests and stuff
	function getreq( $mode )
	{
		global $db, $errors;
		
		// query for the list
		$sql = "SELECT r.*, u.username AS req_author FROM " . REQUESTS_TABLE . " r LEFT JOIN " . USERS_TABLE . " u on r.req_author=u.user_id WHERE r.req_mode='$mode' ORDER BY r.req_solved ASC";
		if ( !$res = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not query data', CRITICAL_ERROR );
		}
		// populate the list
		$list = $db->sql_fetchrowset( $res );
		
		// gui it
		$this->gui->requests( $list, $mode );
	}
	
	// adds a new requests
	function addreq( )
	{
		global $security, $db, $errors, $basic_lang, $userdata;
		
		// we tell the poor sap they cannot do this if they're not logged in :)
		if ( !( $userdata[ 'user_level' ] >= ADMIN ) )
		{
			$errors->report_error( sprintf( $basic_lang[ 'Need_login' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
		}
		
		// secure the data
		$author = $userdata[ 'user_id' ];
		$desc = ( isset( $_POST[ 'desc' ] ) ) ? strval( $_POST[ 'desc' ] ) : '';
		$w = ( isset( $_GET[ 'w' ] ) ) ? strval( $_GET[ 'w' ] ) : '';
		
		if ( !isset( $_POST[ 'addreq' ] ) || empty( $w ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'req_errform' ], $security->append_sid( '?' . MODE_URL . '=bugs' ) ), GENERAL_ERROR );
		}
		
		if ( empty( $desc ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'req_errempty' ], $security->append_sid( '?' . MODE_URL . '=bugs' ) ), GENERAL_ERROR );
		}
		
		// query it
		$sql = "INSERT INTO " . REQUESTS_TABLE . " VALUES ( '', '" . time() . "', '$w', '0', '$author', '$desc' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not insert into database', CRITICAL_ERROR );
		}
		$errors->report_error( sprintf( $this->lang[ 'req_done' ], $security->append_sid( '?' . MODE_URL . '=bugs' ) ), MESSAGE );
	}
	
	//
	// End of Bugs class
	//
}


?>