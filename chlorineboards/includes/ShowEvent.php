<?php

/**
*     defines the ShowEvent class
*     @file                ShowEvent.php
*     @see ShowEvent
*/
/**
* deals with everything partaining to ShowEventes
*     @class		  ShowEvent
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.2.5
*     @since       10th April 2007
*     @package		     ShowEvent
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/
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
eval( Varloader::createclass( 'ShowEvent', $vars, $visible ) );
// end class creation

class ShowEvent extends ShowEvent_def
{
	/**
	* constructor
	*/
	function ShowEvent( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax, $basic_gui, $Sajax;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/ShowEvent_gui' . phpEx );
		$this->gui = new ShowEvent_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ShowEvent' );
		$this->gui->lang = $this->lang;
		
		$this->gui->funct = $this;
	}
	/**
	* displays the calendar eh
	*/
	function show_calendar()
	{
		global $basic_gui, $Sajax;
		
		$this->del_cat();
		
		$month = date( 'n', EXECUTION_TIME );
		$year = date( 'Y', EXECUTION_TIME );
		$calendar = array();
		for ( $m = $month; $m <= $month+1; $m++ )
		{
			$calendar[] = $this->month( $m, $year );
		}
		
		// some JS is needed for the calendar
		$basic_gui->add_JS( 'includes/ShowEvent.js' );
		
		// ajax the months
		$Sajax->add2export( 'ShowEvent->month', '$month, $year, $id' );
		$Sajax->add2export( 'ShowEvent->calendarinfo', '$day, $month, $year' );
		
		$this->gui->show_calendar( $calendar );
	}
	/**
	* returns a calendary month
	*/
	function month( $month, $year, $id = -1 )
	{
		global $cache, $db, $security, $basic_gui, $lang_loader;
		
		if ( $month > 12 )
		{
			$month = 1;
			$year++;
		}
		
		$calendar = '<table border="0" padding="0" width="100%"><tr>';
		
		for ( $i = 0; $i < 7; $i++ )
		{
			$calendar .= '<td class="day"><b>' . $this->lang[ 'Day_' . $i ] .'</b></td>';
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
		$today = explode( '.', date( 'j.n.Y', EXECUTION_TIME ) );
		
		$rows = 0;
		
		// get the news for the needed timeframe
		$from = mktime( 0, 0, 0, $month-1, $lastday1-$starton, $year ); // first day on calendar
		$to = mktime( 23, 59, 59, $month+1, 6-$stopon, $year ); // last day on calendar
		$items = array();
		/*$sql = "SELECT item_id, item_time_to_stamp, item_time_from_stamp, item_title FROM " . SHOWEVENT_ITEM_TABLE . " WHERE (" .
												"('$from' BETWEEN item_time_from_stamp AND item_time_to_stamp)OR" .
												"('$to' BETWEEN item_time_from_stamp AND item_time_to_stamp)OR" .
												"(item_time_from_stamp BETWEEN '$from' AND '$to')OR" .
												"(item_time_to_stamp BETWEEN '$from' AND '$to' )" .
												") AND item_language='" . $lang_loader->board_lang . "' AND item_isdeleted=0";*/
		$sql = "SELECT i.item_id, i.item_time_to_stamp, i.item_time_from_stamp, i.item_title FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . 
											SHOWEVENT_CAT2ITEM_TABLE . " c2i ON i.item_id=c2i.item_id LEFT JOIN " .
											SHOWEVENT_CATEGORY_TABLE . " c ON c2i.cat_id=c.cat_id " . 
											
											" WHERE (" .
												"('$from' BETWEEN i.item_time_from_stamp AND i.item_time_to_stamp)OR" .
												"('$to' BETWEEN i.item_time_from_stamp AND i.item_time_to_stamp)OR" .
												"(i.item_time_from_stamp BETWEEN '$from' AND '$to')OR" .
												"(i.item_time_to_stamp BETWEEN '$from' AND '$to' )" .
												") AND i.item_language='" . $lang_loader->board_lang . "' AND i.item_isdeleted=0 " .
												"AND c.cat_parent <> 1 AND c.cat_parent <> 18";
		
		$result = $db->sql_query( $sql );
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			for( $t = $row[ 'item_time_from_stamp' ]; $t <= $row[ 'item_time_to_stamp' ]; $t += 50*60*24 )
			{
				$m = date( 'n', $t );
				$d = date( 'j', $t );
				if ( !isset( $items[ $m ] ) )
				{
					$items[ $m ] = array();
				}
				if ( !isset( $items[ $m ][ $d ] ) )
				{
					$items[ $m ][ $d ] = $row[ 'item_title' ] . '<br />';
				}else
				{
					$items[ $m ][ $d ] .= $row[ 'item_title' ] . '<br />';
				}
			}	
		}
		
		$calendar .= '<tr>';
		$row = '';
		for ( $i = 0, $j = $lastday1; $i < $starton; $i++, $j-- )
		{
			if ( $j == $today[ 0 ] && $month-1 == $today[ 1 ] && $year == $today[ 2 ] )
			{
				$class2 = 'today';
			}else
			{
				$class2 = '';
			}
			if ( isset( $items[ $month-1 ][ $j ] ) )
			{
				$tooltip = $basic_gui->make_tooltip( $items[ $month-1 ][ $j ], 'buttontip' );
				$row = '<td class="dimday ' . $class2 . '"><a href="#" onclick="showinfo( ' . $j . ', ' . ($month-1) . ', ' . $year . ' ); return false"><u>'  . $j . '</u></a></td>' . $row;
			}else
			{
				$row = '<td class="dimday ' . $class2 . '">'  . $j . '</td>' . $row;
			}
		}
		for ( $i = 1; $i <= 6 - $starton + 1; $i++ )
		{
			if ( $i == $today[ 0 ] && $month == $today[ 1 ] && $year == $today[ 2 ] )
			{
				$class2 = 'today';
			}else
			{
				$class2 = '';
			}
			if ( ($i+$starton) % 7 == 0 )
			{
				$class = 'class="sunday ' . $class2 . '"';
			}else
			{
				$class = ( empty( $class2 ) ) ? '' : 'class="' . $class2 . '"';
			}
			if ( isset( $items[ $month ][ $i ] ) )
			{
				$tooltip = $basic_gui->make_tooltip( $items[ $month ][ $i ], 'buttontip' );
				$row .= '<td ' . $class . '><b><a href="#" onclick="showinfo( ' . $i . ', ' . $month . ', ' . $year . ' ); return false"><u>' . $i . '</u></a></b></td>';
			}else
			{
				$row .= '<td ' . $class . '><b>' . $i . '</b></td>';
			}
		}
		$calendar .= $row . '</tr><tr>';
		$rows++;
		for ( $i = $i; $i <= $lastday2; $i++ )
		{
			if ( $i == $today[ 0 ] && $month == $today[ 1 ] && $year == $today[ 2 ] )
			{
				$class2 = 'today';
			}else
			{
				$class2 = '';
			}
			if ( ($i+$starton) % 7 == 0 )
			{
				$class = 'class="sunday ' . $class2 . '"';
			}else
			{
				$class = ( empty( $class2 ) ) ? '' : 'class="' . $class2 . '"';
			}
			if ( isset( $items[ $month ][ $i ] ) )
			{
				$tooltip = $basic_gui->make_tooltip( $items[ $month ][ $i ], 'buttontip' );
				$calendar .= '<td ' . $class . '><b><a href="#" onclick="showinfo( ' . $i . ', ' . $month . ', ' . $year . ' ); return false"><u>' . $i . '</u></a></b></td>';
			}else
			{
				$calendar .= '<td ' . $class . '><b>' . $i . '</b></td>';
			}
			if ( ($i+$starton) % 7 == 0 )
			{
				$calendar .= '</tr><tr>';
				$rows++;
			}
		}
		for ( $i = 1; $i <= 6 - $stopon; $i++ )
		{
			if ( $i == $today[ 0 ] && $month+1 == $today[ 1 ] && $year == $today[ 2 ] )
			{
				$class2 = 'today';
			}else
			{
				$class2 = '';
			}
			if ( isset( $items[ $month+1 ][ $i ] ) )
			{
				$calendar .= '<td class="dimday ' . $class2 . '"><a href="#" onclick="showinfo( ' . $i . ', ' . ($month+1) . ', ' . $year . ' ); return false"><u>' . $i . '</u></a></td>';
			}else
			{
				$calendar .= '<td class="dimday ' . $class2 . '">' . $i . '</td>';
			}
		}
		$calendar .= '</tr>';
		$rows++;
		
		if ( $rows < 7 )
		{
			$row = '';
			for( $i = 0; $i < 6-$rows; $i++ )
			{
				$row .= '<tr>';
				for ( $j = 0; $j < 7; $j++ )
				{
					$row .= '<td></td>';
				}
				$row .= '</tr>';
			}
			$calendar .= "$row";
		}
		
		$calendar .= '<tr><td colspan="7" class="title">' . $this->lang[ 'Month_' . $month ] . '</td></tr>';
		
		$calendar .= "</table>";
		
		if ( $id == -1 )
		{
			return $calendar;
		}else
		{
			return array( $calendar, $id, $year, $month );
		}
	}
	/**
	* returns the data needed to display the daily information
	*/
	function calendarinfo( $day, $month, $year )
	{
		global $db, $errors, $lang_loader;
		
		$from = mktime( 0, 0, 0, $month, $day, $year ); // first second of day
		$to = mktime( 23, 59, 59, $month, $day, $year ); // last second of day
		$date = date( 'd. ', $from ) . $this->lang[ 'Month_' . $month ] . ' ' . $year;
		
		/*$sql = "SELECT * FROM " . SHOWEVENT_ITEM_TABLE . " WHERE (" .
												"('$from' BETWEEN item_time_from_stamp AND item_time_to_stamp)OR" .
												"('$to' BETWEEN item_time_from_stamp AND item_time_to_stamp)OR" .
												"(item_time_from_stamp BETWEEN '$from' AND '$to')OR" .
												"(item_time_to_stamp BETWEEN '$from' AND '$to' )" .
												") AND item_language='" . $lang_loader->board_lang . "' AND item_isdeleted=0 ORDER BY item_isevent ASC, item_time_from_stamp ASC";*/
												
		$sql = "SELECT i.* FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . 
											SHOWEVENT_CAT2ITEM_TABLE . " c2i ON i.item_id=c2i.item_id LEFT JOIN " .
											SHOWEVENT_CATEGORY_TABLE . " c ON c2i.cat_id=c.cat_id " . 
											
											" WHERE (" .
												"('$from' BETWEEN i.item_time_from_stamp AND i.item_time_to_stamp)OR" .
												"('$to' BETWEEN i.item_time_from_stamp AND i.item_time_to_stamp)OR" .
												"(i.item_time_from_stamp BETWEEN '$from' AND '$to')OR" .
												"(i.item_time_to_stamp BETWEEN '$from' AND '$to' )" .
												") AND i.item_language='" . $lang_loader->board_lang . "' AND i.item_isdeleted=0 " .
												"AND c.cat_parent <> 1 AND c.cat_parent <> 18 " . 
												"ORDER BY i.item_isevent ASC, i.item_time_from_stamp DESC";
												
		if ( !$result = $db->sql_query( $sql ) )
		{
			$err = $errors->return_error( 'Error while fetching data', CRITICAL_ERROR );
			return array( $date, $err, $err );
		}
		$items = $db->sql_fetchrowset( $result );
		$events = $this->gui->parseItems( $items, 'events' );
		$shows = $this->gui->parseItems( $items, 'shows' );
		
		return array( $date, $events, $shows );
	}
	/**
	* all that is needed for all the various types of showing :)
	*/
	function show()
	{
		global $db, $errors, $lang_loader, $basic_gui, $Sajax, $board_config, $userdata, $mod_loader;
		
		$mode = ( isset( $_GET[ SUBMODE_URL ] ) ) ? $_GET[ SUBMODE_URL ] : 'front';
		
		// decide what to do
		switch ( $mode )
		{
			case 'singular':
				$id = ( isset( $_GET[ 'id' ] ) ) ? intval( $_GET[ 'id' ] ) : 0;
				// if comments enabled use them
				if ( $board_config[ 'showevent_usecomments' ] )
				{
					$mods = $mod_loader->getmodule( 'showevent_display', MOD_FETCH_MODE, NOT_ESSENTIAL );
					$mod_loader->port_vars( array( 'showevent_id' => $id ) );
					$mod_loader->execute_modules( 0, 'showevent_display' );
					$add = $mod_loader->get_vars( 'showevent_add' );
				}else
				{
					$add = '';
				}
				
				$galsql = "SELECT * FROM " . SHOWEVENT_IMAGES_TABLE . " WHERE item_id='$id' ORDER BY image_id";
				$sql = "SELECT i.*, c.cat_id, c.cat_parent, c.cat_name, cp.cat_id AS parent_id, cp.cat_name AS parent_name FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . SHOWEVENT_CAT2ITEM_TABLE . " c2i ON i.item_id=c2i.item_id LEFT JOIN " . SHOWEVENT_CATEGORY_TABLE . " c ON c2i.cat_id=c.cat_id LEFT JOIN " . SHOWEVENT_CATEGORY_TABLE . " cp ON c.cat_parent=cp.cat_id WHERE i.item_id='$id' LIMIT 1";
				$what = '';
				// some JS is needed for the gallery
				$basic_gui->add_JS( 'includes/ShowEvent.js' );
				// need this sajax function
				$Sajax->add2export( 'ShowEvent->submitinvite', '$name, $mail, $all, $item' );
				break;
				
			case 'category':
				$add = '';
				$this->del_cat();
				$cat = ( isset( $_GET[ 'cat' ] ) ) ? intval( $_GET[ 'cat' ] ) : 0;
				$show = ( isset( $_GET[ 'show' ] ) ) ? $_GET[ 'show' ] : 'nope';
				
				$isdel = ( $show == 'all' ) ? '' : "AND i.item_isdeleted='0'";
				
				$sql = array(
					"SELECT c.*, cp.cat_id AS parent_id, cp.cat_name AS parent_name FROM " . SHOWEVENT_CATEGORY_TABLE . " c LEFT JOIN " . SHOWEVENT_CATEGORY_TABLE . " cp ON c.cat_parent=cp.cat_id WHERE c.cat_id='$cat' OR c.cat_parent='$cat' ",
					"SELECT i.*, im.image_smallthumb as item_smallthumnail, im.image_thumbnail as item_thumbnail, c.cat_name, c.cat_id FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . SHOWEVENT_CAT2ITEM_TABLE . " c2i ON c2i.item_id=i.item_id  LEFT JOIN " . SHOWEVENT_IMAGES_TABLE . " im ON i.item_thumbnail=im.image_id LEFT JOIN " . SHOWEVENT_CATEGORY_TABLE . " c ON c2i.cat_id=c.cat_id WHERE c2i.cat_id='$cat' AND  i.item_language='" . $lang_loader->board_lang . "' $isdel",
				);
				$galsql = '';
				break;
				
			case 'front':
				$add = '';
				$this->del_cat();
				$what = '';
				$sql = array(
					"SELECT i.*, im.image_thumbnail as item_thumbnail FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . SHOWEVENT_IMAGES_TABLE . " im ON i.item_thumbnail=im.image_id LEFT JOIN " . SHOWEVENT_CAT2ITEM_TABLE . " c2i ON c2i.item_id=i.item_id WHERE i.item_language='" . $lang_loader->board_lang . "' AND i.item_isevent='0' AND c2i.cat_id='17' AND i.item_isdeleted='0' ORDER BY i.item_time_to_stamp DESC LIMIT " . $board_config[ 'showevent_front_shows' ],
					"SELECT i.*, im.image_thumbnail as item_thumbnail FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . SHOWEVENT_IMAGES_TABLE . " im ON i.item_thumbnail=im.image_id LEFT JOIN " . SHOWEVENT_CAT2ITEM_TABLE . " c2i ON c2i.item_id=i.item_id WHERE i.item_language='" . $lang_loader->board_lang . "' AND i.item_isevent='1' AND c2i.cat_id='17' AND i.item_isdeleted='0' ORDER BY i.item_time_to_stamp DESC LIMIT " . $board_config[ 'showevent_front_events' ],
				);
				$galsql = '';
				break;
				
			case 'overview':
				$add = '';
				$this->del_cat();
				$isevent = ( isset( $_GET[ 'event' ] ) ) ? intval( $_GET[ 'event' ] ) : 0;	
				$what = ( $isevent ) ? 'events' : 'shows';
				$actual = array( 
							( $isevent ) ? 'LEFT JOIN ' . SHOWEVENT_CAT2ITEM_TABLE . ' c2i ON i.item_id=c2i.item_id' : '',
							( $isevent ) ? 'AND c2i.cat_id=\'16\'' : '',
						);
				
				$basic_gui->set_level( 1, 'ShowEvent', '', array( array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=overview&event=' . $isevent, 'title' => ( $what == 'shows' ) ? $this->lang[ 'Show_shows' ] : $this->lang[ 'Show_events' ] ) ) );
				
				$sql = "SELECT i.*, im.image_thumbnail as item_thumbnail FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . SHOWEVENT_IMAGES_TABLE . " im ON i.item_thumbnail=im.image_id ${actual[ 0 ]} WHERE i.item_isevent='$isevent' AND i.item_language='" . $lang_loader->board_lang . "' AND i.item_isdeleted='0' ${actual[ 1 ]} ORDER BY i.item_time_to_stamp DESC";
				$galsql = '';
				break;
				
			case 'archive':
				$add = '';
				$this->del_cat();
				$what = '';
				$isevent = ( isset( $_GET[ 'event' ] ) ) ? intval( $_GET[ 'event' ] ) : 0;
				$year = ( isset( $_GET[ 'year' ] ) ) ? intval( $_GET[ 'year' ] ) : date( 'Y', EXECUTION_TIME );
				$month1 = ( isset( $_GET[ 'year' ]) ) ? 1 : date( 'n', EXECUTION_TIME );
				$month2 = ( isset( $_GET[ 'year' ]) ) ? 12 : date( 'n', EXECUTION_TIME );
				$month1 = ( isset( $_GET[ 'month' ] ) ) ? intval( $_GET[ 'month' ] ) : $month1;
				$month2 = ( isset( $_GET[ 'month' ] ) ) ? intval( $_GET[ 'month' ] ) : $month2;
				$from = mktime( 0, 0, 0, $month1, 1, $year );
				$to = mktime( 23, 59, 59, $month2, 31, $year );
				
				
				
				if ( isset( $_GET[ 'month' ] ) || !isset( $_GET[ 'year' ] ) )
				{
					$basic_gui->set_level( 3, 'ShowEvent', '', array(
								array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive', 'title' => $this->lang[ 'Archive' ] ),
								array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&event=' . $isevent . '&year=' . $year, 'title' => ( $isevent == 0 ) ? $this->lang[ 'Show_shows' ] . ' ' . $year : $this->lang[ 'Show_events' ] . ' ' . $year ),
								array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&event=' . $isevent . '&year=' . $year . '&month=' . $month1, 'title' => ( $isevent == 0 ) ? $this->lang[ 'Show_shows' ] . ' ' . $year . ' - ' . $this->lang[ 'Month_' . $month1 ] : $this->lang[ 'Show_events' ] . ' ' . $year . ' - ' . $this->lang[ 'Month_' . $month1 ] ),
							) );
				}else
				{
					$basic_gui->set_level( 2, 'ShowEvent', '', array( 
								array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive', 'title' => $this->lang[ 'Archive' ] ),
								array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&event=' . $isevent . '&year=' . $year, 'title' => ( $isevent == 0 ) ? $this->lang[ 'Show_shows' ] . ' ' . $year : $this->lang[ 'Show_events' ] . ' ' . $year ),
							) );
				}
				
				$sql = array(
					"SELECT item_time_from_stamp FROM " . SHOWEVENT_ITEM_TABLE . " WHERE item_language='" . $lang_loader->board_lang . "' AND item_isevent='$isevent' AND item_isdeleted='0' ORDER BY item_time_from_stamp ASC LIMIT 1",
					"SELECT * FROM " . SHOWEVENT_ITEM_TABLE . " WHERE item_isevent='$isevent' AND item_time_from_stamp BETWEEN '$from' AND '$to' AND item_language='" . $lang_loader->board_lang . "' AND item_isdeleted='0' ORDER BY item_time_from_stamp DESC"
				);
				$what = $isevent;
				break;
				
			case 'search':
				$add = '';
				$this->del_cat();
				$basic_gui->set_level( 1, 'ShowEvent', '', array( array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=search', 'title' => $this->lang[ 'Show_search' ] ) ) );
				
				$search = ( isset( $_POST[ 'search' ] ) ) ? strval( $_POST[ 'search' ] ) : '';
				if ( !$search )
				{
					$search = ( isset( $_GET[ 'search' ] ) ) ? strval( $_GET[ 'search' ] ) : '';
				}
				$sql = "SELECT i.*, im.image_smallthumb as item_smallthumb, im.image_thumbnail as item_thumbnail FROM " . SHOWEVENT_ITEM_TABLE . " i LEFT JOIN " . SHOWEVENT_IMAGES_TABLE . " im ON i.item_thumbnail=im.image_id WHERE i.item_language='" . $lang_loader->board_lang . "' AND ( i.item_title LIKE '%$search%' OR i.item_excerpt LIKE '%$search%' OR i.item_description LIKE '%$search%' ) AND i.item_isdeleted='0' ORDER BY i.item_time_to_stamp DESC";
				break;
			case 'unsubscribe':
				$id = ( isset( $_GET[ 'id' ] ) ) ? intval( $_GET[ 'id' ] ) : 0;
				$sql = "DELETE FROM " . SHOWEVENT_MAIL_TABLE . " WHERE mail_id='$id'";
				if ( !$res = $db->sql_query( $sql ) )
				{
					$errors->report_error( $this->lang[ 'Invite_unsubscribe_no' ], MESSAGE );
				}
				$errors->report_error( $this->lang[ 'Invite_unsubscribe_no' ], MESSAGE );
				
			default:
				$errors->report_error( 'Are you trying to hack?', CRITICAL_ERROR );
		}
		
		$items = array();
		
		if ( !is_array( $sql ) )
		{
			$sql = array( $sql );
		}
		$noshow = FALSE;
		foreach ( $sql as $query )
		{
			if ( !$result = $db->sql_query( $query) )
			{
				$errors->report_error( 'Error fetching data', CRITICAL_ERROR );
			}
			if ( $db->sql_numrows( $result ) != 0 )
			{
				//$errors->report_error( $this->lang[ 'No_show' ], MESSAGE );
				$items = array_merge( $items, $db->sql_fetchrowset( $result ) );
			}elseif ( empty( $items ) )
			{
				$noshow = TRUE;
			}
		}
		
		if ( !empty( $galsql ) )
		{
			if ( !$result = $db->sql_query( $galsql ) )
			{
				$errors->report_error( 'Error fetching data', CRITICAL_ERROR );
			}
			$gallery = array();
			while ( $row = $db->sql_fetchrow( $result ) )
			{
				$gallery[ $row[ 'image_id' ] ] = $row;
			}
		}else
		{
			$gallery = array();
		}
		
		$this->gui->show( $items, $mode, $what, $gallery, $add, $noshow );
	}
	/**
	* returns image info for a new image in the gallery
	*/
	function newimage( $point, $id, $by, $width )
	{
		global $db, $board_config, $basic_gui;
		
		$sql = "SELECT * FROM " . SHOWEVENT_IMAGES_TABLE . " WHERE item_id='$id' ORDER BY image_id";
		$result = $db->sql_query( $sql );
		$gallery = $db->sql_fetchrowset( $result );
		
		//$point -= 1;
		$point += $by*2;
		if ( $point < 0 )
		{
			$point = count( $gallery );
		}elseif( $point > count( $gallery ) )
		{
			$point = 0;
		}
		
		$image = $gallery[ $point ];
		$path1 = $basic_gui->get_URL() . '/' . $board_config[ 'showevent_imagepath' ] . '/' . $image[ 'image_image' ];
		$path2 = $basic_gui->get_URL() . '/' . $board_config[ 'showevent_imagepath' ] . '/' . $image[ 'image_mini' ];
		
		$html = "<img src=\"$path2\" />";		
		return array( $html, $by, $width );
	}
	/**
	* used to set what category the user is currently viewing to the session
	*/
	function set_cat( $id, $name )
	{
		$_SESSION[ 'show_cat' ] = array( 'id' => $id, 'name' => $name );
	}
	/**
	* used to remove the category info from session so it doesn't get stale
	*/
	function del_cat()
	{
		unset( $_SESSION[ 'show_cat' ] );
	}
	/**
	* adds an email to the invite list
	*/
	function submitinvite( $name, $mail, $all, $item )
	{
		global $security, $db, $lang_loader;
		
		$name = $security->parsevar( urldecode( $name ), ADD_SLASHES, TRUE );
		$mail = $security->parsevar( urldecode( $mail ), ADD_SLASHES, TRUE );
		$item = intval( $item );
		$all = intval( $all );
		$lang = $lang_loader->board_lang;
		
		$sql = "INSERT INTO " . SHOWEVENT_MAIL_TABLE . " ( mail_name, mail_mail, mail_item, mail_lang, mail_all ) VALUES ( '$name', '$mail', '$item', '$lang', '$all' )";
		if ( !$db->sql_query( $sql ) )
		{
			return array( 0, $this->lang[ 'Invite_no' ] );
		}else
		{
			return array( 1, $this->lang[ 'Invite_yes' ] );
		}
	}
	
	//
	// End of ShowEvent class
	//
}

?>