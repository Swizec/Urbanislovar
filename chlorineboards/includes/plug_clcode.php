<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:              plug_clcode.php                        //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:        08th July 2005                         //
//     version:               0.4.5                              //
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
// shows people what a blank module looks like
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
$vars = array( 'debug', 'gui', 'patterns', 'replaces', 'clcode', 'npatterns', 'nreplaces' );
$visible = array( 'private', 'private', 'private', 'private', 'private', 'private', 'private' );
eval( Varloader::createclass( 'plug_clcode', $vars, $visible ) );
// end class creation

class Plug_clcode extends plug_clcode_def
{
	
	// constructor
	function Plug_clcode( $debug = FALSE )
	{
		global $Cl_root_path, $template, $board_config, $cache, $lang_loader, $basic_gui;
		
		$this->debug = $debug;
		
		// load the language because it might be needed
		$this->lang = $lang_loader->get_lang( 'clcode' );
		
		// try reading the code from cache :)
		if ( $code = $cache->pull( 'ClCode' ) )
		{
			$this->npatterns = $code[ 'npatterns' ];
			$this->nreplaces = $code[ 'nreplaces' ];
			$this->patterns = $code[ 'patterns' ];
			$this->replaces = $code[ 'replaces' ];
			$this->clcode = $code[ 'clcode' ];
			return TRUE;
		}
		
		// read the clconfig file to obtain all the clcode :)
		if ( !@include( $template->folder . 'clcode' . cfgEx ) )
		{ // didn't work
			// so lets try it with the default
			if ( !@include( $Cl_root_path . 'template/' . $board_config[ 'def_temp' ] . '/clcode' . cfgEx ) )
			{
				// just set it to empty :(
				$this->clcode = FALSE;
			}else
			{ // worked, say we have it
				$this->clcode = TRUE;
			}
		}else
		{ // worked, say we have it
			$this->clcode = TRUE;
		}
		
		// now set the patterns and replaces, keys are patterns and values are replaces
		// we do this here so the script will run faster as this loop will be done only once
		$this->patterns = array();
		$this->replaces = array();
		
		if ( $this->clcode )
		{ // only do this if we have anything
			if ( is_array( $clcode ) ) // might be a fault in the file
			{
				foreach ( $clcode as $p => $r )
				{
					$this->patterns[] = $p;
					
					// a replace might have language dependant stuff, so we give it to it
					preg_match_all( '#{(.*?)}#', $r, $matches );
					foreach ( $matches[ 1 ] as $m )
					{
						$r = str_replace( '{' . $m . '}', $this->lang[ $m ], $r );
					}
					$this->replaces[] = $r;
				}
			}
			// this is used to define stuff that protects parts from being parsed
			if ( is_array( $noparse ) )
			{
				foreach ( $noparse as $p => $r )
				{
					$this->npatterns[] = $p;
					
					// a replace might have language dependant stuff, so we give it to it
					preg_match_all( '#{(.*?)}#', $r, $matches );
					foreach ( $matches[ 1 ] as $m )
					{
						$r = str_replace( '{' . $m . '}', $this->lang[ $m ], $r );
					}
					$this->nreplaces[] = $r;
				}			
			}
		}
		
		// store to cache
		$code = array( 'npatterns' => $this->npatterns, 'nreplaces' => $this->nreplaces, 'patterns' => $this->patterns, 'replaces' => $this->replaces, 'clcode' => $this->clcode );
		$cache->push( 'ClCode', $code, TRUE );
		
		// fetch the smileys thingies :P
		if ( !$this->smileys = $cache->pull( 'ClCode_smileys' ) )
		{ // have to read it from the disk
			if ( is_readable( $Cl_root_path . 'includes/FCKeditor/editor/images/smiley/smileys' . phpEx ) )
			{
				include( $Cl_root_path . 'includes/FCKeditor/editor/images/smiley/smileys' . phpEx );
				$this->smileys = $smileys;
				// store it
				$cache->push( 'ClCode_smileys', $this->smileys, TRUE );
			}
		}
		// set up the smiley replaces so it'll go quicker when needed
		$this->smileys_rep = array();
		$this->smileys_seek = array();
		$uri = $basic_gui->get_URL();
		if ( is_array( $this->smileys ) )
		{
			foreach ( $this->smileys as $seek => $rep )
			{
				$this->smileys_rep[] = '<img src="' . str_replace( '/', '\/', $uri . 'includes/FCKeditor/editor/images/smiley/' ) . $rep . '">';
				$this->smileys_seek[] = ' ' . $seek . ' ';
			}
		}
		
// 		print_R( $this->smileys );die();
	}
	
	// masks certain characters from being changed or considered in further parsing
	function maskchars( $str )
	{
// 		$str = htmlspecialchars( $str );
		// first make the two arrys
		$chars = array(
				'(',
				')',
				'[',
				']',
				'{',
				'}',
				':',
				'=',
				'*',
				'-',
			);
		$replaces = array(
				'&#40;',
				'&#41;',
				'&#91;',
				'&#93;',
				'&#123;',
				'&#125;',
				'&#58;',
				'&#61;',
				'&#42;',
				'&#45;',
			);

		// return it masked
		return str_replace( $chars, $replaces,  $str );
	}
	
	// parses the clcode into printable html
	function parse( $text, $allowhtml = FALSE )
	{
		global $basic_gui;
		
		// if allowhtml is false html needs killing
		$text = ( !$allowhtml ) ? str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), $text ) : $text;
		$text = $basic_gui->gennuline( $text );
		// this will enable more than one space
// 		$text = preg_replace( '#\s \s#', '&nbsp;&nbsp;&nbsp;', $text );
		
		// do the ClCode parsing with parse protection
		foreach ( $this->npatterns as $i => $p )
		{
			// the code
			$text = preg_replace( $p, $this->nreplaces[ $i ], $text );
			// now protect what was marked for protection
			preg_match_all( '#%%(.*?)%%#si', $text, $matches ); // first find it
			// then protect it
			$p = array();
			$r = array();
			foreach ( $matches[ 0 ] as $i => $m )
			{
				$p[] = $m;
				$r[] = $this->maskchars( $matches[ 1 ][ $i ] );
			}
			$text = str_replace( $p, $r, $text );
		}
		
		// do the ClCode parsing
		$text = preg_replace( $this->patterns, $this->replaces, $text );
		// and the smileys
		$text = str_replace( $this->smileys_seek, $this->smileys_rep, $text );
		
		// line breaks
		$breaks = array( "<br />\n", "\n<br />", "\n" );
		$text = str_replace( '<br>', '<br />', $text );
		$text = str_replace( $breaks, '<br />', $text );
		
		// and we return the thingy
		return $text;
	}
	
	//
	// End of Plug_clcode class
	//
}


?>