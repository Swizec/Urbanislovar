<?php

/**
*     defines the ACP_sidebars class
*     @file                ACP_sidebars.php
*     @see ACP_sidebars
*/
/**
* this is an ACP panel for sidebars
*     @class		   ACP_pages
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.2.6
*     @since        30th March 2006
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
eval( Varloader::createclass( 'ACP_sidebars', $vars, $visible ) );
// end class creation

class ACP_sidebars extends ACP_sidebars_def
{
	/**
	* constructor
	*/
	function ACP_sidebars( $debug = FALSE )
	{
		global $Cl_root_path, $basic_gui, $lang_loader, $security;
		
		$this->debug = $debug;
	
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_sidebars' );
		
		// make the two urls
		$url1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_sidebars&s=add' );
		$url2 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_sidebars&s=edit' );
		$url3 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_sidebars&s=convert' );
		
		// add to sidebar
		// add to sidebar
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $url1 . '">' . $this->lang[ 'Side_add' ] . '</a><br /><a href="' . $url2 . '">' . $this->lang[ 'Side_edit' ] . '</a><br /><a href="' . $url3 . '">' . $this->lang[ 'Convert' ] . '</a></span>' );
		
		// get the sidebars array
		$dir = $Cl_root_path . 'kernel/config/';
		$file = $dir . 'static_sidebars' . phpEx;
		if ( is_file( $file ) )
		{
			$sidebars_array = @file_get_contents( $file );
			$this->sidebars_array = unserialize( $sidebars_array );
		}else
		{
			$this->sidebars_array = array();
		}
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $template, $errors, $Cl_root_path;
		
		$template->assign_files( array(
			'ACP_sidebars' => 'ACP/sidebars' . tplEx
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
						$this->add_bar();
						break;
					case 'edit':
						$this->edit_bar();
						break;
					default:
						$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
						break;
				}
				break;
			case 'convert':
				include( $Cl_root_path . 'kernel/config/static_sidebars' . phpEx );
				if ( isset( $sidebars ) )
				{
					$this->sidebars_array = $sidebars;
				}
				$this->save_sidebars();
				$errors->report_error( $this->lang[ 'Converted' ], MESSAGE );
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* shows the panel for the adding of static sidebars
	*/
	function adding()
	{
		global $template, $mod_loader, $security, $lang_loader;
		
		// get the editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => FALSE ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$sidebar = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
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
			'L_TITLE2' => $this->lang[ 'Add_title2' ],
			'L_LEFT' => $this->lang[ 'Left' ],
			'L_RIGHT' => $this->lang[ 'Right' ],
			'L_WHERE' => $this->lang[ 'Add_where' ],
			'L_LANGUAGE' => $this->lang[ 'Add_language' ],
			'L_AUTH' => $this->lang[ 'Add_auth' ],
			'L_HIDE' => $this->lang[ 'Add_hide' ],
			'L_ORDER' => $this->lang[ 'Add_order' ],
			
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_sidebars&s=add_bar' ),
			'S_SIDEBAR' => $sidebar[ 'editor_HTML' ],
			'S_WYSIWYG' => $sidebar[ 'editor_WYSIWYG' ],
			'S_MODE' => 'create',
			'S_LANGS' => $langs,
			'S_AUTH' => $auth2,
		) );
		
		$template->assign_switch( 'add', TRUE );
	}
	/**
	* adds the static sidebar according to the submitted form
	*/
	function add_bar()
	{
		global $errors, $Cl_root_path, $cache, $basic_gui;
		
		// main check
		if ( !isset( $_POST[ 'submit_sidebar' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// get data
		$title = ( isset( $_POST[ 'title' ] ) ) ? strval( $_POST[ 'title' ] ) : '';
		$where = ( isset( $_POST[ 'where' ] ) ) ? strval( $_POST[ 'where' ] ) : '';
		$lang = ( isset( $_POST[ 'language' ] ) ) ? strval( $_POST[ 'language' ] ) : '';
		$text =  str_replace( '&nbsp;', ' ', $basic_gui->gennuline( ( isset( $_POST[ 'editor1' ] ) ) ? strval( $_POST[ 'editor1' ] ) : '' ) );
		$auth =  ( isset( $_POST[ 'auth' ] ) ) ? intval( $_POST[ 'auth' ] ) : GUEST;
		$hide =  ( isset( $_POST[ 'hide' ] ) && $_POST[ 'hide' ] == 'on' ) ? TRUE : FALSE;
		$order = ( isset( $_POST[ 'order' ] ) ) ? intval( $_POST[ 'order' ] ) : '';
		
// 		$text = str_replace( "\n", '<br />', $text );
		
		if ( empty( $title ) || empty( $text ) )
		{ // need these
			$errors->report_error( $this->lang[ 'No_data' ], GENERAL_ERROR );
		}
		
		// add to array
		$bar = array( 'side' => $where, 'content' => $text, 'auth' => $auth, 'hidden' => $hide, 'order' => $order );
		$this->sidebars_array[ $lang ][ $title ] = $bar;
		
		// write it
		$this->save_sidebars();
		
		$errors->report_error( $this->lang[ 'Added' ], MESSAGE );
	}
	/**
	* shows the panel for editing of the static sidebars
	*/
	function editting()
	{
		global $template, $mod_loader, $security, $errors, $lang_loader;
		
		// which is chosen
		$b = ( isset( $_GET[ 'bar' ] ) ) ? str_replace( '%20', ' ', strval( $_GET[ 'bar' ] ) ) : '';
		
		// create the list
		$list = '<select onchange="window.location.href = this.value">';
		$list .= ( empty( $b ) ) ? '<option selected value=""> </option>' : '<option value=""> </option>';
		if ( is_array( $this->sidebars_array ) )
		{
			foreach ( $this->sidebars_array as $lang => $bars )
			{
				foreach ( $bars as $name => $bar )
				{
					$list .= ( $b == $lang . '-' . $name ) ? '<option selected ' : '<option ';
					$list .= 'value="' . $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_sidebars&s=edit&bar=' . $lang . '-' . $name ) . '">' . $lang . ' :: ' . $name . '</option>';
				}
			}
		}
		$list .= '</select>';
		
		// determine the content of the editee
		if ( !empty( $b ) )
		{
			// get the content
			$content = explode( '-', $b );
			$side = $this->sidebars_array[ $content[ 0 ] ][ $content[ 1 ] ][ 'side' ];
			$order = $this->sidebars_array[ $content[ 0 ] ][ $content[ 1 ] ][ 'order' ];
			$title = $content[ 1 ];
			$language = $content[ 0 ];
			$auth = $this->sidebars_array[ $content[ 0 ] ][ $content[ 1 ] ][ 'auth' ];
			$hide = ( $this->sidebars_array[ $content[ 0 ] ][ $content[ 1 ] ][ 'hidden' ] ) ? 'TRUE' : 'FALSE';
			$content = $this->sidebars_array[ $content[ 0 ] ][ $content[ 1 ] ][ 'content' ];
			// side selection thing
			$side_sel = '<select name="side">';
			$side_sel .= ( $side == 'left' ) ? '<option selected value="left">' . $this->lang[ 'Left' ] . '</option>' : '<option value="left">' . $this->lang[ 'Left' ] . '</option>';
			$side_sel .= ( $side == 'right' ) ? '<option selected value="right">' . $this->lang[ 'Right' ] . '</option>' : '<option value="right">' . $this->lang[ 'Right' ] . '</option>';
			$side_sel .= '</select>';
		}else
		{
			$content = '';
			$side_sel = '';
			$order = '';
			$title = '';
			$language = '';
			$auth = '';
			$hide = '';
		}
		
		// get the editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => FALSE, 'def_text' => stripslashes( $content ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$sidebar = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		if ( !$sidebar[ 'editor_WYSIWYG' ] )
		{ // make it so that newlines are actually newlines in the textarea
			$htm = $sidebar[ 'editor_HTML' ];
			$htm = str_replace( '<textarea name="editor1" id="editor1" style="width: 100%; height: 78%">' . htmlspecialchars( $content ) . '</textarea>', '<textarea name="editor1" id="editor1" style="width: 100%; height: 78%">' . htmlspecialchars( str_replace( '<br />', "\r\n", $content ) ) . '</textarea>', $htm );
			$sidebar[ 'editor_HTML' ] = $htm;
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
			'S_FORM_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_sidebars&s=add_bar' ),
			'S_SIDEBAR' => $sidebar[ 'editor_HTML' ],
			'S_TITLE' => $title,
			'S_SIDES' => $side_sel,
			'S_SELECT' => $list,
			'S_LANGS' => $langs,
			'S_MODE' => 'edit',
			'S_HIDE' => $hide,
			'S_HIDE2' =>( $hide == 'TRUE' ) ? 'checked' : '',
			'S_AUTH2' => $auth2,
			'S_ORDER' =>$order,
			
			'L_TITLE' => $this->lang[ 'Edit_title' ],
			'L_EXPLAIN' => $this->lang[ 'Edit_explain' ],
			'L_TITLE2' => $this->lang[ 'Edit_title2' ],
			'L_SIDE' => $this->lang[ 'Edit_side' ],
			'L_LANGUAGE' => $this->lang[ 'Edit_language' ],
			'L_SELECT' => $this->lang[ 'Edit_select' ],
			'L_HIDE' => $this->lang[ 'Edit_hide' ],
			'L_AUTH' => $this->lang[ 'Edit_auth' ],
			'L_REMOVE' => $this->lang[ 'Edit_remove' ],
			'L_ORDER' => $this->lang[ 'Edit_order' ],
		) );
		
		// woot visible
		$template->assign_switch( 'edit', TRUE );
	}
	/**
	* edits the static page according to the submitted form
	*/
	function edit_bar()
	{
		global $errors, $cache, $basic_gui, $security;
		
		// basic error check
		if ( !isset( $_POST[ 'submit_sidebar' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], CRITICAL_ERROR );
		}
		
		// get data
		$title = ( isset( $_POST[ 'title' ] ) ) ? strval( $_POST[ 'title' ] ) : '';
		$where = ( isset( $_POST[ 'side' ] ) ) ? strval( $_POST[ 'side' ] ) : '';
		$lang = ( isset( $_POST[ 'language' ] ) ) ? strval( $_POST[ 'language' ] ) : '';
		$text =  str_replace( '&nbsp;', ' ', $basic_gui->gennuline( ( isset( $_POST[ 'editor1' ] ) ) ? strval( $_POST[ 'editor1' ] ) : '' ) );
		$auth =  ( isset( $_POST[ 'auth' ] ) ) ? intval( $_POST[ 'auth' ] ) : GUEST;
		$hide =  ( isset( $_POST[ 'hide' ] ) && $_POST[ 'hide' ] == 'on' ) ? TRUE : FALSE;
		$remove =  ( isset( $_POST[ 'remove' ] ) && $_POST[ 'remove' ] == 'on' ) ? TRUE : FALSE;
		$order = ( isset( $_POST[ 'order' ] ) ) ? intval( $_POST[ 'order' ] ) : 1;
		
// 		$text = str_replace( "\n", '<br />', $text );
		
		if ( empty( $title ) || empty( $text ) )
		{ // need these
			$errors->report_error( $this->lang[ 'No_data' ], GENERAL_ERROR );
		}
		
		// add to array
		if ( !is_array( $this->sidebars_array[ $lang ] ) )
		{ // make it an array, just to be sure
			$this->sidebars_array[ $lang ] = array();
		}
		if ( !$remove )
		{
			$bar = array( 'side' => $where, 'content' => $text, 'auth' => $auth, 'hidden' => $hide, 'order' => $order );
			$this->sidebars_array[ $lang ][ $title ] = $bar;
		}else
		{
			unset( $this->sidebars_array[ $lang ][ $title ] );
		}
		
		// write it
		$this->save_sidebars();
		
		$errors->report_error( $this->lang[ 'Edited' ], MESSAGE );
	}
	/**
	* this writes the sidebars file
	*/
	function save_sidebars()
	{
		global $Cl_root_path, $errors, $cache, $security;
		
		// construct filename
		$dir = $Cl_root_path . 'kernel/config/';
		$file = $dir . 'static_sidebars' . phpEx;
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
		}elseif( !@is_writable( $file ) )
		{
			if ( !@chmod( $file, 0644 ) )
			{
				$errors->report_error( $this->lang[ 'No_writable' ], CRITICAL_ERROR );
			}
		}
		
		// go through the array and make sure everything is escaped properly
// 		for ( $i = 0; $i < count( $this->sidebars_array )-1; $i++ )
// 		{
// 			$this->sidebars_array[ $i ][ 'content' ] = $security->parsevar( serialize( $this->sidebars_array ), ADD_SLASHES, TRUE );
// 		}
		
		// now open the file
		if ( !$f = @fopen( $file, 'wb' ) )
		{
			$errors->report_error( $this->lang[ 'No_writable' ], CRITICAL_ERROR );
		}
		
		// write the stuff
		$ser = serialize( $this->sidebars_array );
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
		$cache->delete( 'static_sidebars' );
		$cache->delete( 'static_sidebars_hash' );
	}
	
	//
	// End of ACP_sidebars class
	//
}


?>