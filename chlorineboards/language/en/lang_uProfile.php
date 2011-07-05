<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:         lang_uProfile.php[English]              //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        17th December 2006                        //
//     version:               0.1.0                             //
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
// the language stuff for the uProfile
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'nouid' ] = 'No user was specified';
$lang[ 'avvy' ] = 'User avatar';
$lang[ 'location' ] = 'Location';
$lang[ 'age' ] = 'Age';
$lang[ 'email' ] = 'E-Mail';
$lang[ 'site' ] = 'Website(s)';
$lang[ 'signature' ] = 'Signature';
$lang[ 'extra' ] = 'Additional info';
$lang[ 'noextra' ] = 'The administrator has chosen not to ask for any additional information';

?>