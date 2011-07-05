<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:           lang_ACP_news.php[English]                //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        13th March 2006                        //
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
// the news language stuff for the ACP
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'News';
$lang[ 'Side_add' ] = 'Add News';
$lang[ 'Side_edit' ] = 'Edit News';
$lang[ 'Side_settings' ] = 'Settings';
$lang[ 'Side_cat' ] = 'Manage categories';
$lang[ 'Side_rss' ] = 'RSS';

$lang[ 'Add_title' ] = 'Post new news';
$lang[ 'Add_explain' ] = 'From here you can easily post news entries for your website.';
$lang[ 'Add_title2' ] = 'News title';
$lang[ 'Add_language' ] = 'Language';
$lang[ 'Add_preview' ] = 'Preview';
$lang[ 'Add_news' ] = 'Body';
$lang[ 'Add_category' ] = 'Category';

$lang[ 'Wrong_form' ] = 'The form was wrongly submited';
$lang[ 'No_data' ] = 'Essential data entry fields were left empty';
$lang[ 'Added' ] = 'The news post was succesfully inserted into the database';
$lang[ 'Unknown_mode' ] = 'The mode you requested is unknown';

$lang[ 'Edit_title' ] = 'Edit news posts';
$lang[ 'Edit_explain' ] = 'From here you can easily edit news entries for your website.';
$lang[ 'Edit_title2' ] = 'News title';
$lang[ 'Edit_language' ] = 'Language';
$lang[ 'Edit_delete' ] = 'Delete';
$lang[ 'Edit_undelete' ] = 'Undelete';
$lang[ 'Edited' ] = 'The news post has been succesfully edited';
$lang[ 'Edit_preview' ] = 'Preview';
$lang[ 'Edit_news' ] = 'Body';
$lang[ 'Edit_category' ] = 'Category';

$lang[ 'Sett_title' ] = 'Settings';
$lang[ 'Sett_explain' ] = 'Here you can configure some of the settings associated with news posts';
$lang[ 'Sett_done' ] = 'Settings written to database.';
$lang[ 'news_front_num' ] = 'Number of news on front page';

$lang[ 'Cat_title' ] = 'Categories';
$lang[ 'Cat_explain' ] = 'Here you can add and/or manage the categories under which you wish to categorise your news posts';
$lang[ 'Cat_name' ] = 'Name';
$lang[ 'Cat_lang' ] = 'Language';
$lang[ 'Cat_delete' ] = 'Delete';
$lang[ 'Cat_done' ] = 'The category was succesfully added or modified';

$lang[ 'RSS_title' ] = 'RSS';
$lang[ 'RSS_explain' ] = 'Here you can manage the RSS channels for the news';
$lang[ 'RSS_title2' ] = 'Title';
$lang[ 'RSS_description' ] = 'Description';
$lang[ 'RSS_copyright' ] = 'Copyright';
$lang[ 'RSS_webmaster' ] = 'Webmaster';
$lang[ 'RSS_editor' ] = 'Editor';
$lang[ 'RSS_category' ] = 'Categories separated by commas';
$lang[ 'RSS_language' ] = 'Language';
$lang[ 'RSS_enable' ] = 'Enable RSS';

?>