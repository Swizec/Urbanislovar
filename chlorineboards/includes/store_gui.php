<?php

/**
*     defines the ACP_store class
*     @file                store_gui.php
*     @see store_gui
*/
/**
* gui for the store module
*     @class		  store_gui
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since       3rd March 2007
*     @package		     store
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
eval( Varloader::createclass( 'store_gui', $vars, $visible ) );
// end class creation

class store_gui extends store_gui_def
{
	function store_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'store' => 'store' . tplEx
		) );
	}
	/**
	* shows the store in a lovable fashion
	*/
	function show( $items, $categories, $cat, $from, $many, $count, $mode )
	{
		global $template, $basic_gui, $security, $board_config;
		
		$uPrev = $security->append_sid( '?' . MODE_URL . '=store&cat=' . $cat . '&page=' . floor( ($from-$many)/$many ) );
		$Prev = ( $from >=  $many ) ? 1 : 0;
		
		$uNext = $security->append_sid( '?' . MODE_URL . '=store&cat=' . $cat . '&page=' . floor( ($from+$many)/$many ) );
		$Next = ( $from <=  $count-$many ) ? 1 : 0;
		
		$template->assign_block_vars( 'store', '', array(
			'L_PREV' => $this->lang[ 'Store_prev' ],
			'L_NEXT' => $this->lang[ 'Store_next' ],
			'L_NOTHING' => $this->lang[ 'Store_nothing' ],
			'L_INTRO' => $this->lang[ 'Store_intro' ],
			
			'U_PREV' => $uPrev,
			'U_NEXT' => $uNext,
			
			'PREV' => $Prev,
			'NEXT' => $Next,
			'NOTHING' => intval( count( $items ) == 0 ),
			'MODE' => $mode
		) );
		$template->assign_switch( 'store', TRUE );
		
		if ( is_array( $categories ) )
		{
			foreach ( $categories as $category )
			{
				if ( $cat == $category[ 'cat_id' ] )
				{
					$basic_gui->set_level( 2, 'Store', '', array( array( 'URL' => '?' . MODE_URL . '=store&cat=' . $category[ 'cat_id' ], 'title' => $category[ 'cat_name' ] ) ) );
				}
				$template->assign_block_vars( 'catrow', '', array(
					'U_CAT' => $security->append_sid( '?' . MODE_URL . '=store&cat=' . $category[ 'cat_id' ] ),
					'L_CAT' => $category[ 'cat_name' ],
					'SELECTED' => ( $cat == $category[ 'cat_id' ] ) ? 1 : 0,
				) );
				$template->assign_switch( 'catrow', TRUE );
			}
		}
		
		if ( is_array( $items ) )
		{
			foreach ( $items as $item )
			{
				$image = $basic_gui->get_URL() . '/' . $board_config[ 'store_imagepath' ] . '/' . $item[ 'image_image' ];
				$basic_gui->add_pop( '<img src="' . $image . '" />' );
				$template->assign_block_vars( 'itemrow', '', array(
					'ID' =>$item[ 'item_id' ],
					'TITLE' => $item[ 'item_title' ],
					'EXCERPT' => substr( $item[ 'item_description' ], 0, $board_config[ 'store_shortDescription' ] ),
					'DESCRIPTION' => $item[ 'item_description' ],
					'PRICE' => $item[ 'item_price' ],
					'THUMBNAIL' => $basic_gui->get_URL() . '/' . $board_config[ 'store_imagepath' ] . '/' . $item[ 'image_thumbnail' ],
					'MINI' => $basic_gui->get_URL() . '/' . $board_config[ 'store_imagepath' ] . '/' . $item[ 'image_mini' ],
					'IMAGE' => $image,
					'HEIGHT' => $board_config[ 'store_thumb_height' ],
				) );
				$template->assign_switch( 'itemrow', TRUE );
			}
		}
		
		$basic_gui->add_file( 'store' );
	}

	//
	// End of store-gui class
	//
}


?>