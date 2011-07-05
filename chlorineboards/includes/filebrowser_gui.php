<?php

/**
*     defines the filebrowser_gui class
*     @file                filebrowser_gui.php
*     @see filebrowser_gui
*/
/**
* ui for the file_browser module
*     @class		   filebrowser_gui
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        04th April 2006
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

// class creation
$vars = array( );
$visible = array( );
eval( Varloader::createclass( 'filebrowser_gui', $vars, $visible ) );
// end class creation

class filebrowser_gui extends filebrowser_gui_def
{
	function filebrowser_gui()
	{
		global $template;
				
		// open up the tpl file
		$template->assign_files( array(
			'filebrowser' => 'file_browser' . tplEx
		) );
	}
	/**
	* displays the file browser
	* @access private
	*/
	function show( $upload )
	{
		global $template, $basic_gui, $Cl_root_path, $security;
		
		// basic thingies for the template
		$template->assign_block_vars( 'file_browser', '', array(
			'L_TITLE' => $this->lang[ 'Browser_title' ],
			'L_EXPLAIN' => $this->lang[ 'Browser_explain' ],
			'L_LOAD' => $this->lang[ 'Loading' ],
			'L_EDIT' => $this->lang[ 'Edit' ],
			'L_DELETE' => $this->lang[ 'Delete' ],
			'L_DOWNLOAD' => $this->lang[ 'Download' ],
			'L_VIEW' => $this->lang[ 'View' ],
			'L_RENAME' => $this->lang[ 'Rename' ],
			'L_UPREPORT' => $upload,
			
			'U_ACP' => $security->append_sid( '?' . MODE_URL . '=ACP' ),
			
			
			'ROOT' => $Cl_root_path,
		) );
		$template->assign_switch( 'file_browser', TRUE );
		// add to executionation
		$basic_gui->add_file( 'filebrowser' );
	}


	//
	// End of file_browser-gui class
	//
}


?>