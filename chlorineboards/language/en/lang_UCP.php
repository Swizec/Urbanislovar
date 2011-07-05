<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:             lang_UCP.php[English]                   //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        14th June 2006                        //
//     version:               0.1.1                              //
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
// the language stuff for the ACP
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'No_admin' ] = 'This area of the website is restricted to users only and you do not seem to be one. Please login or create an account.';

?>