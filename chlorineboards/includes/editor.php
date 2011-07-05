<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:                editor.php                           //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        13th March 2005                        //
//     version:               0.2.3                              //
//                                                               //
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
//                                                               //
// This program is free software; you can redistribute it        //
// and/or modify it under the terms of the GNU General Public    //
// License as published by the Free Software Foundation;         //
// either version 2 of the License, or (at your option)          //
// any later version.                                            //
//                                                               //
///////////////////////////////////////////////////////////////////

//
// the editor for ClB
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// debug :: debug flag

// class creation
$vars = array( 'debug' );
$visible = array( 'private' );
eval( Varloader::createclass( 'editor', $vars, $visible ) );
// end class creation

class Editor extends editor_def
{
	
	// constructor
	function Editor( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax;
		
		$this->debug = $debug;
		$this->instances = array();
		// load the language
		$this->lang = $lang_loader->get_lang( 'editor' );
		$Sajax->add2export( 'editor->getsmileys', '$name' );
	}
	
	function display( $name, $quickpost, $def_text = '' )
	{
		global $Cl_root_path;
		
		if ( is_readable( $Cl_root_path . 'includes/tiny_mce' ) )
		{
			$this->display_MCE( $name, $quickpost, $def_text );
		}elseif ( is_readable( $Cl_root_path . 'includes/FCKeditor/fckeditor.php' ) )
		{
			$this->display_FCK( $name, $quickpost, $def_text );
		}
	}
	
