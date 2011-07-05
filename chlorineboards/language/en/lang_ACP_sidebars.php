<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:        lang_ACP_sidebars.php[English]               //
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
// the Sidebars language stuff for the ACP
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'Sidebars';
$lang[ 'Side_add' ] = 'Add Sidebar';
$lang[ 'Side_edit' ] = 'Edit Sidebar';

$lang[ 'Add_title' ] = 'Add a sidebar';
$lang[ 'Add_explain' ] = 'This panel will allow you to add a constant sidebar to your website. Please keep in mind that this will appear as very narrow.';
$lang[ 'Add_title2' ] = 'Sidebar name';
$lang[ 'Left' ] = 'Left';
$lang[ 'Right' ] = 'Right';
$lang[ 'Add_where' ] = 'Sidebar side';
$lang[ 'Add_language' ] = 'Language';
$lang[ 'Add_hide' ] = 'Hidden by default';
$lang[ 'Add_auth' ] = 'Auth level to see';
$lang[ 'Add_order' ] = 'Order number';

$lang[ 'Edit_title' ] = 'Edit a sidebar';
$lang[ 'Edit_explain' ] = 'This panel will allow you to edit a constant sidebar of your website. Please keep in mind that this will appear as very narrow.';
$lang[ 'Edit_title2' ] = 'Sidebar name';
$lang[ 'Edit_side' ] = 'Sidebar side';
$lang[ 'Edit_language' ] = 'Language';
$lang[ 'Edit_select' ] = 'Select a sidebar';
$lang[ 'Edit_hide' ] = 'Hidden by default';
$lang[ 'Edit_auth' ] = 'Auth level to see';
$lang[ 'Edit_remove' ] = 'Remove sidebar';
$lang[ 'Edit_order' ] = 'Order number';

$lang[ 'Wrong_form' ] = 'Wrongly submitted form';
$lang[ 'Wrong_mode' ] = 'Unknown mode';
$lang[ 'No_data' ] = 'Essential fields have been left empty';
$lang[ 'No_writable' ] = 'The file cannot be written';
$lang[ 'No_write' ] = 'Failed writing file';
$lang[ 'Added' ] = 'The static sidebar has been succesfully added. It will show up everywhere except on the ACP';
$lang[ 'No_read' ] = 'The file is not readable';
$lang[ 'Edited' ] = 'The static sidebar has been succesfully edited. You may observe your changes by checking the public part of the website.';

$lang[ 'Guest' ] = 'Guest';
$lang[ 'Inactive' ] = 'Inactive';
$lang[ 'Admin' ] = 'Admin';
$lang[ 'Super_mod' ] = 'Super Mod';
$lang[ 'Mod' ] = 'Mod';
$lang[ 'User' ] = 'User';

$lang[ 'Converted' ] = 'Sidebar storage converted to the new system';
$lang[ 'Convert' ] = 'Convert to new system';

?>