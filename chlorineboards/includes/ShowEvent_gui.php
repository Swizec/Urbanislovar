<?php

/**
*     defines the ACP_ShowEvent class
*     @file                ShowEvent_gui.php
*     @see ShowEvent_gui
*/
/**
* gui for the ShowEvent module
*     @class		  ShowEvent_gui
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.2
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

// class creation
$vars = array( );
$visible = array( );
eval( Varloader::createclass( 'ShowEvent_gui', $vars, $visible ) );
// end class creation

class ShowEvent_gui extends ShowEvent_gui_def
{
	function ShowEvent_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'ShowEvent' => 'ShowEvent' . tplEx
		) );
	}
	/**
	* I wonder what
	*/
	function show_calendar( $calendar )
	{
		global $template, $basic_gui;
		
		$basic_gui->set_level( 1, 'calendar' );
		
		$template->assign_block_vars( 'calendar', '', array(
			'L_BACK' => $this->lang[ 'Calendar_back' ],
			'L_FORTH' => $this->lang[ 'Calendar_forth' ],
			'L_EVENTS' => $this->lang[ 'Calendar_events' ],
			'L_SHOWS' => $this->lang[ 'Calendar_shows' ],
			
			'DAY' => date( 'j', EXECUTION_TIME ),
			'MONTH' => date( 'n', EXECUTION_TIME ),
			'YEAR' => date( 'Y', EXECUTION_TIME ),
			'MONTH1' => $calendar[ 0 ],
			'MONTH2' => $calendar[ 1 ],
		) );
		$template->assign_switch( 'calendar', TRUE );
		
		$basic_gui->add_file( 'ShowEvent' );
	}
	/**
	* parses items
	*/
	function parseItems( &$items, $what )
	{
		global $template, $security;
		
		$template->clear( 'item' );
		$template->assign_files( array(
			$what => 'ShowEvent_infoItem' . tplEx
		) );
		
		if ( is_array( $items ) )
		{
			foreach ( $items as $i => $item )
			{
				$NO = FALSE;
				switch( $what )
				{
					case 'shows':
						
						if ( $item[ 'item_isevent' ] )
						{
							$NO = TRUE;
						}

						break;
					case 'events':
						if ( !$item[ 'item_isevent' ] )
						{
							$NO = TRUE;
						}
				}
				if ( !$NO )
				{
					$arr = array();
					foreach ( array_keys( $item ) as $key )
					{
						$arr[ strtoupper( substr( $key, 5 ) ) ] = $item[ $key ];
					}
					$arr[ 'URL' ] = $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=' . $item[ 'item_id' ] );
					$template->assign_block_vars( 'item', '', $arr );
					$template->assign_switch( 'item', TRUE );
				}
			}
		}
		
		return $template->justcompile( $what );
	}
	/**
	* deals with choosing what to do when showing stuff
	*/
	function show( $items, $mode, $what='', $gallery=array(), $add = '', $noshow = FALSE )
	{
		global $basic_gui, $errors;
		
		if ( $noshow )
		{ // in some cases having nothing to show needs an error
			switch( $mode )
			{
				case 'front':
				case 'singular':
				case 'overview':
				case 'search':
				case 'category':
					$errors->report_error( $this->lang[ 'No_show' ], GENERAL_ERROR );
					break;
			}
		}
		
		switch( $mode )
		{
			case 'front':
				$this->front( $items );
				break;
			case 'singular':
				$this->singular( $items, $gallery, $add );
				break;
			case 'overview':
				$this->overview( $items, $what );
				break;
			case 'archive':
				$this->archive( $items, $what, $noshow );
				break;
			case 'search':
				$this->listE( $items, $this->lang[ 'Show_search' ], 'search' );
				break;
			case 'category':
				$catID = $items[ 0 ][ 'cat_id' ];
				$catName = $items[ 0 ][ 'cat_name' ];
				$parID = $items[ 0 ][ 'parent_id' ];
				$parName = $items[ 0 ][ 'parent_name' ];
				if ( empty( $parID ) || empty( $parName ) )
				{
					$basic_gui->set_level( 1, 'ShowEvent', '', array( array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=' . $catID, 'title' => $catName ) ) );
				}else
				{
					$basic_gui->set_level( 2, 'ShowEvent', '', array( 
												array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=' . $parID, 'title' => $parName ),
												array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=' . $catID, 'title' => $catName ) ) );
				}
				$this->funct->set_cat( $catID, $catName );
				$this->listE( $items, $catName, 'category' );
				break;
		}
	}
	/**
	* shows a single show or event
	*/
	function singular( $items, $gallery, $add )
	{
		global $basic_gui, $template, $board_config, $basic_gui, $userdata, $security, $Cl_root_path4template, $Cl_root_path;
	
		$item = $items[ 0 ];
		
		$basic_gui->set_level( 2, 'ShowEvent', '', array( 
							array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=' . $item[ 'parent_id' ], 'title' => $item[ 'parent_name' ] ),
							array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=' . $item[ 'cat_id' ], 'title' => $item[ 'cat_name' ] ) 
						) );
		// $basic_gui->set_level( 2, 'ShowEvent', '', array( array( 'URL' => '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=' . $id, 'title' => $item[ 'item_title' ] ) ) );
		
		$arr = array();
		foreach ( array_keys( $item ) as $key )
		{
			$arr[ strtoupper( substr( $key, 5 ) ) ] = $item[ $key ];
		}
		$arr[ 'TIME_FROM_STAMP' ] = date( $userdata[ 'user_timeformat' ], $arr[ 'TIME_FROM_STAMP' ] );
		$arr[ 'TIME_TO_STAMP' ] = date( $userdata[ 'user_timeformat' ], $arr[ 'TIME_TO_STAMP' ] );
		
		$images = '';
		//$im = '<a href="' . $basic_gui->get_URL() . '/' . $board_config[ 'showevent_imagepath' ] . '/%s" target="_blank" border="0"><img src="' . $basic_gui->get_URL() . '/' . $board_config[ 'showevent_imagepath' ] . '/%s" border="0" /></a>';
		$im = '<a href="#" %s border="0"><img src="' . $basic_gui->get_URL() . '/' . $board_config[ 'showevent_imagepath' ] . '/%s" border="0" /></a>';
		$cont = '<div style="text-align: center; vertical-align: middle"><img src="' . $basic_gui->get_URL() . '/' . $board_config[ 'showevent_imagepath' ] . '/%s" border="0"/></div>';
		foreach ( $gallery as $image )
		{
			//$images .= sprintf( $im, $image[ 'image_image' ], $image[ 'image_mini' ] );
			$size = getimagesize( $Cl_root_path . $board_config[ 'showevent_imagepath' ] . '/' . $image[ 'image_image' ] );
			$pop = $basic_gui->add_pop( sprintf( $cont, $image[ 'image_image' ] ), '50%', '50%', $size[ 0 ], $size[ 1 ] );
			$images .= sprintf( $im, $pop, $image[ 'image_mini' ] );
		}
		
		$template->assign_block_vars( 'singular', '', array_merge( $arr, array(
			'L_LOCATION' => $this->lang[ 'Show_location' ],
			'L_FROM' => $this->lang[ 'Show_from' ],
			'L_TO' => $this->lang[ 'Show_to' ],
			'L_PRICE' => $this->lang[ 'Show_price' ],
			'L_DURATION' => $this->lang[ 'Show_duration' ],
			'L_BACK' => $this->lang[ 'Show_back' ],
			'L_FORTH' => $this->lang[ 'Show_forth' ],
			'L_SCHEDULE' => $this->lang[ 'Show_schedule' ],
			'L_ADDITIONAL' => $this->lang[ 'Show_additional' ],
			'L_MORE' => $this->lang[ 'More' ],
			'L_LESS' => $this->lang[ 'Less' ],
			'INVITE' => array( 
				'L_TITLE' => $this->lang[ 'Invite_title' ],
				'L_VERBOSE' => $this->lang[ 'Invite_verbose' ],
				'L_NAME' => $this->lang[ 'Invite_name' ],
				'L_MAIL' => $this->lang[ 'Invite_mail' ],
				'L_ALL' => $this->lang[ 'Invite_all' ],
				'L_LOADING' => $this->lang[ 'Invite_loading' ],
				'L_ALREADY' => $this->lang[ 'Invite_already' ],
				'L_NONO' => $this->lang[ 'Invite_nono' ],
				'L_INVITE' => $this->lang[ 'Invite_invite' ],
				
				'ALREADY' => ( $item[ 'item_time_from_stamp' ] <= EXECUTION_TIME ) ? 1 : 0,
				'ID' => $item[ 'item_id' ],
			),
			
			'PATH' => $board_config[ 'showevent_imagepath' ],
			'IMAGES' => $images,
			'MAXNUM' => count( $gallery ),
			'NUM' => 1,
			'WIDTH' => $board_config[ 'showevent_mini_width' ],
			'WIDTH2' => $board_config[ 'showevent_mini_width' ]*2+20,
			'BIGWIDTH' => $board_config[ 'showevent_mini_width' ] * 3 + 30,
			'BIGWIDTH2' => $board_config[ 'showevent_mini_width' ] * count( $gallery ),
			'HEIGHT' => $board_config[ 'showevent_mini_height' ],
			'ADD' => $add,
			'ADD2' => ( empty( $add ) ) ? 0 : 1,
			'NOINVITE' => ( $item[ 'cat_id' ] == 1 || $item[ 'cat_parent' ] == 1 ) ? 1 : 0,
		) ) );
		$template->assign_switch( 'singular', TRUE );
		
		$basic_gui->add_file( 'ShowEvent' );
	}
	/**
	* shows the overview, go figure
	*/
	function overview( $items, $what )
	{
		global $basic_gui, $template, $board_config, $userdata, $security;
		
		$template->assign_block_vars( 'overview', '', array(
			'L_TITLE' => $this->lang[ 'Show_' . $what ],
			'L_LOCATION' => $this->lang[ 'Show_location' ],
			'L_FROM' => $this->lang[ 'Show_from' ],
			'L_TO' => $this->lang[ 'Show_to' ],
			'L_PRICE' => $this->lang[ 'Show_price' ],
			'L_DURATION' => $this->lang[ 'Show_duration' ],
			'L_MORE' => $this->lang[ 'Show_more' ],
			'L_SCHEDULE' => $this->lang[ 'Show_schedule' ],
			'L_ADDITIONAL' => $this->lang[ 'Show_additional' ],
			'L_EMPTY' => $this->lang[ 'No_show' ],
			
			'WHAT' => $what,
			'EMPTY' => ( empty( $items ) ) ? 1 : 0,
		) );		
		$template->assign_switch( 'overview', TRUE );
		
		$template->assign_files( array(
			'shows' => 'ShowEvent_small_' . $what . tplEx
		) );
		
		foreach ( $items as $item )
		{
			$arr = array();
			foreach ( array_keys( $item ) as $key )
			{
				$arr[ strtoupper( substr( $key, 5 ) ) ] = $item[ $key ];
			}
			$arr[ 'PATH' ] = $board_config[ 'showevent_imagepath' ];
			$arr[ 'URL' ] = $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=' . $item[ 'item_id' ] );
			$arr[ 'TIME_FROM_STAMP' ] = date( $userdata[ 'user_timeformat' ], $arr[ 'TIME_FROM_STAMP' ] );
			$arr[ 'TIME_TO_STAMP' ] = date( $userdata[ 'user_timeformat' ], $arr[ 'TIME_TO_STAMP' ] );
			
			$template->assign_block_vars( 'itemrow', '', $arr );
			$template->assign_switch( 'itemrow', TRUE );
		}
		
		$basic_gui->add_file( 'ShowEvent' );
	}
	/**
	* deals with displaying the front page
	*/
	function front( $items )
	{
		global $template, $basic_gui, $board_config, $security, $userdata;
		
		$template->assign_block_vars( 'front', '', array(
			'L_EVENTS' => $this->lang[ 'Show_events' ],
			'L_SHOWS' => $this->lang[ 'Show_shows' ],
			'L_LOCATION' => $this->lang[ 'Show_location' ],
			'L_FROM' => $this->lang[ 'Show_from' ],
			'L_TO' => $this->lang[ 'Show_to' ],
			'L_PRICE' => $this->lang[ 'Show_price' ],
			'L_DURATION' => $this->lang[ 'Show_duration' ],
			'L_MORE' => $this->lang[ 'Show_more' ],
		) );
		$template->assign_switch( 'front', TRUE );
		
		// now do that thingy
		foreach ( $items as $item )
		{
			$arr = array();
			foreach ( array_keys( $item ) as $key )
			{
				$arr[ strtoupper( substr( $key, 5 ) ) ] = $item[ $key ];
			}
			$arr[ 'PATH' ] = $board_config[ 'showevent_imagepath' ];
			$arr[ 'URL' ] = $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=' . $item[ 'item_id' ] );
			$arr[ 'TIME_FROM_STAMP' ] = date( $userdata[ 'user_timeformat' ], $arr[ 'TIME_FROM_STAMP' ] );
			$arr[ 'TIME_TO_STAMP' ] = date( $userdata[ 'user_timeformat' ], $arr[ 'TIME_TO_STAMP' ] );
			
			$itemrow = ( $item[ 'item_isevent' ] ) ? 'eventrow' : 'showrow';
			
			$template->assign_block_vars( $itemrow, '', $arr );
			$template->assign_switch( $itemrow, TRUE );
		}
		
		$basic_gui->add_file( 'ShowEvent' );
	}
	/**
	* displays the archive
	*/
	function archive( $items, $isevent, $noshow )
	{
		global $security, $template, $userdata, $basic_gui;
		
		if ( !empty( $items ) )
		{
			// get the first year to go back to
			$from = array_shift( $items );
			$from = date( 'Y', $from[ 'item_time_from_stamp' ] );
			$to = date( 'Y', EXECUTION_TIME );
			
			for ( $i = $to; $i >= $from; $i-- )
			{
				$months = '';
				for ( $j = 1; $j <= 12; $j++ )
				{
					$uri = $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&event=' . $isevent . '&year=' . $i . '&month=' . $j );
					$months .= '<a href="' . $uri . '">' . $this->lang[ 'MonthS_' . $j ] . '</a> / ';
				}
				$template->assign_block_vars( 'linklist', '', array(
					'L_LINK' => sprintf( $this->lang[ 'Archive_link' ], $i ),
					'U_LINK' => $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=archive&year=' . $i . '&event=' . $isevent ),
					'MONTHS' => $months,
				) );
				$template->assign_switch( 'linklist', TRUE );
			}
		
			foreach ( $items as $item )
			{
				$arr = array();
				foreach ( array_keys( $item ) as $key )
				{
					$arr[ strtoupper( substr( $key, 5 ) ) ] = $item[ $key ];
				}
				$arr[ 'URL' ] = $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=' . $item[ 'item_id' ] );
				$arr[ 'TIME_FROM_STAMP' ] = date( 'd.m.', $arr[ 'TIME_FROM_STAMP' ] );
				$arr[ 'TIME_TO_STAMP' ] = date( 'd.m.y', $arr[ 'TIME_TO_STAMP' ] );
				
				$template->assign_block_vars( 'itemrow', '', $arr );
				$template->assign_switch( 'itemrow', TRUE );
			}
		}
		
		$template->assign_block_vars( 'archive', '', array(
			'L_EVENTS' => $this->lang[ 'Show_events' ],
			'L_SHOWS' => $this->lang[ 'Show_shows' ],
			'L_LOCATION' => $this->lang[ 'Show_location' ],
			'L_FROM' => $this->lang[ 'Show_from' ],
			'L_TO' => $this->lang[ 'Show_to' ],
			'L_PRICE' => $this->lang[ 'Show_price' ],
			'L_DURATION' => $this->lang[ 'Show_duration' ],
			'L_MORE' => $this->lang[ 'Show_more' ],
			'L_NOSHOW' => $this->lang[ 'No_show' ],
			'NOSHOW' => ( $noshow ) ? 1 : 0,
		) );
		$template->assign_switch( 'archive', TRUE );
		
		$basic_gui->add_file( 'ShowEvent' );
	}
	/**
	* lists items for whatever use needed
	*/
	function listE( $items, $title, $block = 'list' )
	{
		global $basic_gui, $template, $board_config, $userdata, $security;
		
		if ( $block == 'category' )
		{
			$this->cat_stuff( $items, $items[ 0 ][ 'cat_id' ] );
			
			$info = $template->blockinfo( 'catrow' );
			if ( $info[ 0 ] )
			{
				$block = 'list';
				$template->assign_block_vars( $block, '', array(
					'CATEGORY' => 1,
					'IMG' => $board_config[ 'showevent_imagepath' ] . '/' . $items[ 0 ][ 'cat_image' ],
					'DESCRIPTION' => $items[ 0 ][ 'cat_description' ],
				) );
			}else
			{
				array_shift( $items );
				$this->overview( $items, ( $items[ 0 ][ 'item_isevent' ] ) ? 'events' : 'shows' );
				return;
			}
		}
		
		$template->assign_block_vars( $block, '0', array(
			'L_TITLE' => $title,
			'L_LOCATION' => $this->lang[ 'Show_location' ],
			'L_FROM' => $this->lang[ 'Show_from' ],
			'L_TO' => $this->lang[ 'Show_to' ],
			'L_PRICE' => $this->lang[ 'Show_price' ],
			'L_DURATION' => $this->lang[ 'Show_duration' ],
			'L_MORE' => $this->lang[ 'Show_more' ],
			'L_SCHEDULE' => $this->lang[ 'Show_schedule' ],
			'L_ADDITIONAL' => $this->lang[ 'Show_additional' ],
			'L_EMPTY' => $this->lang[ 'No_show' ],
		) );
		$template->assign_switch( $block, TRUE );
		
		if ( empty( $items ) )
		{
			$template->assign_block_vars( $block, '0', array(
				'EMPTY' => 1
			) );
		}
		
		foreach ( $items as $item )
		{
			if ( !isset( $item[ 'item_id' ] ) )
			{
				continue;
			}
			$arr = array();
			foreach ( array_keys( $item ) as $key )
			{
				$arr[ strtoupper( substr( $key, 5 ) ) ] = $item[ $key ];
			}
			$arr[ 'PATH' ] = $board_config[ 'showevent_imagepath' ];
			$arr[ 'URL' ] = $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=singular&id=' . $item[ 'item_id' ] );
			$arr[ 'TIME_FROM_STAMP' ] = date( $userdata[ 'user_timeformat' ], $arr[ 'TIME_FROM_STAMP' ] );
			$arr[ 'TIME_TO_STAMP' ] = date( $userdata[ 'user_timeformat' ], $arr[ 'TIME_TO_STAMP' ] );
			
			$template->assign_block_vars( 'itemrow', '', $arr );
			$template->assign_switch( 'itemrow', TRUE );
		}
		
		$basic_gui->add_file( 'ShowEvent' );
	}
	/**
	* the category needs some stuff to be set eh
	*/
	function cat_stuff( $items, $no )
	{
		global $board_config, $security, $template;
		
		foreach ( $items as $item )
		{
			if ( !isset( $item[ 'cat_name' ] ) || $item[ 'cat_id' ] == $no )
			{ // we rely on the fact that categories come first
				continue;
			}
			$arr = array();
			foreach ( array_keys( $item ) as $key )
			{
				$arr[ strtoupper( substr( $key, 4 ) ) ] = $item[ $key ];
			}
			$arr[ 'PATH' ] = $board_config[ 'showevent_imagepath' ];
			$arr[ 'URL' ] = $security->append_sid( '?' . MODE_URL . '=showevent&' . SUBMODE_URL . '=category&cat=' . $item[ 'cat_id' ] );
			
			$template->assign_block_vars( 'catrow', '', $arr );
			$template->assign_switch( 'catrow', TRUE );
		}
	}

	//
	// End of ShowEvent-gui class
	//
}


?>