<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:         lang_ACP_uProfile.php[English]               //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        11th Decemberl 2006                        //
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
// the advance language stuff for the ACP
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'User Profile';
$lang[ 'Sidebar_base' ] = 'Basic profile';
$lang[ 'Sidebar_extra' ] = 'Extra stuff';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised.';
$lang[ 'Wrong_form' ] = 'The form was wrongly submitted';

$lang[ 'base_title' ] = 'Basic User Profile';
$lang[ 'base_explain' ] = 'Here you can configure some settings about the basic user profile.';

$lang[ 'base_avy' ] = 'Avatar';
$lang[ 'base_avyexplain' ] = 'An avatar is a small image that appears next to user\'s nicknames when they post messages or do other things.';
$lang[ 'base_avyuse' ] = 'Use avatars';
$lang[ 'base_avyul' ] = 'Allow avatar uploading';
$lang[ 'base_avyrem' ] = 'Allow remote avatars';
$lang[ 'base_avywidth' ] = 'Max avatar width';
$lang[ 'base_avyheight' ] = 'Max avatar height';
$lang[ 'base_avysize' ] = 'Max avatar size';
$lang[ 'base_avydeful' ] = 'Upload default avatar';
$lang[ 'base_avydefault' ] = 'Default avatar';
$lang[ 'base_avyulfail' ] = 'The avatar was not uploaded correctly or there was an error handling the upload';

$lang[ 'base_info' ] = 'Information';
$lang[ 'base_infoexplain' ] = 'Here you can set some options about basic user information.';
$lang[ 'base_infolocation' ] = 'Show user location';
$lang[ 'base_infobirth' ] = 'Show user age';

$lang[ 'base_contact' ] = 'Contact';
$lang[ 'base_contactexplain' ] = 'These are some simple configuration options about contact information within user profiles';
$lang[ 'base_contactemail' ] = 'Use public emails';
$lang[ 'base_contactim' ] = 'List of IM addresses';
$lang[ 'base_contactsites' ] = 'Number of websites';

$lang[ 'base_pixel' ] = 'px';
$lang[ 'base_kb' ] = 'kB';
$lang[ 'base_done' ] = 'The settings have been succesfully changed';

$lang[ 'extra_title' ] = 'Extra information';
$lang[ 'extra_explain' ] = 'Here you can add the information you would like your users to share. You can define the type and publicity of a certain field.';
$lang[ 'extra_existant' ] = 'Existant fields';
$lang[ 'extra_none' ] = 'There have been no fields added yet. Use the form below to add them.';
$lang[ 'extra_add' ] = 'Add fields';
$lang[ 'extra_addexplain' ] = 'Using this form you can add a field to your users\' profiles';
$lang[ 'extra_addname' ] = 'Name';
$lang[ 'extra_addtype' ] = 'Type';
$lang[ 'extra_addpublic' ] = 'Public';
$lang[ 'extra_adddel' ] = 'Delete';
$lang[ 'extra_type_mini_text' ] = '10 characters';
$lang[ 'extra_type_short_text' ] = '50 characters';
$lang[ 'extra_type_text' ] = '255 characters';
$lang[ 'extra_type_long_text' ] = 'Long text';
$lang[ 'extra_type_number' ] = 'Whole number';
$lang[ 'extra_type_float' ] = 'Rational number';
$lang[ 'extra_done' ] = 'The changes have been succesfully saved';

?>