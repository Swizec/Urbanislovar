<?php

/**
* Tag cloud creation class
*     @class		   TagCloud
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        16th July 2007
* @filesource
*/

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
eval( Varloader::createclass( 'TagCloud', $vars, $visible ) );
// end class creation

class TagCloud extends TagCloud_def
{
	/**
	* constructor
	*/
	function TagCloud( $debug )
	{
		global $template, $Cl_root_path, $lang_loader, $Sajax, $basic_gui, $userdata;
		
		// change skin to the one proper for this
		$userdata[ 'user_skin' ] = 'TagCloud';
		$template->change_folder( $Cl_root_path . 'template/TagCloud/' );
		$basic_gui->reload_config();
		
		//$this->full_rmdir( $Cl_root_path . 'cache' );
		
		$this->lang = $lang_loader->get_lang( 'TagCloud', TRUE );
		
		$this->mode = isset( $_GET[ SUBMODE_URL ] ) ? $_GET[ SUBMODE_URL ] : 'interface';
		
		$Sajax->add2export( 'TagCloud->check_done', '$time' );
		$Sajax->add2export( 'TagCloud->funny_image', '' );
		$Sajax->add2export( 'TagCloud->check_upload', '$id, $time' );
		$Sajax->add2export( 'TagCloud->add_uri', '$id, $time, $uri' );
		$Sajax->add2export( 'TagCloud->removeFile', '$id, $time' );
		$Sajax->add2export( 'TagCloud->addInfo', '$words, $sentences, $paragraphs, $language, $time, $id' );
		$Sajax->add2export( 'TagCloud->checkDoneExport', '$time, $id, $size' );
		$Sajax->add2export( 'TagCloud->clickies', '$time, $id' );
		$Sajax->add2export( 'TagCloud->getAnalysis', '$word, $id, $time, $cluster' );
		$Sajax->add2export( 'TagCloud->sessionName', '$time' );
		$Sajax->add2export( 'TagCloud->changeSessionName', '$time, $name' );
		$Sajax->add2export( 'TagCloud->saveSession', '$time' );
		$Sajax->add2export( 'TagCloud->documentName', '$time, $id' );
		$Sajax->add2export( 'TagCloud->changeDocumentName', '$time, $name, $id' );
		$Sajax->add2export( 'TagCloud->removeDocument', '$id, $time' );
		$Sajax->add2export( 'TagCloud->removeSession', '$time' );
		$Sajax->add2export( 'TagCloud->swapImage', '$id, $time' );
		$Sajax->add2export( 'TagCloud->newTextPad', '$id, $time' );
		$Sajax->add2export( 'TagCloud->getTextPad', '$id, $time' );
		$Sajax->add2export( 'TagCloud->saveTextPad', '$id, $time, $contents' );
		$Sajax->add2export( 'TagCloud->removeTextPad', '$id, $time' );
		$Sajax->add2export( 'TagCloud->moveFile', '$id, $fromtime, $totime, $dirid, $olddir' );
		$Sajax->add2export( 'TagCloud->newScrapBook', '' );
		
		// used extensively in all parts of the UI
		$basic_gui->add_JS( 'includes/TagCloud/TagCloud.js' );
		
		$template->assign_files( array(
			'TagCloud' => 'TagCloud' . tplEx,
			'TagCloud_filerow' => 'TagCloud_filerow' . tplEx,
		) );
		
		$template->assign_vars( array(
			'L_BROWSER_WARN' => $this->lang[ 'Browser_warn' ],
		) );
		
		$this->stemHash = Array();
	}
	/**
	* does the display stuff
	*/
	function displayFunc()
	{
		global $template, $basic_gui, $userdata, $Cl_root_path, $security;
		
		if ( $this->mode == 'activate' )
		{
			$this->activate();
			header( 'Location: ' . $security->append_sid( '?' . MODE_URL . '=tagcloud' ) );
			die();
		}
		
		if ( $userdata[ 'user_level' ] == GUEST)
		{ // guests only get to login
			$this->login();
		}else
		{
			switch( $this->mode )
			{ // let's see what stuff we need to do
				case 'intake':
					if ( !$_GET[ 'AJAX_CALL' ] )
					{
						$this->intake();
					}
					break;
				case 'display':
					$this->display();
					break;
				case 'file':
					$this->file();
					break;
				case 'export':
					$this->export();
					break;
				case 'export2':
					$this->export2();
					break;
				default:
					$this->interfac();
			}
		}
		
		$basic_gui->add_file( 'TagCloud' );
	}
	/**
	* takes care of login stuff
	*/
	function login()
	{
		global $template, $security;
		
		$template->assign_block_vars( 'login', '', array(
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=tagcloud&' . SUBMODE_URL . '=activate' ),
			
			'L_USERNAME' => $this->lang[ 'login_username' ],
			'L_PASSWORD' => $this->lang[ 'login_password' ],
		) );
		$template->assign_switch( 'login', TRUE );
	}
	/**
	* activates the user session :)
	*/
	function activate()
	{
		global $mod_loader, $userdata;
		
		$mods = $mod_loader->getmodule( 'login', MOD_FETCH_NAME, ESSENTIAL );
		$mod_loader->port_vars( array( 'username' => $_POST[ 'meow' ], 'password' => $_POST[ 'purr' ], 'autolog' => FALSE ) );
		$mod_loader->execute_modules( 0, 'activation' );
		
		$success = $mod_loader->get_vars( array( 'success', 'user_level' ) );
		
		if ( $success[ 'success' ] )
		{
			$userdata[ 'user_level' ] = $success[ 'user_level' ];
		}
	}
	/**
	* takes care of interface stuff
	*/
	function interfac()
	{
		global $template, $security, $basic_gui;
		
		$basic_gui->add_JS( 'includes/TagCloud/TagCloud_upload.js' );
		
		$langs = array( 'en' => $this->lang[ 'Interface_en' ], 'sl' => $this->lang[ 'Interface_sl' ] );
		$langOpt = '<option value="%s">%s</option>';
		$languages = '<select name="language">';
		
		foreach ( $langs as $lang => $name )
		{
			$languages .= sprintf( $langOpt, $lang, $name );
		}
		
		$languages .= '</select>';
		
		$dTime = $this->directTime();
		
		$template->assign_block_vars( 'interface', '', array(
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=tagcloud&' . SUBMODE_URL . '=intake' ),
			'S_LANGUAGE' => $languages,
			'S_SIZE' => $size,
			'S_FILE_ACTION' => $security->append_sid( '?' . MODE_URL . '=tagcloud&' . SUBMODE_URL . '=file' ),
			'S_TIME' => $this->microtime_float(),
			'SD_TIME' => $dTime,
			
			'L_LANGUAGE' => $this->lang[ 'Interface_language' ],
			'L_TEXT' => $this->lang[ 'Interface_text' ],
			'L_SIZE' => $this->lang[ 'Interface_size' ],
			'L_DIRECT' => $this->lang[ 'Interface_direct' ],
			
			'U_SELF' => $security->append_sid( '?' . MODE_URL . '=tagcloud' ),
			'U_DIRECT' => $security->append_sid( '?'. MODE_URL . '=tagcloud&' . SUBMODE_URL . '=display&time=' . $dTime )
		) );
		$template->assign_switch( 'interface', TRUE );
	}
	/**
	* does the stuff needed to prepare the text and fork the process for the results
	*/
	function intake()
	{
		global $basic_gui, $Sajax, $template, $Cl_root_path, $errors;
		
		$basic_gui->add_JS( 'includes/TagCloud/TagCloud_upload.js' );
		
		$text = ( isset( $_POST[ 'text' ] ) ) ? $_POST[ 'text' ] : '';
		//$size = ( isset( $_POST[ 'size' ] ) ) ? $_POST[ 'size' ] : '300x150';
		$time = ( isset( $_POST[ 'time' ] ) ) ? $_POST[ 'time' ] : $this->microtime_float(); // because potentially a second just isn't accurate enough when several people are using the service
		
		$size = '1024x800';
		
		if ( !empty( $text ) )
		{
			if ( !is_dir( $Cl_root_path . 'cache/' . $time ) )
			{
				mkdir( $Cl_root_path . 'cache/' . $time, 0777 );
				chmod( $Cl_root_path . 'cache/' . $time, 0777 );
			}
			
			$hash = $Cl_root_path . 'cache/' . $time . '/filehash.php';
			
			if ( is_readable( $hash ) )
			{
				include( $hash );
				$n = count( $inputHash );
				$content = file_get_contents( $hash );
			}else
			{
				$n = 0;
				$content = '<?php $inputHash = array();?>';
			}
			
			$file = $_SERVER[ 'DOCUMENT_ROOT' ] . '/clb/cache/' . $time . '/' . $n . '.txt';
			file_put_contents( $file, $text );
			$s = strlen( $text );
			
			$add = " \$inputHash[] = array( 'target' => '$file', 'type' => 'text/plain', 'name' => '$n.txt', 'error' => FALSE, 'done' => TRUE, 'size' => $s, 'format' => 'txt' );?>";
			
			$content = str_replace( '?>', $add, $content );
			file_put_contents( $hash, $content );
		}
		
		$this->runBackend( $time, $size );
		
		$template->assign_block_vars( 'loading', '', array(
			'L_LOADING' => $this->lang[ 'Loading_loading' ],
			
			'TIME' => $time,
		) );
		$template->assign_switch( 'loading', TRUE );
	}
	/**
	* makes pretty microtime
	*/
	function microtime_float()
	{
   		list($usec, $sec) = explode(" ", microtime());
   		return round( ((float)$usec + (float)$sec), 1 );
	}
	/**
	* checks if the background process has finished
	*/
	function check_done( $time )
	{
		global $Cl_root_path, $security;
		
		$file = $Cl_root_path . "cache/$time/done.txt";
		
		if ( is_readable( $file ) )
		{
			$uri = $security->append_sid( '?' . MODE_URL . '=tagcloud&' . SUBMODE_URL . '=display&time=' . $time );
			return array( 1, $time, $uri );
		}else
		{
			return array( 0, $time );
		}
	}
	/**
	* displays the results
	*/
	function display()
	{
		global $Cl_root_path, $template, $basic_gui, $security, $userdata;
		
		$basic_gui->add_JS( 'includes/TagCloud/TagCloud_inside.js' );
		
		$time = ( isset( $_GET[ 'time' ] ) ) ? $_GET[ 'time' ] : '';
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		//include( $path . '/filehash' . phpEx );
		
		$img = '';
		
		// first do the current session thingy
		$dir = $Cl_root_path . 'cache/';
		$i = 0;
		if ( is_dir( $dir . '/' . $time ) )
		{
			$this->add_dispDir( $dir, $time, $i, $img, $time );
		}else
		{
			$i = -1;
		}
		
		//$path = $Cl_root_path . 'cache/' . $time;		
		$dir = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ];
		$textpads = array();
		if ( is_dir ( $dir ) )
		{
			$d = dir( $dir );
			$i++;
			
			while ( FALSE !== ( $entry = $d->read() ) )
			{
				if ( $entry == '.' || $entry == '..' || !is_dir( $dir . '/' . $entry ) )
				{
					continue;
				}
				if ( substr( $entry, 0, 8 ) == 'textpad_' )
				{
					$textpads[] = $entry;
					$i -= 1;
				}else
				{
					$this->add_dispDir( $dir, $entry, $i, $img, $entry );
				}
				
				$i++;
			}
		}
		
