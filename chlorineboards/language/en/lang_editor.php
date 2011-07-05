<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:           lang_editor.php[English]                  //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        13th March 2005                        //
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
// the language stuff for the banners module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}


$lang[ 'Default_text' ] = '';

$lang[ 'Bold' ] = 'Bold';
$lang[ 'Italic' ] = 'Italic';
$lang[ 'Underline' ] = 'Underline';
$lang[ 'Left' ] = 'Justify left';
$lang[ 'Center' ] = 'Justify center';
$lang[ 'Right' ] = 'Justify right';
$lang[ 'Fonts' ] = 'Fonts';
$lang[ 'Size' ] = 'Size';


?>