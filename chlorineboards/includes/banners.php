<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:                banners.php                          //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:      29th November 2005                       //
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
// this mostly just displays a list of banners :)
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
eval( Varloader::createclass( 'banners', $vars, $visible ) );
// end class creation

class Banners extends banners_def
{
	
	// constructor
	function Banners( $debug = FALSE )
	{
		global $Cl_root_path;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/banners_gui' . phpEx );
		$this->gui = new Banners_gui( );
	}
	
	// displays and that's that
	function display()
	{
		global $db, $errors, $basic_gui;
		
		// set pagination
		$basic_gui->set_level( 1, 'banners' );
		
		// fetch data from the db and pass it onto the gui :)
		$sql = "SELECT * FROM " . BANNERS_TABLE;
		if ( !$res = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not query for data', CRITICAL_ERROR );
		}
		$list = $db->sql_fetchrowset( $res );
		
		// do it
		$this->gui->display( $list );
	}
	
	
	//
	// End of Banners class
	//
}


?>