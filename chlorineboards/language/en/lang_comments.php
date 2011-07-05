<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:         lang_comments.php[English]              //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        31st December 2006                        //
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

$lang[ 'news_numcomments' ] = '<b><a href="%s">%d comments</a></b>';
$lang[ 'by' ] = 'by';
$lang[ 'reply_quick' ] = 'Quick reply';
$lang[ 'reply_this' ] = 'Reply to this';
$lang[ 'reply' ] = 'Comment';
$lang[ 'nickname' ] = 'Nickname';
$lang[ 'email' ] = 'E-Mail';
$lang[ 'website' ] = 'Website';
$lang[ 'title' ] = 'Title';

$lang[ 'wrong_form' ] = 'The form was wrongly submitted';
$lang[ 'empty' ] = 'Some mandatory fields have been left empty.';
$lang[ 'captcha' ] = 'The entered CAPTCHA is incorrect';
$lang[ 'email2' ] = 'The E-Mail you have entered is incorrect';
$lang[ 'guest' ] = 'You must be logged in to comment';

$lang[ 'replied' ] = 'The reply was successfully posted.<br /><a href="%s">Back to your comment</a>';

?>