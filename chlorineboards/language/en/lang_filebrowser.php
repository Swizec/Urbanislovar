<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:         lang_filebrowser.php[English]              //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        04th April 2006                        //
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
// the language stuff for the file browser
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Browser_title' ] = 'File Browser';
$lang[ 'Browser_explain' ] = 'Here you can access all of the files that are a part of your Chlorine Boards installation. To perform any action just select an entry and then click on the wished action.';

$lang[ 'Loading' ] = 'Loading...';
$lang[ 'Edit' ] = 'Edit';
$lang[ 'Delete' ] = 'Delete';
$lang[ 'Download' ] = 'Download';
$lang[ 'View' ] = 'View';
$lang[ 'Rename' ] = 'Rename';
$lang[ 'Upload' ] = 'Upload';

$lang[ 'ACP' ] = 'ACP';

$lang[ 'Upload_file' ] = 'File to upload';
$lang[ 'Upload_error' ] = 'Error uploading file';
$lang[ 'Upload_good' ] = 'The file has been uploaded';

$lang[ 'Written' ] = 'The changes have been succesfully written to the file';
$lang[ 'Deleted' ] = 'The selected file has been succesfully removed';

$lang[ 'No_perm'  ] = 'You do not have permission to access this part of the website.';
$lang[ 'No_editable' ] = 'The file is not readable, writable or is not a text file';
$lang[ 'No_write' ] = 'There were errors trying to write the file to disk';
$lang[ 'No_write2' ] = 'The parent directory is not writable';
$lang[ 'No_delete' ] = 'There was a problem deleting the selected file.';
$lang[ 'No_download' ] = 'The file is not readable';
$lang[ 'No_view' ] = 'The selected object is either not readable or not a file';
$lang[ 'No_view2' ] = 'The file is not a text file or an image';
$lang[ 'No_rename' ] = 'The entry is not writable';
$lang[ 'No_rename2' ] = 'There was an error trying to rename the entry';

$lang[ 'Msg_delete' ] = 'Are you sure you wish to completely remove %s?<br /><input type="submit" value="Yes" onclick="%s">&nbsp;<input type="submit" value="No" onclick="%s">';

$lang[ 'Download_click' ] = 'Click here to commence the file download';
$lang[ 'Download_err' ] = 'Errors have occured while trying to create an archive of the requested directory';

$lang[ 'Renamed' ] = 'The entry has been succesfully renamed';

?>