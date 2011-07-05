<?php

/**
*     defines the ACP_pages class
*     @file                ACP_pages.php
*     @see ACP_pages
*/
/**
* ACP panel for administration of the static pages
*     @class		   ACP_pages
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.2.5
*     @since        01st April 2006
*     @package		     ClB_base
*     @subpackage	     ClB_ACP
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
eval( Varloader::createclass( 'ACP_pages', $vars, $visible ) );
// end class creation

class ACP_pages extends ACP_pages_def
{
	/**
	* constructor
	*/
	function ACP_pages( $debug = FALSE )
	{
		global $Cl_root_path, $basic_gui, $lang_loader, $security;
		
		$this->debug = $debug;
	
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_pages' );
		
		// make the two urls
		$url1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_pages&s=add' );
		$url2 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_pages&s=edit' );
		$url3 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_pages&s=convert' );
		
		// add to page
		// add to page
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $url1 . '">' . $this->lang[ 'Side_add' ] . '</a><br /><a href="' . $url2 . '">' . $this->lang[ 'Side_edit' ] . '</a><br /><a href="' . $url3 . '">' . $this->lang[ 'Convert' ] . '</a></span>' );
		
		// get the pages array
		$dir = $Cl_root_path . 'kernel/config/';
		$file = $dir . 'static_pages' . phpEx;
		if ( is_file( $file ) )
		{
			$this->pages_array = unserialize( @file_get_contents( $file ) );
		}else
		{
			$this->pages_array = array();
		}
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $template, $errors, $Cl_root_path;
		
		$template->assign_files( array(
			'ACP_pages' => 'ACP/pages' . tplEx
		) );
		
		// get the subsubmode
		$sub = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : 'add';
		
			
		switch( $sub )
		{
			case 'add':
				$this->adding();
				break;
			case 'edit':
				$this->editting();
				break;
			case 'add_bar':
				$mode = ( isset( $_POST[ 'MODE' ] ) ) ? strval( $_POST[ 'MODE' ] ) : '';
				switch( $mode )
				{
					case 'create':
						$this->add_page();
						break;
					case 'edit':
						$this->edit_page();
						break;
					default:
						$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
						break;
				}
				break;
			case 'convert':
				include( $Cl_root_path . 'kernel/config/static_pages' . phpEx );
				if ( isset( $pages ) )
				{
					$this->pages_array = $pages;
				}
				$this->save_pages();
				$errors->report_error( $this->lang[ 'Converted' ], MESSAGE );
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* shows the panel for the adding of static pages
	*/
	function adding()
	{
		global $template, $mod_loader, $security, $lang_loader;
		
		// get the editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => FALSE ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$page = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// construct the language selection list
		$langl = $lang_loader->get_langlist();
		$langs = '<select name="language">';
		for ( $i = 0; $i < count( $langl ); $langs .= '<option>' . $langl[ $i ] . '</option>',$i++ );
		$langs .= '</select>';
		
		// construct the auth selection list :)
		$auth2 = '<select name="auth">';
		$s[ 0 ] = ( $auth == GUEST ) ? 'selected' : '';
		$s[ 1 ] = ( $auth == INACTIVE ) ? 'selected' : '';
		$s[ 2 ] = ( $auth == ADMIN ) ? 'selected' : '';
		$s[ 3 ] = ( $auth == SUPER_MOD ) ? 'selected' : '';
		$s[ 4 ] = ( $auth == MOD ) ? 'selected' : '';
		$s[ 5 ] = ( $auth == USER ) ? 'selected' : '';
		$auth2 .= '<option value="' . GUEST . '" ' . $s[ 0 ]  . '>' . $this->lang[ 'Guest' ] . '</option>';
		$auth2 .= '<option value="' . INACTIVE . '" ' . $s[ 1 ] . '>' . $this->lang[ 'Inactive' ] . '</option>';
		$auth2 .= '<option value="' . ADMIN . '" ' . $s[ 2 ] . '>' . $this->lang[ 'Admin' ] . '</option>';
		$auth2 .= '<option value="' . SUPER_MOD . '" ' . $s[ 3 ] . '>' . $this->lang[ 'Super_mod' ] . '</option>';
		$auth2 .= '<option value="' . MOD . '" ' . $s[ 4 ] . '>' . $this->lang[ 'Mod' ] . '</option>';
		$auth2 .= '<option value="' . USER . '" ' . $s[ 5 ] . '>' . $this->lang[ 'User' ] . '</option>';
		$auth2 .= '</select>';
		
		$template->assign_block_vars( 'add', '', array(
			'L_TITLE' => $this->lang[ 'Add_title' ],
			'L_EXPLAIN' => $this->lang[ 'Add_explain' ],
			'L_TITLE2' => $this->lang[ 'Add_title' ],
			'L_LANGUAGE' => $this->lang[ 'Add_language' ],
			'L_AUTH' => $this->lang[ 'Add_auth' ],
			
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_pages&s=add_bar' ),
			'S_PAGE' => $page[ 'editor_HTML' ],
			'S_WYSIWYG' => $page[ 'editor_WYSIWYG' ],
			'S_MODE' => 'create',
			'S_LANGS' => $langs,
			'S_AUTH' => $auth2,
		) );
		
		$template->assign_switch( 'add', TRUE );
	}
	/**
	* adds the static page according to the submitted form
	*/
	function add_page()
	{
		global $errors, $Cl_root_path, $basic_gui;
		
		// main check
		if ( !isset( $_POST[ 'submit_page' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// get data
		$title = ( isset( $_POST[ 'title' ] ) ) ? strval( $_POST[ 'title' ] ) : '';
		$lang = ( isset( $_POST[ 'language' ] ) ) ? strval( $_POST[ 'language' ] ) : '';
		$text =  str_replace( '&nbsp;', ' ', $basic_gui->gennuline( ( isset( $_POST[ 'editor1' ] ) ) ? strval( $_POST[ 'editor1' ] ) : '' ) );
		$auth =  ( isset( $_POST[ 'auth' ] ) ) ? intval( $_POST[ 'auth' ] ) : GUEST;
		
// 		$text = str_replace( "\n", '<br />', $text );
		
		if ( empty( $title ) || empty( $text ) )
		{ // need these
			$errors->report_error( $this->lang[ 'No_data' ], GENERAL_ERROR );
		}
		
		// save to the array
		$pag = array( 'content' => $text, 'auth' => $auth );
		if ( !is_array( $this->pages_array[ $lang ] ) )
		{ // make it an array, just to be sure
			$this->pages_array[ $lang ] = array();
		}
		$this->pages_array[ $lang ][ $title ] = $pag;
		
		// now write it
		$this->save_pages();
		
		$errors->report_error( $this->lang[ 'Added' ], MESSAGE );
	}
	/**
	* shows the panel for editing of the static pages
	*/
	function editting()
	{
		global $template, $mod_loader, $security, $errors, $lang_loader;
		
		// which is chosen
		$b = ( isset( $_GET[ 'pag' ] ) ) ? str_replace( '%20', ' ', strval( $_GET[ 'pag' ] ) ) : '';
		
		// create the list
		$list = '<select onchange="window.location.href = this.value">';
		$list .= ( empty( $b ) ) ? '<option selected value=""> </option>' : '<option value=""> </option>';
		if ( is_array( $this->pages_array ) )
		{
			foreach ( $this->pages_array as $lang => $pags )
			{
				foreach ( $pags as $name => $pag )
				{
					$list .= ( $b == $lang . '-' . $name ) ? '<option selected ' : '<option ';
					$list .= 'value="' . $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_pages&s=edit&pag=' . $lang . '-' . $name ) . '">' . $lang . ' :: ' . $name . '</option>';
				}
			}
		}
		$list .= '</select>';
		
		// determine the content of the editee
		if ( !empty( $b ) )
		{
			// get the content
			$content = explode( '-', $b );
			$title = $content[ 1 ];
			$language = $content[ 0 ];
			$auth = $this->pages_array[ $content[ 0 ] ][ $content[ 1 ] ][ 'auth' ];
			$content = $this->pages_array[ $content[ 0 ] ][ $content[ 1 ] ][ 'content' ];
		}else
		{
			$content = '';
			$title = '';
			$language = '';
			$auth = '';
		}
		
		// get the editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => FALSE, 'def_text' => stripslashes( $content ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$page = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		if ( !$page[ 'editor_WYSIWYG' ] )
		{ // make it so that newlines are actually newlines in the textarea
			$htm = $page[ 'editor_HTML' ];
			$htm = str_replace( '<textarea name="editor1" id="editor1" style="width: 100%; height: 78%">' . htmlspecialchars( $content ) . '</textarea>', '<textarea name="editor1" id="editor1" style="width: 100%; height: 78%">' . htmlspecialchars( str_replace( '<br />', "\r\n", $content ) ) . '</textarea>', $htm );
			$page[ 'editor_HTML' ] = $htm;
		}
		
		// construct the language selection list
		$langl = $lang_loader->get_langlist();
		$langs = '<select name="language">';
		for ( $i = 0; $i < count( $langl ); $i++ )
		{
			$langs .= ( $langl[ $i ] == $language ) ? '<option selected>' . $langl[ $i ] . '</option>' : '<option>' . $langl[ $i ] . '</option>';
		}
		$langs .= '</select>';
		
		// construct the auth selection list :)
		$auth2 = '<select name="auth">';
		$s[ 0 ] = ( $auth == GUEST ) ? 'selected' : '';
		$s[ 1 ] = ( $auth == INACTIVE ) ? 'selected' : '';
		$s[ 2 ] = ( $auth == ADMIN ) ? 'selected' : '';
		$s[ 3 ] = ( $auth == SUPER_MOD ) ? 'selected' : '';
		$s[ 4 ] = ( $auth == MOD ) ? 'selected' : '';
		$s[ 5 ] = ( $auth == USER ) ? 'selected' : '';
		$auth2 .= '<option value="' . GUEST . '" ' . $s[ 0 ]  . '>' . $this->lang[ 'Guest' ] . '</option>';
		$auth2 .= '<option value="' . INACTIVE . '" ' . $s[ 1 ] . '>' . $this->lang[ 'Inactive' ] . '</option>';
		$auth2 .= '<option value="' . ADMIN . '" ' . $s[ 2 ] . '>' . $this->lang[ 'Admin' ] . '</option>';
		$auth2 .= '<option value="' . SUPER_MOD . '" ' . $s[ 3 ] . '>' . $this->lang[ 'Super_mod' ] . '</option>';
		$auth2 .= '<option value="' . MOD . '" ' . $s[ 4 ] . '>' . $this->lang[ 'Mod' ] . '</option>';
		$auth2 .= '<option value="' . USER . '" ' . $s[ 5 ] . '>' . $this->lang[ 'User' ] . '</option>';
		$auth2 .= '</select>';
		
		// the template stuff eh
		$template->assign_block_vars( 'edit', '', array(
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_pages&s=add_bar' ),
			'S_PAGE' => $page[ 'editor_HTML' ],
			'S_TITLE' => $title,
			'S_CONTENT' => htmlentities( $content ),
			'S_SELECT' => $list,
			'S_LANGS' => $langs,
			'S_MODE' => 'edit',
			'S_AUTH' => $auth,
			'S_AUTH2' => $auth2,
			
			'L_TITLE' => $this->lang[ 'Edit_title' ],
			'L_EXPLAIN' => $this->lang[ 'Edit_explain' ],
			'L_TITLE2' => $this->lang[ 'Edit_title2' ],
			'L_LANGUAGE' => $this->lang[ 'Edit_language' ],
			'L_SELECT' => $this->lang[ 'Edit_select' ],
			'L_AUTH' => $this->lang[ 'Edit_auth' ],
			'L_REMOVE' => $this->lang[ 'Edit_remove' ],
		) );
		
		// woot visible
		$template->assign_switch( 'edit', TRUE );
	}
	/**
	* edits the static page according to the submitted form
	*/
	function edit_page()
	{
		global $errors, $cache, $security, $basic_gui;
		
		// basic error check
		if ( !isset( $_POST[ 'submit_page' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// get data
		$title = ( isset( $_POST[ 'title' ] ) ) ? strval( $_POST[ 'title' ] ) : '';
		$lang = ( isset( $_POST[ 'language' ] ) ) ? strval( $_POST[ 'language' ] ) : '';
		$text =  str_replace( '&nbsp;', ' ', $basic_gui->gennuline( ( isset( $_POST[ 'editor1' ] ) ) ? strval( $_POST[ 'editor1' ] ) : '' ) );
		$auth =  ( isset( $_POST[ 'auth' ] ) ) ? intval( $_POST[ 'auth' ] ) : GUEST;
		$remove =  ( isset( $_POST[ 'remove' ] ) && $_POST[ 'remove' ] == 'on' ) ? TRUE : FALSE;
		
// 		$text = str_replace( "\n", '<br />', $text );
		
		if ( empty( $title ) || empty( $text ) )
		{ // need these
			$errors->report_error( $this->lang[ 'No_data' ], GENERAL_ERROR );
		}
		
		// save to the array
		if ( !is_array( $this->pages_array[ $lang ] ) )
		{ // make it an array, just to be sure
			$this->pages_array[ $lang ] = array();
		}
		if ( !$remove )
		{
			$pag = array( 'content' => $text, 'auth' => $auth );
			$this->pages_array[ $lang ][ $title ] = $pag;
		}else
		{
			unset( $this->pages_array[ $lang ][ $title ] );
		}
		
		// now write it
		$this->save_pages();
		
		$errors->report_error( $this->lang[ 'Edited' ], MESSAGE );
	}
	/**
	* this writes the pages file
	*/
	function save_pages()
	{
		global $Cl_root_path, $errors, $cache;
		
		// construct filename
		$dir = $Cl_root_path . 'kernel/config/';
		$file = $dir . 'static_pages' . phpEx;
		if ( !is_file( $file ) )
		{ // attempt to create it
			if ( !is_writable( $dir ) )
			{ // attempt to make it writable
				if ( !@chmod( $dir, 0755 ) )
				{
					$errors->report_error( $this->lang[ 'No_writable' ], CRITICAL_ERROR );
				}
			}
			if ( !@touch( $file ) )
			{
				$errors->report_error( $this->lang[ 'No_writable' ], CRITICAL_ERROR );
			}
		}elseif( !is_writable( $file ) )
		{
			if ( !@chmod( $file, 0644 ) )
			{
				$errors->report_error( $this->lang[ 'No_writable' ], CRITICAL_ERROR );
			}
		}
		
		// now open the file
		if ( !$f = @fopen( $file, 'wb' ) )
		{
			$errors->report_error( $this->lang[ 'No_writable' ], CRITICAL_ERROR );
		}
		
		// write the stuff
		$ser = serialize( $this->pages_array );
		if ( !@fwrite( $f, $ser ) )
		{
			$errors->report_error( $this->lang[ 'No_write' ], CRITICAL_ERROR );
		}
		
		// close the file
		@fclose( $f );
		// rechmod thingies
		@chmod( $dir, 0544 );
		@chmod( $file, 0444 );
		
		// clear cache
		$cache->delete( 'static_pages_menu' );
		$cache->delete( 'static_pages' );
	}
	
	//
	// End of ACP_pages class
	//
}

?>