<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:            lang_basic.php[English]                  //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        24th June 2005                         //
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
// the general language stuff for the board
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Bottom_stats' ] = 'The page was generated in %s seconds using %d sql queries.';
$lang[ 'JSnotice' ] = 'You seem to have JavaScript disabled. This will greatly hinder your ability to use/see this website properly';

$lang[ 'General_error' ] = 'General Error';
$lang[ 'Critical_error' ] = 'Critical Error';
$lang[ 'on_line' ] = 'on line';
$lang[ 'Message' ] = 'Message';

$lang[ 'Class' ] = 'Class';
$lang[ 'Function' ] = 'Function';
$lang[ 'Line' ] = 'Line';

$lang[ 'Logo_title' ] = 'Chlorine Boards';

$lang[ 'Back' ] = 'Go Back';
$lang[ 'Reload' ] = 'Reload';

$lang[ 'Reset' ] = 'Reset';
$lang[ 'Submit' ] = 'Submit';
$lang[ 'Yes' ] = 'Yes';
$lang[ 'No' ] = 'No';
$lang[ 'Close' ] = 'Close';

$lang[ 'err_wrongpath' ] = 'The pagination path \'%s\' does not exist within the paths.cfg file.';

$lang[ 'Welcome' ] = 'Welcome';
$lang[ 'online_since' ] = 'You have been online since';
$lang[ 'last_activity' ] = 'You were last active on';
$lang[ 'Guest' ] = 'Guest';

$lang[ 'Site' ] = 'Site';
$lang[ 'Home' ] = 'Home';
$lang[ 'Banners' ] = 'Banners';

$lang[ 'Distribution' ] = 'Distribution';
$lang[ 'Download' ] = 'Download';
$lang[ 'List' ] = 'Modules list';

$lang[ 'Documentation' ] = 'Documentation';
$lang[ 'Administrator' ] = 'Administrator';
$lang[ 'User' ] = 'User';
$lang[ 'Developer' ] = 'Developer';
$lang[ 'Add' ] = 'Add';

$lang[ 'Bugs' ] = 'Bugs';
$lang[ 'Report bug' ] = 'Report bug';
$lang[ 'Read bugs' ] = 'Read bugs';
$lang[ 'Request feature' ] = 'Request feature';
$lang[ 'Request module' ] = 'Request module';

$lang[ 'Login' ] = 'Login/Register';
$lang[ 'Password retrieve' ] = 'Retrieving password';
$lang[ 'Logout' ] = 'Logout';
$lang[ 'Registration' ] = 'Registration';

$lang[ 'Need_login' ] = 'You need to be logged in in order to perform this action. <br/><a href="%s">Go to login</a>';

$lang[ 'Forums' ] = 'Forums';

$lang[ 'Comics' ] = 'Comics';

$lang[ 'Pages' ] = 'Pages';
$lang[ 'Unknown_page' ] = 'The page you requested is unavailable at this time.';
$lang[ 'Page_auth' ] = 'You do not seem to have sufficient permission to view this page';

$lang[ 'News' ] = 'News';

$lang[ 'FlexiBase' ] = 'FlexiBase';

?>