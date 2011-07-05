<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:          lang_banners.php[English]                  //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       29th November 2005                      //
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
// the language stuff for the banners module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}


$lang[ 'title' ] = 'ClB Banners';
$lang[ 'text' ] = 'Here you can find some Chlorine Boards banners witg which you can help spread the word about this website solution.';
$lang[ 'output' ] = 'Result';
$lang[ 'html' ] = 'HTML';
$lang[ 'bbcode' ] = 'BBCode';
$lang[ 'wikicode' ] = 'Wiki Code';


?>