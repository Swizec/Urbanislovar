<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:         lang_UCP_uProfile.php[English]               //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        15th Decemberl 2006                        //
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
// the advance language stuff for the UCP
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'User Profile';
$lang[ 'Sidebar_base' ] = 'Basic profile';
$lang[ 'Sidebar_signature' ] = 'Signature';
$lang[ 'Sidebar_extra' ] = 'Extra stuff';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised.';
$lang[ 'Wrong_form' ] = 'The form was wrongly submitted';

$lang[ 'base_title' ] = 'Basic User Profile';
$lang[ 'base_explain' ] = 'Here you can change some things pertaining to your basic profile';

$lang[ 'base_avy' ] = 'Avatar';
$lang[ 'base_avyexplain' ] = 'An avatar is a small image that appears next to user\'s nicknames when they post messages or do other things.';
$lang[ 'base_avyimage' ] = 'Your avatar';
$lang[ 'base_avyul' ] = 'Upload new';
$lang[ 'base_avyurl' ] = 'Remote URL';
$lang[ 'base_avyulfail' ] = 'The avatar was not uploaded correctly or there was an error handling the upload';
$lang[ 'base_avysize' ] = 'The image you are trying to use is breaching one of the maximum size constraints';
$lang[ 'base_avyconstraint' ] = 'The image must not have a width greater than %d px nor height than %d px. It\'s size must not exceed %d kB';
$lang[ 'base_avydisable' ] = 'The administrator of this website has chosen to disable avatars.';
$lang[ 'base_avyremove' ] = 'Remove avatar';

$lang[ 'base_info' ] = 'Basic information';
$lang[ 'base_infoexplain' ] = 'This sections deals with some basic user information that you might want to make public.';
$lang[ 'base_infolocation' ] = 'Location';
$lang[ 'base_infobirth' ] = 'Birth date';
$lang[ 'base_infoshowage' ] = 'Age is public';

$lang[ 'base_contact' ] = 'Contact';
$lang[ 'base_contactexplain' ] = 'Here you can edit some of your contact information to give others some idea about contacting you';
$lang[ 'base_contactemail' ] = 'Public E-Mail';
$lang[ 'base_contactsites' ] = 'Home Page, or two';
$lang[ 'base_contactim' ] = 'Instant Messaging';

$lang[ 'base_done' ] = 'The changes have been succesfully saved';
$lang[ 'base_public' ] = 'View my public profile';

$lang[ 'signature_title' ] = 'Signature';
$lang[ 'signature_explain' ] = 'Enter some text you would like to be shown under some of your actions. Where the signature is shown is decided by the used module and cannot be fully predicted.';
$lang[ 'signature_done' ] = 'The signature has been changed';

$lang[ 'month1' ] = 'January';
$lang[ 'month2' ] = 'February';
$lang[ 'month3' ] = 'March';
$lang[ 'month4' ] = 'April';
$lang[ 'month5' ] = 'May';
$lang[ 'month6' ] = 'June';
$lang[ 'month7' ] = 'July';
$lang[ 'month8' ] = 'August';
$lang[ 'month9' ] = 'September';
$lang[ 'month10' ] = 'October';
$lang[ 'month11' ] = 'November';
$lang[ 'month12' ] = 'December';

$lang[ 'extra_title' ] = 'Extra stuff';
$lang[ 'extra_explain' ] = 'Here you can configure the profile options your administrator has manually added to user profiles. In most cases these will only appear on your profile page.';
$lang[ 'extra_none' ] = 'The administrator hasn\'t set any extra fields';
$lang[ 'extra_done' ] = 'The changes have been succesfully saved';

?>