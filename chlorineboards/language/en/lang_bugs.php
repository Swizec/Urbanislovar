 <?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:            lang_bugs.php[English]                   //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       25th November 2005                      //
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
// the language stuff for the bugs module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'err_nomode' ] = 'There was no mode specified or it was unknown.';

// index
$lang[ 'inx_title' ] = 'Bug Tracker';
$lang[ 'inx_text' ] = 'As we wish to bring everyone as best working software as possible this bug tracker has been set up. Here you can report bugs that you find in the code and you can help solve bugs just as well. A nice feature that allows you to request modules or specific features for the kernel can also be found here.<br /><br />Please only report bugs in the core modules, we are not responsible for bugs found in modules other than the core ones. Report those bugs to the modules author.<br /><br />Thank You.';
$lang[ 'inx_report' ] = 'Follow this link to report a bug with any of the core code or the console';
$lang[ 'inx_read' ] = 'Follow this link to read a list of the reported bugs and reply to them';
$lang[ 'inx_module' ] = 'Follow this link to request for a module to be made';
$lang[ 'inx_feature' ] = 'Follow this link to request a feature to be added to the core code';

// report
$lang[ 'rep_title' ] = 'Bug Reporting';
$lang[ 'rep_text' ] = 'Thank you for reporting a bug that you have found. We hope you will not find any more. You should make sure the bug lies in either the core code or the console and nothing else. You should try to explain the bug and the situation it occured in as best as you can so we can solve it as soon as possible. Please do not use this to ask for help, it will only slow down the process of brushing out bugs.';
$lang[ 'rep_author' ] = 'Author';
$lang[ 'rep_btitle' ] = 'Title';
$lang[ 'rep_desc' ] = 'Description';
$lang[ 'rep_tauthor' ] = 'Insert here your nickname or what ever you wish to be recognised by';
$lang[ 'rep_ttitle' ] = 'Try to input a title that will clearly state what the bug is about in a short manner. You have 200 words.';
$lang[ 'rep_tdesc' ] = 'Explain the problem as clearly as you can here. Do not forget to state what kind of system the bug occured on.';

$lang[ 'rep_errsubm' ] = 'Wrongly submitted form<br/><a href="%s">Return to reporting</a>';
$lang[ 'rep_errempty' ] = 'You submited a partially filled form. All entries must be set.<br/><a href="%s">Return to reporting</a>';
$lang[ 'rep_errquery' ] = 'There was a problem inserting your bug report into the database.<br/><a href="%s">Return to reporting</a>';
$lang[ 'rep_done' ] = 'Thank you for your submission. We will try our best to find a fix for the bug you have found<br/><a href="%s">Return to bugs</a>';

// read
$lang[ 'read_title' ] = 'Reported Bugs';
$lang[ 'read_text' ] = 'Here you can read and help solve reported bugs. All help is appreciated of course.';
$lang[ 'read_bugby' ] = 'Bug sumbited by <b>%s</b>.';
$lang[ 'read_author' ] = 'Poster';
$lang[ 'read_reply' ] = 'Reply';
$lang[ 'read_replyhead' ] = 'Reply from <i>%s</i> on %s';
$lang[ 'read_replied' ] = 'You have succesfully replied to this bug report. We thank you for your contribution.<br/><a href="%s">Return to bugs</a>';

$lang[ 'read_errquery' ] = 'Could not query bugs info from database';

// requests
$lang[ 'req_ftitle' ] = 'Request feature';
$lang[ 'req_mtitle' ] = 'Request module';
$lang[ 'req_ftext' ] = 'Here you can request a feature that you would like to be added to either the core code or the Console. We will try our best to provide you with a solution as soon as possible.';
$lang[ 'req_mtext' ] = 'Here you can request a module, or rather post an idea for a module. Someone will try to fulfil your request but there can hardly be any promises as there are no "official" module authors.';
$lang[ 'req_author' ] = 'Requester';
$lang[ 'req_desc' ] = 'Description';
$lang[ 'req_tauthor' ] = 'Something to identify you by. 200 characters allowed';
$lang[ 'req_tdesc' ] = 'Describe what you want here in as much detail as possible';
$lang[ 'req_head' ] = 'Requested by %s on %s --- %s';
$lang[ 'req_solved' ] = 'Solved';
$lang[ 'req_unsolved' ] = 'Unsolved';

$lang[ 'req_errform' ] = 'Wrongly submitted form<br/><a href="%s">Return to bugs</a>';
$lang[ 'req_errempty' ] = 'You have to fill out both the poster and the description field.<br/><a href="%s">Return to bugs</a>';

$lang[ 'req_done' ] = 'Thank you for your submission.<br />Your request has been succesfully submitted. We will see what we can do about it.<br/><a href="%s">Return to bugs</a>';

?>