<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:         lang_ACP_advance.php[English]               //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        02nd April 2006                        //
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

$lang[ 'Sidebar_title' ] = 'Advanced';
$lang[ 'Side_settings' ] = 'Settings';
$lang[ 'Side_browser' ] = 'File Browser';
$lang[ 'Side_clear' ] = 'Clear diskcache';
$lang[ 'Side_console' ] = 'ClB Console';
$lang[ 'Side_key' ] = 'Console Key';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised.';

$lang[ 'Sett_title' ] = 'Advanced Settings';
$lang[ 'Sett_explain' ] = 'Here you can edit all entries in the website\'s configuration table. Be careful though as some of these may result in some serious consequence';
$lang[ 'Sett_done' ] = 'The changes you made have been succesfully stored and should show results within the next reload of the page.';

$lang[ 'No_clear' ] = 'Failed clearing the on disk part of the cache';
$lang[ 'Cleared' ] = 'Succesfully cleared the cache';

$lang[ 'No_console' ] = 'The files necessary for ClB Console seem to not be present on the server. This can be rectified by uploading the console to it\'s intended position.';
$lang[ 'No_mykey' ] = 'The needed file mykey.dat appears to be missing from the console\'s configuration. Upload it accordingly to the instructions on console use';
$lang[ 'Console_title' ] = 'ClB Concole - ACP interface';
$lang[ 'Console_explain' ] = 'You can use the Chlorine Boards Console in the same way here as if accessing it directly at it\'s URL.';

$lang[ 'Key_ulfield' ] = 'File to upload';
$lang[ 'Key_remove' ] = 'Remove the key file';
$lang[ 'Key_download' ] = 'Download the key file';
$lang[ 'Key_upload' ] = 'Upload the key file';
$lang[ 'Key_title' ] = 'ClB Console key file administration';
$lang[ 'Key_explain' ] = 'As it is wise practice to remove the key file from your console whenever you are done working with it so as to prevent unwanted access you are able to upload, download or remove said file with the help of this ACP panel.';
$lang[ 'Key_err1' ] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
$lang[ 'Key_err2' ] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
$lang[ 'Key_err3' ] = 'The uploaded file was only partially uploaded.';
$lang[ 'Key_err4' ] = 'No file was uploaded.';
$lang[ 'Key_err6' ] = 'Missing a temporary folder.';
$lang[ 'Key_err7' ] = 'Failed to write file to disk.';
$lang[ 'Key_errx' ] = 'An unknown error has occured while uploading the file.';
$lang[ 'Key_uploaded' ] = 'The file has been succesfully uploaded, you may now use the console.';
$lang[ 'Key_noread' ] = 'The key file is unreadable!';
$lang[ 'Key_removed' ] = 'The key file has been succesfully removed.';
$lang[ 'Key_notremoved' ] = 'The key file has not been removed!';

?>