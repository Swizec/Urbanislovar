<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:                 news.php                            //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       01st April 2006                         //
//     version:               0.3.3                              //
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
// the news module, yay
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// debug :: debug flag
// gui :: the gui subclass

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'news', $vars, $visible ) );
// end class creation

class news extends news_def
{
	
	// constructor
	function news( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $basic_gui, $security, $board_config, $db, $cache, $Sajax, $plug_RSS;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/news_gui' . phpEx );
		$this->gui = new news_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'news' );
		$this->gui->lang = $this->lang;
		
		// make the categories selection
		$nope = FALSE;
		$categories = array();
		if ( !$categories = $cache->pull( 'news_categories_list' ) )
		{
			$nope = TRUE;
		}else
		{
			$categories = $categories[ $lang_loader->board_lang ];
		}
		if ( $nope || !isset( $categories[ $lang_loader->board_lang ] ) )
		{ // have to construct it
			$sql  = "SELECT * FROM " . NEWSCAT_TABLE . " WHERE cat_lang='" . $lang_loader->board_lang . "' ORDER BY cat_name ASC";
			if ( $result = $db->sql_query( $sql ) )
			{
				$categories[ 0 ] = '<a href="' . $security->append_sid( '?' . MODE_URL . '=news&cat=0' ) . '">' . $this->lang[ 'Nocat' ] . '</a>';
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$categories[ $row[ 'cat_id' ] ] = '<a href="' . $security->append_sid( '?' . MODE_URL . '=news&cat=' . $row[ 'cat_id' ] ) . '">' . $row[ 'cat_name' ] . '</a>';
				}
			}
			// store it
			$cats[ $lang_loader->board_lang ] = $categories;
			$cache->push( 'news_categories_list', $cats, TRUE );
		}
		$this->catlist = $categories;
		
		// show the sidebar with stuff
		if ( $board_config[ 'news_showsidebar' ] )
		{
			// make the categories selection
			$categories = implode( '<br />', $categories );
			
			$cid = ( isset( $_GET[ 'cat' ] ) ) ? intval( $_GET[ 'cat' ] ) : -1;
			
			// sidebar html
			$uri = $security->append_sid( '?' . MODE_URL . '=news&' . SUBMODE_URL . '=search&cat=' . $cid );
			$Lcats = $this->lang[ 'Categories' ];
			$sidebar = "<form action=\"$uri\" method=\"POST\">\n<input type=\"text\" cols=\"10\" name=\"search\" />\n<input type=\"submit\" value=\"" . $this->lang[ 'Search' ] . "\" /></form>\n";
			$sidebar .= ( $board_config[ 'news_enableRSS' ] ) ? '<a href="' . $plug_RSS->get_channel( 'news-' . $lang_loader->board_lang ) . '"><b>RSS</b></a><br />' . "\n" : '';
			$sidebar .= "<strong>$Lcats:</strong><div>$categories</div>\n";
			$sidebar .= '<div id="news_calendar">' . $this->calendar( intval( date( 'n', EXECUTION_TIME ) ), intval( date( 'Y', EXECUTION_TIME ) ) ) . '</div>';
			$basic_gui->add2sidebar( 'right', 'News', $sidebar );
			
			// the calendar's ajax
			$Sajax->add2export( 'news->calendar', '$month, $year' );
		}
		
		if ( $board_config[ 'news_enableRSS' ] )
		{
			$channel = $plug_RSS->channel_data( 'news-' . $lang_loader->board_lang );
			$title = $channel[ 'title' ];
			$link = $plug_RSS->get_channel( 'news-' . $lang_loader->board_lang );
			$basic_gui->add_head( '<link rel="alternate" type="application/rss+xml" title="' . $title . '" href="' . $link .'" />' );
		}
	}
	
	function display( $fetch = FALSE )
	{
		global $db, $errors, $cache, $lang_loader, $userdata, $security, $board_config;
		
		// get the id and stuff
		$id = ( isset( $_GET[ 'id' ] ) ) ? intval( $_GET[ 'id' ] ) : '';
		$day = ( isset( $_GET[ 'day' ] ) ) ? strval( $_GET[ 'day' ] ) : '';
		$id_hash = array();
		$issearch = ( $_GET[ SUBMODE_URL ] == 'search' ) ? TRUE : FALSE;
		$page = ( isset( $_GET[ 'page' ] ) ) ? intval( $_GET[ 'page' ] ) : 0;
		$cid = ( isset( $_GET[ 'cat' ] ) ) ? intval( $_GET[ 'cat' ] ) : -1;
		if ( $cid == -1 )
		{
			$sql_category = '';
		}else
		{
			$sql_category = ( $cid == 0 ) ? " AND news_category='' " : " AND news_category LIKE '%$cid%' ";
			$issearch = TRUE;
		}
		
		if ( ( empty( $id ) && empty( $day ) ) || $issearch )
		{ // no choice but to go and pull the last one from the db *sigh*
		  // upon search we have to do this every time, duh
			// we check if a search was performed
			if ( $issearch )
			{ // was a search, the query is different now
				$search = '';
				$query = strval( $_POST[ 'search' ] );
				if ( !$query )
				{
					$query = strval( $_GET[ 'search' ] );
				}
				$searchquery = $query;
				$fields = array( 'news_title', 'news_text', 'news_preview' );
				foreach ( $fields as $field )
				{
					$search .= $field . " LIKE '%$query%' OR ";
				}
				$search = '( ' . substr( $search, 0, -4 ) . ' )';
				$sql = "SELECT n.*, u.username FROM " . NEWS_TABLE . " n LEFT JOIN " . USERS_TABLE . " u ON n.news_poster=u.user_id WHERE n.news_deleted=0 AND n.news_lang='" . $lang_loader->board_lang . "' AND $search $sql_category ORDER BY n.news_time DESC LIMIT " . ($page*$board_config[ 'news_front_num' ]) .", " . $board_config[ 'news_front_num' ];
			}else
			{
				// or rather the number set in the config as this is surely the index ^^
				$sql = "SELECT n.*, u.username FROM " . NEWS_TABLE . " n LEFT JOIN " . USERS_TABLE . " u ON n.news_poster=u.user_id WHERE n.news_deleted=0 AND n.news_lang='" . $lang_loader->board_lang . "' ORDER BY n.news_time DESC LIMIT " . ($page*$board_config[ 'news_front_num' ]) .", " . $board_config[ 'news_front_num' ];
			}
			if ( !$res = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not fetch from database', CRITICAL_ERROR );
			}
			// will have to loop this and make a wee hash
			while ( $row = $db->sql_fetchrow( $res ) )
			{
				$news[ $row[ 'news_id' ] ] = $row;
				$id_hash[] = $row[ 'news_id' ];
			}
			if ( !$issearch )
			{ // don't cache if a result of searching
				$cache->push( 'news_posts', $news, TRUE );
			}
		}elseif ( !empty( $day ) )
		{ // choice was made by day
			if ( !$news = $cache->pull( 'news_posts:' . $day ) )
			{ // oh well, have to fetch
				$d = explode( '-', $day );
				if ( !empty( $d ) )
				{
					$from = mktime( 0, 0, 0, $d[ 1 ], $d[ 0 ], $d[ 2 ] );
					$to = mktime( 23, 59, 59, $d[ 1 ], $d[ 0 ], $d[ 2 ] );
				}
				$sql = "SELECT n.*, u.username FROM " . NEWS_TABLE . " n LEFT JOIN " . USERS_TABLE . " u ON n.news_poster=u.user_id WHERE n.news_deleted=0 AND n.news_lang='" . $lang_loader->board_lang . "' AND n.news_time BETWEEN $from AND $to ORDER BY n.news_time DESC LIMIT " . ($page*$board_config[ 'news_front_num' ]) .", " . $board_config[ 'news_front_num' ];
				if ( !$res = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Could not fetch from database', CRITICAL_ERROR );
				}
				// will have to loop this and make a wee hash
				while ( $row = $db->sql_fetchrow( $res ) )
				{
					$news[ $row[ 'news_id' ] ] = $row;
					$id_hash[] = $row[ 'news_id' ];
				}
				$cache->push( 'news_posts:' . $day . ':' . $lang_loader->board_lang, $news, TRUE );
				// for filtering thingies
				$issearch = TRUE;
				$sql_day = "news_time BETWEEN $from AND $to";
			}
		}else
		{
			// fetch from cache
			$news = array();
			$news = $cache->pull( 'news_posts' );
			if ( !$news || !isset( $news[ $id ] )  )
			{
				// don't have it, gah
				$sql = "SELECT n.*, u.username FROM " . NEWS_TABLE . " n LEFT JOIN " . USERS_TABLE . " u ON n.news_poster=u.user_id WHERE n.news_id='$id' LIMIT 1";
				if ( !$res = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Could not fetch from database', CRITICAL_ERROR );
				}
				$row = $db->sql_fetchrow( $res );
				$news[ $row[ 'news_id' ] ] = $row;
				// back to cache
				$cache->push( 'news_posts', $news, TRUE );
			}
			// just to keep things simplish
			$id_hash[] = $id;
		}
		
		if ( !$issearch )
		{ // now need the whole list, this is a tad of a simpler query
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
		}else
		{ // need the whole list but filtered through the search thing
			$tmp = $search . $sql_category;
			$sql_day = ( !empty( $tmp ) ) ? ' AND ' . $sql_day : $sql_day;
			$sql = "SELECT news_id, news_title, news_lang, news_deleted, news_time FROM " . NEWS_TABLE . " WHERE $search $sql_category $sql_day ORDER BY news_time DESC";
			if ( !$res = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Could not fetch from database', CRITICAL_ERROR );
			}
			$news_list = $db->sql_fetchrowset( $res );
		}
// 		// parse the time
// 		$news[ $id ][ 'news_time' ] = date( $userdata[ 'user_timeformat' ], $news[ $id ][ 'news_time' ] );
		
		// so now we have both the whole list and the particular needed one :P
		
		// build the selection thingy ^^
		$list = '<select onchange="window.location.href = this.value"><option value="">' . $this->lang[ 'Choose_news' ] . '</option>';
		for ( $i = 0; $i < count( $news_list ); $i++ )
		{
			if ( $news_list[ $i ][ 'news_deleted' ] || $news_list[ $i ][ 'news_lang' ] != $lang_loader->board_lang )
			{ // not this time ^^
				continue;
			}
			$uri = $security->append_sid( '?' . MODE_URL . '=news&id=' . $news_list[ $i ][ 'news_id' ] );
			$list .= ( $news_list[ $i ][ 'news_id' ] == $id ) ? '<option selected ' : '<option ';
			$list .= 'value="' . $uri . '">' . date( $userdata[ 'user_timeformat' ], $news_list[ $i ][ 'news_time' ] ) . ' :: ' . $news_list[ $i ][ 'news_title' ] . '</option>';
		}
		$list .= '</select>';
		
		$URL = '?' . MODE_URL . '=news&id=' . $id_hash[ 0 ];

		// loop throuh the hash and set the categories
		foreach ( $id_hash as $id )
		{
			$news[ $id ][ 'news_category' ] = $this->getcat( $news[ $id ] );
		}
		
		// construct the uris so as to not lose search and category data when changing pages
		$uri1 =  '?' . MODE_URL . '=news&page=' . ($page+1) ;
		$uri2 =  '?' . MODE_URL . '=news&page=' . ($page-1);
		$uri1 .= ( $cid != -1 ) ? '&cat=' . $cid : '';
		$uri2 .= ( $cid != -1 ) ? '&cat=' . $cid : '';
		$uri1 .= ( $day != '' ) ? '&day=' . $day : '';
		$uri2 .= ( $day != '' ) ? '&day=' . $day : '';
		$uri1 .= ( $_GET[ SUBMODE_URL ] == 'search' ) ? '&' . SUBMODE_URL . '=search&search=' . $searchquery : '';
		$uri2 .= ( $_GET[ SUBMODE_URL ] == 'search' ) ? '&' . SUBMODE_URL . '=search&search=' . $searchquery : '';
		// calculate the pagination
		$pagi[ 'unext' ] = $security->append_sid( $uri1 );
		$pagi[ 'uprevious' ] = $security->append_sid( $uri2 );
		$pagi[ 'previous' ] = ( $page > 0 ) ? 1 : 0;
		$pagi[ 'next' ] = ( $page < ceil( count( $news_list ) / $board_config[ 'news_front_num' ] )-1 ) ? 1 : 0;
		
		if ( $fetch )
		{
			return $this->gui->show( $news, $id_hash, $list, $URL, $pagi, FALSE );
		}
		$this->gui->show( $news, $id_hash, $list, $URL, $pagi );
	}
	
	function fetchdisplay()
	{
		global $mod_loader;
		
		$disp = $this->display( TRUE );
		
		$mod_loader->port_vars( array( 'news_HTML' => $disp ) );
	}
	/**
	* monthly calendar display with linkages
	*/
	function calendar( $month, $year )
	{
		global $cache, $db, $security, $basic_gui, $lang_loader;
		
		// try getting it from cache
		$calendar_cache = $cache->pull( 'news_calendar' );
		if ( isset( $calendar_cache[ $year ][ $month ][ $lang_loader->board_lang ] ) )
		{ // have it
			return $calendar_cache[ $year ][ $month ][ $lang_loader->board_lang ];
		}
		
		$calendar = '<div class="gensmall"><div style="text-align: center" class="gen"><strong>' . $this->lang[ 'Month_' . $month ] . '&nbsp;&nbsp;' . $year. '</strong></div><table border="0" padding="0" width="100%"><tr>';
		
		for ( $i = 0; $i < 7; $i++ )
		{
			$calendar .= '<td><b>' . $this->lang[ 'Day_' . $i ] .'</b></td>';
		}
		$calendar .= '</tr>';
		
		// create the calendar
		$lastday1 = date( 'j', mktime( 0, 0, 0, $month, 0, $year ) ); // last day of previous month
		$lastday2 = date( 'j', mktime( 0, 0, 0, ($month+1), 0, $year ) ); // last day of this month
		$starton = date( 'w', mktime( 0,0, 0, $month, 1, $year ) ); // month started on
		$starton -= 1;
		$starton = ( $starton == -1 ) ? 6  : $starton;
		$stopon =  date( 'w', mktime( 0, 0, 0, ($month+1), 0, $year ) );
		$stopon -= 1;
		$stopon = ( $starton == -1 ) ? 6  : $stopon;
		
		// get the news for the needed timeframe
		$from = mktime( 0, 0, 0, $month-1, $lastday1-$starton, $year ); // first day on calendar
		$to = mktime( 23, 59, 59, $month+1, 6-$stopon, $year ); // last day on calendar
		$news = array();
		$sql = "SELECT news_id, news_time, news_title FROM " . NEWS_TABLE . " WHERE news_time >= $from AND news_time <= $to AND news_deleted=0 AND news_lang='" . $lang_loader->board_lang . "'";
		$result = $db->sql_query( $sql );
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$day = date( 'j', $row[ 'news_time' ] );
			$m = date( 'n', $row[ 'news_time' ] );
			$Y = date( 'Y', $row[ 'news_time' ] );
			if ( !isset( $news[ $m ] ) )
			{
				$news[ $m ] = array();
			}
			$news[ $m ][ $day ] = array();
			$news[ $m ][ $day ][ 'url' ] = $security->append_sid( '?' . MODE_URL . '=news&day=' . $day . '-' . $m . '-' . $Y );
			$news[ $m ][ $day ][ 'title' ] = $row[ 'news_title' ];
		}
		
		$calendar .= '<tr>';
		$row = '';
		for ( $i = 0, $j = $lastday1; $i < $starton; $i++, $j-- )
		{
			if ( isset( $news[ $month-1 ][ $j ] ) )
			{
				$row = '<td><i><a href="' . $news[ $month-1 ][ $j ][ 'url' ] . '" title="' . $news[ $month-1 ][ $j ][ 'title' ] . '"><u>'  . $j . '</u></a></i></td>' . $row;
			}else
			{
				$row = '<td><i>'  . $j . '</i></td>' . $row;
			}
		}
		for ( $i = 1; $i <= 6 - $starton + 1; $i++ )
		{
			if ( isset( $news[ $month ][ $i ] ) )
			{
				$row .= '<td><b><a href="' . $news[ $month ][ $i ][ 'url' ] . '" title="' . $news[ $month ][ $i ][ 'title' ] . '"><u>' . $i . '</u></a></b></td>';
			}else
			{
				$row .= '<td><b>' . $i . '</b></td>';
			}
		}
		$calendar .= $row . '</tr><tr>';
		for ( $i = $i; $i <= $lastday2; $i++ )
		{
			if ( isset( $news[ $month ][ $i ] ) )
			{
				$calendar .= '<td><b><a href="' . $news[ $month ][ $i ][ 'url' ] . '" title="' . $news[ $month ][ $i ][ 'title' ] . '"><u>' . $i . '</u></a></b></td>';
			}else
			{
				$calendar .= '<td><b>' . $i . '</b></td>';
			}
			if ( ($i+$starton) % 7 == 0 )
			{
				$calendar .= '</tr><tr>';
			}
		}
		for ( $i = 1; $i <= 6 - $stopon; $i++ )
		{
			if ( isset( $news[ $month+1 ][ $i ] ) )
			{
				$calendar .= '<td><i><a href="' . $news[ $month+1 ][ $i ][ 'url' ] . '" title="' . $news[ $month+1 ][ $i ][ 'title' ] . '"><u>' . $i . '</u></a></i></td>';
			}else
			{
				$calendar .= '<td><i>' . $i . '</i></td>';
			}
		}
		$calendar .= '</tr>';
		
		// make the little navigation arrows at the bottom
		$calendar .= '</table>';
		$calendar .= '<div class="gen" style="width: 40%; float:left; margin-left: 10%"><a href="#" onclick="news_calendar_previous( ' . $month . ', ' . $year . ')"><b>&lt;&lt;</b></a></div>';
		$calendar .= '<div class="gen" style="width: 40%; float:left; text-align: right;  margin-right: 10%"><a href="#" onclick="news_calendar_next( ' . $month . ', ' . $year . ')"><b>&gt;&gt;</b></a></div>';
		$calendar .= '<br /></div>';
		
		// store to cache
		$calendar_cache[ $year ][ $month ][ $lang_loader->board_lang ] = $calendar;
		$cache->push( 'news_calendar', $calendar_cache, TRUE );
		
		return $calendar;
	}
	/**
	* fetches the category list, names and such
	*/
	function getcat( $news )
	{
		$got = array();
		foreach ( explode( ',', $news[ 'news_category' ] ) as $cid )
		{
			$got[ $cid ] = $this->catlist[ $cid ];
		}
		return $got;
	}
	
	//
	// End of news class
	//
}


?>
