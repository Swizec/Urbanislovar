<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:         lang_ACP_flexibase.php[English]               //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        26th January 2007                        //
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

$lang[ 'Sidebar_title' ] = 'FlexiBase';
$lang[ 'Sidebar_manage' ] = 'Manage databases';
$lang[ 'Sidebar_add' ] = 'Manage items';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised.';
$lang[ 'Wrong_form' ] = 'The form was wrongly submitted';

$lang[ 'Add_new' ] = 'Add new';

$lang[ 'Manage_title' ] = 'Manage Databases';
$lang[ 'Manage_explain' ] = 'With this panel you can manage your differeng flexibase databases. You can add new ones or simply change the object already existant ones use';
$lang[ 'Manage_select' ] = 'Select';
$lang[ 'Manage_database' ] = 'Database';
$lang[ 'Manage_name' ] = 'Name';
$lang[ 'Manage_language' ] = 'Language';
$lang[ 'Manage_description' ] = 'Description';
$lang[ 'Manage_fields' ] = 'Fields';
$lang[ 'Manage_delete' ] = 'Remove this flexibase';
$lang[ 'Manage_delwarn' ] = 'Removing a flexibase loses all its data';

$lang[ 'Field_new' ] = 'Add Field';
$lang[ 'Field_name' ] = 'Choose a name';
$lang[ 'Field_type' ] = 'Choose a type';
$lang[ 'Field_del' ] = 'Delete';
$lang[ 'Field_delwarn' ] = 'Deleting a field loses all its data!';
$lang[ 'Field_noadd' ] = 'A field with the name of %s already exists, please choose a different name for the new field.';

$lang[ 'Type_varchar' ] = 'String';
$lang[ 'Type_int' ] = 'Number';
$lang[ 'Type_float' ] = 'Rational number';
$lang[ 'Type_text' ] = 'Text';
$lang[ 'Type_blob' ] = 'Binary data';

$lang[ 'Finished' ] = 'The changes have been successfully executed';

$lang[ 'Item_title' ] = 'Manage items';
$lang[ 'Item_explain' ] = 'With this panel you can administer the items within your databases, like deleting unwanted ones or adding new ones.';
$lang[ 'Item_choose' ] = 'Select a database to begin managing its items.';
$lang[ 'Item_none' ] = 'There are no items currently within this flexibase';
$lang[ 'Item_add' ] = 'Add item';
$lang[ 'Item_inside' ] = 'Existant items';
$lang[ 'Item_show' ] = 'Show';
$lang[ 'Item_showy' ] = 'item(s) starting from #';
$lang[ 'Item_filewrong' ] = 'There was an error uploading the file for %s';
$lang[ 'Item_edit' ] = 'Edit';
$lang[ 'Item_delete' ] = 'Delete';
$lang[ 'Item_edititem' ] = 'Edit item';

$lang[ 'Field_wrong' ] = 'Some of the fields youa re trying to set are not set properly.';

?>