	function test( $name='test', $quickpost=FALSE, $def_text='testicle' )
	{
		global $basic_gui, $mod_loader;
		
		// load the tiny_mce javascript
		$basic_gui->add_JS( 'includes/tiny_mce/tiny_mce.js' );
		
		// some configuration :)
		$editor_HTML = '<textarea name="' . $name . '" style="width:500px; height:400px;">' . $def_text . '</textarea>';
		if ( !$quickpost )
		{
			$basic_gui->add_JS_text( 
				"tinyMCE.init({ \n" .
					"\tmode : \"textareas\",\n" . 
					"\ttheme : \"advanced\",\n" .
					"\tplugins : \"table,advhr,advimage,advlink,iespell,insertdatetime,preview,zoom,flash,searchreplace,print,contextmenu,spellchecker,xhtmlxtras,visualchars,style,directionality,paste,inlinepopups\",\n" .
					"\ttheme_advanced_buttons1_add_before : \"\",\n" .
					"\ttheme_advanced_buttons1_add : \"fontselect,fontsizeselect\",\n" .
					"\ttheme_advanced_buttons2_add : \"separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor\",\n" .
					"\ttheme_advanced_buttons2_add_before: \"cut,copy,paste,separator,search,replace,separator\",\n" .
					"\ttheme_advanced_buttons3_add_before : \"tablecontrols,separator\",\n" .
					"\ttheme_advanced_buttons3_add : \"emotions,iespell,flash,advhr,separator,print,spellchecker\",\n" .
					"\ttheme_advanced_buttons4 : \"visualchars,|,styleprops,|,abbr,acronym,cite,del,ins,|,ltr,rtl,|,pastetext,pasteword,selectall\",\n" .
					"\ttheme_advanced_toolbar_location : \"top\",\n" .
					"\ttheme_advanced_toolbar_align : \"left\",\n" .
					"\ttheme_advanced_path_location : \"bottom\",\n" .
					"\tplugin_insertdate_dateFormat : \"%Y-%m-%d\",\n" .
					"\tplugin_insertdate_timeFormat : \"%H:%M:%S\",\n" .
					"\textended_valid_elements : \"a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]\",\n" .
					"\texternal_link_list_url : \"example_data/example_link_list.js\",\n" .
					"\texternal_image_list_url : \"example_data/example_image_list.js\",\n" .
					"\tflash_external_list_url : \"example_data/example_flash_list.js\",\n" .
					"\ttheme_advanced_resize_horizontal : false,\n" .
					"\ttheme_advanced_resizing : true,\n" .
					"\tapply_source_formatting : true, \n" .
					"\tconvert_urls : false \n" .
					"\tentity_encoding : \"numeric\" \n" . 
				"});\n"
			);
		}else
		{
			$basic_gui->add_JS_text( 
				"tinyMCE.init({ \n" .
					"\tmode : \"textareas\",\n" . 
					"\ttheme : \"advanced\",\n" .
					"\tplugins : \"table,advhr,advimage,advlink,iespell,insertdatetime,preview,zoom,flash,searchreplace,print,contextmenu,spellchecker,xhtmlxtras,visualchars,style,directionality,paste,inlinepopups,simplebrowser\",\n" .
					"\ttheme_advanced_buttons1_add_before : \"\",\n" .
					"\ttheme_advanced_buttons1_add : \"fontselect,fontsizeselect\",\n" .
					"\ttheme_advanced_buttons2_add : \"separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor\",\n" .
					"\ttheme_advanced_buttons2_add_before: \"cut,copy,paste,separator,search,replace,separator\",\n" .
					"\ttheme_advanced_buttons3_add_before : \"tablecontrols,separator\",\n" .
					"\ttheme_advanced_buttons3_add : \"emotions,iespell,flash,advhr,separator,print,spellchecker\",\n" .
					"\ttheme_advanced_buttons4 : \"visualchars,|,styleprops,|,abbr,acronym,cite,del,ins,|,ltr,rtl,|,pastetext,pasteword,selectall\",\n" .
					"\ttheme_advanced_toolbar_location : \"top\",\n" .
					"\ttheme_advanced_toolbar_align : \"left\",\n" .
					"\ttheme_advanced_path_location : \"bottom\",\n" .
					"\tplugin_insertdate_dateFormat : \"%Y-%m-%d\",\n" .
					"\tplugin_insertdate_timeFormat : \"%H:%M:%S\",\n" .
					"\textended_valid_elements : \"a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]\",\n" .
					"\texternal_link_list_url : \"example_data/example_link_list.js\",\n" .
					"\texternal_image_list_url : \"example_data/example_image_list.js\",\n" .
					"\tflash_external_list_url : \"example_data/example_flash_list.js\",\n" .
					"\ttheme_advanced_resize_horizontal : false,\n" .
					"\ttheme_advanced_resizing : true,\n" .
					"\tapply_source_formatting : true, \n" .
					"\tconvert_urls : false, \n" .
					"\tplugin_simplebrowser_width : '800', //default \n" .
					"\tplugin_simplebrowser_height : '600', //default \n" .
					"\tplugin_simplebrowser_browselinkurl : 'tinymce/jscripts/tiny_mce/plugins/simplebrowser/browser.html?Connector=connectors/php/connector.php', \n" .
					"\tplugin_simplebrowser_browseimageurl : 'tinymce/jscripts/tiny_mce/plugins/simplebrowser/browser.html?Type=Image&Connector=connectors/php/connector.php', \n" .
					"\tplugin_simplebrowser_browseflashurl : 'tinymce/jscripts/tiny_mce/plugins/simplebrowser/browser.html?Type=Flash&Connector=connectors/php/connector.php' \n" .
				"});\n"
			);
		}
		
		echo $editor_HTML;
	}
	