		for ( $j = 0; $j < count( $textpads ); $j++, $i++ )
		{
			$this->add_dispDir( $dir, $textpads[ $j ], $i, $img, file_get_contents( $dir . '/' . $textpads[ $j ] . '/time.txt' ), $textpads[ $j ] );
		}
		
		include( $path . '/filehash' . phpEx );
		$hashKeys = array_keys( $inputHash );
		$clickies = $this->clickies( $time, $hashKeys[ 0 ] );
		
		$export = array();
		for ( $i = 1; $i <= 5; $i++ )
		{
			$export[ 'TOOL' . $i ] = $basic_gui->make_tooltip( sprintf( $this->lang[ 'Advanced_export' ], $this->lang[ 'Advanced_size_' . $i ] ), 'tip' );
			$export[ 'URL' . $i ] = $security->append_sid( '?' . MODE_URL . '=tagcloud&' . SUBMODE_URL . "=export&size=$i&time=$time&id=" );
			$export[ 'TIME' . $i ] = $time;
		}
		
		$advtools = array(
			'REMOVE' => $basic_gui->make_tooltip( $this->lang[ 'Advtool_remove' ], 'tip' ),
			'SAVE2' => $basic_gui->make_tooltip( $this->lang[ 'Advtool_save2' ], 'tip' ),
			'REMOVE2' => $basic_gui->make_tooltip( $this->lang[ 'Advtool_remove2' ], 'tip' ),
			'RENAME' => $basic_gui->make_tooltip( $this->lang[ 'Advtool_rename' ], 'tip' ),
			'RENAME2' => $basic_gui->make_tooltip( $this->lang[ 'Advtool_renameDocument' ], 'tip' ),
			'TEXTPAD' => $basic_gui->make_tooltip( $this->lang[ 'Advtool_newTextPad' ], 'tip' ),
			'SCRAPBOOK' => $basic_gui->make_tooltip( $this->lang[ 'Advtool_newScrapBook' ], 'tip' ),
		);
		
