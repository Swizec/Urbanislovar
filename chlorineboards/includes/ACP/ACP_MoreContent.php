<?php

/**
*     defines the ACP_MoreContent class
*     @file                ACP_MoreContent.php
*     @see ACP_MoreContent
*/
/**
* ACP panel for administration of the intricate static pages
*     @class		   ACP_MoreContent
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        21st February 2006
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
eval( Varloader::createclass( 'ACP_MoreContent', $vars, $visible ) );
// end class creation

class ACP_MoreContent extends ACP_MoreContent_def
{
	/**
	* constructor
	*/
	function ACP_MoreContent( $debug = FALSE )
	{
		global $Cl_root_path, $basic_gui, $lang_loader, $security;
		
		$this->debug = $debug;
	
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_MoreContent' );
		
		// make the two urls
		$url1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_MoreContent&s=manage' );
		
		// add to page
		// add to page
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $url1 . '">' . $this->lang[ 'Side_manage' ] . '</a></span>' );
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $template, $errors, $Cl_root_path;
		
		$template->assign_files( array(
			'ACP_MoreContent' => 'ACP/MoreContent' . tplEx
		) );
		
		// get the subsubmode
		$sub = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : 'add';
		
			
		switch( $sub )
		{
			case 'manage':
				$this->manage();
				break;
			case 'manage2':
				$this->manage2();
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* shows the management panel, nothing too fancy
	*/
	function manage()
	{
		global $template, $errors, $db, $mod_loader, $security;
		
		$menuid = ( isset( $_GET[ 'menuid' ] ) ) ? intval( $_GET[ 'menuid' ] ) : 0;
		
		if ( $menuid != 0 )
		{
			$sql = "SELECT * FROM " . MORECONTENT_MENU_TABLE . " WHERE menu_parent='$menuid'";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
			}
			$submenus = $db->sql_fetchrowset( $result );
			
			$sql = "SELECT m.*, c.* FROM " . MORECONTENT_MENU_TABLE . " m LEFT JOIN " . MORECONTENT_CONTENT_TABLE . " c ON c.menu_id=m.menu_id WHERE m.menu_id='$menuid' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
			}
			$menu = $db->sql_fetchrow( $result );
		}else
		{
			$sql = "SELECT * FROM " . MORECONTENT_MENU_TABLE . " WHERE menu_level='0'";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
			}
			$submenus = $db->sql_fetchrowset( $result );
			
			$menu = array( 'menu_id' => 0, 'menu_parent' => 0, 'menu_level' => -1, 'menu_title' => $this->lang[ 'Menu_na' ], 'menu_content' => 0, 'id' => 0, 'content' => '' );
		}
		
		// get the editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'editor1', 'quickpost' => FALSE,  'def_text' => stripslashes( $menu[ 'content' ] ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$editor = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		$frame = '<b><a href="%s">%s</a></b> :: ';
		$parsed_submenus = '';
		if ( is_array( $submenus ) )
		{
			foreach ( $submenus as $sub )
			{
				$url = $security->append_sid(  '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_MoreContent&s=manage&menuid=' . $sub[ 'menu_id' ] );
				$parsed_submenus .= sprintf( $frame, $url, $sub[ 'menu_title' ] );
			}
		}
		
		$template->assign_block_vars( 'manage', '', array(
			'L_TITLE' => $this->lang[ 'Manage_title' ],
			'L_EXPLAIN' => $this->lang[ 'Manage_explain' ],
			'L_TITLE2' => $this->lang[ 'Menu_title' ],
			'L_TITLE3' => $menu[ 'menu_title' ],
			'L_ADDMENU' => $this->lang[ 'Menu_add' ],
			'L_ADDTITLE' => $this->lang[ 'Menu_addtitle' ],
			'L_DELMENU' => $this->lang[ 'Menu_delete' ],
			'L_CHANGEMENU' => $this->lang[ 'Menu_change' ],
			'L_UP' => $this->lang[ 'Menu_up' ],
			
			'S_EDITOR' => $editor[ 'editor_HTML' ],
			'S_MENUS' => $parsed_submenus,
			'S_FORM_ACTION' => $security->append_sid(  '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_MoreContent&s=manage2&menuid=' . $menu[ 'menu_id' ] . '&menulevel=' . $menu[ 'menu_level' ] ),
			
			'U_UP' => $security->append_sid(  '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_MoreContent&s=manage&menuid=' . $menu[ 'menu_parent' ] ),
		) );
		$template->assign_switch( 'manage', TRUE );
	}
	/**
	* deals with submissions
	*/
	function manage2()
	{
		global $errors;
		
		if ( isset( $_POST[ 'iaddmenus' ] ) )
		{
			$this->addmenu();
			return;
		}
		if ( isset( $_POST[ 'isubmitpages' ] ) )
		{
			$this->submitpage();
			return;
		}
		if ( isset( $_POST[ 'ideletestuff' ] ) )
		{
			$this->remove();
			return;
		}
		if ( isset( $_POST[ 'ichangetitles' ] ) )
		{
			$this->change();
			return;
		}
		
		$errors->report_error( $this->lang[ 'Wrong_form' ] );
	}
	/**
	* adds a menu
	*/
	function addmenu()
	{
		global $errors, $db;
		
		$parent = ( isset( $_GET[ 'menuid' ] ) ) ? intval( $_GET[ 'menuid' ] ) : 0;
		$level = ( isset( $_GET[ 'menulevel' ] ) ) ? intval( $_GET[ 'menulevel' ] )+1 : 0;
		$title = $_POST[ 'newmenu' ];
		
		$sql = "INSERT INTO " . MORECONTENT_MENU_TABLE . " ( menu_parent, menu_level, menu_title )VALUES( '$parent', '$level', '$title' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t insert', CRITICAL_ERROR );
		}
		
		$sql = "SELECT menu_id FROM " . MORECONTENT_MENU_TABLE . " ORDER BY menu_id DESC LIMIT 1";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read', CRITICAL_ERROR );
		}
		$menuid = $db->sql_fetchfield( 'menu_id' );
		
		$sql = "INSERT INTO " . MORECONTENT_CONTENT_TABLE . " ( menu_id, content )VALUES( '$menuid', '' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t insert', CRITICAL_ERROR );
		}
		
		$errors->report_error( $this->lang[ 'Menu_added' ], MESSAGE );
	}
	/**
	* modifies a page
	*/
	function submitpage()
	{
		global $errors, $db;
		
		$menuid = ( isset( $_GET[ 'menuid' ] ) ) ? intval( $_GET[ 'menuid' ] ) : 0;
		
		if ( $menuid == 0 )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
		}
		
		$content = str_replace( '&nbsp;', ' ', $_POST[ 'editor1' ] );
		
		$sql = "UPDATE " . MORECONTENT_CONTENT_TABLE . " SET content='$content' WHERE menu_id='$menuid' LIMIT 1";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t insert', CRITICAL_ERROR );
		}
		
		$errors->report_error( $this->lang[ 'Content_done' ], MESSAGE );
	}
	/**
	* removes a menu
	*/
	function remove()
	{
		global $errors, $db;
		
		$menuid = ( isset( $_GET[ 'menuid' ] ) ) ? intval( $_GET[ 'menuid' ] ) : 0;
		
		$sql = "DELETE FROM " . MORECONTENT_MENU_TABLE . " WHERE menu_id='$menuid' LIMIT 1";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t delete', CRITICAL_ERROR );
		}
		
		$sql ="DELETE FROM " . MORECONTENT_CONTENT_TABLE . " WHERE menu_id='$menuid' LIMIT 1";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t delete', CRITICAL_ERROR );
		}
		
		$errors->report_error( $this->lang[ 'Menu_deleted' ], MESSAGE );
	}
	/**
	* changes the title of a menu
	*/
	function change()
	{
		global $errors, $db;
		
		$menuid = ( isset( $_GET[ 'menuid' ] ) ) ? intval( $_GET[ 'menuid' ] ) : 0;
		$title = $_POST[ 'newmenutitle' ];
		
		$sql = "UPDATE " . MORECONTENT_MENU_TABLE . " SET menu_title='$title' WHERE menu_id='$menuid' LIMIT 1";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t modify', CRITICAL_ERROR );
		}
		
		$errors->report_error( $this->lang[ 'Menu_changed' ], MESSAGE );
	}
	
	//
	// End of ACP_MoreContent class
	//
}

?>