	function display_MCE( $name, $quickpost, $def_text )
	{
		global $basic_gui, $mod_loader;
		
		// load the tiny_mce javascript
		$basic_gui->add_JS( 'includes/tiny_mce/tiny_mce.js' );
		
		// some configuration :)
		$editor_HTML = '<textarea name="' . $name . '" style="width:100%; height:100%">' . $def_text . '</textarea>';
		
		if ( !$quickpost )
		{
			$basic_gui->add_JS_text( 
				"tinyMCE.init({ \n" .
					"\tmode : \"textareas\",\n" . 
					"\ttheme : \"advanced\",\n" .
					"\tplugins : \"table,advhr,advimage,advlink,iespell,insertdatetime,preview,zoom,flash,searchreplace,print,contextmenu,spellchecker,xhtmlxtras,visualchars,style,directionality,paste,inlinepopups,simplebrowser\",\n" .
					"\ttheme_advanced_buttons1_add_before : \"\",\n" .
					"\ttheme_advanced_buttons1_add : \"fontselect,fontsizeselect\",\n" .
					"\ttheme_advanced_buttons2_add : \"separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor\",\n" .
					"\ttheme_advanced_buttons2_add_before: \"cut,copy,paste,separator,search,replace,separator\",\n" .
					"\ttheme_advanced_buttons3_add_before : \"tablecontrols,separator\",\n" .
					"\ttheme_advanced_buttons3_add : \"emotions,iespell,flash,advhr,separator,print,spellchecker\",\n" .
					"\ttheme_advanced_buttons4 : \"visualchars,|,styleprops,|,abbr,acronym,cite,del,ins,|,ltr,rtl,|,pastetext,pasteword,selectall,|,browser\",\n" .
					"\ttheme_advanced_toolbar_location : \"top\",\n" .
					"\ttheme_advanced_toolbar_align : \"left\",\n" .
					"\ttheme_advanced_path_location : \"bottom\",\n" .
					"\tplugin_insertdate_dateFormat : \"%Y-%m-%d\",\n" .
					"\tplugin_insertdate_timeFormat : \"%H:%M:%S\",\n" .
					"\textended_valid_elements : \"a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]\",\n" .
					"\texternal_link_list_url : \"example_data/example_link_list.js\",\n" .
					"\texternal_image_list_url : \"example_data/example_image_list.js\",\n" .
					"\tflash_external_list_url : \"example_data/example_flash_list.js\",\n" .
					"\ttheme_advanced_resize_horizontal : false,\n" .
					"\ttheme_advanced_resizing : true,\n" .
					"\tapply_source_formatting : true, \n" .
					"\tconvert_urls : false, \n" .
					"\tplugin_simplebrowser_width : '800', //default \n" .
					"\tplugin_simplebrowser_height : '600', //default \n" .
					"\tplugin_simplebrowser_browselinkurl : '../../../tiny_mce/plugins/simplebrowser/browser.html?Connector=connectors/php/connector.php', \n" .
					"\tplugin_simplebrowser_browseimageurl : '../../../tiny_mce/plugins/simplebrowser/browser.html?Type=Image&Connector=connectors/php/connector.php', \n" .
					"\tplugin_simplebrowser_browseflashurl : '../../../tiny_mce/plugins/simplebrowser/browser.html?Type=Flash&Connector=connectors/php/connector.php' \n" .
				"});\n"
			);
		}else
		{
			$basic_gui->add_JS_text( 
				"tinyMCE.init({ \n" .
					"\tmode : \"textareas\",\n" . 
					"\ttheme : \"advanced\",\n" .
					"\ttheme_advanced_toolbar_location : \"top\",\n" .
					"\ttheme_advanced_toolbar_align : \"left\",\n" .
					"\ttheme_advanced_resize_horizontal : false,\n" .
					"\ttheme_advanced_resizing : false,\n" .
					"\tapply_source_formatting : true, \n" .
					"\tconvert_urls : false \n" .
				"});\n"
			);
		}
		
		// return the editor
		$mod_loader->port_vars( array( 'editor_HTML' => $editor_HTML, 'editor_WYSIWYG' => 1 ) );
	}
	