		$template->assign_block_vars( 'display', '', array(
			'TIME' => $time,
			'EXPORT' => $export,
			'CLICKIES' => $clickies,
			'ADVTOOLS' => $advtools,
		
			'U_IMAGE' => $img,
			
			'L_NAME' => $this->lang[ 'Info_name' ],
			'L_WORDS' => $this->lang[ 'Info_words' ],
			'L_SENTENCES' => $this->lang[ 'Info_sentences' ],
			'L_PARAGRAPHS' => $this->lang[ 'Info_paragraphs' ],
			'L_LANGUAGE' => $this->lang[ 'Info_language' ],
			'L_ANALYZE' => $this->lang[ 'Advanced_analyze' ],
			'L_QUICK' => $this->lang[ 'Quick_title' ],
			'L_INSTRUCTIONS1' => $this->lang[ 'Quick_instructions1' ],
			'L_INSTRUCTIONS2' => $this->lang[ 'Quick_instructions2' ],
			'L_INSTRUCTIONS3' => $this->lang[ 'Quick_instructions3' ],
			'L_SESSION_RENAMED' => $this->lang[ 'Session_renamed' ],
			'L_LOADING' => $this->lang[ 'Loading_loading' ],
		) );
		$template->assign_switch( 'display', TRUE );
	}
	/**
	* adds a directory to the display
	*/
	function add_dispDir( $dir, $entry, $i, &$img, $time, $textpad = '' )
	{
		global $Cl_root_path, $template, $basic_gui, $security, $userdata;
		
		include( $dir . '/' . $entry . '/filehash' . phpEx );
		
		$basic_gui->add_drag( 'dir_' . $i, 'NO_DRAG' );
		$basic_gui->add_drag( 'endOfDir_' . $i, array( 'NO_DRAG', 'DETACH_CHILDREN' ) );
		$dirname = $dir . '/' . $entry . '/name.txt';
		$dirname = ( is_readable( $dirname ) ) ? urldecode( file_get_contents( $dirname ) ) : '';
		
		$icon = $dir . '/' . $entry . '/icon.txt';
		$icon = ( is_readable( $icon ) ) ? file_get_contents( $icon ) : 'folder.png';
		
		$tooltip = array(
			'L_NAME' => $this->lang[ 'Info_name' ],
			'L_FILES' => $this->lang[ 'Info_files' ],
			
			'TIME' => date( $userdata[ 'user_timeformat' ], $time ),
			'NAME' => $dirname,
			'FILES' => count( $inputHash )
		);
		$template->assign_block_vars( 'dirrow', '', array(
			'ID' => $i,
			'INFO' => $basic_gui->make_tooltip( '', 'info', 'dir_' . $i . 'Tip' ),
			'TIME' => ( $textpad != '' ) ? "'$textpad'" : $time,
			'ICON' => $icon,
			
			'TIP' => $tooltip
		) );
		$template->assign_switch( 'dirrow', TRUE );
		
		if ( !empty( $inputHash ) )
		{
			foreach ( $inputHash as $j => $file )
			{
				$this->add_fileRow( $file, $img, $i, $j, $dir, $entry );
			}
		}
		
// 		$template->assign_switch( 'dirrow', TRUE );
	}
	/**
	* adds a file to the display
	*/
	function add_fileRow( $file, &$img, $i, $j, $dir, $entry )
	{
		global $Cl_root_path, $template, $basic_gui, $security, $userdata;
		
		if ( $file[ 'format' ] == 'unknown' || !is_readable( "$dir/$entry/$j.txt" ) )
		{
			return;
		}
		if ( empty( $img ) )
		{
			$img = $dir . '/' . $entry . '/image_' . $j . '.png';
		}
		
		$basic_gui->add_drag( 'file_' . $i . '_' . $j );
		$basic_gui->add_drag( 'shadow_' . $i . '_' . $j, 'NO_DRAG' );
		$basic_gui->add_drag( 'replacement_' . $i . '_' . $j, 'NO_DRAG' );
		
		$tooltip = "<span class=\"info\">${file[ 'name' ]}<br />" .
					"<b>" . $this->lang[ 'Info_language' ] . ": </b>" . $this->lang[ 'Info_lang_' . $file[ 'language' ] ] . "<br />" . 
					"<b>" . $this->lang[ 'Info_words' ] . ": </b>${file[ 'words' ]}<br />" .
					"<b>" . $this->lang[ 'Info_sentences' ] . ": </b>${file[ 'sentences' ]}<br />" .
					"<b>" . $this->lang[ 'Info_paragraphs' ] . ": </b>${file[ 'paragraphs' ]}<br /></span>";
		
		$template->assign_block_vars( 'dirrow.filerow', '', array(
			'ID' => $i . '_' . $j,
			'ICON' => $file[ 'format' ] . '_72.png',
			'SHADOW_ICON' => $file[ 'format' ] . '_72_shadow.png',
			'NAME' => $file[ 'name' ],
			'WORDS' => $file[ 'words' ],
			'SENTENCES' => $file[ 'sentences' ],
			'PARAGRAPHS' => $file[ 'paragraphs' ],
			'LANGUAGE' => $this->lang[ 'Info_lang_' . $file[ 'language' ] ],
			'INFO' => $basic_gui->make_tooltip( '', 'info', 'file_' . $i . '_' . $j . 'Tip' ),
			'TYPE' => $file[ 'format' ],
			'TIP' => array(
					'NAME' => ( isset( $file[ 'displayName' ] ) ) ? $file[ 'displayName' ] : $file[ 'name' ],
					'LANGUAGE' => $this->lang[ 'Info_lang_' . $file[ 'language' ] ],
					'WORDS' => $file[ 'words' ],
					'SENTENCES' => $file[ 'sentences' ],
					'PARAGRAPHS' => $file[ 'paragraphs' ],
					'URI' => ( isset( $file[ 'URI' ] ) ) ? $file[ 'URI' ] : '',
				)
		) );
		$template->assign_switch( 'dirrow.filerow', TRUE );
	}
	/**
	* Converts XML into Array 
	*
	* @param array $result
	* @param object  $root
	* @param string $rootname
	*/
	function convert_xml2array(&$result,$root,$rootname='root')
	{
		$n=count($root->children());

		if ($n>0){
			if (!isset($result[$rootname]['@attributes'])){
				$result[$rootname]['@attributes']=array();
				foreach ($root->attributes() as $atr=>$value){
					$result[$rootname]['@attributes'][$atr]=(string)$value;
				}
			}
			
			foreach ($root->children() as $child){
				$name=$child->getName();     
				$this->convert_xml2array($result[$rootname][],$child,$name);
			}
		} else {
			$result[$rootname]= (array) $root;
			if (!isset($result[$rootname]['@attributes'])){
				$result[$rootname]['@attributes']=array();
			}
		}
	}
	/**
	* displays funny images during loading
	*/
	function funny_image()
	{
		global $Cl_root_path;
		
		$file = $Cl_root_path . 'cache/tagcloud_funny' . phpEx;
		
		if ( is_readable( $file ) )
		{
			include( $file );
		}else
		{
			$images = array( 'time' => 0, 'xml' => '' );
		}
		
		if ( $images[ 'time' ] < EXECUTION_TIME - 86400 || empty( $images[ 'xml' ] ) )
		{ // the feed will be checked only if the last check was over a day ago
			$images[ 'time' ] = EXECUTION_TIME;
			$images[ 'xml' ] = file_get_contents( 'http://dropline.net/cats/rss.xml' );
			
			file_put_contents( $file, '<?php $images = array( "time" => ' . $images[ 'time' ] . ', "xml" => \'' . $images[ 'xml' ] . '\' ); ?>' );
		}
		
		$xml = array();
		error_reporting( E_ALL );
		if ( !$doc = simplexml_load_string( $images[ 'xml' ] ) )
		{
			die( 'BU' );
		}
		$this->convert_xml2array( $xml, $doc );
		
		$xml = $xml[ 'root' ][ 0 ][ 'channel' ];
		unset( $xml[ '@attributes' ] );
		
		for ( $i = 0; $i < 4; $i++, array_shift( $xml ) );
		
		$key = array_rand( $xml );
		
		return str_replace( 'style="', 'style="width: 500px"', $xml[ $key ][ 'item' ][ 1 ][ 'description' ][ 0 ] );
	}
	/**
	* process an uploaded file
	*/
	function processFile( $file, $time, $return = FALSE )
	{
		global $Cl_root_path;
		
		$ext = substr( strrchr( $file[ 'name' ], '.' ), 1 );
		$name = $file[ 'name' ];
		$target = $Cl_root_path . 'cache/' . $time . '/' . $name . '.file.' . $ext;
		//$target = $Cl_root_path . 'cache/' . $time . '/' . $name . '.file.' . $ext;
		$target = str_replace( ' ', '', $target );
		
		if ( is_readable( $target ) )
		{
			return 'done';
		}
		
		if ( !move_uploaded_file( $file[ 'tmp_name' ], $target ) )
		{ // tsk, no good
			return;
		}
		
		if ( $return )
		{ // get contents of file
			$contents = file_get_contents( $target );
			
			if ( $file[ 'type' ] != 'text/plain' )
			{ // convert and stuff
				$contents = $this->readFile( $target, $file[ 'type' ], $contents, $time );
			}
			
			return $contents;
		}else
		{
			return $target;
		}
	}
	/**
	* does everything needed to read obscure file formats
	*/
	function readFile( $target, $type, $contents, $time )
	{
		global $Cl_root_path;
		
		if ( $type == 'text/plain' )
		{
			return $contents;
		}
		
		$dir = basename( $target );
		mkdir ( $Cl_root_path . 'cache/' . $time . '/dir' . $dir, 0777 );
		chmod( $Cl_root_path . 'cache/' . $time . '/dir' . $dir, 0777 );
		
		if ( $type != 'application/octet-stream' )
		{ // convert into odt
			//$pyPath = 'C:\Program Files\OpenOffice.org 2.2\program\python'; // openoffice's python binary
			//$pyPath = '/var/lib/openoffice/program/python';
			$pyPath = '/usr/bin/python';
			$abs = str_replace( array( 'includes/TagCloud.php', 'includes\\TagCloud.php' ), '', __FILE__ );
			
			$sPath = $target; // source
			chmod( $sPath, 0666 );
			$tPath = str_replace( $dir, 'dir' . $dir . '/' . $dir . '.odt', $target ); // target
			$target = str_replace( $dir, 'dir' . $dir . '/' . $dir . '.odt', $target );
			
			// openoffice must be run as service with the following command
			// soffice -accept="socket,port=8100;urp;" -invisible &

			exec( "\"$pyPath\" ${abs}includes/TagCloud/DocumentConverter.py $sPath $tPath", $result );
// 			echo "\"$pyPath\" ${abs}includes/TagCloud/DocumentConverter.py $sPath $tPath";
			//exec( "\"$pyPath\" /home/www/DocumentConverter.py $sPath $tPath", $result );
			
			if ( !empty( $result ) )
			{ // verily there was a problem converting
				return 'ERROR: ' . implode( "\n", $result );
			}
		}
		
		include_once( $Cl_root_path . 'includes/TagCloud/Zip' . phpEx );
		$path = $Cl_root_path . 'cache/' . $time . '/dir' . $dir . '/';
		@unlink( $path . 'content.xml' ); // might've been something old there, kill it
		
		$Zip = new Archive_Zip( $target );
		$Zip->extract( array( 'add_path' => $path ) );
		
		clearstatcache();
		if ( !is_readable( $path . '/content.xml' ) )
		{
			return 'ERRROR: ' . $Zip->errorInfo( TRUE );
		}
		$oContents = file_get_contents( $path . '/content.xml' );
		$contents = preg_replace( '#\<.*?\>#s', ' ', $oContents );
		
		$this->full_rmdir( $path, array( 'content.xml', basename( $target ) ) );
		
		return array( $contents, $oContents );
	}
	/**
	* file upload stuff
	*/
	function file()
	{
		global $Cl_root_path;
		$time = ( isset( $_POST[ 'time' ] ) ) ? $_POST[ 'time' ] : $this->microtime_float();
		
		if ( !is_dir( $Cl_root_path . 'cache/' . $time ) )
		{
			mkdir( $Cl_root_path . 'cache/' . $time, 0777 );
			chmod( $Cl_root_path . 'cache/' . $time, 0777 );
		}
		
		if ( $_FILES[ 'file' ][ 'error' ] == UPLOAD_ERR_OK )
		{ // file upload
			$file = $this->processFile( $_FILES[ 'file' ], $time, FALSE );
			$error = FALSE;
		}else
		{
			$error = TRUE;
		}
		
		$hash = $Cl_root_path . 'cache/' . $time . '/filehash.php';
		if ( !@include( $hash ) )
		{
			$inputHash = array();
		}
		
		$format = $this->fileFormat( $_FILES[ 'file' ][ 'name' ] );
		
		$inputHash[ count( $inputHash ) ] = array( 'target' => $file, 'type' => $_FILES[ 'file' ][ 'type' ], 'name' => $_FILES[ 'file' ][ 'name' ], 'error' => $error, 'done' => TRUE, 'size' => $_FILES[ 'file' ][ 'size' ], 'format' => $format );
		
		$this->rewriteHash( $inputHash, $time );
	}
	/**
	* determines if a file has finished uploading
	*/
	function check_upload( $id, $time )
	{
		global $Cl_root_path, $basic_gui;
		
		$hash = $Cl_root_path . 'cache/' . $time . '/filehash.php';
		clearstatcache();
		
		if ( !is_readable( $hash ) )
		{
			return array( $id, 0, $time );
		}
		include( $hash );
		
		$done = FALSE;
		if ( !isset( $inputHash[ $id ]) )
		{
			return array( $id, 0, $time );
		}
		
		$file = $inputHash[ $id ];
		$info = '';
		
		if ( $file[ 'target' ] == 'done' )
		{
			$file[ 'error' ] = TRUE;
			$info = $this->lang[ 'Interface_done' ] . '<br />';
		}
		
		if ( !$file[ 'error' ] )
		{
			$done = $file[ 'done' ];
		}else
		{
			$done = TRUE;
		}
		
		$stuff = array();
		if ( $done || $file[ 'error' ] )
		{
			$contents = '';
			if ( !$file[ 'error' ] )
			{
				$format = $this->fileFormat( $file[ 'target' ] );
				
				if ( $format != 'unknown' )
				{
					$contents = $this->readFile( $file[ 'target' ], $file[ 'type' ], file_get_contents( $file[ 'target' ] ), $time );
					if ( is_array( $contents ) )
					{
						$oContents = $contents[ 1 ];
						$contents = $contents[ 0 ];
					}
					
					if ( substr( $contents, 0, 6 ) == 'ERROR:' )
					{
						$format = 'unknown';
						$info = $this->lang[ 'Error_unknown' ];
						$contents = '';
					}else
					{
						file_put_contents( $Cl_root_path . 'cache/' . $time . '/' . $id . '.txt', $contents );
					}
				}else
				{
					$info .= $this->lang[ 'Error_unknown' ];
				}
			}else
			{
				$format = 'unknown';
				
				$info = $this->lang[ 'Error_ul' ];
			}
			
			$proto = '<b>%s: </b>%s<br />';
			$info .= ( !empty( $info ) ) ? '<br />' : '';
			
			$info .= sprintf( $proto, $this->lang[ 'Info_name' ], $file[ 'name' ] );
			
			
			$info .= sprintf( $proto, $this->lang[ 'Info_size' ], $this->kiloSize( $file[ 'size' ] ) );
			
			if ( !empty( $contents ) )
			{
				preg_match_all( '#\b(\w+)\b#sm', $contents, $words );
				$info .= sprintf( $proto, $this->lang[ 'Info_words' ], count( $words[ 1 ] ) );
				
				$stuff[ 'words' ] = count( $words[ 1 ] );
				
				preg_match_all( '#\p{Lu}.+?[.?!]#usm', $contents, $sentences );
				$info .= sprintf( $proto, $this->lang[ 'Info_sentences' ], count( $sentences[ 0 ] ) );
				
				$stuff[ 'sentences' ] = count( $sentences[ 0 ] );
				
				if ( $file[ 'type' ] != 'text/plain' )
				{
					$count = substr_count( $oContents, '<text:p' );
				}else
				{
					preg_match_all( '#\p{Zp}.+?\p{Zp}#usm', $contents, $paragraphs );
					$count = count( $paragraphs[ 0 ] );
				}
				$info .= sprintf( $proto, $this->lang[ 'Info_paragraphs' ], $count );
				$stuff[ 'paragraphs' ] = $count;
				
				include( $Cl_root_path . 'includes/TagCloud/detectLang' . phpEx );
				$detect = new detectLang( $Cl_root_path );
				$language = $detect->detect( mb_substr( $contents, 0, 5000 ) );
				$language = substr( $language, 0, 2 );
				$stuff[ 'language' ] = $language;
				$language = $this->lang[ 'Info_lang_' . $language ];
				
				$info .= sprintf( $proto, $this->lang[ 'Info_language' ], $language );
			}
			
			$uri = $basic_gui->get_URL();
			$html = "<div id=\"filecontrol_$id\" class=\"filecontrol\">\n" .
						"\t<img src=\"$uri/template/TagCloud/images/fileclose.png\" onclick=\"removeFile( $id, $time );\" />\n" . 
						"\t<img src=\"$uri/template/TagCloud/images/info.png\" onclick=\"showinfo( '$info' )\" />\n" . 
					"</div>";
			$html .= "\n<img src=\"$uri/template/TagCloud/images/$format.png\" class=\"fileback\" id=\"fileback_$id\" />";
		}else
		{
			$html = '';
		}
		
		if ( !empty( $stuff ) )
		{
			return array( $id, $done, $time, $html, $stuff );
		}else
		{
			return array( $id, $done, $time, $html );
		}
	}
	/*
	* retrieves a website from the web and readies it for picking and choosing by the user
	*/
	/*function add_uri( $id, $time, $uri )
	{
		global $basic_gui;
		
		$uri = escapeshellarg( $uri );
		$filepath = '/var/www/clb';
		$command = "LD_LIBRARY_PATH=/usr/lib/firefox python ${filepath}/includes/TagCloud/screenshot.py $uri ${filepath}/cache > ${filepath}/cache/screenie.txt 2> ${filepath}/cache/screenie_err.txt &";

		exec( $command );

		$html = '<b>OMFG</b>';

		return array( urlencode( $html ), urlencode( $command ) );
	}*/
	/**
	* retrieves a website from a given uri and heuristically tries to figure out what the useful text in it is
	*/
	function add_uri( $id, $time, $uri )
	{
		global $basic_gui, $Cl_root_path;
		
		$prefix = substr( $uri, 0, 7 );
		if ( strtolower( $prefix ) != 'http://' )
		{
			$uri = 'http://' . $uri;
		}
		$uri = ( substr( $uri, -1 ) == '/' ) ? substr( $uri, 0, -1 ) : $uri;
		$name = preg_replace( '#[^a-zA-Z]+#', '', strrchr( $uri, '/' ), 1 );
		$stuff = array();
	
		$html = @file_get_contents( $uri );
	
		if ( !empty( $html ) )
		{
			$format = 'website';
			
			$contents = $this->webHeuristics( $html );
			$this->writeWeb( $contents, $format, $name, $time, $id, $uri );
			
			$size = strlen( $contents );
		}else
		{
			$format = 'unknown';
			
			$info = $this->lang[ 'Error_web' ];
			$size = 0;
		}
		
		$proto = '<b>%s: </b>%s<br />';
		$info .= ( !empty( $info ) ) ? '<br />' : '';
		
		$info .= sprintf( $proto, $this->lang[ 'Info_name' ], $name );
		$info .= sprintf( $proto, $this->lang[ 'Info_uri' ], $uri );
		$info .= sprintf( $proto, $this->lang[ 'Info_size' ], $this->kiloSize( $size ) );
		
		if ( !empty( $contents ) )
		{ // more info
			preg_match_all( '#\b(\w+)\b#sm', $contents, $words );
			$info .= sprintf( $proto, $this->lang[ 'Info_words' ], count( $words[ 1 ] ) );
			
			$stuff[ 'words' ] = count( $words[ 1 ] );
			
			preg_match_all( '#\p{Lu}.+?[.?!]#usm', $contents, $sentences );
			$info .= sprintf( $proto, $this->lang[ 'Info_sentences' ], count( $sentences[ 0 ] ) );
			
			$stuff[ 'sentences' ] = count( $sentences[ 0 ] );
			
			$count = substr_count( $contents, '<text:p' );
			
			$info .= sprintf( $proto, $this->lang[ 'Info_paragraphs' ], $count );
			$stuff[ 'paragraphs' ] = $count;
			
			include( $Cl_root_path . 'includes/TagCloud/detectLang' . phpEx );
			$detect = new detectLang( $Cl_root_path );
			$language = $detect->detect( mb_substr( $contents, 0, 5000 ) );
			$language = substr( $language, 0, 2 );
			$stuff[ 'language' ] = $language;
			$language = $this->lang[ 'Info_lang_' . $language ];
			
			$info .= sprintf( $proto, $this->lang[ 'Info_language' ], $language );
		}
		
		$uri = $basic_gui->get_URL();
		$Ohtml = "<div id=\"filecontrol_$id\" class=\"filecontrol\">\n" .
					"\t<img src=\"$uri/template/TagCloud/images/fileclose.png\" onclick=\"removeFile( $id, $time );\" />\n" . 
					"\t<img src=\"$uri/template/TagCloud/images/info.png\" onclick=\"showinfo( '$info' )\" />\n" . 
				"</div>";
		$Ohtml .= "\n<img src=\"$uri/template/TagCloud/images/$format.png\" class=\"fileback\" id=\"fileback_$id\" />";
		
		if ( !empty( $stuff ) )
		{
			return array( $id, TRUE, $time, $Ohtml, $stuff );
		}else
		{
			return array( $id, TRUE, $time, $Ohtml );
		}
	}
	/**
	* the heuristics above function uses
	*/
	function webHeuristics( $html )
	{
		// the heuristic is simple, all somewhat relevant elements with more than five words are game
		
		$crap = array( // some crap can really fuck up the results
						"#<!--.*?-->#",
						"#<form .*?</form>#i",
						"#<select .*?</select>#i",
						"#<textarea .*?</textarea>#i",
						"#<object .*?</object>#i"
					);
		
		$html = preg_replace( $crap, '', $html );
		$html = html_entity_decode( $html, ENT_COMPAT, 'UTF-8' );
		
		preg_match_all( "#<(?P<tag>p|h[0-9]+|div|span).*?>(.*?)</\k{tag}>#is", $html, $matches );
		
		$FinalContent = '<?xml version="1.0" encoding="UTF-8"?><office:document-content><office:body><office:text>';

		for ( $i = 0; $i < count( $matches[ 0 ] ); $i++ )
		{
			$content = preg_replace( '#\s+#', ' ', $matches[ 0 ][ $i ] );
			$content = preg_split( '#\<.*?\>#s', $content );
			
			$Content = array();
			for ( $j = 0; $j < count( $content ); $j++ )
			{
				if ( !empty( $content[ $j ] ) )
				{
					if ( mb_substr_count( $content[ $j ], ' ' ) >= 5 )
					{
						$Content[] = $content[ $j ];
					}
				}
			}
			
			$FinalContent .= '<text:p>' . implode( ' ', $Content ) . '</text:p>';
		}
		
		$FinalContent .= '</office:text></office:body></office:document-content>';
		$FinalContent = str_replace( '<text:p></text:p>', '', $FinalContent );
	
		return $FinalContent;
	}
	/**
	* writes the website to cache and makes it look like a regular file
	*/
	function writeWeb( $contents, $format, $name, $time, $id, $uri )
	{
		global $Cl_root_path;
		
		if ( !is_dir( $Cl_root_path . 'cache/' . $time ) )
		{
			mkdir( $Cl_root_path . 'cache/' . $time, 0777 );
			chmod( $Cl_root_path . 'cache/' . $time, 0777 );
		}
		
		$ext = 'web';
		$target = $Cl_root_path . 'cache/' . $time . '/' . $name . '.file.' . $ext;
		//$target = $Cl_root_path . 'cache/' . $time . '/' . $name . '.file.' . $ext;
// 		$target = str_replace( ' ', '', $target );

		$dir = basename( $target );
// 		echo $Cl_root_path . 'cache/' . $time . '/dir' . $dir;
		mkdir ( $Cl_root_path . 'cache/' . $time . '/dir' . $dir, 0777 );
		chmod( $Cl_root_path . 'cache/' . $time . '/dir' . $dir, 0777 );
		$path = $Cl_root_path . 'cache/' . $time . '/dir' . $dir . '/';
		
		file_put_contents( $path . 'content.xml', $contents );
		file_put_contents( $target, $contents );
		
		file_put_contents( $Cl_root_path . 'cache/' . $time . '/' . $id . '.txt', preg_replace( '#\<.*?\>#s', ' ', $contents ) );
		
		$hash = $Cl_root_path . 'cache/' . $time . '/filehash.php';
		if ( !@include( $hash ) )
		{
			$inputHash = array();
		}
		
		$inputHash[ count( $inputHash ) ] = array( 'target' => $target, 'type' => 'website', 'name' => $name, 'error' => 0, 'done' => TRUE, 'size' => strlen( $contents ), 'format' => $format, 'URI' => $uri );
		
		$this->rewriteHash( $inputHash, $time );
	}
	/**
	* removes a file from uploads
	*/
	function removeFile( $id, $time )
	{
		global $Cl_root_path;
		
		$hash = $Cl_root_path . 'cache/' . $time . '/filehash.php';
		
		include( $hash );
		
		$file = $inputHash[ $id ];
		
		unlink( $file[ 'target' ] );
		unlink( $Cl_root_path . 'cache/' . $time . '/' . $id . '.txt' );
		$dir = basename( $file[ 'target' ] );
		$dir = $Cl_root_path . 'cache/' . $time . '/dir' . $dir;
		if ( is_dir( $dir ) )
		{
			$this->full_rmdir( $dir );
		}
		
		unset( $inputHash[ $id ] );

		$this->rewriteHash( $inputHash, $time );
	}
	/**
	* returns filesize in a more human readable form
	*/
	function kiloSize( $size )
	{
		$units = array( 'B', 'kiB', 'MiB', 'GiB' );
		
		for ( $i = 0; $size >= 1024; $i++ )
		{
			$size = $size / 1024;
		}
		
		return round( $size, 2 ) . ' ' . $units[ $i ];
	}
	/**
	* returns the file format as decided by the extension
	*/
	function fileFormat( $name )
	{
		$ext = substr( strrchr( $name, '.' ), 1 );
		
		switch ( $ext )
		{
			case 'txt':
				$format = 'txt';
				break;
			case 'psw':
			case 'pdb':
				$format = 'palm';
				break;
			case 'odt':
			case 'sxw':
			case 'doc':
			case 'sdw':
			case 'jtd':
			case 'hwp':
			case 'wps':
			case 'rtf':
				$format = 'doc';
				break;
			case 'html':
			case 'htm':
				$format = 'html';
				break;
			case 'xml':
				$format = 'xml';
				break;
			default:
				$format = 'unknown';	
		}
		
		return $format;
	}
	/**
	* recursively removes a directory
	*/
	function full_rmdir( $dir, $omit = array() )
	{
		if ( !is_writable( $dir ) )
		{
			if ( !@chmod( $dir, 0777 ) )
			{
				return FALSE;
			}
		}
		
		$d = dir( $dir );
		while ( FALSE !== ( $entry = $d->read() ) )
		{
			if ( $entry == '.' || $entry == '..' )
			{
				continue;
			}
			$Entry = $dir . '/' . $entry;
			if ( is_dir( $Entry ) )
			{
				if ( !$this->full_rmdir( $Entry, $omit ) )
				{
					return FALSE;
				}
				continue;
			}
			if ( !in_array( $entry, $omit ) )
			{
				if ( !@unlink( $Entry ) )
				{
					$d->close();
					return FALSE;
				}
			}
		}
		
		$d->close();
		
		@rmdir( $dir );
		
		return TRUE;
	}
	/**
	* rewrites the file hash
	*/
	function rewriteHash( $inputHash, $time )
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{		
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		$hash = $path . '/filehash.php';
		
		$php = "<?php \n\n \$inputHash = array(); \n";
		foreach ( $inputHash as $i => $arr )
		{
			$php .= "\$inputHash[ $i ] = array( \n\t";
			foreach ( $arr as $k => $v )
			{
				$php .= "'$k' => ";
				if ( is_bool( $v ) )
				{
					$php .= ( $v ) ? 'TRUE' : 'FALSE';
				}else
				{
					$php .= ( !is_numeric( $v ) ) ? "'$v'" : $v;
				}
				$php .= ", \n\t";
			}
			$php .= "\n); \n";
		}
		$php .= '?>';
		
		if ( !is_dir( $path ) )
		{
			mkdir( $path, 0777 );
		}
		file_put_contents( $hash, $php );
	}
	/**
	* used for adding additional file info to the hash
	*/
	function addInfo( $words, $sentences, $paragraphs, $language, $time, $id )
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{		
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		include( $path . '/filehash.php' );
		
		$inputHash[ $id ][ 'words' ] = $words;
		$inputHash[ $id ][ 'sentences' ] = $sentences;
		$inputHash[ $id ][ 'paragraphs' ] = $paragraphs;
		$inputHash[ $id ][ 'language' ] = $language;
		
		$this->rewriteHash( $inputHash, $time );
	}
	/**
	* used for exporting different image sizes
	*/
	function export()
	{
		global $Cl_root_path, $security, $userdata;
		
		$time = ( isset( $_GET[ 'time' ] ) ) ? $_GET[ 'time' ] : 0;
		$size = ( isset( $_GET[ 'size' ] ) ) ? $_GET[ 'size' ] : 1;
		$id = ( isset( $_GET[ 'id' ] ) ) ? $_GET[ 'id' ] : 0;
		$doit = ( isset( $_GET[ 'doit' ] ) ) ? $_GET[ 'doit' ] : 0;
		
		if ( $time == 0 )
		{
			return FALSE;
		}
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{		
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		$size = $this->getSize( $size );
		
		if ( $doit == 1 )
		{
			$hash = $path . '/filehash' . phpEx;
			include( $hash );
			$name = $inputHash[ $id ][ 'name' ];
			$name = str_replace( strrchr( $name, '.' ), '', $name );
			$name .= '_' . $size . '.png';
			
			$f_location = "$path/${size}image_$id.png";
			header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header('Content-Description: File Transfer');
			header('Content-Type: image/png');
			header('Content-Length: ' . filesize($f_location));
			header('Content-Disposition: attachment; filename=' . $name);
			readfile($f_location);
			die();
		}else
		{
			$this->runBackend( $time, $size, $id );
			die();
		}
	}
	/**
	* checks if exporting is done
	*/
	function checkDoneExport( $time, $id, $size )
	{
		global $Cl_root_path, $security, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{		
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		$file = $path . '/' . $this->getSize( $size ) . 'image_' . $id . '.png';
	
		clearstatcache();
		if ( is_readable( $file ) )
		{
			$uri = $security->append_sid( '?' . MODE_URL . '=tagcloud&' . SUBMODE_URL . "=export&size=$size&time=$time&id=$id&doit=1" );
			return array( 1, $uri );
		}else
		{
			return array( 0, $time, $id, $size, $file );
		}
	}
	/**
	* converts size id to size
	*/
	function getSize( $size )
	{
		switch ( $size )
		{
			case 1:
				$size = '200x200';
				break;
			case 2:
				$size = '400x250';
				break;
			case 3:
				$size = '500x500';
				break;
			case 4:
				$size = '600x400';
				break;
			case 5:
			default;
				$size = '1024x800';
		}
		
		return $size;
	}
	/**
	* runs the backend script
	*/
	function runBackend( $time, $size, $id = FALSE )
	{
		global $basic_gui, $userdata, $Cl_root_path;
		
		//$path = 'C:\AppServ';
		//$php = 'C:\AppServ\php5\php';
		$php = 'php';
		//$filepath = $path . '\www\clb';
		$filepath = '/var/www/clb';
		//$nohup = 'C:\AppServ\php5\bgrun.exe';
		$nohup = 'nohup';
		$templatePath = $basic_gui->get_URL();
		
		$Path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $Path ) )
		{		
			$Path = $Cl_root_path . 'cache/' . $time;
		}
		
		$input = "$filepath/$Path/filehash.php";
		//$input = $filepath . '/' . $input;
		
		if ( $id !== FALSE )
		{
			$Id = $id;
		}else
		{
			$Id = '';
		}
		
		exec( "$nohup $php $filepath/includes/TagCloud/TagCloud_engine.php $filepath $input image $time $size $templatePath /$Path $Id > $Path/tagcloud.txt &" );

// 		echo "$nohup $php $filepath/includes/TagCloud/TagCloud_engine.php $filepath $input image $time $size $templatePath /$Path $Id > $Path/tagcloud.txt &";
	}
	/**
	* creates the divs for clicking upon
	*/
	function clickies( $time, $id )
	{
		global $Cl_root_path, $userdata;
		
		if ( strpos( $id, '_' ) !== FALSE )
		{
			$id = explode( '_', $id );
			$id = $id[ 1 ];
		}
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{		
			$path = $Cl_root_path . 'cache/' . $time;
		}
	
		$proto = "\t\t\t" . '<div class="clickie" style="top: %dpx; left: %dpx; width: %dpx; height: %dpx;" onclick="advancedWord( \'%s\' )"></div>' . "\n";
		
		$stuff = file_get_contents( $path . '/wordhash_' . $id . phpEx );
		$stuff = unserialize( $stuff );
		$clickies = '';
		for ( $j = 0; $j < count( $stuff ); $j++ )
		{
			$word = $stuff[ $j ];
			$clickies .= sprintf( $proto, $word[ 'y' ], $word[ 'x' ], $word[ 'tw' ], $word[ 'th' ], $word[ 'word' ] );
		}
		
		return $clickies;
	}
	/**
	* returns deeper analysis of words
	*/
	function getAnalysis( $word, $id, $time, $cluster )
	{
		global $Cl_root_path, $basic_gui, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		$statistics = "$path/statistics_$id" . phpEx;
		$statistics = unserialize( file_get_contents( $statistics ) );
		
		$statisticsHash = "$path/statisticshash_$id" . phpEx;
		$statisticsHash = unserialize( file_get_contents( $statisticsHash ) );
		
		$word = urldecode( $word );
		
// 		echo intval( $cluster );
		
		if ( $cluster )
		{
			$word = explode( ' ', $word );
			$glue = '\s+';
		}else
		{
			$word = explode( '<plus> + </plus>', $word );
			$glue = '\b.*?\b';
		}
		
		include( $path . '/filehash' . phpEx );
		$dir = 'dir' . basename( $inputHash[ $id ][ 'target' ] );
		$lang = $inputHash[ $id ][ 'language' ];
		
		$where = array();
		$where[ 0 ] = $this->_getWhere( $word[ 0 ], $lang, $statistics, $statisticsHash );
// 		$where[ 0 ] = $statistics[ $statisticsHash[ $word[ 0 ] ] ][ 'where' ];
		$min = count( $where[ 0 ] );
		$Count = $min;
		for ( $i = 1; $i < count( $word ); $i++ )
		{
			$where[ $i  ] = $this->_getWhere( $word[ $i ], $lang, $statistics, $statisticsHash );
// 			$where[ $i ] = $statistics[ $statisticsHash[ $word[ $i ] ] ][ 'where' ];
			$c = count( $where[ $i ] );
			$Count += $c;
			if ( $c < $min )
			{
				$min = $c;
			}
		}
		
		$splitters = array( 'en' => '#\W+#', 'sl' => '#[^\p{L}]+#u' );
		
		$text = file_get_contents( "$path/$dir/content.xml" );
		$textSplit = preg_split( $splitters[ $lang ], str_replace( "'", '', $this->xml_entity_decode( file_get_contents( "$path/$id.txt" ) ) ) );
		
		$Sentences = array();
		$Paragraphs = array();
		
		$abbreviations = array( 'Dr.', 'prof.', 'A.D.', 'B.C.', 'mr.', 'mrs.', 'ms.', 'g.', 'ga.' );

		$abbreviations = implode( '|', $abbreviations );
		preg_match_all( "#(?<=>|[.?!]\s)\p{Lu}($abbreviations|[\p{L}\s\p{Po}\p{Pc}\p{Pd}\p{Pi}\p{Ps}\p{S}\p{M}\p{N}]|<text:s/>)+([.?!]|(?=</))#Uum", 
			str_replace( array( '«', '»' ), '', $text ), $sentences );
		preg_match_all( "#<text:p.*>.+</text:p>#Usm", $text, $paragraphs );
		
// 		print_R( $sentences );
		$html = '';
		
		for ( $i = 0; $i < $min; $i++ )
		{
			$wrd = array();
			for ( $k = 0; $k < count( $where ); $k++ )
			{
				$wrd[] = $textSplit[ $where[ $k ][ $i ] ];
			}
			$wrd = implode( $glue, $wrd );
// 			echo $i . '::' . $wrd . "\n";
// 			print_R( $sentences );
// 			die();
			for ( $j = 0; $j < count( $sentences[ 0 ] ); $j++ )
			{
				$sentence = $sentences[ 0 ][ $j ];
				if ( strpos( $sentence, '<' ) !== FALSE )
				{
					$sentences[ 0 ][ $j ] = preg_replace( '#<.*?>#', '', $sentence );
				}

				
				if ( preg_match( "#\b$wrd\b#", $sentence ) )
				{
// 					echo "pling\n";
					$item = $sentence;
					
					if ( !$cluster )
					{
						foreach ( explode( $glue, $wrd ) as $w )
						{
							$item = preg_replace( "#(\b)($w)(\b)#", '$1<result>$2</result>$3', $item );
						}
					}else
					{
						$item = preg_replace( "#(\b)($wrd)(\b)#", '$1<result>$2</result>$3', $item );
					}
					$item .= '<br /><br />';
					$Sentences[] = $item;
					unset( $sentences[ 0 ][ $j ] );
				}
			}
			
			for ( $j = 0; $j < count( $paragraphs[ 0 ] ); $j++ )
			{
				$paragraph = $paragraphs[ 0 ][ $j ];
				if ( strpos( $paragraph, '<' ) !== FALSE )
				{
					$paragraphs[ 0 ][ $j ] = preg_replace( '#<.*?>#', '', $paragraph );
				}
				
				if ( preg_match( "#\b$wrd\b#i", $paragraph ) )
				{
					$item = $paragraph;
					if ( !$cluster )
					{
						foreach ( explode( $glue, $wrd ) as $w )
						{
							$item = preg_replace( "#(\b)($w)(\b)#", '$1<result>$2</result>$3', $item );
						}
					}else
					{
						$item = preg_replace( "#(\b)($wrd)(\b)#", '$1<result>$2</result>$3', $item );
					}
					$item .= '<br /><br />';
					$Paragraphs[] = $item;
					unset( $paragraphs[ 0 ][ $j ] );
				}
			}
		}
		
		$scroll = '<img src="' . $basic_gui->get_URL() . '/template/TagCloud/images/uparrow.png" onmouseover="startScrollResult( \'%1$s\', -10 )" onmouseout="stopScrollResult( \'%1$s\' )" />' . 
				'<img src="' . $basic_gui->get_URL() . '/template/TagCloud/images/downarrow.png" onmouseover="startScrollResult( \'%1$s\', 10 )" onmouseout="stopScrollResult( \'%1$s\' )" />';
		
		$analysis = ( count( $where ) > 1 ) ? $this->lang[ 'Analysis_founds' ] : $this->lang[ 'Analysis_found' ];
		$Count = ( count( $where ) > 1 ) ? count( $Paragraphs ) : $Count;
		
		$html .= '<img src="' . $basic_gui->get_URL() . '/template/TagCloud/images/close.png" style="float: right; cursor: pointer; margin: 5px; margin-bottom: -100%" onclick="closeWhatever()" />';
		$html .= '<h1>' . implode( '+', $word ) . '</h1>';
		$html .= '<p>' . sprintf( $analysis, $Count, count( $Sentences ), count( $Paragraphs ) ) . '</p>';
		$html .= '<h2>' . $this->lang[ 'Analysis_sentences' ] . ':</h2>' . sprintf( $scroll, 'analysisS' );
		$html .= '<p class="result" id="analysisS" ondblclick="changeScroll( \'analysisS\' )">' . implode( $Sentences ) . '</p>';
		$html .= '<h2>' . $this->lang[ 'Analysis_paragraphs' ] . ':</h2>' . sprintf( $scroll, 'analysisP' );
		$html .= '<p class="result" id="analysisP" ondblclick="changeScroll( \'analysisP\' )">' . implode( $Paragraphs ) . '</p>';
		
		return array( $html );
	}
	/**
	* parses stats and hashes and stuff to return the wheres of a word
	*/
	function _getWhere( $word, $lang, &$statistics, &$statisticsHash )
	{
		global $Cl_root_path;
		
		if ( isset( $statistics[ $statisticsHash[ $word ] ][ 'where' ] ) )
		{
			return $statistics[ $statisticsHash[ $word ] ][ 'where' ];
		}
		
		if ( !defined( 'PATH' ) )
		{
			define( 'PATH', '/var/www/clb' );
		}
		
		if ( !is_object( $this->stemmer ) )
		{
			$class = 'Tagcloud_' . $lang;
			if ( !class_exists( 'Tagcloud_sl' ) )
			{
				include( $Cl_root_path . 'includes/TagCloud/TagCloud_engine_' . $lang . '.php' );
			}
			
			$this->stemmer = new $class();
		}
		
		$word = strtolower( $word );
		
		if ( !$seek = $this->stemHash[ $word ] )
		{
			$seek = $this->stemmer->stem( $word );
		}
		
		return $statistics[ $seek ][ 'where' ];
	}
	/**
	* decodes built-in XML entities
	*/
	function xml_entity_decode( $source )
	{
		$what = array( '&quot;', '&amp;', '&apos;', '&lt;', '&gt;' );
		$with = array( '"', '&', "'", '<', '>' );
		return str_replace( $what, $with, $source );
	}
	/**
	* returns the given name of a session
	*/
	function sessionName( $time )
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{		
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		$file = $path . '/name.txt';
		
		if ( is_readable( $file ) )
		{
			return file_get_contents( $file );
		}else
		{
			return '';
		}
	}
	/**
	* changes the given name of a session
	*/
	function changeSessionName( $time, $name )
	{
		global $Cl_root_path, $userdata;
		
		$name = $name;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		return file_put_contents( $path . '/name.txt', $name );
	}
	/**
	* saves a session
	*/
	function saveSession( $time )
	{
		global $userdata, $Cl_root_path;
		
		if ( !is_dir( $Cl_root_path . 'cache/' . $time ) )
		{
			return $this->lang[ 'Session_saved' ];
		}
		
		$path = $Cl_root_path . 'Tag_data';
		if ( !is_dir( $path ) )
		{
			mkdir( $path );
		}
		
		$path .= '/' . $userdata[ 'user_id' ];
		if ( !is_dir( $path ) )
		{
			mkdir( $path );
		}
		
		$this->full_copy( $Cl_root_path . 'cache/' . $time, $path . '/' . $time );
		
		$this->full_rmdir( $Cl_root_path . 'cache/' . $time );
		
		return $this->lang[ 'Session_saved' ];
	}
	/**
	* recursive copy function
	*/
	function full_copy( $source, $target )
	{
		if ( is_dir( $source ) )
		{
			@mkdir( $target );
			
			$d = dir( $source );
			
			while ( FALSE !== ( $entry = $d->read() ) ) 
			{
				if ( $entry == '.' || $entry == '..' )
				{
					continue;
				}
				
				$Entry = $source . '/' . $entry;			
				if ( is_dir( $Entry ) )
				{
					$this->full_copy( $Entry, $target . '/' . $entry );
					continue;
				}
				copy( $Entry, $target . '/' . $entry );
			}
			
			$d->close();
		}else
		{
			copy( $source, $target );
		}
	}
	/**
	* returns the given name of a document
	*/
	function documentName( $time, $id )
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		include( $path . '/filehash.php' );
		
		$file = $inputHash[ $id ];
		
		if ( isset( $file[ 'displayName' ] ) )
		{
			return $file[ 'displayName' ];
		}else
		{
			return $file[ 'name' ];
		}
	}
	/**
	* changes the given name of a document
	*/
	function changeDocumentName( $time, $name, $id )
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		include( $path . '/filehash.php' );
		
		$inputHash[ $id ][ 'displayName' ] = urldecode( $name );
		
		$this->rewriteHash( $inputHash, $time );
		
		return TRUE;
	}
	/**
	* removes a document from a session
	*/
	function removeDocument( $id, $time )
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{		
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		include( $path . '/filehash.php' );
	
		$file = $inputHash[ $id ];
		
		$this->full_rmdir( $path . '/dir' . basename( $file[ 'target' ] ) );
		
		@chmod( $file[ 'target' ], 0777 );
		@unlink( $file[ 'target' ] );
		foreach ( glob( $path . '/*' . $id . '.*' ) as $filename )
		{
			@chmod( $filename, 0777 );
			@unlink( $filename );
		}
		
		unset( $inputHash[ $id ] );
		$this->rewriteHash( $inputHash, $time );
		
		return sprintf( $this->lang[ 'Session_removeDoc' ], $file[ 'name' ] );
	}
	/**
	* removes a session from saved sessions
	*/
	function removeSession( $time )
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data' . '/' . $userdata[ 'user_id' ];
		if ( is_dir( $path ) )
		{
			$this->full_rmdir( $path );
		}
		
		return $this->lang[ 'Session_removed' ];
	}
	/**
	* returns the correct image path
	*/
	function swapImage( $id, $time )
	{
		global $Cl_root_path, $userdata, $basic_gui;
		
		$id = explode( '_', $id );
		$id = $id[ 1 ];
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		if ( !is_dir( $path ) )
		{		
			$path = $Cl_root_path . 'cache/' . $time;
		}
		
		return str_replace( './', $basic_gui->get_URL() . '/', $path . '/image_' . $id . '.png' );
	}
	/**
	* returns the last stored session or false if none
	*/
	function directTime()
	{
		global $userdata, $Cl_root_path;
		
		$dir = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ];
		
		if ( !is_dir( $dir ) )
		{
			return FALSE;
		}
		
		$d = dir( $dir );
		$l = 0;
		$min = 999999999999999999999.0;
		
		while ( FALSE !== ( $entry = $d->read() ) )
		{
			if ( $entry != '.' && $entry != '..' && is_dir( $dir . '/' . $entry ) )
			{
				if ( substr( $entry, 0, 8 ) != 'textpad_' )
				{
					$entry = (float) $entry;
					if ( $entry < $min )
					{
						$min = $entry;
					}
				}
			}
		}
		
		$d->close();
		
		return $min;
	}
	/**
	* creates a new textpad in a session
	* if the session has not been saved, it gets saved now
	*/
	function newTextPad( $time )
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		$saved = FALSE;
		
		if ( !is_dir( $path ) )
		{
			$this->saveSession( $time );
			$saved = TRUE;
		}
		
		include( $path . '/filehash.php' );
		
		$id = $this->getNextHashId( $inputHash );
		
		touch( "$path/textpad_$id.txt" );
		touch( "$path/$id.txt" );
		
		$inputHash[ $id ] = array( 'target' => "$path/textpad_$id.txt", 'type' => 'textpad', 'format' => 'textpad', 'name' => "textpad_$id.txt" );
		
		$this->rewriteHash( $inputHash, $time );
	
		return ( $saved ) ? $this->lang[ 'Textpad_created' ] . '<br />' . $this->lang[ 'Session_saved' ] : $this->lang[ 'Textpad_created' ];
	}
	/**
	* fetches a textpad's html for displayal
	*/
	function getTextPad( $id, $time )
	{
		global $Cl_root_path, $userdata, $basic_gui;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		
		include( $path . '/filehash.php' );
		
		$file = $inputHash[ $id ];
		
		$contents = file_get_contents( $file[ 'target' ] );
		
		$name = ( isset( $file[ 'displayName' ] ) ) ? $file[ 'displayName' ] : $file[ 'name' ];
		
		$closeTool = $basic_gui->make_tooltip( $this->lang[ 'Textpad_remove' ], 'tip' );
	
		$html = '<img src="' . $basic_gui->get_URL() . '/template/TagCloud/images/close.png" style="float: right; cursor: pointer; margin: 5px; margin-bottom: -100%" onclick="closeWhatever()" />';
		$html .= '<span id="textPadName"><h1 onclick="renameTextPad1( \'' . $name . '\' )" style="cursor: pointer">' . $name . '</h1></span>';
		$html .= '<img src="' . $basic_gui->get_URL() . '/template/TagCloud/images/fileclose.png" style="margin-top: -5px; margin-bottom: -15px" ' . $closeTool . ' onclick="removeTextPad( ' . $id . ', ' . $time . ' )" /><br />';
		$html .= '<textarea style="width: 790px; height: 550px; overflow: auto" id="textpad">' . $contents . '</textarea>';
		
		return array( $html );
	}
	/**
	* saves a textpad's contents
	*/
	function saveTextPad( $id, $time, $contents )
	{
		global $Cl_root_path, $userdata, $basic_gui;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		
		include( $path . '/filehash.php' );
		
		$file = $inputHash[ $id ];
		
		$contents = urldecode( $contents );
		
		if ( file_put_contents( $file[ 'target' ], $contents ) === FALSE )
		{
			return $this->lang[ 'Textpad_notsaved' ];
		}else
		{
			return $this->lang[ 'Textpad_saved' ];
		}
	}
	/**
	* removes a textpad from a session
	*/
	function removeTextPad( $id, $time )
	{
		global $Cl_root_path, $userdata, $basic_gui;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $time;
		
		include( $path . '/filehash.php' );
		
		unlink( "$path/textpad_$id.txt" );
		unlink( "$path/$id.txt" );
		unset( $inputHash[ $id ] );
		
		$this->rewriteHash( $inputHash, $time );
		
		return array( $this->lang[ 'Textpad_removed' ], $id );
	}
	/**
	* moves a file from one session to another
	*/
	function moveFile( $id, $fromtime, $totime, $dirid, $olddir )
	{
		global $Cl_root_path, $userdata, $basic_gui, $template, $lang_loader;
		
		$path1 = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $fromtime;
		if ( !is_dir( $path1 ) )
		{
			$path1 = $Cl_root_path . 'cache/' . $fromtime;
		}
		$path2 = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ] . '/' . $totime;
		if ( !is_dir( $path2 ) )
		{
			$path2 = $Cl_root_path . 'cache/' . $totime;
		}
		
		include( $path2 . '/filehash.php' );
		
		$newid = $this->getNextHashId( $inputHash );
		$newFileNum = count( $inputHash );
		
		unset( $inputHash );
		include( $path1 . '/filehash.php' );
		$oldFileNum = count( $inputHash )-1;
		
		$file = substr( strrchr( $inputHash[ $id ][ 'target' ], '/' ), 1 );
		
		copy( "$path1/$id.txt", "$path2/$newid.txt" );
		copy( "$path1/statistics_$id.php", "$path2/statistics_$newid.php" );
		copy( "$path1/statisticshash_$id.php", "$path2/statisticshash_$newid.php" );
		copy( "$path1/wordhash_$id.php", "$path2/wordhash_$newid.php" );
		copy( "$path1/image_$id.png", "$path2/image_$newid.png" );
		copy( "$path1/$file", "$path2/$file" );
		$this->full_copy( "$path1/dir$file", "$path2/dir$file" );
		
		$file = $inputHash[ $id ];
		unset( $inputHash[ $id ] );
		$this->rewriteHash( $inputHash, $fromtime );
		unset( $inputHash );
		
		include( $path2 . '/filehash.php' );
		$inputHash[ $newid ] = $file;
		$this->rewriteHash( $inputHash, $totime );
		
		
		$template->clear();
		
		$GLOBALS[ 'Cl_root_path4template' ] = '../../../';
		$basic_gui->reload_config();
		
		$template->assign_files( array(
			'TagCloud_filerow' => 'TagCloud_filerow' . tplEx,
			'TagCloud_newfile' => 'TagCloud_newfile' . tplEx
		) );
		
		$template->assign_vars( array(
			'ROOT_PATH' => '../../../'
		) );
		
		$template->assign_block_vars( 'dirrow', '', array(
			'ID' => $dirid,
		) );
		$template->assign_switch( 'dirrow', TRUE );
		
		$img = '';
		$this->add_fileRow( $file, $img, $dirid, $newid, $path2, '' );
		
		$this->lang = $lang_loader->get_lang( 'TagCloud', TRUE );
		
		$html = $template->justcompile( 'TagCloud_newfile' );
		$html = preg_replace( '#<script.+?</script>#s', '', $html );
		
		
		return array( $dirid, $newid, $html, $file[ 'format' ], $id, $olddir, $newFileNum+1, $oldFileNum );
	}
	/**
	* returns the next id to go inside a filehash
	*/
	function getNextHashId( $inputHash )
	{
		$keys = array_keys( $inputHash );
		
		return $keys[ count( $keys )-1 ]+1;
	}
	/**
	* creates a scrapbook
	*/
	function newScrapBook()
	{
		global $Cl_root_path, $userdata;
		
		$path = $Cl_root_path . 'Tag_data/' . $userdata[ 'user_id' ];
		
		if ( !is_dir( $path ) )
		{
			if ( !@mkdir( $path ) )
			{
				return $this->lang[ 'Scrapbook_notcreated' ];
			}
		}
		
		$d = dir( $path );
		$id = 0;
		
		while ( FALSE !== ( $entry = $d->read() ) )
		{
			if ( substr( $entry, 0, 8 ) == 'textpad_' )
			{
				$i = intval( substr( $entry, 8 ) );
				if ( $i > $id )
				{
					$id = $i+1;
				}
			}
		}
		
		$d->close();
		
		if ( !@mkdir( $path . '/textpad_' . $id ) )
		{
			return $this->lang[ 'Scrapbook_notcreated' ];
		}
		
		$php =  "<?php\n\n\$inputHash = array();\n\n?>";
		
		if ( @file_put_contents( $path . '/textpad_' . $id . '/filehash.php', $php ) === FALSE )
		{
			return $this->lang[ 'Scrapbook_notcreated' ];
		}
		
		if ( @file_put_contents( $path . '/textpad_' . $id . '/time.txt', EXECUTION_TIME ) === FALSE )
		{
			return $this->lang[ 'Scrapbook_notcreated' ];
		}
		
		if ( @file_put_contents( $path . '/textpad_' . $id . '/icon.txt', 'scrapbook.png' ) === FALSE )
		{
			return $this->lang[ 'Scrapbook_notcreated' ];
		}
		
		return $this->lang[ 'Scrapbook_created' ];
	}

	//
	// End of TagCloud class
	//
}

?>