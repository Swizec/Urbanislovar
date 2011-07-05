<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:            lang_plug_captchas.php[English]                 //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       29th december 2006                      //
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
// the forums language stuff
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'logic' ] = array( 
		'If %s is %s, what is %s?',
		'%s can run, who can run?',
		'The last printed word. %s',
	);

?>