<?php

/**
*     defines the filebrowser class
*     @file                filebrowser.php
*     @see filebrowser
*/
/**
* the file browser thingy for well... file browsing
*     @class		   filebrowser
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.1
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
// debug :: debug flag

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'filebrowser', $vars, $visible ) );
// end class creation

class filebrowser extends filebrowser_def
{
	/**
	* constructor
	*/
	function filebrowser( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax, $basic_gui;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/filebrowser_gui' . phpEx );
		$this->gui = new filebrowser_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'filebrowser', TRUE );
		$this->gui->lang = $this->lang;
		
		$Sajax->add2export( 'filebrowser->gettree', '$folder' );
		$Sajax->add2export( 'filebrowser->shw_edit', '$file, $id' );
		$Sajax->add2export( 'filebrowser->do_edit', '$file, $content' );
		$Sajax->add2export( 'filebrowser->shw_delete', '$file, $id' );
		$Sajax->add2export( 'filebrowser->do_delete', '$file' );
		$Sajax->add2export( 'filebrowser->shw_download', '$file, $id' );
		$Sajax->add2export( 'filebrowser->shw_view', '$file, $id' );
		$Sajax->add2export( 'filebrowser->do_rename', '$file, $val' );
		$Sajax->add2export( 'filebrowser->shw_upload', '$folder' );
		
		$basic_gui->add_JS( 'includes/filebrowser/browser.js' );
	}
	/**
	* takes care of choosing the correct thing to display
	*/
	function display()
	{
		global $errors, $userdata;
		
		// check perm
		if ( $userdata[ 'user_level' ] != ADMIN )
		{
			$errors->report_error( $this->lang[ 'No_perm' ], CRITICAL_ERROR );
		}
		
		if ( isset( $_POST[ 'uploaded' ] ) )
		{
			$upload = $this->file_upload();
		}else
		{
			$upload = '';
		}
		
		$this->gui->show( $upload );
	}
	/**
	* fetches the tree for a dir
	* @param string $dir path to dir
	* @return string the html of the tree
	* @access private
	*/
	function gettree( $dir )
	{
		global $basic_gui, $Cl_root_path4template, $template, $board_config;
	
		// this will be used for sorting the hash
		// there were errors without the check *shrugs*
		if ( !function_exists( 'cmp_dir' ) )
		{
			function cmp_dir( $a, $b )
			{
				if ( $a[ 0 ] && !$b[ 0 ] )
				{
					return -1;
				}elseif ( $b[ 0 ] && !$a[ 0 ] )
				{
					return 1;
				}elseif ( $a[ 0 ] == $b[ 0 ] )
				{
					if ( $a[ 1 ] == $b[ 1 ] )
					{
						return 0;
					}else
					{
						return ( $a[ 1 ] < $b[ 1 ] ) ? -1 : 1;
					}
				}
			}
		}
		
		// directory fixing
		if ( substr( $dir, -2 ) == '..' )
		{ // updir eh
			if ( $dir == './..' )
			{ // no outting, gah
				$dir = './';
			}else
			{
				// now simply remove the last part and stuff
				$dir = str_replace( substr( strrchr( str_replace( '/..', '', $dir ), '/' ), 0 ) . '/..', '', $dir );
			}
		}elseif ( substr( $dir, -1 ) == '.' )
		{ // current dir, wtf, no need
			$dir = str_replace( '/.', '', $dir );
		}
		
		// some var setup
		$file = '<div class="browser_entry" id="%s" onmousedown="change_selected( \'%s\' );" ondblclick="%s">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="%s" />&nbsp;&nbsp;%s%s%s</div>';
		$ret = array();
		$hash = array();
		$id = 'browser_file_%d';
		
		// the first is always the current dir
		$ret[] = $dir;
		
		// the top one eh :p
		$img = $Cl_root_path4template . $board_config[ 'script_path' ] . $template->folder . 'images/FolderOpened.gif';
		$ret[] = array( '', '', '<div class="browser_entry"><img src="' . $img . '" />&nbsp;&nbsp;<b>' . $dir . '</b></div>' );
		
		// go through the folder and create the list
		$d = dir( $dir );
		while ( FALSE !== ( $entry = $d->read() ) )
		{
			// add to hash first
			if ( is_dir( $dir . '/' . $entry ) )
			{
				$hash[] = array( TRUE, $entry );
			}else
			{
				$hash[] = array( FALSE, $entry );
			}
		}
		
		usort( $hash, "cmp_dir" );
		
		// create the output with the sorted list
		for ( $i = 0; $i < count( $hash ); $i++ )
		{
			$idd = sprintf( $id, $i );
			$b = ( $hash[ $i ][ 0 ] ) ? '<b>' : '';
			$b2 = ( $hash[ $i ][ 0 ] ) ? '</b>' : '';
			$dbl = ( $hash[ $i ][ 0 ] ) ? 'x_gettree( \'' . $dir . '/' . $hash[ $i ][ 1 ] . '\', return_tree );' : "''";
			// set up the image
			if ( $hash[ $i ][ 0 ] )
			{ // directory
				if ( $hash[ $i ][ 1 ] == '..' )
				{ // directory image with an arrow thingy
					$img = $Cl_root_path4template . $board_config[ 'script_path' ] . $template->folder . 'images/FolderUp.gif';
				}else
				{ // the regular directory image
					$img = $Cl_root_path4template . $board_config[ 'script_path' ] . $template->folder . 'images/Folder.gif';
				}
			}else
			{ // regular files
				$img = strtolower( substr( strrchr( $hash[ $i ][ 1 ], '.' ), 1) ); // get the type or rather extension
				$img = $template->folder . 'images/icons/' . $img . '.gif';
				$img = ( is_readable( $img ) ) ? $Cl_root_path4template . $board_config[ 'script_path' ] . $img : $Cl_root_path4template . $board_config[ 'script_path' ] . $template->folder . 'images/icons/default_icon.gif';
			}
			$ret[] = array( $idd, $dir . '/' . $hash[ $i ][ 1 ], sprintf( $file, $idd, $idd, $dbl, $img, $b, $hash[ $i ][ 1 ], $b2 ) );
		}
		
// 		print_R( $ret );
		
		return $ret;
	}
	/**
	* shows the file edit dialog
	* @param string $file path to file
	* @param string $id id of the file in the tree
	* @return string html of the dialog
	* @access private
	*/
	function shw_edit( $file, $id )
	{
		global $errors, $basic_lang;
		
		// first check if editable
		if ( !@is_writable( $file ) || !@is_readable( $file ) || !@is_file( $file ) )
		{
			return $errors->return_error( $this->lang[ 'No_editable' ], GENERAL_ERROR );
		}elseif( function_exists( 'mime_content_type' ) )
		{ // this function isn't always there
			if ( strpos( mime_content_type( $file ), 'text' ) !== TRUE )
			{ // if it can be checked then check if the file is text
				return $errors->return_error( $this->lang[ 'No_editable' ], GENERAL_ERROR );
			}
		}
		
		// everything was fine
		
		// read the text
		$text = file_get_contents( $file );
		
		// make the html
		$ret = '<form action="do_edit();" method="POST">';
		$ret .= '<textarea style="width: 400px; height: 400px;" id="edit_text">' . $text . '</textarea>';
		$ret .= '<br /><input type="submit" value="' . $basic_lang[ 'Submit' ] . '" name="submit_edit" onclick="do_edit();">';
		$ret .= '</form>';
		
		return $ret;
	}
	/**
	* does the edit of the file
	* @param string $file path to file
	* @param string $content new content of the file
	* @return string html of the error or success message
	* @access private
	*/
	function do_edit( $file, $content )
	{
		global $errors;
		
		// all the checks have been already done
		
		if ( !$f = @fopen( $file, 'wb' ) )
		{
			return $errors->return_error( $this->lang[ 'No_write' ], GENERAL_ERROR );
		}
		
		if ( !@fwrite( $f, $content ) )
		{
			return $errors->return_error( $this->lang[ 'No_write' ], GENERAL_ERROR );
		}
		
		@fclose( $f );
		
		return $errors->return_error( $this->lang[ 'Written' ], MESSAGE );
	}
	/**
	* shows the delete file dialog
	* @param string $file path to file
	* @param string $id id of the file in the tree
	* @return string html of the dialog
	* @access private
	*/
	function shw_delete( $file, $id )
	{
		global $errors, $basic_lang;
		
		// check stuff
		$f = explode( '/', $file );
		array_pop( $f );
		$f = implode( '/', $f );
		if ( !@is_writable( $f ) )
		{ // dir not writable
			return $errors->return_error( $this->lang[ 'No_write2' ], GENERAL_ERROR );
		}
		
		$text = sprintf( $this->lang[ 'Msg_delete' ], $file, 'do_delete();', 'window.location.reload();' );
		
		return $errors->return_error( $text, MESSAGE );
	}
	/**
	* recursively deletes a directory
	* @param string $path path to the dir
	* @access private
	*/
	function rcr_del( $path )
	{
		$d = dir( $path );
		
		while ( FALSE !== ( $entry = $d->read( ) ) )
		{
			if ( $entry == '.' || $entry == '..' )
			{ // these are no touch
				continue;
			}
			if ( @is_dir( $path . '/' . $entry ) )
			{ // is directory, recurse
				$this->rcr_del( $path . '/' . $entry );
				if ( !@rmdir( $path . '/' . $entry ) )
				{ // remove the dir itself
					return FALSE;
				}
			}elseif ( !@unlink( $path . '/' . $entry ) )
			{ // just a file, get rid of it
				return FALSE;
			}
		}
		if ( !@rmdir( $path . '/' . $entry ) )
		{ // remove the main dir itself
			return FALSE;
		}
		
		return TRUE;
	}
	/**
	* deletes a file or dir
	* @param string $file path to deletee
	* @access private
	*/
	function do_delete( $file )
	{
		global $errors;
		
		// check if directory
		if ( @is_dir( $file ) )
		{
			// recursively
			if ( !$this->rcr_del( $file ) )
			{
				return $errors->return_error( $this->lang[ 'No_delete' ], GENERAL_ERROR );
			}else
			{
				return $errors->return_error( $this->lang[ 'Deleted' ], GENERAL_ERROR );
			}
		}
		
		// not, so just remove and move on
		if ( !@unlink( $file ) )
		{
			return $errors->return_error( $this->lang[ 'No_delete' ], GENERAL_ERROR );
		}else
		{
			return $errors->return_error( $this->lang[ 'Deleted' ], GENERAL_ERROR );
		}
	}
	/**
	* shows the download file dialog
	* @param string $file path to file
	* @param string $id id of the file in the tree
	* @return string html of the dialog
	* @access private
	*/
	function shw_download( $file, $id )
	{
		global $errors, $basic_gui, $Cl_root_path;
		
		// check if it's readable
		if ( !@is_readable( $file ) )
		{
			return $errors->return_error( $this->lang[ 'No_download' ], GENERAL_ERROR );
		}
		
		if ( @is_file( $file ) )
		{
			// check if it's a php file
			$ext = substr( strrchr( $file, "."), 1 );
			if ( strtolower( $ext ) == 'php' )
			{
				// copy it into the cache as phps to avoid execution
				@copy( $file, $Cl_root_path . 'cache/' . substr( strrchr( $file, "/"), 1 ) . 's' );
				$file = $basic_gui->get_URL() . '/cache/' . substr( strrchr( $file, "/"), 1 ) . 's';
			}else
			{
				// make it an absolute path
				$file = str_replace( './', $basic_gui->get_URL(), $file );
			}
			
			// now make the html
			$html = "<span class=\"gen\"><a href=\"$file\" target=\"_blank\">" . $this->lang[ 'Download_click' ] . "</a></span>";
		}elseif( @is_dir( $file ) )
		{ // archive it and download that
			$name = substr( strrchr( $file, "/"), 1 );
			// create a temporary php file to run in the background
			$f = '<?php define( \'RUNNING_CL\', TRUE ); 
			define( \'PHPVER\', phpversion() );
			define( \'phpEx\',  \'' . phpEx . '\' );
			$Cl_root_path = \'' . $Cl_root_path . '\'; 
			include( $Cl_root_path . \'kernel/archive.php\' ); 
			$name = \'' . $name . '\'; $file = \'' . $file . '\';
			$arch = new zip_file( $Cl_root_path . \'cache/\' . $name . \'.zip\' ); // start a zip
			$arch->set_options( array( \'inmemory\' => 0, \'storepaths\' => 1, \'recurse\' => 1, \'overwrite\' => 1 ) ); // set some options
			$arch->add_files( $file ); // add the whole dir
			$arch->create_archive(); // create the archive
			if ( count( $arch->errors ) > 0 )
			{
				print( implode( \'<br />\', $arch->errors ) );
			}
			?>';
			$ff = @fopen( $Cl_root_path . 'cache/zip.php', 'wb' );
			@fwrite( $ff, $f );
			@fclose( $ff );
			// now execute it through the system, basically creates a background process
			$output = shell_exec( 'php ' . $Cl_root_path . 'cache/zip.php' );
			
			// check for errors
			if ( !empty( $output ) )
			{
				// there's errors
				return $errors->return_error( $this->lang[ 'Download_err' ] . '<br />' . $output, GENERAL_ERROR );
			}
			// now make the html
			$file = $basic_gui->get_URL() . '/cache/' . $name . '.zip';
			$html = "<span class=\"gen\"><a href=\"$file\" target=\"_blank\">" . $this->lang[ 'Download_click' ] . "</a></span>";
		}
		
		return $html;
	}
	/**
	* shows the view file dialog
	* @param string $file path to file
	* @param string $id id of the file in the tree
	* @return string html of the dialog
	* @access private
	*/
	function shw_view( $file, $id )
	{
		global $errors, $basic_gui, $Cl_root_path;
		
		if ( !@is_readable( $file ) || !@is_file( $file ) )
		{
			return $errors->return_error( $this->lang[ 'No_view' ], GENERAL_ERROR );
		}
		
		// see the mime
		if( function_exists( 'mime_content_type' ) )
		{ // this function isn't always there
			$mime = mime_content_type( $file );
		}else
		{ // do it the hard way
			$mime = shell_exec( 'python ' . $Cl_root_path . 'includes/filebrowser/getmime.py ' . str_replace( $Cl_root_path, $basic_gui->get_URL(), $file ) );
		}
		
		// now look if it's an image or text
		if ( strpos( $mime, 'text' ) === FALSE )
		{
			if ( strpos( $mime, 'image' ) === FALSE )
			{ // if it can be checked then check if the file is text
				if ( strpos( $mime, 'flash' ) !== FALSE )
				{ // display flash too
					$file = str_replace( $Cl_root_path, $basic_gui->get_URL(), $file );
					$output = '<object width="300" height="400">
						<param name="movie" value="' . $file .'">
						<embed src="' . $file . '" width="300" height="400">
						</embed>
						</object>';
				}else
				{
					return $errors->return_error( $this->lang[ 'No_view2' ], GENERAL_ERROR );
				}
			}else
			{ // an image
				$file = str_replace( $Cl_root_path, $basic_gui->get_URL(), $file );
				$output = '<div style="overflow:auto; width: 99%; height: 99%;"><img src="' . $file . '"></div>';
			}
		}else
		{ // text
			$contents = str_replace( "\n", '<br />', htmlspecialchars( $basic_gui->gennuline( @file_get_contents( $file ) ) ) );
			$output = '<div style="overflow:auto; width: 99%; height: 99%; text-align: justify;" class="gen">' . $contents . '</div>';
		}
		
		return $output;
	}
	/**
	* renames a file
	* @param string $file path to file
	* @param string $name new name
	* @return string html of the error or success message
	* @access private
	*/
	function do_rename( $file, $name )
	{
		global $errors;
		
		if ( !@is_writable( $file ) )
		{ // not writable
			return $errors->return_error( $this->lang[ 'No_rename' ], GENERAL_ERROR );
		}
		
		// rename it
		// construct the new name
		$n = explode( '/', $file );
		array_pop( $n );
		$name = implode( '/', $n ) . '/' . $name;
		
		if ( !@rename( $file, $name ) )
		{ // darn, didn't work
			return $errors->return_error( $this->lang[ 'No_rename2' ], GENERAL_ERROR );
		}
		
		return $errors->return_error( $this->lang[ 'Renamed' ], MESSAGE );
	}
	/**
	* returns the things for uploading
	*/
	function shw_upload( $dir )
	{
		$html = '<form enctype="multipart/form-data" action="" method="POST"><input type="hidden" value="' . $dir . '" name="directory" />
				<b>' . $this->lang[ 'Upload_file' ] . ':</b> <input name="uploadfile" type="file" /><br /><input type="submit" name="uploaded" value="' . $this->lang[ 'Upload' ] . '" /></form>';
	
		return $html;
	}
	/**
	* uploads a file
	*/
	function file_upload()
	{
		$dir = $_POST[ 'directory' ];
	
		// directory fixing
		if ( substr( $dir, -2 ) == '..' )
		{ // updir eh
			if ( $dir == './..' )
			{ // no outting, gah
				$dir = './';
			}else
			{
				// now simply remove the last part and stuff
				$dir = str_replace( substr( strrchr( str_replace( '/..', '', $dir ), '/' ), 0 ) . '/..', '', $dir );
			}
		}elseif ( substr( $dir, -1 ) == '.' )
		{ // current dir, wtf, no need
			$dir = str_replace( '/.', '', $dir );
		}
		$dir = str_replace( '//', '/', $dir );
		
		$file = $_FILES[ 'uploadfile' ];
		if ( $file[ 'error' ] != 0 )
		{
			return $this->lang[ 'Upload_error' ];
		}
		
		if ( !@copy( $file[ 'tmp_name' ], $dir . '/' . $file[ 'name' ] ) )
		{
			return $this->lang[ 'Upload_error' ];
		}
		
		return $this->lang[ 'Upload_good' ];
	}
	
	//
	// End of filebrowser class
	//
}

?>