	function display_FCK( $name, $quickpost, $def_text )
	{
		global $mod_loader, $basic_gui, $Cl_root_path;
		
		// first get the editor
		include( "FCKeditor/fckeditor.php" );
		
		// then instantiate it
		$FCK = new FCKeditor( $name );
		// then do some config
		$FCK->BasePath = $basic_gui->get_URL() . $Cl_root_path . 'includes/FCKeditor/';
		$FCK->Value = ( empty( $def_text ) ) ? $this->lang[ 'Default_text' ] : $def_text;
		$FCK->Width = '100%';
		$FCK->Height = '100%';
		$FCK->ToolbarSet = ( $quickpost ) ? 'Quick' : 'Advanced';
		// get the HTML
		$editor_HTML = $FCK->Create();
		
		// add some stuff for the nonwysiwyg type
		if ( !$FCK->IsCompatible( ) )
		{
			$this->FCK = $FCK;
			$editor_HTML = $this->nonwysiwygHTML( $FCK ) . $editor_HTML;
		}
		
		// return the stuff
		$mod_loader->port_vars( array( 'editor_HTML' => $editor_HTML, 'editor_WYSIWYG' => intval( $FCK->IsCompatible() ) ) );
	}
	
	function nonwysiwygHTML( $FCK )
	{
		global $Cl_root_path4template, $basic_gui;
		
		//
		// construct the lovely toolbar :P
		//
		// first some type saving
		$smiley_root = $Cl_root_path4template . 'includes/FCKeditor/images/smiley/';
		$skin_root = $Cl_root_path4template . 'includes/FCKeditor/editor/skins/silver/';
		$button = '<td class="TB_Button_Off_2"><span class="TB_Icon_2" &tooltip& ><img src="' . $skin_root . '%s" border="0" onclick="editor_add_code( \'%s\', \'' . $FCK->InstanceName . '\' );"></span></td>';
		
		// then add the toolbar css
		$basic_gui->add_CSS( 'includes/FCKeditor/editor/skins/silver/fck_contextmenu.css' );
		$basic_gui->add_CSS( 'includes/FCKeditor/editor/skins/silver/fck_editor.css' );
		// and the JS
		$basic_gui->add_JS( 'includes/FCKeditor/editor/js/nonwysiwyg.js' );
		
		// BBCode and stuff
		$code_names = array( 'bold', 'italic', 'underline', 'left', 'center', 'right' );
		$code_opens = array( '[b]', '[i]', '[u]', '[left]', '[center]', '[right]' );
		$code_closes = array( '[/b]', '[/i]', '[/u]', '[/left]', '[/center]', '[/right]' );
		
		// start the bar
		$toolbar = '<div class="TB_ToolbarSet"><table><tr>';
// 		definecode( ' . $code_names . ', ' . $code_opens . ', ' . $code_closes . ' );
		$toolbar .= '<script type="text/javascript">definecode( \'' . implode( ';', $code_names ) . '\', \'' . implode( ';', $code_opens ) . '\', \'' . implode( ';', $code_closes ) . '\' );</script>';
		
		if ( $FCK->ToolbarSet == 'Quick' )
		{ // quickpost
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Bold' ], 'buttontip' ), sprintf( $button, 'toolbar/bold.gif', 'bold' ) ); // bold
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Italic' ], 'buttontip' ), sprintf( $button, 'toolbar/italic.gif', 'italic' ) ); // italic
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Underline' ], 'buttontip' ), sprintf( $button, 'toolbar/underline.gif', 'underline' ) ); // underline
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Left' ], 'buttontip' ), sprintf( $button, 'toolbar/justifyleft.gif', 'left' ) ); // left
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Center' ], 'buttontip' ), sprintf( $button, 'toolbar/justifycenter.gif', 'center' ) ); // center
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Right' ], 'buttontip' ), sprintf( $button, 'toolbar/justifyright.gif', 'right' ) ); // right
			
			$toolbar .= '<td><img src="' . $skin_root . 'images/toolbar.separator.gif"></td>';
			
			$toolbar .= '<td onclick="show_menu( \'fontbar\', \'' . $FCK->InstanceName . '\' );" class="TB_Button_Off_2">' . $this->lang[ 'Fonts' ] . '</td>';
			$toolbar .= '<td onclick="show_menu( \'sizebar\', \'' . $FCK->InstanceName . '\' );" class="TB_Button_Off_2">' . $this->lang[ 'Size' ] . '</td>';
			$toolbar .= '<td class="TB_Button_Off_2"><span class="TB_Icon_2"><img src="' . $skin_root . 'toolbar/smiley.gif" border="0" onclick="show_menu( \'smileybar\', \'' . $FCK->InstanceName . '\' );"></td></td>';
		}else
		{
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Bold' ], 'buttontip' ), sprintf( $button, 'toolbar/bold.gif', 'bold' ) ); // bold
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Italic' ], 'buttontip' ), sprintf( $button, 'toolbar/italic.gif', 'italic' ) ); // italic
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Underline' ], 'buttontip' ), sprintf( $button, 'toolbar/underline.gif', 'underline' ) ); // underline
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Left' ], 'buttontip' ), sprintf( $button, 'toolbar/justifyleft.gif', 'left' ) ); // left
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Center' ], 'buttontip' ), sprintf( $button, 'toolbar/justifycenter.gif', 'center' ) ); // center
			$toolbar .= str_replace( '&tooltip&', $basic_gui->make_tooltip( $this->lang[ 'Right' ], 'buttontip' ), sprintf( $button, 'toolbar/justifyright.gif', 'right' ) ); // right
			
			$toolbar .= '<td><img src="' . $skin_root . 'images/toolbar.separator.gif"></td>';
			
			$toolbar .= '<td onclick="show_menu( \'fontbar\', \'' . $FCK->InstanceName . '\' );" class="TB_Button_Off_2">' . $this->lang[ 'Fonts' ] . '</td>';
			$toolbar .= '<td onclick="show_menu( \'sizebar\', \'' . $FCK->InstanceName . '\' );" class="TB_Button_Off_2">' . $this->lang[ 'Size' ] . '</td>';
			$toolbar .= '<td class="TB_Button_Off_2"><span class="TB_Icon_2"><img src="' . $skin_root . 'toolbar/smiley.gif" border="0" onclick="show_menu( \'smileybar\', \'' . $FCK->InstanceName . '\' );"></td></td>';
		}
		// end it
		$toolbar .= '</tr></table></div>';
		
// 		$test = $this->getsmileys( 'bla' );
// 		echo htmlspecialchars( $test[0] );
		
		return $toolbar;
	}
	
