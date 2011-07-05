<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:         lang_flexibase.php[English]              //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       31st January 2007                      //
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
// the language stuff for the flexibase
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised';
$lang[ 'Empty' ] = 'There are currently no items to show here';
$lang[ 'Back' ] = '<-- Back';
$lang[ 'Perpage' ] = 'items per page';
$lang[ 'Page' ] = 'Page';
$lang[ 'Search' ] = 'Search';
$lang[ 'General' ] = 'General';

?>