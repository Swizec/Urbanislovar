<?php

/**
*     defines the flexibase class
*     @file                flexibase_gui.php
*     @see flexibase_gui
*/
/**
* gui for the flexibase module
*     @class		  flexibase_gui
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since       31st January 2007
*     @package		     flexibase
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
eval( Varloader::createclass( 'flexibase_gui', $vars, $visible ) );
// end class creation

class flexibase_gui extends flexibase_gui_def
{
	function flexibase_gui()
	{
		global $template, $Sajax;
		
		// open up the tpl file
		$template->assign_files( array(
			'flexibase' => 'flexibase' . tplEx
		) );
		
		$this->thumbnames = array( 'preview', 'thumbnail', 'thm', 'thmb', 'thumb', 'prev' );
	}
	/**
	* displays the list thing
	*/
	function listitems( $items, $base, $baseid, $page )
	{
		global $template, $basic_gui, $Cl_root_path, $security;
		
		// try to find a visual representation field
		$preview = '';
		$list = strtolower( $base[ 'fieldlist' ] );
		foreach ( $this->thumbnames as $suggest )
		{
			if ( strpos( $list, $suggest . '::blob' ) !== FALSE )
			{ // we can safely assume this is a thumbnail of sorts
				$preview = $suggest;
				break;
			}
		}
		$name = $this->getname( $base[ 'fieldlist' ] );
		
		// go through the items
		if ( is_array( $items ) )
		{
			foreach ( $items as $i => $item )
			{
				// make the thumbnail if available
				if ( !empty( $preview ) )
				{
					// write it to disk, or at least try
					$f = @fopen( $Cl_root_path . 'cache/flexibase_image_' . $i . '.jpg', 'w' );
					@fwrite( $f, $item[ $preview ] );
					@fclose( $f );
					$uthumb = $Cl_root_path . 'cache/flexibase_image_' . $i . '.jpg';
				}else
				{
					$uthumb = '';
				}
				
				$template->assign_block_vars( 'itemlist', '', array(
					'U_THUMB' => $uthumb . '?' . EXECUTION_TIME,
					'U_LINK' => $security->append_sid( '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=item&baseid=' . $baseid . '&itemid=' . $item[ 'id' ] ),
					
					'L_NAME' => $item[ $name ],
				) );
				$template->assign_switch( 'itemlist', TRUE );
			}
		}
		
		// go through the fields and make the search form
		$searchL = '<b>' . $this->lang[ 'General' ] . ': </b><input type="text" id="search__" /><br />';
		$searchR = '';
		$searchargs = '\'search__\'';
		foreach ( explode( ':::', $base[ 'fieldlist' ] ) as $i => $field )
		{
			$field = explode( '::', $field );
			
			if ( $field[ 1 ] == 'blob' )
			{ // can't search in these
				continue;
			}
			
			$add = '<b>' . $field[ 0 ] . ': </b><input type="text" id="search' . $field[ 0 ] . '" /><br />';
			$searchargs .= ', \'search' . $field[ 0 ] . '\'';
			
			if ( $i % 2 == 0 )
			{ // right
				$searchR .= $add;
			}else
			{ // left
				$searchL .= $add;
			}
		}
		
		if ( isset( $_GET[ 'search' ] ) )
		{ // so listing through results can be achieved
			$search = 'search=' . $_GET[ 'search' ] . '&';
		}
		
		$template->assign_block_vars( 'items', '', array(
			'L_TITLE' => $base[ 'name' ],
			'L_EMPTY' => $this->lang[ 'Empty' ],
			'L_PERPAGE' => $this->lang[ 'Perpage' ],
			'L_PAGE' => $this->lang[ 'Page' ],
			'L_SEARCH' => $this->lang[ 'Search' ],
			
			'SEARCHR' => $searchR,
			'SEARCHL' => $searchL,
			'SEARCHARGS' => $searchargs,
			'SHOWEMPTY' => ( is_array( $items ) ) ? 0 :1,
			'DESCRIPTION' => $base[ 'description' ],
			'PERPAGE' => $page[ 0 ],
			'VALCOUNT' => $page[ 2 ],
			'VALSTART' => $page[ 1 ],
			'VALURL' => $security->append_sid( '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=' . $baseid . '&' . $search . 'showfrom=SHOWFROM&shownum=SHOWNUM' ),
			'VALSURL' => $security->append_sid( '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=' . $baseid . '&search=SEARCHARGS' )
		) );
		$template->assign_switch( 'items', TRUE );
		
		$basic_gui->add_file( 'flexibase' );
	}
	/**
	* returns name of the first varchar
	*/
	function getname( $list )
	{
		// use the first varchar as title
		$name = '';
		foreach ( explode( ':::', $list ) as $field )
		{
			$field = explode( '::', $field );
			if ( $field[ 1 ] == 'varchar' )
			{
				$name = $field[ 0 ];
				break;
			}
		}
		
		return $name;
	}
	/**
	* displays one item
	*/
	function showitem( $item, $base, $baseid, $itemid )
	{
		global $template, $basic_gui, $Cl_root_path, $security, $mod_loader;
		
		$name = $item[ $this->getname( $base[ 'fieldlist' ] ) ];
		
		foreach ( explode( ':::', $base[ 'fieldlist' ] ) as $i => $field )
		{
			$field = explode( '::', $field );
			
			if ( empty( $item[ $field[ 0 ] ] ) )
			{ // now what would we do with this, really
				continue;
			}
			
			if ( $field[ 1 ] == 'blob' )
			{ // do the image stuff
				if (  in_array( $field[ 0 ], $this->thumbnames ) )
				{ // not an image this one
					continue;
				}
				
				// make the image ya
				$f = @fopen( $Cl_root_path . 'cache/flexibase_image_' . $i . '.jpg', 'w' );
				@fwrite( $f, $item[ $field[ 0 ] ] );
				@fclose( $f );
				
				// add a popup for it
				$uimg = $basic_gui->get_URL() . '/cache/flexibase_image_' . $i . 'R.jpg?' . EXECUTION_TIME;
				$clickme = $basic_gui->add_pop( '<div style="text-align: center; vertical-align: middle"><img src="' . $uimg . '" /></a>', 100, 100, 850, 650 );
				
				$template->assign_block_vars( 'imagelist', '', array(
					'NAME' => $field[ 0 ],
					'IMAGE' => 'flexibase_image_' . $i . '.jpg',
					'CLICK' => $clickme,
				) );
				$template->assign_switch( 'imagelist', TRUE );
			}else
			{ // display a normal field
				$template->assign_block_vars( 'fieldlist', '', array(
					'NAME' => str_replace( '_', ' ', $field[ 0 ] ),
					'VALUE' => $item[ $field[ 0 ] ],
					'TYPE' => $field[ 1 ],
				) );
				$template->assign_switch( 'fieldlist', TRUE );
			}
		}
		
		// maybe somebody has something to say (hopefully the comments module
		$mods = $mod_loader->getmodule( 'flexibase_show', MOD_FETCH_MODE, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'item_id' => $baseid . $itemid ) );
		$mod_loader->execute_modules( 0, 'flexibase_show' );
		$add = $mod_loader->get_vars( 'flexibase_add' );
		
		$template->assign_block_vars( 'item', '', array(
			'L_TITLE' => $name,
			'L_BACK' => $this->lang[ 'Back' ],
			
			'U_BACK' => $security->append_sid( '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=' . $baseid ),
			
			'ADD' => $add,
		) );
		$template->assign_switch( 'item', TRUE );
		
		$basic_gui->add_file( 'flexibase' );
	}
	/**
	* shows the index I guess
	*/
	function showindex( $bases )
	{
		global $template, $security, $basic_gui;
		
		foreach ( $bases as $base )
		{
			$desc = strip_tags( $base[ 'description' ] );
		
			$template->assign_block_vars( 'baserow', '', array(
				'U_BASE' => $security->append_sid( '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=' . $base[ 'id' ] ),
				'TITLE' => $base[ 'name' ],
				'DESC' => ( strlen( $desc ) > 100 ) ? substr( $desc, 0, 100 ) . '...' : $desc,
			) );
			$template->assign_switch( 'baserow', TRUE );
		}
		
		$template->assign_switch( 'index', TRUE );
		
		$basic_gui->add_file( 'flexibase' );
	}


	//
	// End of flexibase-gui class
	//
}


?>