<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:               ACP_news.php                          //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       30th March 2006                         //
//     version:               0.3.1                              //
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
// this is an ACP panel for news thingy
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// debug :: debug flag

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'ACP_news', $vars, $visible ) );
// end class creation

class ACP_news extends ACP_news_def
{
	
	// constructor
	function ACP_news( $debug = FALSE )
	{
		global $Cl_root_path, $basic_gui, $lang_loader, $security;
		
		$this->debug = $debug;
	
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_news' );
		
		// make the two urls
		$url1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=add' );
		$url2 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=edit' );
		$url3 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=settings' );
		$url4 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=cat' );
		$url5 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=rss' );
		
		// add to sidebar
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $url1 . '">' . $this->lang[ 'Side_add' ] . '</a><br /><a href="' . $url2 . '">' . $this->lang[ 'Side_edit' ] . '</a><br /><a href="' . $url3 . '">' . $this->lang[ 'Side_settings' ] . '</a><br /><a href="' . $url4 . '">' . $this->lang[ 'Side_cat' ] . '</a><br /><a href="' . $url5 . '">' . $this->lang[ 'Side_rss' ] . '</a></span>' );
	}
	
	function show_panel()
	{
		global $template, $errors;
		
		// get the mode
		$s = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : 'add';
		
		// fire the template
		$template->assign_files( array(
			'ACP_news' => 'ACP/news' . tplEx
		) );
		
		// decide upon the mode
		switch ( $s )
		{
			case 'add':
				$this->add();
				break;
			case 'add_real':
				$this->add_real();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'edit_real':
				$this->edit_real();
				break;
			case 'settings':
				$this->settings();
				break;
			case 'settings_real':
				$this->settings_real();
				break;
			case 'cat':
				$this->categories();
				break;
			case 'rss':
				$this->rss();
				break;
			default:
				$errors->report_error( $this->lang[ 'Unknown_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	
	function add()
	{
		global $template, $mod_loader, $security, $lang_loader, $board_config, $db;
		
		// construct the language selection list
		$langl = $lang_loader->get_langlist();
		$langs = '<select name="language">';
		for ( $i = 0; $i < count( $langl ); $langs .= '<option>' . $langl[ $i ] . '</option>',$i++ );
		$langs .= '</select>';
		
		// construct the category selection
		$sql = "SELECT * FROM " . NEWSCAT_TABLE . " ORDER BY cat_lang ASC, cat_name ASC";
		$result =$db->sql_query( $sql );
		
		$categories = '<select onchange="document.getElementById( \'category\' ).value =document.getElementById( \'category\' ).value + this.value "><option></option>';
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$categories .= '<option value="' . $row[ 'cat_name' ] . ',">' . $row[ 'cat_lang' ] . ' :: ' . $row[ 'cat_name' ] . '</option>';
		}
		$categories .= '</select>';
		
		// get editor first
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => FALSE ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$news = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		$mod_loader->port_vars( array( 'name' => 'editor2', 'quickpost' => FALSE ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$preview = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// assign vars
		$template->assign_block_vars( 'form', '', array(
			'L_TITLE' => $this->lang[ 'Add_title' ],
			'L_EXPLAIN' =>$this->lang[ 'Add_explain' ],
			'L_TITLE2' => $this->lang[ 'Add_title2' ],
			'L_LANGUAGE' => $this->lang[ 'Add_language' ],
			'L_PREVIEW' => $this->lang[ 'Add_preview' ],
			'L_NEWS' => $this->lang[ 'Add_news' ],
			'L_CATEGORY' => $this->lang[ 'Add_category' ],
			
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=add_real' ),
			'S_EDITOR' => $news[ 'editor_HTML' ],
			'S_EDITOR2' => $preview[ 'editor_HTML' ],
			'S_MODE' => 'create',
			'S_LANGS' => $langs,
			'S_CATEGORIES' => $categories,
			
			'SHOWPREVIEW' => ( $board_config[ 'news_preview' ] ) ? 1 : 0,
		) );
		
		$template->assign_switch( 'form', TRUE );
	}
	
	function add_real()
	{
		global $errors, $db, $cache, $userdata, $plug_RSS, $board_config, $basic_gui;
		
		// basic check
		if ( !isset( $_POST[ 'submit_news' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// get data
		$title = ( isset( $_POST[ 'title' ] ) ) ? strval( $_POST[ 'title' ] ) : '';
		$text = str_replace( '&nbsp;', ' ', ( isset( $_POST[ 'editor1' ] ) ) ? strval( $_POST[ 'editor1' ] ) : '' );
		$text2 = str_replace( '&nbsp;', ' ', ( isset( $_POST[ 'editor2' ] ) ) ? strval( $_POST[ 'editor2' ] ) : '' );
		$language = ( isset( $_POST[ 'language' ] ) ) ? strval( $_POST[ 'language' ] ) : '';
		$category = ( isset( $_POST[ 'category' ] ) ) ? $_POST[ 'category' ] : '';
		$category = ( $category{strlen( $category )-1} == ',' ) ? substr( $category, 0, -1 ) : $category;
		$time = time();
		$uid = $userdata[ 'user_id' ];
		
		// transform the category list to ids
		$category = explode( ',', $category );
		$category = "'" . implode( "','", $category ) . "'";
		$sql = "SELECT cat_id FROM " . NEWSCAT_TABLE . " WHERE cat_name IN ($category) AND cat_lang='$language'";
		$result = $db->sql_query( $sql );
		$categories = '';
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$categories .= $row[ 'cat_id' ] . ',';
		}
		$category = substr( $categories, 0, -1 );
		
		// more checks
		if ( empty( $title ) || empty( $text ) )
		{
			$errors->report_error( $this->lang[ 'No_data' ], GENERAL_ERROR );
		}
		
		// insert it
		$sql = "INSERT INTO " . NEWS_TABLE . " ( news_time, news_poster, news_lang, news_title, news_text, news_preview, news_category ) VALUES ( '$time', '$uid', '$language', '$title', '$text', '$text2', '$category' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Error inserting into db', CRITICAL_ERROR );
		}
		$newsid = $db->sql_nextid();
		
		// update cache, sadly with another query :(
		$news = $cache->pull( 'news_posts' );
		$id = $db->sql_nextid();
		$sql = "SELECT n.*, u.username FROM " . NEWS_TABLE . " n LEFT JOIN " . USERS_TABLE . " u ON n.news_poster=u.user_id WHERE news_id='$id' LIMIT 1";
		if ( !$res = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not fetch from database', CRITICAL_ERROR );
		}
		$row = $db->sql_fetchrow( $res );
		$news[ $row[ 'news_id' ] ] = $row;
		$cache->push( 'news_posts', $news, TRUE );
		// the per-day cache gets killed
		$cache->delete( 'news_posts:' . date( 'd-n-Y', $time ) . ':' . $language );
		
		// calendar cache
		$calendar = $cache->pull( 'news_calendar' );	
		$year = date( 'Y', $time );
		$month = date( 'n', $time );
		unset( $calendar[ $year ][ $month ] );
		$cache->push( 'news_calendar', $calendar, TRUE );
		
		// the RSS
		if ( $board_config[ 'news_enableRSS' ] )
		{
			$channel = 'news-' . $language;
			$link = $basic_gui->get_URL() . '/index.php/' . MODE_URL . '=news/id=' . $newsid . '/lang=' . $language;
			$category =$_POST[ 'category' ];
			$category = ( $category{strlen( $category )-1} == ',' ) ? substr( $category, 0, -1 ) : $category;
			$category = explode( ',',str_replace( ' ', '', $category ) );
			$plug_RSS->add_item( $channel, $title, ( !empty( $text2 ) ) ? $text2 : $text, $link, $category );
		}
		
		$errors->report_error( $this->lang[ 'Added' ], MESSAGE );
	}
	
	function edit()
	{
		global $template, $mod_loader, $security, $lang_loader, $cache, $db, $errors;
		
		$id = ( isset( $_GET[ 'id' ] ) ) ? intval( $_GET[ 'id' ] ) : '';
		
		// fetch from cache
		$news = $cache->pull( 'news_posts' ); 
		if ( !$news || ( !isset( $news[ 'id' ] ) && !empty( $id ) ) )
		{
			// don't have it, gah
			$sql = "SELECT n.*, u.username FROM " . NEWS_TABLE . " n LEFT JOIN " . USERS_TABLE . " u ON n.news_poster=u.user_id WHERE news_id='$id' LIMIT 1";
			if ( !$res = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not fetch from database', CRITICAL_ERROR );
			}
			$row = $db->sql_fetchrow( $res );
			$news[ $row[ 'news_id' ] ] = $row;
			// back to cache
			$cache->push( 'news_posts', $news, TRUE );
		}
		// now need the whole list, this is a tad of a simpler query
		if ( !$news_list = $cache->pull( 'news_posts_list' ) )
		{
			// not on cache :/
			$sql = "SELECT news_id, news_title, news_lang, news_deleted, news_time FROM " . NEWS_TABLE . " ORDER BY news_time DESC";
			if ( !$res = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not fetch from database', CRITICAL_ERROR );
			}
			$news_list = $db->sql_fetchrowset( $res );
			// back to cache
			$cache->push( 'news_post_list', $news_list, TRUE );
		}
		
		if ( empty( $id ) )
		{
			$news = array();
		}else
		{
			$news = $news[ $id ];
		}
		
		// now loop through the list and build the selection thingy
		$list = '<select onchange="window.location.href = this.value"><option> </option>';
		for ( $i = 0; $i < count( $news_list ); $i++ )
		{
			$list .= ( $news_list[ $i ][ 'news_id' ] == $id ) ? '<option selected ' : '<option ';
			$list .= 'value="' . $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=edit&id=' . $news_list[ $i ][ 'news_id' ] ) . '">' . $news_list[ $i ][ 'news_lang' ] . ' :: ' . $news_list[ $i ][ 'news_title' ] . '</option>';
		}
		$list .= '</select>';
		
		// construct the language selection list
		$langl = $lang_loader->get_langlist();
		$langs = '<select name="language">';
		for ( $i = 0; $i < count( $langl ); $i++ )
		{
			$langs .= ( $langl[ $i ] == $news[ 'news_lang' ] ) ? '<option selected>' . $langl[ $i ] . '</option>' : '<option>' . $langl[ $i ] . '</option>';
		}
		$langs .= '</select>';
		
		// category list
		$sql = "SELECT * FROM " . NEWSCAT_TABLE;
		$result =$db->sql_query( $sql );
		
		$cats = explode( ',', $news[ 'news_category' ] );
		$category = '';
		$categories = '<select onchange="document.getElementById( \'category\' ).value =document.getElementById( \'category\' ).value + this.value "><option></option>';
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$categories .= '<option value="' . $row[ 'cat_name' ] . ',">' . $row[ 'cat_lang' ] . ' :: ' . $row[ 'cat_name' ] . '</option>';
			if ( in_array( $row[ 'cat_id' ], $cats ) )
			{ // this one was used
				$category .= $row[ 'cat_name' ] . ',';
			}
		}
		$categories .= '</select>';
		$category = substr( $category, 0, -1 );
		
		// get editor first
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => FALSE, 'def_text' => stripslashes( $news[ 'news_text' ] ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$newsy = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		$mod_loader->port_vars( array( 'name' => 'editor2', 'quickpost' => FALSE, 'def_text' => stripslashes( $news[ 'news_preview' ] ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$preview = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// assign vars
		$template->assign_block_vars( 'form', '', array(
			'L_TITLE' => $this->lang[ 'Edit_title' ],
			'L_EXPLAIN' =>$this->lang[ 'Edit_explain' ],
			'L_TITLE2' => $this->lang[ 'Edit_title2' ],
			'L_LANGUAGE' => $this->lang[ 'Edit_language' ],
			'L_PREVIEW' => $this->lang[ 'Edit_preview' ],
			'L_NEWS' => $this->lang[ 'Edit_news' ],
			'L_CATEGORY' => $this->lang[ 'Edit_category' ],
			
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=edit_real&id=' . $id ),
			'S_EDITOR' => $newsy[ 'editor_HTML' ],
			'S_EDITOR2' => $preview[ 'editor_HTML' ],
			'S_MODE' => 'create',
			'S_LANGS' => $langs,
			'S_LIST' => $list,
			'S_TITLE' => $news[ 'news_title' ],
			'S_CATEGORIES' => $categories,
			'S_CATEGORY' => $category . ',',
			
			'SHOWPREVIEW' => ( $board_config[ 'news_preview' ] ) ? 1 : 0,
		) );
		$template->assign_switch( 'form', TRUE );
		$template->assign_block_vars( 'del', '', array(
			'L_DEL' => ( $news[ 'news_deleted' ] ) ? $this->lang[ 'Edit_undelete' ] : $this->lang[ 'Edit_delete' ],
			'S_DEL' => $news[ 'news_deleted' ],
		) );
		$template->assign_switch( 'del', TRUE );
	}
	
	function edit_real()
	{
		global $errors, $db, $cache;
		
		// basic check
		if ( !isset( $_POST[ 'submit_news' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// get data
		$title = ( isset( $_POST[ 'title' ] ) ) ? strval( $_POST[ 'title' ] ) : '';
		$text = str_replace( '&nbsp;', ' ', ( isset( $_POST[ 'editor1' ] ) ) ? strval( $_POST[ 'editor1' ] ) : '' );
		$text2 = str_replace( '&nbsp;', ' ', ( isset( $_POST[ 'editor2' ] ) ) ? strval( $_POST[ 'editor2' ] ) : '' );
		$language = ( isset( $_POST[ 'language' ] ) ) ? strval( $_POST[ 'language' ] ) : '';
		$category = ( isset( $_POST[ 'category' ] ) ) ? $_POST[ 'category' ] : '';
		$category = ( $category{strlen( $category )-1} == ',' ) ? substr( $category, 0, -1 ) : $category;
		$changedel = ( isset( $_POST[ 'changedel' ] ) && $_POST[ 'changedel' ] == 'on' ) ? TRUE : FALSE;
		$deleted = ( isset( $_POST[ 'deleted' ] ) ) ? intval( $_POST[ 'deleted' ] ) : 0;
		$id = ( isset( $_GET[ 'id' ] ) ) ? intval( $_GET[ 'id' ] ) : '';
		
		// transform the category list to ids
		$category = explode( ',', $category );
		$category = "'" . implode( "','", $category ) . "'";
		$sql = "SELECT cat_id FROM " . NEWSCAT_TABLE . " WHERE cat_name IN ($category)";
		$result = $db->sql_query( $sql );
		$categories = '';
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$categories .= $row[ 'cat_id' ] . ',';
		}
		$category = substr( $categories, 0, -1 );
		
		// more checks
		if ( empty( $title ) || empty( $text ) )
		{
			$errors->report_error( $this->lang[ 'No_data' ], GENERAL_ERROR );
		}
		if ( empty( $id ) )
		{
			$errors->report_error( 'Missing id', CRITICAL_ERROR );
		}
		
		// stuff
		if ( $changedel )
		{
			$deleted = ( $deleted == 0 ) ? 1 : 0;
		}
		
		// the sql stuff
		$sql = "UPDATE " . NEWS_TABLE . " SET news_title='$title', news_text='$text', news_preview='$text2', news_lang='$language', news_deleted='$deleted', news_category='$category' WHERE news_id='$id' LIMIT 1";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not write to database', CRITICAL_ERROR );
		}
		
		// now to update the cache... bah
		$news = $cache->pull( 'news_posts' );
		if ( isset( $news[ $id ] ) )
		{ // the array with the news thingies
			$news[ $id ][ 'news_title' ] = $title;
			$news[ $id ][ 'news_text' ] = $text;
			$news[ $id ][ 'news_preview' ] = $text2;
			$news[ $id ][ 'news_lang' ] = $language;
			$news[ $id ][ 'news_deleted' ] = $deleted;
			$news[ $id ][ 'news_category' ] = $category;
			$cache->push( 'news_posts', $news, TRUE );
		}
		$news_list = $cache->pull( 'news_posts_list' );
		// this lame loop is the only way of doing this...
		for ( $i = 0; $i < 0; $i++ )
		{
			if ( $news_list[ $i ][ 'news_id' ] == $id )
			{
				$news_list[ $i ][ 'news_language' ] = $language;
				$news_list[ $i ][ 'news_title' ] = $title;
				$news_list[ $i ][ 'news_deleted' ] = $deleted;
				break;
			}
		}
		// the per-day cache gets killed
		$cache->delete( 'news_posts:' . date( 'd-n-Y', $news[ $id ][ 'news_time' ] ) . ':' . $language );
		
		if ( $deleted )
		{ // it was deleted so the calendar changes and cache gets updated
			$calendar = $cache->pull( 'news_calendar' );
			
			$sql = "SELECT news_time FROM " . NEWS_TABLE . " WHERE news_id='$id' LIMIT 1";
			$result = $db->sql_query( $sql );
			$time = $db->sql_fetchfield( 'news_time' );
			$year = date( 'Y', $time );
			$month = date( 'n', $time );
			
			delete( $calendar[ $year ][ $month ] );
			$cache->push( 'news_calendar', $calendar, TRUE );
		}
		
		// done
		$errors->report_error( $this->lang[ 'Edited' ], MESSAGE );
	}
	
	function settings()
	{
		global $template, $board_config, $security;
		
		// set us up the bomb
		$template->assign_block_vars( 'settings', '', array(
			'L_TITLE' => $this->lang[ 'Sett_title' ],
			'L_EXPLAIN' => $this->lang[ 'Sett_explain' ],
			
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=settings_real' )
		) );
		$template->assign_switch( 'settings', TRUE );
		
		// now go through the config in search of thingies
		foreach ( $board_config as $var => $val )
		{
			if ( strtolower( substr( $var, 0, 5 ) ) != 'news_' )
			{ // only the ones to do with news
				continue;
			}
			$template->assign_block_vars( 'confrow', '', array(
				'NAME' => $var,
				'VALUE' => $val,
				'TITLE' => ( isset( $this->lang[ $var ] ) ) ? $this->lang[ $var ] : $var,
			) );
			$template->assign_switch( 'confrow', TRUE );
		}
	}
	
	function settings_real()
	{
		global $errors, $db, $board_config, $cache;
		
		// basic check
		if ( !isset( $_POST[ 'submit_sett' ] ) )
		{
			$errrors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// now build the query
		// do the same table is done as many in order to use only one query
		$tables = array();
		$wheres = array();
		$sets = array();
		$i = 0;
		// loop
		foreach ( $board_config as $name => $void )
		{
			if ( strtolower( substr( $name, 0, 5 ) ) != 'news_' )
			{ // so any funny business is prevented and only news things are changed
				continue;
			}
			if ( isset( $_POST[ $name ] ) )
			{
				$post = $_POST[ $name ];
				$t = 'c' . $i;
				$tables[] = CONFIG_TABLE . ' ' . $t;
				$wheres[] = "$t.config_name='$name'";
				$sets[] = "$t.config_value='$post'";
				$i++;
			}
		}
		// make the query
		$sql = "UPDATE " . implode( ', ', $tables ) . " SET " . implode( ', ', $sets ) . " WHERE " . implode( ' AND ', $wheres );
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t write to database', CRITICAL_ERROR );
		}
		
		// remove from cache
		$cache->delete( 'board_config' );
		
		$errors->report_error( $this->lang[ 'Sett_done' ], MESSAGE );
	}
	/**
	* deasl with news categories
	*/
	function categories()
	{
		global $db, $errors, $template, $security, $lang_loader, $cache;
		
		// prepare some variables
		$id = ( isset( $_GET[ 'catid' ] ) ) ? intval( $_GET[ 'catid' ] ) : 0;
		$id = ( isset( $_POST[ 'catid' ] ) ) ? intval( $_POST[ 'catid' ] ) : $id;
		$name = '';
		$lang = '';
		$report = '';
		
		if ( isset( $_POST[ 'submitcat' ] ) )
		{
			$nam = $_POST[ 'name' ];
			$lan = $_POST[ 'language' ];
			if ( $id == 0 )
			{ // add it
				$sql = "INSERT INTO " . NEWSCAT_TABLE . " ( cat_name, cat_lang ) VALUES ( '$nam', '$lan' )";
			}else
			{ // edit it
				if ( isset( $_POST[ 'delete' ] ) )
				{ // delete it
					$sql = "DELETE FROM " . NEWSCAT_TABLE . " WHERE cat_id='$id' LIMIT 1";
				}else
				{
					$sql = "UPDATE " . NEWSCAT_TABLE . " SET cat_name='$nam', cat_lang='$lan' WHERE cat_id='$id' LIMIT 1";
				}
			}
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not insert', CRITICAL_ERROR );
			}
			// deletin is much simpler than trying to update
			$cache->delete( 'news_categories' );
			$cache->delete( 'news_categories_list' );
			$report = $this->lang[ 'Cat_done' ];
		}
		
		// fetch all the categories
		$sql = "SELECT * FROM " . NEWSCAT_TABLE . " ORDER BY cat_id";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Cannot read database', CRITICAL_ERROR );
		}
		
		// make the selectoin list
		$select = '<select name="catid" onchange="window.location.href=this.value;"><option value="0"></option>';
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$uri = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=cat&catid=' . $row[ 'cat_id' ] );
			$select .= '<option value="' . $uri . '">' . $row[ 'cat_lang' ] . ' :: ' . $row[ 'cat_name' ] .  '</option>';
			if ( $row[ 'cat_id' ] == $id )
			{ // this one was selected for editation
				$name = $row[ 'cat_name' ];
				$lang = $row[ 'cat_lang' ];
			}
		}
		$select .= '</select>';
		
		// construct the language selection list
		$langl = $lang_loader->get_langlist();
		$langs = '<select name="language">';
		for ( $i = 0; $i < count( $langl ); $i++ )
		{
			$langs .= ( $langl[ $i ] == $lang ) ? '<option selected>' . $langl[ $i ] . '</option>' : '<option>' . $langl[ $i ] . '</option>';
		}
		$langs .= '</select>';
		
		$template->assign_block_vars( 'categories', '', array(
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=cat' ),
			'S_ID' =>$id,
			'S_NAME' => $name,
			'S_NAMES' => $select,
			'S_LANGS' => $langs,
			
			'L_NAME' => $this->lang[ 'Cat_name' ],
			'L_LANG' => $this->lang[ 'Cat_lang' ],
			'L_TITLE' => $this->lang[ 'Cat_title'  ],
			'L_EXPLAIN' => $this->lang[ 'Cat_explain' ],
			'L_REPORT' => $report,
			'L_DELETE' => $this->lang[ 'Cat_delete' ],
		) );
		$template->assign_switch( 'categories', TRUE );
	}
	/**
	* deals with managing the RSS
	*/
	function rss()
	{
		global $Cl_root_path, $template, $lang_loader, $plug_RSS, $security, $basic_gui, $board_config, $db;
		
		if ( isset( $_POST[ 'rssubmit' ] ) )
		{ // form was submited
			foreach ( $_POST[ 'language' ] as $i => $lang )
			{
				$title =$_POST[ 'title' ][ $i ];
				$description =$_POST[ 'description' ][ $i ];
				$copyright =$_POST[ 'copyright' ][ $i ];
				$category =$_POST[ 'category' ][ $i ];
				$category = explode( ',',str_replace( ' ', '', $category ) );
				$editor =$_POST[ 'editor' ][ $i ];
				$webmaster =$_POST[ 'webmaster' ][ $i ];
				$link = $basic_gui->get_URL() . '/index.php/' . MODE_URL . '=news/lang=' . $lang;
				
				if ( !empty( $title ) && !empty( $description ) )
				{ // at least these need to be set
					$plug_RSS->create_channel( 'news-' . $lang, $title, $description, $link, $lang, $copyright, $category, $editor, $webmaster );
				}
			}
			
			$enable = intval( isset( $_POST[ 'enable' ] ) );
			if ( $board_config[ 'news_enableRSS' ] != $enable )
			{
				$sql = "UPDATE " . CONFIG_TABLE . " SET config_value='$enable' WHERE config_name='news_enableRSS' LIMIT 1";
				$db->sql_query( $sql );
			}
			$board_config[ 'news_enableRSS' ] = $enable;
		}
		
		// show the form
		$template->assign_block_vars( 'rss', '', array(
			'L_TITLE' => $this->lang[ 'RSS_title' ],
			'L_EXPLAIN' => $this->lang[ 'RSS_explain' ],
			'L_TITLE2' => $this->lang[ 'RSS_title2' ],
			'L_DESCRIPTION' =>$this->lang[ 'RSS_description' ],
			'L_COPYRIGHT' => $this->lang[ 'RSS_copyright' ],
			'L_CATEGORY' =>$this->lang[ 'RSS_category' ],
			'L_EDITOR' => $this->lang[ 'RSS_editor' ],
			'L_WEBMASTER' =>$this->lang[ 'RSS_webmaster' ],
			'L_LANGUAGE' =>$this->lang[ 'RSS_language' ],
			'L_ENABLE' => $this->lang[ 'RSS_enable' ],
			
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_news&s=rss' ),
			'S_ENABLE' => ( $board_config[ 'news_enableRSS' ] ) ? 'checked' : '',
		) );
		$template->assign_switch( 'rss' );
		
		$langs = $lang_loader->get_langlist();
		foreach ( $langs as $lang )
		{
			$channel = $plug_RSS->channel_data( 'news-' . $lang );
			
			if ( is_array( $channel[ 'category' ] ) )
			{
				$category = implode( ',', $channel[ 'category' ] );
			}else
			{
				$category = '';
			}
		
			$template->assign_block_vars( 'rssrow', '', array(
				'S_TITLE' => $channel[ 'title' ],
				'S_DESCRIPTION' => $channel[ 'description' ],
				'S_COPYRIGHT' => $channel[ 'copyright' ],
				'S_CATEGORY' =>  $category,
				'S_EDITOR' => $channel[ 'editor' ],
				'S_WEBMASTER' => $channel[ 'webmaster' ],
				'S_LANGUAGE' =>$lang,
			) );
			$template->assign_switch( 'rssrow', TRUE );
		}
	}
	
	//
	// End of ACP_news class
	//
}


?>
