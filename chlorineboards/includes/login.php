<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:              login_mod.php                          //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:      08th December 2005                       //
//     version:               0.5.0                              //
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
// shows people what a login module looks like
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
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'login', $vars, $visible ) );
// end class creation

class Login extends login_def
{
	
	// constructor
	function Login( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/login_gui' . phpEx );
		$this->gui = new Login_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'login' );
		$this->gui->lang = $this->lang;
		
		$Sajax->add2export( 'Login->captcha', '' );
		$Sajax->add2export( 'Login->captcha2', '' );
		
		$this->user_level = GUEST;
	}
	
	// this is the main function here :P
	function display()
	{
		global $basic_gui, $userdata;
		
		// first read the submode
		$mode = ( $_GET[ SUBMODE_URL ] ) ? strval( $_GET[ SUBMODE_URL ] ) : 'index';
		
		// now we obey the mode
		switch( $mode )
		{
			case 'index':
				$basic_gui->set_level( 1, 'login' );
				$this->gui->index();
				break;
			case 'login':
				$basic_gui->set_level( 1, 'login' );
				$this->userlogin();
				break;
			case 'forgot':
				$basic_gui->set_level( 2, 'login' );
				$this->gui->forgot();
				break;
			case 'fetchpass':
				$basic_gui->set_level( 2, 'login' );
				$this->fetchpass();
				break;
			case 'logout':
				$basic_gui->set_level( 2, 'login', '0,1' );
				$this->logout();
				break;
			case 'register':
				$basic_gui->set_level( 2, 'login', '0,2' );
				$this->gui->register();
				break;
			case 'register2':
				$basic_gui->set_level( 2, 'login', '0,2' );
				$this->register();
				break;
			case 'captcha':
				echo $this->captcha();
				break;
		}
	}
	
	// this basically logs you in
	function userlogin( $username = '', $password = '', $autolog = FALSE, $loaded = FALSE )
	{
		global $errors, $db, $security, $encryption, $board_config, $basic_gui;
		
		
		// first check if the form was sent correctly
		if ( !isset( $_POST[ 'userlogin' ] ) && !$loaded )
		{
			$errors->report_error( sprintf( $this->lang[ 'log_errform' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), CRITICAL_ERROR );
		}
		
		// get the form data
		$username = ( isset( $_POST[ 'username' ] ) ) ? strval( $_POST[ 'username' ] ) : $username;
		$password = ( isset( $_POST[ 'password' ] ) ) ? strval( $_POST[ 'password' ] ) : $password;
		$autolog = ( isset( $_POST[ 'autolog' ] ) ) ? TRUE : $autolog;
		
		// then see if all is entered :P
		if ( empty( $username ) || empty( $password ) )
		{
			if ( !$loaded )
			{
				$errors->report_error( sprintf( $this->lang[ 'log_errempty' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
			}else
			{
				return FALSE;
			}
		}
		
		// now we try fetching data from the database
		$sql = "SELECT * FROM " . USERS_TABLE . " WHERE username='$username' LIMIT 1";
		if ( !$res = $db->sql_query( $sql ) )
		{
			if ( !$loaded )
			{
				$errors->report_error( 'Could not run query', CRITICAL_ERROR );
			}else
			{
				return FALSE;
			}
		}
		
		// if there are zero lines login was wrong
		if ( $db->sql_numrows( $res ) == 0 )
		{
			if ( !$loaded )
			{
				$errors->report_error( sprintf( $this->lang[ 'log_erruser' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
			}else
			{
				return FALSE;
			}
		}
		// fetch user data
		$data = $db->sql_fetchrow( $res );
		$user_level = $data[ 'user_level' ];
		
		// now encrypt the password we got
		$password = $encryption->encrypt( $security->make_key( $data[ 'user_email' ] ), $password, 30 );
		
		// check the password
		if ( $password != addslashes( $data[ 'password' ] ) )
		{
			if ( !$loaded )
			{
				$errors->report_error( sprintf( $this->lang[ 'log_errpassword' ], $security->append_sid( '?' . MODE_URL . '=login' ) ), GENERAL_ERROR );
			}else
			{
				return FALSE;
			}
		}
		
		switch( $user_level ) 
		{ // for a helpful link to access what the user might want
			case ADMIN:
			case SUPER_MOD:
			case MOD:
				$Uadd = '<br /><a href="' . $security->append_sid( '?' . MODE_URL . '=ACP' ) . '">' . $this->lang[ 'log_toadmin' ] . '</a>';
				break;
			case USER:
				$Uadd = '<br /><a href="' . $security->append_sid( '?' . MODE_URL . '=UCP' ) . '">' . $this->lang[ 'log_toadmin' ] . '</a>';
				break;
			default:
				$Uadd = '';
		}
		
		// now we need to tell the session we're in
		$_SESSION[ 'user_id' ] = $data[ 'user_id' ];
		$_SESSION[ 'autolog' ] = intval( $autolog );
		$_SESSION[ 'setcookie' ] = intval( $autolog );
		$_SESSION[ 'logged_in' ] = TRUE;
		
		// empty the whole page cache...
		$basic_gui->remove_cached_pages( $data[ 'user_id' ] );
		
		// now we tell the user we're in
		if ( !$loaded )
		{
			$errors->report_error( sprintf( $this->lang[ 'log_in' ], $security->append_sid( 'index' . phpEx ) ) . $Uadd, MESSAGE );
		}else
		{
			$this->user_level = $user_level;
			return TRUE;
		}
	}
	
	// sends email with password
	function fetchpass()
	{
		global $security, $errors, $db, $encryption, $userdata, $board_config, $users;
		
		// first see correctness of form
		if ( !isset( $_POST[ 'userfetchpass' ] ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'forgot_errform' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=forgot' ) ), CRITICAL_ERROR );
		}
		
		// get email
		$email = ( isset( $_POST[ 'email' ] ) ) ? strval( $_POST[ 'email' ] ) : '';
		
		// now the thingo needs to be set too right
		if ( empty( $email ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'forgot_errempty' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=forgot' ) ), GENERAL_ERROR );
		}
		
		// now try to fetch the data from the db
		$sql = "SELECT password, user_email FROM " . USERS_TABLE . " WHERE user_email='$email'";
		if ( !$res = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not query data', CRITICAL_ERROR );
		}
		// check if that actually returned something
		if ( $db->sql_numrows( $res ) == 0 )
		{
			$errors->report_error( sprintf( $this->lang[ 'forgot_errfind' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=forgot' ) ), GENERAL_ERROR );
		}
		$pass = $db->sql_fetchrow( $res ); // get pass
		// now decode the pass
		$pass = $encryption->decrypt( $security->make_key( $email ), $pass[ 'password' ] );
		
		// prepare email
		$URL = 'http://' . $board_config[ 'session_domain' ] . $board_config[ 'session_path' ] . '?' . MODE_URL . '=login';
		$body = sprintf( $this->lang[ 'forgot_mail' ], date( $userdata[ 'user_timeformat' ] ), $URL, $pass, $board_config[ 'sitename' ] );
		$subject = sprintf( $this->lang[ 'forgot_mailsubject' ], $board_config[ 'sitename' ] );
		
		// send the email
		if ( $users->clb_Mail( $email, $subject, $body, $board_config[ 'admin_email' ] ) )
		{ // far as we know it workd
			$errors->report_error( sprintf( $this->lang[ 'forgot_done' ], $security->append_sid( 'index' . phpEx ) ), MESSAGE );
		}else
		{ // nope
			$errors->report_error( sprintf( $this->lang[ 'forgot_errsend' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=forgot' ) ), CRITICAL_ERROR );
		}
	}
	
	// this function logs out the user
	function logout()
	{
		global $errors, $userdata, $security;
		
		// first if we have autolog then we need to remove the cookie
		if ( $userdata[ 'autolog' ] )
		{
			setcookie( 'CLBAutolog', '', time()-3600 );
		}
		
		// now we tell the session that we logged out (remove all data that is stored)
		$_SESSION = array();
		
		// now we tell the user of the dreadful thing :P
		$errors->report_error( sprintf( $this->lang[ 'logout_msg' ], $security->append_sid( 'index' . phpEx ) ), MESSAGE );
	}
	
	// this function registers a new user
	function register()
	{
		global $errors, $security, $encryption, $cache, $db, $userdata, $board_config, $users;
		
		// see if form was sent correctly
		if ( !isset( $_POST[ 'registeruser' ] ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'reg_errform' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=register' ) ), CRITICAL_ERROR );
		}
		
		$entries = array( 'username', 'email1', 'email2', 'pass1', 'pass2', 'in_captcha', 'woogabooga' ); // the needed POST values
		$good = array();
		
		// we go through them, if any is empty then we make an error, otherwise we make their input nicer
		foreach ( $entries as $k )
		{
			if ( !isset( $_POST[ $k ] ) || empty( $_POST[ $k ] ) )
			{
				$errors->report_error( sprintf( $this->lang[ 'reg_errempty' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=register' ) ), GENERAL_ERROR );
			}else
			{
				$good[ $k ] = strval( $_POST[ $k ] );
			}
		}
		
		// first check the captcha
// 		$captcha = $cache->pull( $userdata[ 'sid' ] . 'captchawords', ESSENTIAL ); // get it from storage
// 		$captcha[ 0 ] = $encryption->decrypt( $security->make_key( 'short' ), $captcha[ 0 ] ); // decrypt the short word
// 		$captcha[ 1 ] = $encryption->decrypt( $security->make_key( 'long' ), $captcha[ 1 ] ); // decrypt the long word
// 		$in_captcha = explode( ' ', $good[ 'in_captcha' ] ); // get the input
		// do check
		if ( md5( strtolower( $good[ 'in_captcha' ] ) ) != $good[ 'woogabooga' ] )
		{ // wrongness
			$errors->report_error( sprintf( $this->lang[ 'reg_errcaptcha' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=register' ) ), GENERAL_ERROR );
		}
		
		// now for stuff that needs to match
		if ( $good[ 'email1' ] != $good[ 'email2' ] || strpos( $good[ 'email1' ], '@' ) === FALSE )
		{
			$errors->report_error( sprintf( $this->lang[ 'reg_errmail' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=register' ) ), GENERAL_ERROR );
		}
		if ( $good[ 'pass1' ] != $good[ 'pass2' ] || strlen( $good[ 'pass1' ] ) < 5 )
		{
			$errors->report_error( sprintf( $this->lang[ 'reg_errpass' ], $security->append_sid( '?' . MODE_URL . '=login&' . SUBMODE_URL . '=register' ) ), GENERAL_ERROR );
		}
		
		// all there's left really is to put this into the db
		$username = $good[ 'username' ];
		$email = $good[ 'email1' ];
		$pass = $good[ 'pass1' ];
		$password = $encryption->encrypt( $security->make_key( $email ), $good[ 'pass1' ], 30 );
		$sql = "INSERT INTO " . USERS_TABLE . " ( username, password, user_email, user_level ) VALUES ( '$username', '$password', '$email', '" . USER . "' )";
		
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not query insertion', CRITICAL_ERROR );
		}
		// try to send an email
		$date = date( $userdata[ 'user_timeformat' ] );
		$subject = sprintf( $this->lang[ 'reg_mailsubj' ], $board_config[ 'sitename' ] );
		$URL = 'http://' . $board_config[ 'session_domain' ] . $board_config[ 'session_path' ];
		$body = sprintf( $this->lang[ 'reg_mailbody' ], $date, $board_config[ 'sitename' ], $username, $pass, $good[ 'password' ], $URL );
		
		// here we delete the captcha images from cache
		$this->cleancaptcha();
		
		// send the email
		if ( $users->clb_Mail( $email, $subject, $body, $board_config[ 'admin_email' ] ) )
		{
			$errors->report_error( sprintf( $this->lang[ 'reg_done' ], $this->lang[ 'reg_mailyes' ], $security->append_sid( 'index' . phpEx ) ), MESSAGE );
		}else
		{
			$errors->report_error( sprintf( $this->lang[ 'reg_done' ], $this->lang[ 'reg_mailno' ], $security->append_sid( 'index' . phpEx ) ), MESSAGE );
		}
	}
	
	// makes text on the image somewhat arced
	function arctext( $x, $y, $size, $font, &$img, $str, $col, $w, $ang )
	{
		// a few basic things that we'll need
		$msize = ceil( $size/2 );
		$delta = ceil( $msize/strlen( $str ) );
		$xx = $x;
		$yy = $y;
		$sz = $size;
		
		// just do nice text, it'll be better
		imagettftext( $img, $sz, 0,  $xx, $yy, $col, $font, $str );
		return;
		
		// now print the text
		for ( $i = 0; $i < strlen( $str ); $i++ )
		{
			$s = imagettftext( $img, $sz, $ang, $xx, $yy, $col, $font, $str{$i} );
			$xx = $s[ 2 ];
			$ang = ( $w == 'd' ) ? $ang - 5 : $ang + 5;
			$sz -= $delta;
		}
	}

	// this creates the captcha for registration
	function captcha()
	{
		global $cache, $security, $encryption, $Cl_root_path, $userdata, $Cl_root_path4template;
		
		// first delete the images
		$this->cleancaptcha();
		
		// create an empty image
		$maxx = 400;
		$maxy = 300;
		$img = imagecreatetruecolor( $maxx, $maxy );
		
		// now put in a background
		$b = rand( 0, 2 );
		$bck = imagecreatefromjpeg( $Cl_root_path . 'includes/captcha/back' . $b . '.jpg' );
		$size = getimagesize( $Cl_root_path . 'includes/captcha/back' . $b . '.jpg' );
		imagecopy( $img, $bck, 0, 0, rand( 0, $size[ 0 ]-$maxx ), rand( 0, $size[ 1 ]-$maxy ), $maxx, $maxy );
		imagedestroy( $bck );
		
		// array of available fonts
		$fonts = array( 'times', 'tahoma', 'lsans' );
		// the font we'll be using
		$col = imagecolorallocatealpha( $img, 0, 0, 0, 50 ); // and the colour
		
		// make the array of strings
		include( $Cl_root_path . 'includes/captcha/wordlist' . phpEx ); // get the word list
// 		$list = explode( "\n", file_get_contents( $Cl_root_path . 'includes/captcha/corncob_lowercase.txt' ) ); // get the word list
// 		return 'BU';
		$words = array();
		$got = array();
		srand( (float) microtime() * 10000000 );
		// we pick a random offset then jump for a random number of steps, thus the words are never repeated
		// if we stumble on a length we already have, we linearly move on until it differs
		$k =ceil( count( $list )/10 );
		$x = rand( 1, $k );
		while ( count( $words ) < 10 )
		{
			$w = $list[ $x ];
			if ( $got[ strlen( $w ) ] )
			{
				$i = $x;
				while ( $got[ strlen( $w ) ] )
				{
					$w = $list[ $i ];
					$i++;
				}
			}
			$words[] = $w;
			$got[ strlen( $w) ] = TRUE;
			$x += rand( $i+1, $k-1-$i );
		}
		
		$x = 40;
		$y = 50;
		$size = 22;
		$wordlist = array(); // this will later be used to determine the shortest and longest word
		
		// print text to image
		for ( $i = 0; $i < 4; $i++ )
		{
			// choose font for each line
			$font1 = $Cl_root_path . 'includes/captcha/' . $fonts[ rand( 0, count( $fonts )-1 ) ] . '.ttf';
			$font2 = $Cl_root_path . 'includes/captcha/' . $fonts[ rand( 0, count( $fonts )-1 ) ] . '.ttf';
			// choose word for each line
			$w1 = $words[ $i ];
			$w2 = $words[ count( $words )-$i-1 ];
			// add words to list
			$wordlist[] = $w1;
			$wordlist[] = $w2;
			// write text to image
			$this->arctext( $x + 20, $y + 8, $size, $font1, $img, $w1, $col, 'd', 10 );
			$this->arctext( $x, $y, $size, $font2, $img, $w2, $col, 'd', 10 );
			// change position for next word
			$dx = ( strlen( $w1 ) < strlen( $w2 ) ) ? strlen( $w1 ) * $size : strlen( $w2 ) * $size;
			$x += $dx/4;
			$y += $size + 40;
		}
		
		// now we chop up the image into smaller entities
		
		// first calculate the parts
		$nrows = rand( 2, 10 );
		$ncols = rand( 2, 10 );
		$rows = array( 0 );
		$cols = array( 0 );
		// y borders
		for ( $i = 1; $i < $nrows; $i++ )
		{
			$rows[ $i ] = rand( $rows[ $i - 1 ], $maxy );
			if ( $rows[ $i ] == $maxy )
			{ // break because we found the end
				$nrows = $i;
				break;
			}
		}
		// x borders
		for ( $i = 1; $i < $ncols; $i++ )
		{
			$cols[ $i ] = rand( $cols[ $i - 1 ], $maxx );
			if ( $cols[ $i ] == $maxx )
			{ // break because we found the end
				$ncols = $i;
				break;
			}
		}
		if ( $rows[ $i ] != $maxy )
		{ // we didn't have border yet
			$rows[] = $maxy;
		}
		if ( $cols[ $i ] != $maxx )
		{ // we didn't have border yet
			$cols[] = $maxx;
		}
		
		$tosave = $Cl_root_path . 'cache/captcha_' . $userdata[ 'sid' ] . '_' . time();
		$filelist = array(); // this will be the array of files that will later need deletion
		
		// now do chopping and create the table as we go
		$out = '<table cellspacing="0" cellpadding="0" border="0">';
		for ( $i = 1; $i <= $nrows; $i++ )
		{ // the rows
			$y1 = $rows[ $i -1 ];
			$y2 = $rows[ $i ];
			if ( $y1 == $y2 )
			{ // if the row is 0px big it can be skipped
				continue;
			}
			$out .= '<tr>';
			for ( $ii = 1; $ii <= $ncols; $ii++ )
			{ // the cols
				$x1 = $cols[ $ii - 1 ];
				$x2 = $cols[ $ii ];
				
				if ( $x1 == $x2 )
				{ // if the col is 0px big it can be skipped
					continue;
				}
				
				// make empty image
				$im = imagecreatetruecolor( $x2-$x1, $y2-$y1 );
				// paste part of the captcha onto it
				imagecopy( $im, $img, 0, 0, $x1, $y1, $x2, $y2 );
				// save it to cache
				imagejpeg( $im, $tosave . "_$i:$ii.jpg" );
				// don't need it anymore
				imagedestroy( $im );
				// add to table
				$out .= "<td><img src=\"$Cl_root_path4template$tosave" . "_$i:$ii.jpg\"/></td>";
				// add to filelist
				$filelist[] = $tosave . "_$i:$ii.jpg";
			}
			$out .= '</tr>';
		}
		$out .= '</table>';
		imagedestroy( $img );
		
		// now store the filelist to cache
		$cache->push( $userdata[ 'sid' ] . 'captchalist', $filelist, TRUE, ESSENTIAL );
		// find the shortest and longest used word
		$sh = array( 200, 0 );
		$lng = array( 0, 0 );
		for ( $i = 0; $i < count( $wordlist ); $i++ )
		{
			$l = strlen( $wordlist[ $i ] );
			if ( $l < $sh[ 0 ] )
			{ // shorter than the "shortest"
				$sh[ 0 ] = $l;
				$sh[ 1 ] = $i;
			}
			if ( $l > $lng[ 0 ] )
			{ // longer than the "longest"
				$lng[ 0 ] = $l;
				$lng[ 1 ] = $i;
			}
		}
		// save this too, but encrypted so it cannot be abused
		$w1 = $encryption->encrypt( $security->make_key( 'short' ), $wordlist[ $sh[ 1 ] ], 15 );
		$w2 = $encryption->encrypt( $security->make_key( 'long' ), $wordlist[ $lng[ 1 ] ], 15 );
		$cache->push( $userdata[ 'sid' ] . 'captchawords', array( $w1, $w2 ), TRUE, ESSENTIAL );
		
		return $out;
	}
	
	// this cleans the captcha images from cache
	function cleancaptcha()
	{
		global $cache, $userdata;
		
		// get filelist
		$list = $cache->pull( $userdata[ 'sid' ] . 'captchalist', ESSENTIAL );
		
		if ( empty( $list ) )
		{ // nothing to do
			return;
		}
		
		foreach( $list as $file )
		{ // delete each one
			@unlink( $file );
		}
	}
	
	/**
	* used for session activation when for some reason a different interface is used
	*/
	function activation()
	{
		global $mod_loader;
		
		$vars = $mod_loader->get_vars( array( 'username', 'password', 'autolog' ) );
		$success = $this->userlogin( $vars[ 'username' ], $vars[ 'password' ], $vars[ 'autolog' ], TRUE );
		$mod_loader->port_vars( array( 'success' => $success, 'user_level' => $this->user_level ) );
	}
	
	//
	// End of Login class
	//
}


?>