<?php

/**
*     defines the MoreContent class
*     @file                MoreContent.php
*     @see MoreContent
*/
/**
* it's a sort of wrapper for content that is basically static
*     @class		  MoreContent
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.4
*     @since        22nd February 2006
*     @package		     MoreContent
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
eval( Varloader::createclass( 'MoreContent', $vars, $visible ) );
// end class creation

class MoreContent extends MoreContent_def
{
	/**
	* constructor
	*/
	function MoreContent( $debug = FALSE )
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'MoreContent' => 'MoreContent' . tplEx
		) );
	}
	/**
	* shoves all the menu stuff to the template and loads it ...
	* the caching, that would fit lovely here, is left to the whole page caching of ClB
	* as time progressed more stuff was shoved into this function :)
	*/
	function display( $mitnews = FALSE )
	{
		global $template, $db, $errors, $security, $basic_gui, $mod_loader;
		
		$sql = "SELECT * FROM " . MORECONTENT_MENU_TABLE;
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
		}
		
		$mainmenus = array();
		$submenus = array();
		$menus = array();
		$menuid = ( isset( $_GET[ 'menuid' ] ) ) ? intval( $_GET[ 'menuid' ] ) : 0;
		$menuparent = 0;
		$issubmenu = TRUE;
		
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$m = array( 
				'TITLE' => $row[ 'menu_title' ], 
				'URL' => $security->append_sid( '?' . MODE_URL . '=MoreContent&menuid=' . $row[ 'menu_id' ] ),
				'TOOL' => $basic_gui->make_tooltip( $row[ 'menu_title' ], 'buttontip' )
			);
			if ( isset( $menus[ $row[ 'menu_parent' ] ] ) )
			{
				$menus[ $row[ 'menu_parent' ] ][ $row[ 'menu_id' ] ] = $m;
			}else
			{
				$menus[ $row[ 'menu_parent' ] ] = array( $row[ 'menu_id' ] => $m );
			}
			if ( $row[ 'menu_level' ] == 0 )
			{
				$mainmenus[ $row[ 'menu_id' ] ] = $m;
			}
			if ( $row[ 'menu_parent' ] == $menuid && $menuid != 0 )
			{
				$submenus[ $row[ 'menu_id' ] ] = $m;
			}
			if ( $row[ 'menu_id' ] == $menuid )
			{
				$issubmenu = ( $row[ 'menu_level' ] == 0 ) ? FALSE : TRUE;
				if ( $issubmenu )
				{
					$menuparent = $row[ 'menu_parent' ];
				}
			}
		}
		
		if ( $menuid != 0 && !$mitnews )
		{
			$sql = "SELECT content FROM " . MORECONTENT_CONTENT_TABLE . " WHERE menu_id='$menuid' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
			}
			$content = $db->sql_fetchfield( 'content' );
			
			$content = $template->justcompile( $content, TRUE );
			
			if ( !empty( $submenus ) )
			{
				foreach ( $submenus as $m )
				{
					$template->assign_block_vars( 'subrow', '', $m );
					$template->assign_switch( 'subrow', TRUE );
				}
			}else
			{
				if ( $issubmenu )
				{
					$sql = "SELECT * FROM " . MORECONTENT_MENU_TABLE . " WHERE menu_parent='$menuparent'";
					if ( !$result = $db->sql_query( $sql ) )
					{
						$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
					}
					while( $row = $db->sql_fetchrow( $result ) )
					{
						$template->assign_block_vars( 'subrow', '', array(
							'TITLE' => $row[ 'menu_title' ], 
							'URL' => $security->append_sid( '?' . MODE_URL . '=MoreContent&menuid=' . $row[ 'menu_id' ] ),
							'SEL' => ( $row[ 'menu_id' ] == $menuid ) ? 1 : 0,
							'TOOL' => $basic_gui->make_tooltip( $row[ 'menu_title' ], 'buttontip' ),
						) );
						$template->assign_switch( 'subrow', TRUE );
					}
				}
			}
			
			$basic_gui->set_title( $menus[ $menuid ][ 'TITLE' ] );
		}
		
		if ( !$mitnews )
		{
			$mods = $mod_loader->getmodule( 'news', MOD_FETCH_NAME, NOT_ESSENTIAL );
			$mod_loader->port_vars( array(  ) );
			$mod_loader->execute_modules( 0, 'fetchnews' );
			$news = $mod_loader->get_vars( array( 'news_HTML' ) );
		}else
		{
			$news = array( 'news_HTML' => '' );
		}
		
		$template->assign_var_levels( '', 'MORECONTENT', array(
			'MAINMENUS' => $mainmenus,
			'SUBMENUS' => $submenus,
			'MENUS' => $menus,
			'CONTENT' => ( !$mitnews ) ? $content : '',
			'ISSUBMENU' => intval( $issubmenu ),
			'NEWS' => $news[ 'news_HTML' ],
			'PARENT' => ( $menuparent == 0 ) ? $menuid : $menuparent,
		) );
	}
	/**
	* deals with displaying when news are being displayed
	* oh my has this turned out to be a very project specific module ...
	*/
	function newsdisp()
	{
		// yeah yeah, make it a mere wrapper
		$this->display( TRUE );
	}
	
	
	//
	// End of filebrowser class
	//
}

?>