	function getsmileys( $name )
	{
		global $Cl_root_path;
		
		// scan for the lil' suckers
		$content = '';
		$content .= $this->smileyscan( $Cl_root_path . 'includes/FCKeditor/editor/images/smiley/' );
			
		return array( $content, $name, $width, $height );
	}
	
	function smileyscan( $path )
	{
		global $basic_gui, $Cl_root_path, $plug_clcode;
		
		$FCK = $this->FCK;
		
		$URL = $basic_gui->get_URL();
		
		$content = '';
	
		$dir = dir( $path );
		while ( FALSE !== ( $entry = $dir->read( ) ) )
		{
			if ( $entry == '.' || $entry == '..' )
			{ // don't want to touch these two
				continue;
			}
			if ( is_dir( $path . $entry ) )
			{ // recurse
				$content .= $this->smileyscan( $path . $entry . '/' );
				continue;
			}
			// now for the stuff
			$folder = substr( strrchr( substr( $path, 0, -1 ), '/' ), 1 ); // gets the last dir
			$smile = array_search( $folder . '/' . $entry, $plug_clcode->smileys );
			$content .= ( !empty( $smile ) ) ? '<div class="TB_Button_Off_2" style="float: left;" onclick="add_smiley( \' ' . $smile . ' \', \'' . $FCK->InstanceName . '\' );"><div class="TB_Icon_2"><img src="' . $URL . $path . $entry . '"></div></div>' : '';
		}
		return $content;
	}
	
	//
	// End of Editor class
	//
}


?>