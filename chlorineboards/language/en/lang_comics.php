<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:           lang_comics.php[English]                  //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        25th March 2006                        //
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
// the language stuff for the comics module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Title' ] = 'Title';
$lang[ 'File' ] = 'File';
$lang[ 'Wrong_form' ] = 'The form was wrongly submitted';
$lang[ 'No_data' ] = 'Some entry fields in the upload form were left empty';
$lang[ 'No_mkdir' ] = 'The comics directory does not exist and could not be created';
$lang[ 'No_chmod' ] = 'The comics directory is not writable and coudl not be made writable';
$lang[ 'No_image' ] = 'The uploaded file is not an image';
$lang[ 'No_ul' ] = 'There was an error uploading the file';
$lang[ 'Done' ] = 'The comic was succesfully uploaded';
$lang[ 'No_comic' ] = 'The comic you have requested does not exist';
$lang[ 'First' ] = 'First';
$lang[ 'Prev' ] = 'Previous';
$lang[ 'Next' ] = 'Next';
$lang[ 'Last' ] = 'Last';
$lang[ 'Langs' ] = 'Upload for language';
$lang[ 'Next_update' ] = 'Next update';

?>