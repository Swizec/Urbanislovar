<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:        lang_ACP_pages.php[English]                  //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        30th March 2006                        //
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
// the Pages language stuff for the ACP
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'Pages';
$lang[ 'Side_add' ] = 'Add Page';
$lang[ 'Side_edit' ] = 'Edit Page';

$lang[ 'Add_title' ] = 'Add a page';
$lang[ 'Add_explain' ] = 'This panel will allow you to add a constant page to your website';
$lang[ 'Add_title' ] = 'Page name';
$lang[ 'Left' ] = 'Left';
$lang[ 'Right' ] = 'Right';
$lang[ 'Add_where' ] = 'Page side';
$lang[ 'Add_language' ] = 'Language';
$lang[ 'Add_hide' ] = 'Hidden by default';
$lang[ 'Add_auth' ] = 'Auth level to see';

$lang[ 'Edit_title' ] = 'Edit a page';
$lang[ 'Edit_explain' ] = 'This panel will allow you to edit a constant page of your website';
$lang[ 'Edit_title2' ] = 'Page name';
$lang[ 'Edit_side' ] = 'Page side';
$lang[ 'Edit_language' ] = 'Language';
$lang[ 'Edit_select' ] = 'Select a page';
$lang[ 'Edit_hide' ] = 'Hidden by default';
$lang[ 'Edit_auth' ] = 'Auth level to see';
$lang[ 'Edit_remove' ] = 'Remove page';

$lang[ 'Wrong_form' ] = 'Wrongly submitted form';
$lang[ 'Wrong_mode' ] = 'Unknown mode';
$lang[ 'No_data' ] = 'Essential fields have been left empty';
$lang[ 'No_writable' ] = 'The file cannot be written';
$lang[ 'No_write' ] = 'Failed writing file';
$lang[ 'Added' ] = 'The static page has been succesfully added.';
$lang[ 'No_read' ] = 'The file is not readable';
$lang[ 'Edited' ] = 'The static page has been succesfully edited. You may observe your changes by checking the page.';

$lang[ 'Guest' ] = 'Guest';
$lang[ 'Inactive' ] = 'Inactive';
$lang[ 'Admin' ] = 'Admin';
$lang[ 'Super_mod' ] = 'Super Mod';
$lang[ 'Mod' ] = 'Mod';
$lang[ 'User' ] = 'User';

$lang[ 'Converted' ] = 'Page storage converted to the new system';
$lang[ 'Convert' ] = 'Convert to new system';

?>