 <?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:       lang_documentation.php[English]               //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       04th November 2005                      //
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
// the language stuff for the documentation module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}


// index
$lang[ 'Inx_title' ] = 'ClB Documentation';
$lang[ 'Inx_text' ] = 'This is the place where all that helpful Chlorine Boards documentation is kept. With time, as the database grows, every answer you might have should be answerable by checking an appropriate section here. The best thing about it is that if you find a piece of information which you think is useful and would help others you are very much welcome to add an article to the appropriate section.<br /><br />On the right you can find a list with links to each section.';
$lang[ 'Inx_admin' ] = 'The documentation for website administrators is located here. Articles contain information about installing, configuring and maintaining a Chlorine Boards website.';
$lang[ 'Inx_user' ] = 'Users can come here to search for answers about using websites based on Chlorine Boards. Things like using the user control panel are explained here.';
$lang[ 'Inx_dev' ] = 'Developers again need documentation of their own. If you intend on making a module you will need to know all the secrets and little tricks about making one. Inside are helpful tips, coding practices and publishing methods..';

// documentation
$lang[ 'Add' ] = 'Add article';
$lang[ 'No_doc' ] = 'This section of the documentation currently contains no articles. Add an article by visiting the "Add article" link on the left';

// add
$lang[ 'Author' ] = 'Author';
$lang[ 'Title' ] = 'Title';
$lang[ 'Text' ] = 'Text';
$lang[ 'Err_missing' ] = 'Some fields were left empty. Press back on your browser and fix it.';
$lang[ 'Err_noinsert' ] = 'An error occured while trying to insert your article.';
$lang[ 'Inserted' ] = 'Thank you for your submission.<br /><br /><a href="%s">Return to documentation</a>';
$lang[ 'Greet' ] = 'We here at Chlorine Boards commend yor endeavour to help the community. Use this form to add a helpful, or as most as possible, article to the documentation. HTML is allowed, but if you make something naughty you will be hunted down like a dog and a host of ninja pyrates will come to haunt you.';

// edit
$lang[ 'edit_erruser' ] = 'You are trying to edit an article you have not created<br/><a href="%s">Return to documentation</a>';


?>