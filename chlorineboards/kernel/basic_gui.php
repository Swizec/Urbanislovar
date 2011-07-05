<?php

/**
*     defines the Basic_gui class
*     @file                basic_gui.php
*     @see Basic_gui
*/
/**
* the basic gui with the general stuff
*     @class		   Basic_gui
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.9.44
*     @since        19th June 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
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
// guis :: array of all the files to output
// title :: appendix for site title
// pagination :: pagination upper level
// config :: contents of the cfg file
// pagi_conf :: contents of the pagination config
// lastpop :: next available popup id
// popups :: array with the popups
// meta_tags :: stores the meta part of the header
// copyright :: stores the copyright notice
// JS_list :: stores customly added JS
// CSS_list :: stores customly added CSS
// drag_list :: list of draggable thingies
// sidebar :: both sidebars and modules and stuff
// sidebar_num :: number of built sidebars

// class creation
$vars = array( 'guis', 'title', 'pagi_top', 'config', 'meta_tags', 'copyright' );
$visible = array( 'public', 'private', 'private', 'public', 'public', 'public' );
eval( Varloader::createclass( 'basic_gui', $vars, $visible ) );
// end class creation

class Basic_gui extends basic_gui_def
{
	/**
	* initiates an object
	* @usage $basic_gui = new Basic_gui();
	*/
	function Basic_gui( )
	{
		global $board_config, $cache, $security, $Cl_root_path, $userdata, $Sajax, $security;
	
		$this->guis = array();
		// get the pagination from cache
		include( $Cl_root_path . 'template/' . $userdata[ 'user_skin' ] . '/template' . cfgEx );
		$this->config = $temp_config;
		// load up pagi config
		include( $Cl_root_path . 'kernel/config/paths' . cfgEx );
		$this->pagi_conf = $paths;
		// no popups
		$this->lastpop = 0;
		$this->popups = "PopUps = new Array();\n";
		// some other things
		$this->meta_tags = '';
		$this->copyright = 'Powered by <a href="http://chlorineboards.swizec.com" target="_blank" style="color: #000000; text-decoration: underline;">Chlorine Boards</a> &#169; 2005 - 2007 ClB Group';
		$this->JS_list = array();
		$this->CSS_list = array();
		$this->drag_list = '';
		$this->sidebar = array(); $this->sidebar[ 'left' ] = array(); $this->sidebar[ 'right' ] = array();
		$this->sidebar_num = 0;
		
		// add some basic meta_tags
		$this->addmeta( 'author', 'Chlorine Boards' );
		$this->addmeta( 'keywords', $board_config[ 'meta_keywords' ] );
		$this->addmeta( 'description', $board_config[ 'meta_description' ] );
		
		// for the sidebars' position
		$Sajax->add2export( 'basic_gui->sidebar_pos', '$id' );
		$Sajax->add2export( 'basic_gui->sidebar_store_pos', '$id, $x, $y, $cookie' );
	}
	/**
	* defines the basic global variables used by all templates
	* @usage $basic_gui->defineglobal();
	*/
	function defineglobal()
	{
		global $template, $basic_lang, $Cl_root_path4template;
		
		// some general vars
		$template->assign_vars( array(
			'L_BACK' => $basic_lang[ 'Back' ],
			'L_RELOAD' => $basic_lang[ 'Reload' ],
			
			'L_SUBMIT' => $basic_lang[ 'Submit' ],
			'L_RESET' => $basic_lang[ 'Reset' ],
			
			'ROOT_PATH' => $Cl_root_path4template,
			'U_BACK' => $this->back_URL,
		) );
	}
	/**
	* @usage generates the header of the board
	* $basic_gui->makeheader();
	*/
	function makeheader( $contents = '' )
	{
		global $template, $board_config, $Cl_root_path, $basic_lang, $userdata, $security, $Cl_root_path4template, $lang_loader, $plug_clcode, $cache;
		
		// load the template file
		$filename = 'header' . tplEx;
		$template->assign_files( array(
			'header' => $filename
		) );
		
		// used to create mozilla navigation bar links (opera too)
		$nav_link = '<link rel="%s" href="%s" title="%s" />' . "\n";
		
		// set the contents variable
		$template->assign_vars( array(
			'PAGE_CONTENTS' => $contents,
			'EMPTY_PAGE_CONTENTS' => intval( empty( $contents ) )
		) );
		
		// try to use the browser set charset
		if ( isset( $_SERVER[ 'HTTP_ACCEPT_CHARSET' ] ) && !empty( $_SERVER[ 'HTTP_ACCEPT_CHARSET' ] ) )
		{ // we can use this one
			// since there's more we need to explode
			$charset = explode( ',', $_SERVER[ 'HTTP_ACCEPT_CHARSET' ] );
			// check if the one from the config is acceptable
			foreach ( $charset as $set )
			{
				if ( trim( strtolower( $set ) ) == strtolower( $board_config[ 'site_charset' ] ) )
				{
					$charset = $set;
					break;
				}
			}
			if ( count( $charset ) > 1 )
			{ // wasn't found
				$charset = $charset[ 0 ];
				// change the global option so other parts of the script will know what's being used
				$GLOBALS[ 'board_config' ][ 'site_charset' ] = $charset;
			}
		}else
		{ // use the default
			$charset = $board_config[ 'site_charset' ];
		}
		
		// fetch the static sidebars if needed
		if ( !$static_bars = $cache->pull( 'static_sidebars' ) )
		{
			// used for ordering of sidebars
			if ( !function_exists( _cmp_sidebar ) )
			{
				function _cmp_sidebar( $a, $b )
				{
					if ( $a[ 'order' ] > $b[ 'order' ] )
					{
						return 1;
					}elseif ( $a[ 'order' ] < $b[ 'order' ] )
					{
						return -1;
					}
					return 0;
				}
			}
			// gotta redo, blah
			$static_bars = array();
			if ( is_readable( $Cl_root_path . 'kernel/config/static_sidebars' . phpEx ) )
			{
				/**
				* configuration file for the static sidebars
				*/
				$sidebars_array = @file_get_contents( $Cl_root_path . 'kernel/config/static_sidebars' . phpEx );
				$sidebars = unserialize( $sidebars_array );
				if ( is_array( $sidebars[ $lang_loader->board_lang ] ) )
				{
					foreach ( $sidebars as $lang => $bars )
					{
						foreach ( $bars as $name => $bar )
						{
							// add to normal array
							$static_bars[ $lang ][ $bar[ 'side' ] ][ $name ][ 'contents' ] = stripslashes( $bar[ 'content' ] );
							$static_bars[ $lang ][ $bar[ 'side' ] ][ $name ][ 'auth' ] = $bar[ 'auth' ];
							$static_bars[ $lang ][ $bar[ 'side' ] ][ $name ][ 'hidden' ] = $bar[ 'hidden' ];
							$static_bars[ $lang ][ $bar[ 'side' ] ][ $name ][ 'name' ] = $name;
							$static_bars[ $lang ][ $bar[ 'side' ] ][ $name ][ 'order' ] = $bar[ 'order' ];
						}
						// now order according to wanted order
						if ( is_array( $static_bars[ $lang ][ 'left' ] ) )
						{
							uasort( $static_bars[ $lang ][ 'left' ], '_cmp_sidebar' );
						}
						if ( is_array( $static_bars[ $lang ][ 'right' ] ) )
						{
							uasort( $static_bars[ $lang ][ 'right' ], '_cmp_sidebar' );
						}
					}
				}
			}
		}
		// put together
		$mode_url = ( isset( $_GET[ MODE_URL ] ) ) ? strval( $_GET[ MODE_URL ] ) : '';
		if ( is_array( $static_bars[ $lang_loader->board_lang ] ) && $mode_url != 'ACP' && $mode_url != 'UCP' )
		{
			if ( isset( $static_bars[ $lang_loader->board_lang ][ 'left' ] ) )
			{
				$this->sidebar[ 'left' ] = array_merge( $this->sidebar[ 'left' ], $static_bars[ $lang_loader->board_lang ][ 'left' ] );
			}
			if ( isset( $static_bars[ $lang_loader->board_lang ][ 'right' ] ) )
			{
				$this->sidebar[ 'right' ] = array_merge( $this->sidebar[ 'right' ], $static_bars[ $lang_loader->board_lang ][ 'right' ] );
			}
		}
		
		// header vars
		$template->assign_var_levels( '', 'HEAD', array(
			'NAV_LINKS' => sprintf( $nav_link, 'author', 'http://swizec.com', 'Author' ),
			'SITENAME' => ( !empty( $this->title ) ) ? $board_config[ 'sitename' ] . ' :: ' . $this->title : $board_config[ 'sitename' ],
			'CONTENT_CHARSET' => $charset,
			'LOGO_TITLE' => $basic_lang[ 'Logo_title' ],
			'MENU' => $this->make_menu( 'main' ),
			'POPUPS' => "<!--\n" . $this->popups . "\n//-->",
			'META' => $this->meta_tags,
			'CSS' => implode( "\n", $this->CSS_list ),
			'JSNOTICE' => $basic_lang[ 'JSnotice' ],
			'SIDEBARS' => $this->_buildsidebar( 'left' ) . "\n\n" . $this->_buildsidebar( 'right' ) . "\n",
		) );

		// some stuff to add to the image array
		$template->assign_var_levels( '', 'IMG', array(
			'LOGO' => $Cl_root_path4template . 'images/ClB_logo.png',
			'PAGI_ICO' => ( count( $this->pagination ) > 1 ) ? $Cl_root_path4template . 'images/pagination2.gif' : $Cl_root_path4template . 'images/pagination.gif',
// 			'PAGI_REL' => $Cl_root_path4template . 'images/pagination_rel.gif'
		) );
		
		// the sub-level of pagination needs showing or not
		
		if ( count( $this->pagination ) > 1 )
		{
			$template->assign_switch( 'pagi_sub', TRUE );
			// the sub thing
			$sub = array_pop( $this->pagination );
			$sub = '<a href="' . $security->append_sid( $sub[ 'URL' ] ) . '">' . $sub[ 'title' ] . '</a>';
		}
		// make the pagination
		if ( is_array( $this->pagination ) )
		{
			$upt = array();
			foreach ( $this->pagination as $pag )
			{
				$upt[] = '<a href="' . $security->append_sid( $pag[ 'URL' ] ) . '">' . $pag[ 'title' ] . '</a>';
			}
		}
		$pagSpacer = ( isset( $this->config[ 'paginationSpacer' ] ) ) ? $this->config[ 'paginationSpacer' ] : ' :: ';
		$template->assign_var_levels( '', 'PAGI', array(
			'TOP' => ( is_array( $upt ) ) ? implode( $pagSpacer, $upt ) : '',
			'SUB' => $sub,
			'U_BACK' => $security->append_sid( $this->pagination[ count( $this->pagination )-1 ][ 'URL' ] ),
			'P_BACK' => $this->make_tooltip( $basic_lang[ 'Back' ], 'pagi_back' ),
			'P_RELOAD' => $this->make_tooltip( $basic_lang[ 'Reload' ], 'pagi_reload' ),
		) );
		
		// make the greeting thingy
		$template->assign_var_levels( '', 'USER', array(
			'GREETING' => ( $userdata[ 'user_level' ] != GUEST ) ? $basic_lang[ 'Welcome' ] . ' ' . $userdata[ 'username' ] : $basic_lang[ 'Welcome' ] . ' ' . $basic_lang[ 'Guest' ],
			'ONLINESINCE' => $basic_lang[ 'online_since' ] . ' ' . date( $userdata[ 'user_timeformat' ], $userdata[ 'time_start' ] ),
			'LASTACTIVE' => $basic_lang[ 'last_activity' ] . ' ' . date( $userdata[ 'user_timeformat' ], $userdata[ 'time_lastactive' ] ),
			'LOGGEDIN' => intval( $userdata[ 'logged_in' ] ),
			'MENU' => $this->make_menu( 'user', '<br/>' ),
		) );
		
		// output the header
		$template->output( 'header' );
	}
	/**
	* returns the statistics of the board
	* @return string the stats
	*/
	function getstats( )
	{
		global $timing, $db;
		
		$stats = array();
		
		// get time
		$mtime = explode( " ", microtime() ); 
		$timing[ 'endtime' ] = $mtime[ 1 ] + $mtime[ 0 ];
		
		$start = $timing[ 'starttime' ];
		$end = $timing[ 'endtime' ];
		
		// calculate execution time
		$stats[ 'time' ] =  round( $end - $start, 4 );
		
		// get the number of sql queries
		$stats[ 'queries' ] = $db->num_queries;
		
		return $stats;
	}
	/**
	* generates the footer of the board
	* @usage $basic_gui->makefooter();
	*/
	function makefooter( )
	{
		global $template, $board_config, $basic_lang;
		
		// load the template file
		$filename = 'footer' . tplEx;
		$template->assign_files( array(
			'footer' => $filename
		) );
		
		// maket the stats
		$stats = $this->getstats();
		$template->assign_var_levels( '', 'FOOT', array(
			'STATS' => sprintf( $basic_lang[ 'Bottom_stats' ], $stats[ 'time' ], $stats[ 'queries' ] ),
			'COPYRIGHT' => $this->copyright,
			'DRAG_LIST' => ( !empty( $this->drag_list ) ) ? ', ' . $this->drag_list : $this->drag_list,
		) );
		
		// output it
		$template->output( 'footer' );
	}
	/**
	* recursively makes a list of all .js files within a path
	* @access private
	* @param string $path the path to check
	* @param string $JS the string to parse the paths against
	* @return string html that includes the JS
	*/
	function get_all_JS( $path, $JS, $out )
	{
		global $Cl_root_path4template;
		// open up the directory
		$dir = dir( $path );
		// loop through it and add everything to the list
		while ( FALSE !== ( $entry = $dir->read( ) ) ) {
			if ( $entry == '.' || $entry == '..' )
			{
				continue;
			}
			if ( is_dir( $path . $entry ) )
			{
				$out = $this->get_all_JS( $path . $entry . '/', $JS, $out );
			}else
			{
				if ( strtolower( substr( strrchr( $entry, '.' ), 1 ) ) == 'js' )
				{
					$out .= sprintf( $JS, $Cl_root_path4template . $path . $entry );
				}
			}
		}
		return $out;
	}
	/**
	* generates the page at the end of execution
	* @usage $basic_gui->make_page();
	*/
	function make_page()
	{
		global $template, $Sajax, $cache, $lang_loader, $userdata, $board_config, $Cl_root_path4template;
		
		// init sajax
		$Sajax->sajax_remote_uri = $Sajax->sajax_get_my_uri();
		$Sajax->sajax_init();
		$Sajax->sajax_export();
		$Sajax->sajax_handle_client_request();
		$JS = '<script language="JavaScript" type="text/javascript" src="%s" ></script>'."\n";
		$template->assign_var_levels( '', 'HEAD', array( 
			'SAJAX_CODE' => "<!--\n" . $Sajax->sajax_get_javascript() . "\n//-->",
			'JAVASCRIPT' => $this->get_all_JS( $Cl_root_path . 'javascript/', $JS, '' ) . implode( "\n", $this->JS_list ),
		) );
		
		$contents = '';
		
		// loop through guis and output them
		foreach ( $this->guis as $gui )
		{
			$temp = $template->justcompile( $gui );
			// try to convert the encoding before printing
			$gotmbstring = TRUE;
			if ( !extension_loaded( 'mbstring' ) )
			{ // ok so it isn't here, try to get it
				if ( !dl( 'mbstring' ) )
				{ // damn
					$gotmbstring = FALSE;
				}
			}
			if ( $gotmbstring )
			{
				$oldenc = mb_detect_encoding( $temp );
				$oldenc = ( empty( $oldenc ) ) ? 'UTF-8' : $oldenc;
				$temp = mb_convert_encoding( $temp, strtoupper( $board_config[ 'site_charset' ] ), $oldenc );
			}
			// add it to the contents
			$contents .= $temp;
		}
		
		// send some http headers
		header( 'Content-Type: text/html; charset=' . $board_config[ 'site_charset' ] );
		
		if ( $this->config[ 'wraparound' ] )
		{ // the template requires all dynamic content as a variable
			$this->makeheader( $contents );
		}else
		{ // normal usage
			$this->makeheader();
			echo $contents;
		}
		
		// store the page's html to cache
		if ( !defined( 'DONT_CACHE' ) )
		{
			$arr = array( 'drag_list' => $this->drag_list, 'content' => ob_get_contents(), 'time' => EXECUTION_TIME );
			$name = 'cached_page_' . $lang_loader->board_lang . '_' . $_SERVER[ 'REQUEST_URI' ] . '_' . $userdata[ 'user_id' ];
			$cache->push( $name, $arr, TRUE );
			// add it to the list of cached pages
			if ( !$list1 = $cache->pull( 'cached_page_list_' . $lang_loader->board_lang ) )
			{ // first get the list or make it an empty array
				$list1 = array();
			}
			if ( !$list2 = $cache->pull( 'cached_page_list_' . $lang_loader->board_lang . '_' . $userdata[ 'user_id' ] ) )
			{ // the list for the particular user
				$list2 = array();
			}
			if ( !$list3 = $cache->pull( 'cached_page_userlists' ) )
			{ // the list for the particular user
				$list3 = array();
			}
			$list1[] = $name;
			$list2[] = $name;
			$list3[] = 'cached_page_list_' . $lang_loader->board_lang . '_' . $userdata[ 'user_id' ];
			$cache->push( 'cached_page_list_' . $lang_loader->board_lang, $list1, TRUE );
			$cache->push( 'cached_page_list_' . $lang_loader->board_lang. '_' . $userdata[ 'user_id' ], $list2, TRUE );
			$cache->push( 'cached_page_userlists', $list3, TRUE );
		}
		
		// the footer
		$this->makefooter();
		
		ob_end_flush(); // need to output the buffered stuff
	}
	/**
	* used to add files that need outputting
	* @usage $basic_gui->add_file( 'filehandle' );
	* @param string $file the template handle of the file
	*/
	function add_file( $file )
	{
		// make sure the file hasn't already been added
		if ( !isset( $this->guis[ $file ] ) )
		{
			$this->guis[ $file ] = $file;
		}
	}
	/**
	* used to create the appendix for the title
	* @usage $basic_gui->set_title( 'title' );
	* @param string $str what to append
	*/
	function set_title( $str )
	{
		$this->title = $str;
	}
	/**
	* used to add a level to the pagination
	* @usage $basic_gui->set_level( 1, 'Forum' );
	* @param integer $level level to set
	* @param string $name name of the set of levels
	* @param string $custom custom level name
	* @param mixed $add what to add to the set of levels
	*/
	function set_level( $level, $name, $custom = '', $add = array() )
	{
		global $Cl_root_path, $basic_lang, $board_config, $errors, $security;
		
		if ( empty( $name ) && $level != 0 )
		{
			return;
		}
		
		$this->pagination = array( array( 'URL' => $security->append_sid( 'index' . phpEx ), 'title' => $board_config[ 'sitename' ] ) ); // empty the arry
		
		if ( $level == 0 )
		{
			return;
		}
		// now get the right arry
		if ( is_array( $this->pagi_conf[ $name ] ) )
		{ // all is good
			$path = array_slice( $this->pagi_conf[ $name ], 0, $level ); // kind of a type saver
		}elseif ( count( $add ) > 0 )
		{ // maybe there's enough here
			$path = array();
		}else
		{ // no no no
			$errors->report_error( sprintf( $basic_lang[ 'err_wrongpath' ], $name ), GENERAL_ERROR );
		}
		
		// if the add is set then merge it
		if ( !empty( $add ) )
		{
			$path = array_merge( $path, $add );
		}
		
		$custom = explode( ',', $custom );
		
		// go through it and uh... add
		foreach ( $path as $i => $lvl )
		{
			// check if this forks
			if ( isset( $lvl[ 0 ] ) )
			{ // yep
				// use custom if applicable
				if ( isset( $custom[ $i ] ) )
				{ // custon
					$lvl = $lvl[ $custom[ $i ] ];
				}else
				{ // generic
					$lvl = $lvl[ 0 ];
				}
			}
			// some basic parsing
			$title = isset( $basic_lang[ $lvl[ 'title' ] ] ) ? $basic_lang[ $lvl[ 'title' ] ] : $lvl[ 'title' ];
			$url = $security->append_sid( $lvl[ 'URL' ] );
			
			// add it, if displayable
			if ( !empty( $title ) )
			{
				$this->pagination[] = array( 'URL' => $security->append_sid( $url ), 'title' => $title );
			}
		}
	}
	/**
	* used to create a DHTML tooltip
	* @usage $menu = $basic_gui->make_tooltip( $contents, 'sub_forums' );
	* @param string $contents HTML contents of the tooltip
	* @param string $name configuration name of the tooltip
	* @param string $toolID HTML element to convert into this tooltip
	* @param string $exec on what event to appear
	* @param string $title the title of it
	* @return string ready HTML for insertion
	*/
	function make_tooltip( $contents, $name, $toolID = '', $exec = 'onmouseover', $title = '' )
	{
		// get config
		$cfg = $this->config[ 'tools' ][ $name ];
		$gen = $this->config[ 'tools' ][ '_general_' ];
		// we need these
		$tool = $contents;
		$head = '';
		
		// calculate the offsetx
		if ( $cfg[ 'align' ] == 'right' )
		{
			$offsetx = 0;
		}elseif( $cfg[ 'align' ] == 'center' )
		{
			$offsetx = -( $cfg[ 'width' ] / 2 );
			$cfg[ 'offsetx' ] = -$cfg[ 'offsetx' ];
		}elseif( $cfg[ 'align' ] == 'left' )
		{
			$offsetx = -$cfg[ 'width' ];
			$cfg[ 'offsetx' ] = -$cfg[ 'offsetx' ];
		}else
		{
			$offsetx = 0;
		}
		
		$offsetx += $cfg[ 'offsetx' ];
		
		// first set up the head
		$head .= 'WIDTH, ' . $cfg[ 'width' ] . ', ';
		$head .= 'OFFSETX, ' . $offsetx . ', ';
		$head .= 'TEXTALIGN, \'' . $cfg[ 'textalign' ] . '\', ';
		$head .= 'TITLE, \'' . htmlspecialchars( preg_replace( array( "#^\'#", '#^\"#' ), array( "\'", '\"' ), $title ) ) . '\', ';
		$head .= 'OFFSETY, ' . $cfg[ 'offsety' ] . ', ';
		$head .= ( !empty( $cfg[ 'sticky' ] ) ) ? 'STICKY, ' . $cfg[ 'sticky' ] . ', ' : '';
		// general settings
		foreach ( $gen as $k => $set )
		{
			/*if ( empty( $set ) )
			{
				continue;
			}*/
			if ( is_bool( $set ) || is_int( $set ) && !empty( $set ) )
			{
				$head .= '' . strtoupper( $k ) . ', ' . $set . ', ';
			}else
			{
				$head .= '' . strtoupper( $k ) . ', \'' . $set . '\', ';
			}
		}
		
		$head = substr( $head, 0, -2 );
		
		// then the body
		$tool = preg_replace( array( "#^\'#", '#^\"#' ), array( "\'", '\"' ), $tool );
		$tool = htmlspecialchars( $tool );
		
		// return it
		if ( $toolID == '' )
		{
			return $exec . '="Tip(\'' . $tool . '\', ' . $head . ');"';
		}else
		{
			return $exec . '="TagToTip( \'' . $toolID . '\', ' . $head . ');"';
		}
	}
	/**
	* this creates the output of a menu from menu.cfg
	* from version 0.9.21 it also creates a template array of all URLs and titles to be used in custom menus
	* @access private
	* @param string $which configuration name of the menu
	* @param string $separator separator of entries
	* @return string ready HTML
	*/
	function make_menu( $which = 'main', $separator = '' )
	{
		global $Cl_root_path, $basic_lang, $security, $board_config, $userdata, $cache, $lang_loader, $template;
		
		// first get the config
		include( $Cl_root_path . 'kernel/config/menu' . cfgEx );
		
		// the separator
		if ( isset( ${$which}[ '_separator_' ] ) )
		{
			$separator = ( empty( $separator ) ) ? ${$which}[ '_separator_' ] : $separator;
			unset( ${$which}[ '_separator_' ] );
		}
		
		if ( $which == 'main' )
		{
			// get the pages menu thingy
			if ( !$pages_menu = $cache->pull( 'static_pages_menu' ) )
			{ // guess it has to be rebuilt
				if ( is_readable( $Cl_root_path . 'kernel/config/static_pages' . phpEx ) )
				{ // no file, don't do anything
					$pages_array = unserialize( @file_get_contents( $Cl_root_path . 'kernel/config/static_pages' . phpEx ) );
					if ( !empty( $pages_array ) )
					{
						foreach ( $pages_array as $lang => $pags )
						{
							$pages_menu[ $lang ] = array();
							foreach ( $pags as $name => $pag )
							{
								$pages_menu[ $lang ][] = array( 'title' => $name, 'URL' => '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=' . str_replace( ' ', '%20', $name ) );
							}
						}
						$cache->push( 'static_pages_menu', $pages_menu, TRUE );
					}
				}
			}
			// put 'em together
			$main[ 'pages' ][ 'sub' ] = $pages_menu[ $lang_loader->board_lang ];
		}
		
		$menu = '';
		
		if ( !is_array( $which ) )
		{
// 			return '';
		}
		
		// now we do what the config says eh
		foreach ( ${$which} as $entry )
		{
			// perm stuff
			if ( isset( $entry[ 'perm' ] ) )
			{
				if ( $userdata[ 'user_level' ] < ADMIN )
				{ // for guests and such
					$perm = ( $userdata[ 'user_level' ] >= $entry[ 'perm' ] ) ? TRUE : FALSE;
				}else
				{ // for the rest
					$perm = ( $userdata[ 'user_level' ] <= $entry[ 'perm' ] && $userdata[ 'user_level' ] >= ADMIN ) ? TRUE : FALSE;
				}
				if ( !$perm )
				{ // negative sir
					continue;
				}
			}
			// check the language
			if ( isset( $entry[ 'lang' ] ) )
			{
				if ( $lang_loader->board_lang != $entry[ 'lang' ] )
				{
					continue;
				}
			}
			// check if this has a submenu
			if ( is_array( $entry[ 'sub' ] ) )
			{ // it does
				// make the linkies
				$submenu = '';
				foreach ( $entry[ 'sub' ] as $i => $sub )
				{
					// perm stuff
					if ( isset( $sub[ 'perm' ] ) )
					{
						if ( $userdata[ 'user_level' ] < ADMIN )
						{ // for guests and such
							$perm = ( $userdata[ 'user_level' ] >= $sub[ 'perm' ] ) ? TRUE : FALSE;
						}else
						{ // for the rest
							$perm = ( $userdata[ 'user_level' ] <= $sub[ 'perm' ] && $userdata[ 'user_level' ] >= ADMIN ) ? TRUE : FALSE;
						}
						if ( !$perm )
						{ // negative sir
							continue;
						}
					}
					// check the language
					if ( isset( $sub[ 'lang' ] ) )
					{
						if ( $lang_loader->board_lang != $sub[ 'lang' ] )
						{
							continue;
						}
					}
					$title = ( isset( $basic_lang[ $sub[ 'title' ] ] ) ) ? $basic_lang[ $sub[ 'title' ] ] : $sub[ 'title' ];
					// we do some fixing of the URL :P
					$URL = $security->append_sid( $sub[ 'URL' ] );

					// add it to the manual menu array thing
					$template->assign_var_levels( '', 'MANUALMENU', array( 
						strtoupper( $which ) => array(
							strtoupper( $entry[ '_self_' ][ 'title' ] ) => array(
								$i => array( 
									'TITLE' => $title,
									'URL' => $URL,
								),
								strtoupper( $sub[ 'title' ] ) => array( 
									'TITLE' => $title,
									'URL' => $URL,
								),
							)
						)
					) );
			
					$submenu .= '<a href="' . $URL . '">' . $title . '</a><br />';
				}
				// remove the last <br />
				$submenu = substr( $submenu, 0, -6 );
				// now make it into a tooltip
				$submenu = $this->make_tooltip( $submenu, 'submenu' );
			}else
			{
				$submenu = FALSE;
			}
			$entr = $entry[ '_self_' ];
			$title = ( isset( $basic_lang[ $entr[ 'title' ] ] ) ) ? $basic_lang[ $entr[ 'title' ] ] : $entr[ 'title' ];
			// we do some fixing of the URL :P
			$URL = $security->append_sid( $entr[ 'URL' ] );

			// add to the manual menu thing
			$template->assign_var_levels( '', 'MANUALMENU', array(
				strtoupper( $which ) => array(
					strtoupper( $entry[ '_self_' ][ 'title' ] ) => array(
						'TITLE' => $title,
						'URL' => $URL,
					)
				)
			) );
			
			$menu .= '<a href="' . $URL . '" ' . $submenu . '>' . $title . '</a>' . $separator;
		}
		// last few chars are too much
		$menu = substr( $menu, 0, -strlen( $separator ) );
		
		return $menu;
	}
	/**
	* generalizes line breaks (everything to \n)
	* @usage $str = $basic_gui->gennuline( $str )
	* @param string $str string to parse
	* @return string parsed string
	*/
	function gennuline( $str )
	{
		return str_replace( "\r", "\n", str_replace( "\r\n", "\n", $str ) );
	}
	/**
	* adds a pop up thingy (no popup blocker should be able to block this)
	* @usage $click = $basic_gui->add_pop( $contents );
	* @param string $contents HTML contents of the popup
	* @param integer $left "left" css property, parameter left for historical purposes
	* @param integer $top "top" css property, parameter left for historical purposes
	* @param integer $width "width" css property
	* @param integer $height "height" css property
	* @param string $position "position" css property
	* @retunr string ready to use html
	*/
	function add_pop( $contents, $left = 100, $top = 100, $width = 300, $height = 300, $position = 'fixed' )
	{
		global $basic_lang, $Cl_root_path4template;
		
		// some basic parsing
		$contents = str_replace( '"', '\"', str_replace( "\n", '<br />', $this->gennuline( $contents ) ) );
		// make the html of the popup
		$Z = 100 + $this->lastpop;
// 		$this->popups .= '<div style="overflow: auto; display: none; position: ' . $position . '; top: ' . $top . '; left: ' . $left . '; width: ' . $width . '; height: ' . $height . '; z-index: ' . $Z . '; " id="pop' . $this->lastpop . '">' . $contents . '<center><input type="button" value="' . $basic_lang[ 'Close' ] . '" onclick="switchDiv( \'pop' . $this->lastpop . '\' )"></center></div>' . "\n";
		$this->popups .= "\t\tPopUps[ {$this->lastpop} ] = new Array();\n" .
					"\t\tPopUps[ {$this->lastpop} ][ 'contents' ] = \"$contents\";\n" .
					"\t\tPopUps[ {$this->lastpop} ][ 'width' ] = $width;\n" .
					"\t\tPopUps[ {$this->lastpop} ][ 'height' ] = $height;\n";
		
		// inc the ponter
		$this->lastpop++;
		
		// return the needed call
		return 'onclick="ShowPopUp( ' . ($this->lastpop-1) . ', \''. $Cl_root_path4template . '\' ); return false"';
	}
	/**
	* adds a tag to the meta tags thing
	* @usage $basic_gui -> addmeta( $name, $value );
	* @param string $name the name of the meta tag
	* @param string $value the value of it
	*/
	function addmeta( $name, $value )
	{
		// this will be used
		$meta = '<meta name="%s" content="%s" />';
		
		// add it
		$this->meta_tags .= sprintf( $meta, $name, $value );
	}
	/**
	* adds a line to the header
	* @usage $basic_gui->add_head( $line )
	* @param string $line line for the header
	*/
	function add_head( $line )
	{
		$this->meta_tags .= $line;
	}
	/**
	* adds a line to the copyright notice at the bottom
	* @usage $basic_gui->addcopyright( $string )
	* @param string $str the line to add
	*/
	function addcopyright( $str )
	{
		$this->copyright .= '<br/>' . $str;
	}
	/**
	* adds a JS include entry
	* @usage $basic_gui->add_JS( $file )
	* @param string $file path to the file to include
	*/
	function add_JS( $file )
	{
		global $Cl_root_path4template;
		
		$this->JS_list[] = '<script language="JavaScript" type="text/javascript" src="' . $Cl_root_path4template . $file . '" ></script>';
	}
	/**
	* adds a JS text to the top of the page source
	* @usage $basic_gui->add_JS_text( $text )
	* @param string $file path to the file to include
	*/
	function add_JS_text( $text )
	{
		$this->JS_list[] = '<script language="JavaScript" type="text/javascript">' . "\n" . $text . "\n"  . '</script>';
	}
	/**
	* adds a CSS include entry
	* @usage $basic_gui->add_CSS( $file )
	* @param string $file the path to the file to include
	*/
	function add_CSS( $file )
	{
		global $Cl_root_path4template;
		
		$this->CSS_list[] = '<link rel="stylesheet" href="' . $Cl_root_path4template . $file . '" type="text/css" />';
	}
	/**
	* returns the base URL
	* @usage $URL = $basic_gui->get_URL();
	* @return string the URL
	*/
	function get_URL()
	{
		if ( !defined( 'SITE_URL' ) )
		{
			global $board_config;
			$URL = ( $board_config[ 'session_secure' ] ) ? 'https://' : 'http://';
			$URL .= $board_config[ 'session_domain' ] . $board_config[ 'script_path' ];
			$URL = ( $URL{strlen($URL)-1} == '/' ) ? substr( $URL, 0, -1 ) : $URL;
			define( 'SITE_URL', $URL );
		}
		
		return SITE_URL;
	}
	/**
	* add a div or wtv name to the draggable list
	* @usage $basic_gui->add_drag( $name )
	* @param string $name HTML name of the item to be draggable
	*/
	function add_drag( $name, $add = array() )
	{
		$this->drag_list .= ( empty( $this->drag_list ) ) ? '"' . $name . '"' : ', "' . $name . '"';
		$add = ( is_array( $add ) ) ? implode( '+', $add ) : $add;
		$this->drag_list .= ( !empty( $add ) ) ? '+' . $add : '';
	}
	/**
	* adds a module to the sidebar
	* @usage $basic_gui->add2sidebar( 'left', $name, $contents )
	* @param string $which left or right
	* @param string $name sidebar title
	* @param string $contents HTML body of the sidebat
	* @param integer $auth either of the auth level constants
	* @param bool $hide sidebar is "hidden"
	*/
	function add2sidebar( $which, $name, $contents, $auth = GUEST, $hide = FALSE )
	{
		$which = strtolower( $which ); // for more order
		
		// check if the module already exists
		if ( isset( $this->sidebar[ $which ][ $name ] ) )
		{ // does
			$this->sidebar[ $which ][ $name ][ 'contents' ] .= $contents;
		}else
		{ // doesn't
			$this->sidebar[ $which ][ $name ][ 'contents' ] = $contents;
		}
		// two more thingies
		$this->sidebar[ $which ][ $name ][ 'auth' ] = $auth;
		$this->sidebar[ $which ][ $name ][ 'hidden' ] = $hide;
	}
	/**
	* this is for internal use, it builds a sidebar set
	* @acces private
	* @param string $which left or right
	*/
	function _buildsidebar( $which )
	{
		global $userdata, $template;
		
		// check if even needed
		if ( empty( $this->sidebar[ $which ] ) || defined( 'NO_SIDEBARS' ) )
		{
			return '';
		}
			
// 		print_R( $this->sidebar );
			
		// some thingies
		$top = $this->config[ 'sidebar' ][ $which ][ 'top' ];
		$x = $this->config[ 'sidebar' ][ $which ][ $which ];
		$width = $this->config[ 'sidebar' ][ $which ][ 'width' ];
		
		// the main thing first
		// start
		$sidebar = '<div name="sidebar_' . $which . '" id="sidebar_' . $which . '" style="position: absolute; z-index: ' . (2000+$this->sidebar_num) . '; top: ' . $top . 'px; ' . $which . ': ' . $x . 'px; width: ' . $width . 'px;">';
		$this->sidebar_num++;
		
		// get the sidebar template
		$template->assign_files( array(
			'sidebar' => 'sidebar' . tplEx
		) );
		
		// loop through the thingies
		foreach ( $this->sidebar[ $which ] as $name => $sbar )
		{
			// check the auth :)
			if ( isset( $sbar[ 'auth' ] ) )
			{
				if ( $sbar[ 'auth' ] < ADMIN )
				{ // for wee low folk
					if ( $sbar[ 'auth' ] > $userdata[ 'user_level' ] )
					{ // don't display this one
						continue;
					}
				}else
				{ // for a tad normaler guys
					if ( $userdata[ 'user_level' ] < $sbar[ 'auth' ] )
					{ // don't display this one
						continue;
					}
				}
			}
			// needs to be hidden by default?
			$hide = ( $sbar[ 'hidden' ] == TRUE ) ? 'hidden' : 'visible';
			
			// make it
			$Z = 2000 + $this->sidebar_num;
			// pass vars to the template
			$template->assign_var_levels( '', 'SIDEBAR', array(
				'ID' => 'sidebar_module_' . $which . '_' . $this->sidebar_num,
				'ID2' => 'sidebar_module_' . $which . '_' . $this->sidebar_num . '_c',
				'ZINDEX' => $Z,
				'SIDE' => "$which: 0px;",
				'TITLE' => $name,
				'CONTENT' => $sbar[ 'contents' ],
				'WIDTH' => $width,
				'HIDDEN' => $hide,
			) );
	
			// so it's draggable :p
			$this->add_drag( 'sidebar_module_' . $which . '_' . $this->sidebar_num );
			$sidebar .= $template->justcompile( 'sidebar' );;
			$this->sidebar_num++;
		}
		
		// end
		$sidebar .= '</div>';
		
		return $sidebar;
	}
	/**
	* for displaying of static pages
	* @access private
	*/
	function static_page()
	{
		global $errors, $cache, $Cl_root_path, $lang_loader, $basic_lang, $template, $plug_clcode, $userdata;
		
		// get the name
		$name = ( isset( $_GET[ SUBMODE_URL ] ) ) ? str_replace( '%20', ' ', strval( $_GET[ SUBMODE_URL ] ) ) : '';
		
		// get the pages
		if ( !$pages_array = $cache->pull( 'static_pages' ) )
		{ // need to get them
			if ( !is_readable( $Cl_root_path . 'kernel/config/static_pages' . phpEx ) )
			{ // oh noes, run away
				$errors->report_error( $basic_lang[ 'Unknown_page' ], GENERAL_ERROR );
			}
			$pages_array = unserialize( @file_get_contents( $Cl_root_path . 'kernel/config/static_pages' . phpEx ) );
			$cache->push( 'static_pages', $pages_array, TRUE );
		}
		
		if ( !$page = $pages_array[ $lang_loader->board_lang ][ $name ] )
		{ // didn't get the bastard
			$errors->report_error( $basic_lang[ 'Unknown_page' ], GENERAL_ERROR );
		}
		// check auth
		// check the auth :)
		if ( isset( $page[ 'auth' ] ) )
		{
			if ( $page[ 'auth' ] < ADMIN )
			{ // for wee low folk
				if ( $page[ 'auth' ] > $userdata[ 'user_level' ] )
				{ // don't display this one
					$errors->report_error( $basic_lang[ 'Page_auth' ], GENERAL_ERROR );
				}
			}else
			{ // for a tad normaler guys
				if ( $userdata[ 'user_level' ] < $page[ 'auth' ] )
				{ // don't display this one
					$errors->report_error( $basic_lang[ 'Page_auth' ], GENERAL_ERROR );
				}
			}
		}
		
		// fire up ze template
		$template->assign_files( array(
			'static_page' => 'static_page' . tplEx
		) );
		// stuff add
		$template->assign_block_vars( 'page', '', array(
// 			'BODY' => $plug_clcode->parse( stripslashes( $page[ 'content' ] ), TRUE )
			'BODY' => stripslashes( $page[ 'content' ] )
		) );
		$template->assign_switch( 'page', TRUE );
		
		// some minor thingies eh
		$this->set_title( $name );
		$this->set_level( 1, 'pages', '', array( array( 'URL' => '?' . MODE_URL . '=pages&' . SUBMODE_URL . '=' . str_replace( ' ', '%20', $name ), 'title' => $name ) ) );
		
		// add to output
		$this->add_file( 'static_page' );
	}
	/**
	* returns the draggable javascript
	* @return string HTML for the drag thing
	*/
	function get_drag_code()
	{
		$list = $this->drag_list;
		return "<script type=\"text/javascript\">\n<!--\nSET_DHTML(CURSOR_MOVE, RESIZABLE, TRANSPARENT, SCROLL, $list);\n//-->\n</script>";
	}
	/**
	* this changes the position of the sidebar according to the cookie
	* @access private
	* @param integer $id HTML id of sidebar
	* @return mixed id and new position
	*/
	function sidebar_pos( $id )
	{
		if ( !isset( $_COOKIE[ 'CLB_sidebars' ] ) )
		{ // if cookie not set then return
			return array( $id, -1, -1 );
		}
		
		$sidebars = $_COOKIE[ 'CLB_sidebars' ];
		
		// another check for the sidebar itself
		if ( strpos( $sidebars, $id ) === FALSE )
		{
			return array( $id, -1, -1 );
		}
		
		$sidebars = explode( '$$', $sidebars );
// 		return array( $sidebars[ 2 ] );
		
		// find the one
		for ( $i = 1; $i < count( $sidebars ); $i++ )
		{
			if ( substr( $sidebars[ $i ], 0, strlen( $id ) ) == $id )
			{ // found it
				$sidebars[ $i ] = explode( '!!', $sidebars[ $i ] );
// 				return array( $i );
				return array( $id, $sidebars[ $i ][ 1 ], substr( $sidebars[ $i ][ 2 ], 0, -1 ) );
			}
		}
		
		// all failed... wtf
		return array( $id, -1, -1 );
	}
	/**
	* this stores the sidebars' positoin into the cookie
	* @access private
	* @param integer $id sidebar id
	* @param integer $x position
	* @param integer $y position
	* @param string $cookie cookie value
	*/
	function sidebar_store_pos( $id, $x, $y, $cookie )
	{
		if ( strpos( $cookie, $id ) === FALSE )
		{ // just add to the list
			$cookie .= '$$' . "$id!!$x!!$y!";
		}else
		{ // replace the previous entry for this sidebar
			$cookie = preg_replace( '#\$\$' . $id . '!![0-9]*?!![0-9]*?!#', '$$' . "$id!!$x!!$y!", $cookie );
		}
		
		return $cookie;
	}
	/**
	* this removes all the cached pages from cache
	* @param integer $uid id of the user for whom to remove the page cache
	*/
	function remove_cached_pages( $uid = '' )
	{
		global $cache, $userdata, $lang_loader;
			
		if ( empty( $uid ) )
		{
			$nlist = 'cached_page_list_' . $lang_loader->board_lang;
			if ( !$list = $cache->pull( $nlist ) )
			{ // nothing there
				return TRUE;
			}
			// need to remove the per user lists too
			$list2 = $cache->pull( 'cached_page_userlists' );
		}else
		{
			$nlist = 'cached_page_list_' . $lang_loader->board_lang . '_' . $uid;
			if ( !$list = $cache->pull( $nlist ) )
			{ // nothing there
				return TRUE;
			}
			// no need for more
			$list2 = '';
		}
		
		for ( $i = 0; $i < count( $list ); $i++ )
		{ // go through the list and delete from cache :)
			$cache->delete( $list[ $i ] );
		}
		
		// delete the list
		$cache->delete( $nlist );
		
		// delete the per user lists if needed
		if ( !empty( $list2 ) )
		{
			for ( $i = 0; $i < count( $list2 ); $i++ )
			{
				$cache->delete( $list2[ $i ] );
			}
			$cache->delete( 'cached_page_userlists' );
		}
		return TRUE;
	}
	/**
	* this sets the always available back URL
	*/
	function set_back_URL()
	{
		global $security, $userdata;
		
		if ( isset( $userdata[ 'back_URL' ] ) )
		{ // set the back URL
			$this->back_URL = $userdata[ 'back_URL' ];
		}else
		{ // or make it point to frontpage
			$this->back_URL = $security->append_sid( '?' );
		}
		if ( !isset( $_GET[ 'AJAX_CALL' ] ) )
		{
			// now set the back URL for next time, but only if not in an ajax call
			$URI = '?';
			foreach ( $_GET as $key => $val )
			{ // have to make the whole URI
				$URI .= $key . '=' . $val . '&';
			}
			$URI = substr( $URI, 0, -1 );
			$_SESSION[ 'back_URL' ] = $security->append_sid( $URI );
		}
	}
	/**
	* reloads the template configuration file
	*/
	function reload_config()
	{
		global $userdata, $Cl_root_path;
		
		include( $Cl_root_path . 'template/' . $userdata[ 'user_skin' ] . '/template' . cfgEx );
		$this->config = $temp_config;
	}

	//
	// End of Basic-gui class
	//
}


?>