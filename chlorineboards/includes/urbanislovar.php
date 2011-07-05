<?php

/**
*     defines the urbanislovar class
*     @file                urbanislovar.php
*     @see urbanislovar
*/
/**
* urbanislovar custom module for doing what urbanislovar.org does
*     @class		   urbanislovar
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        18th August 2007
*     @package		     urbanislovar
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
eval( Varloader::createclass( 'urbanislovar', $vars, $visible ) );
// end class creation

class urbanislovar extends urbanislovar_def
{
	/**
	* constructor
	*/
	function urbanislovar( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax, $basic_gui;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/urbanislovar_gui' . phpEx );
		$this->gui = new urbanislovar_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'urbanislovar', TRUE );
		$this->gui->lang = $this->lang;
		
		$basic_gui->add_JS( $Cl_root_path . 'includes/urbanislovar.js' );
		$basic_gui->add_drag( 'blackDim', 'NO_DRAG' );
		$basic_gui->add_drag( 'loading', 'NO_DRAG' );
		
		$Sajax->add2export( 'urbanislovar->search', '$phrase' );
		$Sajax->add2export( 'urbanislovar->addTag', '$tag, $word, $check' );
		
		// this is needed because a plugin loads this module as well as index.php
		$this->executedDefault = FALSE;
	}
	/**
	* takes care of choosing the correct thing to display
	*/
	function display()
	{
		global $errors, $userdata;
		
		if ( $this->executedDefault )
		{
			return;
		}
		
		$mode = ( isset( $_GET[ SUBMODE_URL ] ) ) ? $_GET[ SUBMODE_URL ] : '';
		
		switch ( $mode )
		{
// 			case 'search':
// 				$this->search();
// 				break;
			case 'add':
				$this->gui->add();
				break;
			case 'adding':
				$this->adding();
				break;
			case 'searcherror':
				$this->error();
				break;
			case 'lettersearch':
				$this->firstLetter();
				break;
			case 'author':
				$this->authorSearch();
				break;
// 			case 'addTag':
// 				$this->addTag();
// 				break;
			case 'tagsearch':
				$this->tagSearch();
				break;
			case '':
			default:
				$this->main();
				break;
		}
		
		$this->executedDefault = TRUE;
	}
	/**
	* front page stuff eh
	*/
	function main()
	{
		global $db;
		
		if ( $_GET[ MODE_URL ] == 'searchresult' )
		{
			return;
		}
		
		// popular tags
		$sql = "SELECT MAX( count ) AS max FROM " . URBANISLOVAR_TAG2WORD;
		
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		$max = $db->sql_fetchfield( 'max' );
		
		$sql = "SELECT MAX( tag_score ) AS max FROM " . URBANISLOVAR_TAGS;
		
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		$max2 = $db->sql_fetchfield( 'max' );
		
		$sql = "SELECT t.*, t2w.* FROM " . URBANISLOVAR_TAGS . " t, " . URBANISLOVAR_TAG2WORD . " t2w WHERE " .
				"t.tag_id = t2w.tag_id " . 
				"ORDER BY t2w.count DESC LIMIT 20";
		
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		
		$tags = array();
		$hash = array();
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( !isset( $hash[ $row[ 'tag_tag' ] ] ) )
			{
				$tags[] = array( 'label' => $row[ 'tag_tag' ], 'score' => $row[ 'count' ], 'max' => $max, 'score2' => $row[ 'tag_score' ], 'max2' => $max2 );
				$hash[ $row[ 'tag_tag' ] ] = count( $tags )-1;
			}else
			{
				$tags[ $hash[ $row[ 'tag_tag' ] ] ][ 'score' ] += $row[ 'count' ];
			}
		}
		
		usort( $tags, array( 'urbanislovar', '_tagCompare' ) );
		
		// random words
		$sql = "SELECT word_id FROM " . URBANISLOVAR_WORDS . " WHERE word_id >= ( SELECT FLOOR( MAX( word_id ) * RAND() ) FROM " . URBANISLOVAR_WORDS . " ) ORDER BY word_id LIMIT 5";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		
		$words = array();
		$hash = array();
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			if ( !isset( $hash[ $row[ 'word_id' ] ] ) )
			{
				$words[] = $row[ 'word_id' ];
				$hash[ $row[ 'word_id' ] ] = TRUE;
			}
		}
		$words = '( ' . implode( ', ', $words ) . ' )';
		
		$sql = " SELECT w.*, m.*, q.* FROM " . URBANISLOVAR_WORDS . " w, "  . URBANISLOVAR_W2M . " w2m, " . URBANISLOVAR_MEANS . " m, " . URBANISLOVAR_QUERIES . " q, " . URBANISLOVAR_Q2W . " q2w WHERE
				w.word_id=w2m.word_id AND
				w2m.mean_id=m.mean_id AND 
				w.word_id IN $words AND
				q2w.query_id=q.query_id AND
				q2w.word_id=w.word_id";
			
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		
		$Result = $this->parseSearch( $result );
		
		$this->gui->main( $tags );
		
		$this->gui->search( $Result );
	}
	/**
	* does the search related stuff that is needed
	*/
	function search( $phrase )
	{
		global $security, $db;
		
		if ( empty( $phrase ) )
		{
			return ;
		}
		
		$phrase = urldecode( $phrase );
		
		// ok all is well, do the search
		$query = str_replace( ' ', '-', $phrase );
		
		$sql = "SELECT * FROM " . URBANISLOVAR_QUERIES . "  WHERE query='$query'";
		
		$result = $db->sql_query( $sql );
		if ( $db->sql_numrows( $result ) == 0 )
		{ // try fetching stuff from online sskj
			//$phrase = $phrase;
			$uri = 'http://bos.zrc-sazu.si/cgi/a03.exe?name=sskj_testa&expression=' . $phrase . '&hs=1';
			if ( !$sskj = file_get_contents( $uri ) )
			{
				return array( $security->append_sid( '/searcherror', TRUE ) );
			}
// 			echo "$uri\n\n";
			
			$found = FALSE;
			
			$result = $this->parse_sskj( $phrase, $sskj );
// 			print_R( $result );
			if ( empty( $result ) )
			{ // nothing useful
				$Phrase = $this->stem( $phrase );
				
				$possibles = array( '', 'a', 'i', 'o' );
				
				for ( $i = 0; $i < count( $possibles ); $i++ )
				{
					$phrase = $Phrase . $possibles[ $i ];
					
// 					echo "$phrase\n";
					
					$uri = 'http://bos.zrc-sazu.si/cgi/a03.exe?name=sskj_testa&expression=' . $phrase . '&hs=1';
					$sskj = file_get_contents( $uri );
					
					$res = $this->parse_sskj( $phrase, $sskj );
					if ( !empty( $res ) )
					{ // found it
						$found = TRUE;
						if ( empty( $result ) )
						{
							$result = $res;
						}else
						{
							$result = array_merge( $result, $res );
						}
					}
				}
			}else
			{
				$found = TRUE;
			}
			
			if ( $found )
			{
				// insert into database
				for ( $i = 0; $i < count( $result ); $i++ )
				{
					$word = $this->phonetics( $result[ $i ][ 'word' ] );
					
					// see if word is already in
					$sql = "SELECT word_id FROM " . URBANISLOVAR_WORDS . " WHERE word_word='$word'";
					if ( !$res = $db->sql_query( $sql ) )
					{
						print_R( $db->sql_error() );
						return ;
					}
					
					$needmeans = TRUE;
					
					if ( !$wordid = $db->sql_fetchfield( 'word_id' ) )
					{ // add the word and the query pointing to it
						$sql = "INSERT INTO " . URBANISLOVAR_WORDS . " ( word_word ) VALUES ( '$word' )";
						if ( !$db->sql_query( $sql ) )
						{
							print_R( $db->sql_error() );
							return ;
						}
						$wordid = $db->sql_nextid();
						
						$sql = "INSERT INTO " . URBANISLOVAR_QUERIES . " ( query ) VALUES ( '$query' )";
						if ( !$db->sql_query( $sql ) )
						{
							print_R( $db->sql_error() );
							return ;
						}
						$queryid = $db->sql_nextid();
						
						$sql = "INSERT INTO " . URBANISLOVAR_Q2W . " ( query_id, word_id ) VALUES ( '$queryid', '$wordid' )";
						if ( !$db->sql_query( $sql ) )
						{
							print_R( $db->sql_error() );
							return ;
						}
						
						$w = $this->phonetics( $word );
						mb_internal_encoding( mb_detect_encoding( $word ) );
						$first = urlencode( mb_strtolower( mb_substr( $w, 0, 1 ) ) );
						$sql = "INSERT INTO " . URBANISLOVAR_F2W . " ( first, word_id ) VALUES ( '$first', '$wordid' )";
						if ( !$db->sql_query( $sql ) )
						{
							print_R( $db->sql_error() );
							return ;
						}
						
						// the meanings probably don't exist if the word does not
						$needmeans = TRUE;
					}else
					{
						// word exists, but maybe the query to it does not
						$sql =  "SELECT * FROM " . URBANISLOVAR_QUERIES . " WHERE query='$query' ";
						if ( !$res = $db->sql_query( $sql ) )
						{
							print_R( $db->sql_error() );
							return ;
						}
						
						if ( $db->sql_numrows( $res ) == 0 )
						{ // add this new query
							$sql = "INSERT INTO " . URBANISLOVAR_QUERIES . " ( query ) VALUES ( '$query' )";
							if ( !$db->sql_query( $sql ) )
							{
								print_R( $db->sql_error() );
								return ;
							}
							$queryid = $db->sql_nextid();
							
							$sql = "INSERT INTO " . URBANISLOVAR_Q2W . " ( query_id, word_id ) VALUES ( '$queryid', '$wordid' )";
							if ( !$db->sql_query( $sql ) )
							{
								print_R( $db->sql_error() );
								return ;
							}
						}
						
						// we can probably safely assume the meanings are in there if the word is
						$needmeans = FALSE;
					}
					
					if ( $needmeans )
					{
						for ( $j = 0; $j < count( $result[ $i ][ 'meanings' ] ); $j++ )
						{
							$meaning = $result[ $i ][ 'meanings' ][ $j ];
							$example = $result[ $i ][ 'examples' ][ $j ];
							
							$sql = "INSERT INTO " . URBANISLOVAR_MEANS . " ( mean_author, mean_Uauthor, mean_meaning, mean_example, mean_thumbUp, mean_thumbDown, mean_time )VALUES( 'SSKJ', '$uri', '$meaning', '$example', 0, 0, '" . EXECUTION_TIME . "' )";
							if ( !$db->sql_query( $sql ) )
							{
								print_R( $db->sql_error() );
								return ;
							}
							$meanid = $db->sql_nextid();
							
							$sql = "INSERT INTO " . URBANISLOVAR_W2M . " ( word_id, mean_id ) VALUES ( '$wordid', '$meanid' )";
							if ( !$db->sql_query( $sql ) )
							{
								print_R( $db->sql_error() );
								return ;
							}
						}
					}
				}
			}
		}
		
		//$this->gui->search( $result, str_replace( ' ', '-', $phrase ) );
		//$query = urlencode( str_replace( ' ', '-', $phrase ) );
		
		return array(  $security->append_sid( '/iskanje/' . urlencode( $query ), TRUE ) );
	}
	/**
	* fetches previous search results and shoves them to the gui part
	*/
	function searchresult()
	{
		global $db, $security;
		
		$query = ( isset( $_GET[ 'query' ] ) ) ? $_GET[ 'query' ] : '';
		
		$query = urldecode( $query );
		
		if ( empty( $query ) )
		{
			$this->error();
		}
		
		$fark = FALSE;
		
		for ( ; ; )
		{
			$sql = "SELECT w.*, m.*, q.* FROM " . URBANISLOVAR_WORDS . " w, " . URBANISLOVAR_QUERIES . " q, " . URBANISLOVAR_Q2W . " q2w, " . URBANISLOVAR_W2M . " w2m, " . URBANISLOVAR_MEANS . " m WHERE 
					q.query='$query' AND 
					q2w.query_id=q.query_id AND
					q2w.word_id=w.word_id AND
					w.word_id=w2m.word_id AND
					w2m.mean_id=m.mean_id";
	
			if ( !$result = $db->sql_query( $sql ) )
			{
				$this->error();
			}
			
			if ( $db->sql_numrows( $result ) == 0 )
			{ // perform a search
				if ( $fark )
				{
					break;
				}
				
				$this->search( $_GET[ 'query' ] );
				
				$fark = TRUE;
			}else
			{
				break;
			}
		}
		
		$Result = $this->parseSearch( $result );
		
		if( empty( $Result ) )
		{
			$this->error( sprintf( $this->lang[ 'Error_nofind' ], $security->append_sid( '?dodaj' ) ) );
		}
		
		$this->gui->search( $Result, $query );
	}
	/**
	* fetches words starting with a given letter
	*/
	function firstLetter()
	{
		global $db, $security;
		
		$letter = ( isset( $_GET[ 'letter' ] ) ) ? $_GET[ 'letter' ] : '';
		
		if ( empty( $letter ) )
		{
			$this->error();
		}
		
		$sql = " SELECT w.*, m.*, q.* FROM " . URBANISLOVAR_WORDS . " w, " . URBANISLOVAR_F2W . " f2m, " . URBANISLOVAR_W2M . " w2m, " . URBANISLOVAR_MEANS . " m, " . URBANISLOVAR_QUERIES . " q, " . URBANISLOVAR_Q2W . " q2w  WHERE
				f2m.first='$letter' AND
				f2m.word_id=w.word_id AND
				w.word_id=w2m.word_id AND
				w2m.mean_id=m.mean_id AND
				q2w.query_id=q.query_id AND
				q2w.word_id=w.word_id";
			
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		
		$Result = $this->parseSearch( $result );
		
		if ( empty( $Result ) )
		{
			$this->error( sprintf( $this->lang[ 'Error_nofindF' ], $security->append_sid( '?dodaj' ) ) );
		}
		
		$this->gui->search( $Result );
	}
	/**
	* fetches results when searching by author
	*/
	function authorSearch()
	{
		global $db;
		
		$author = ( isset( $_GET[ 'author' ] ) ) ? urldecode( $_GET[ 'author' ] ) : '';
		
		if ( empty( $author ) )
		{
			$this->error();
		}
		
		$sql = " SELECT w.*, m.*, q.* FROM " . URBANISLOVAR_WORDS . " w, "  . URBANISLOVAR_W2M . " w2m, " . URBANISLOVAR_MEANS . " m, " . URBANISLOVAR_QUERIES . " q, " . URBANISLOVAR_Q2W . " q2w WHERE
				m.mean_author='$author' AND
				w.word_id=w2m.word_id AND
				w2m.mean_id=m.mean_id AND
				q2w.query_id=q.query_id AND
				q2w.word_id=w.word_id";
		
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		
		$Result = $this->parseSearch( $result );
		
		if ( empty( $Result ) )
		{
			$this->error(  );
		}
		
		$this->gui->search( $Result );
	}
	/**
	* fetches all words tagged with a certain tag
	*/
	function tagSearch()
	{
		global $db;
		
		$tag = ( isset( $_GET[ 'tag' ] ) ) ? urldecode( $_GET[ 'tag' ] ) : '';
		
		if ( empty( $tag ) )
		{
			$this->error();
		}
		
		$sql = "SELECT tag_id FROM " . URBANISLOVAR_TAGS . " WHERE tag_tag='$tag' LIMIT 1";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		$tid = $db->sql_fetchfield( 'tag_id' );
		
		$sql = " SELECT w.*, m.*, q.* FROM " . URBANISLOVAR_WORDS . " w, "  . URBANISLOVAR_W2M . " w2m, " . URBANISLOVAR_MEANS . " m, " . URBANISLOVAR_TAG2WORD . " t2w, " . URBANISLOVAR_TAGS . " t, " . URBANISLOVAR_QUERIES . " q, " . URBANISLOVAR_Q2W . " q2w WHERE
				t.tag_tag='$tag' AND
				t.tag_id=t2w.tag_id AND
				t2w.word_id=w.word_id AND
				w.word_id=w2m.word_id AND
				w2m.mean_id=m.mean_id AND
				q2w.query_id=q.query_id AND
				q2w.word_id=w.word_id";
				
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		
		$Result = $this->parseSearch( $result );
		
		if ( empty( $Result ) )
		{
			$this->error(  );
		}
		
		$this->gui->search( $Result );
	}
	/**
	* decides what is needed to be done upon an error
	*/
	function error( $error = '' )
	{
		if ( $_GET[ 'AJAX_CALL' ] )
		{
			return $basic_gui->back_URL;
		}else
		{
			$this->gui->main( array(), ( empty( $error ) ) ? $this->lang[ 'Error_general' ] : $error );
		}
	}
	/**
	* extracts the result from all the mumbo jumbo sskj returns
	* @param string $html
	* @return FALSE on failure, string otherwise
	*/
	function parse_sskj( $word, $html )
	{
		$word = strip_tags( $this->phonetics( urldecode( $word ) ) );
	
		preg_match_all( '#<b>(.+?)</b>(.*?)(?=<li|</ol>)#ums', $html, $matches );
		//print_R( $matches );
		$result = array();
		
// 		print_R( $matches );
// 		die();
		
		for ( $i = 0; $i < count( $matches[ 0 ] ); $i++ )
		{
			$word2 = $this->phonetics( strip_tags( $matches[ 1 ][ $i ] ) );
			
			if ( mb_strlen( $word, 'UTF-8' ) != mb_strlen( $word2, 'UTF-8'  ) )
			{
				break;
			}
			
			$things = preg_split( '#\s+([/]+|(&\#x2666;|&\#x25CF;|&\#x25CA;)* <font.*?>.+?\.</font>|<b>[0-9]+\.</b>)\s+#', $matches[ 2 ][ $i ] );
// 			print_R( $things );
			
			$result[ $i ][ 'word' ] = strip_tags( $matches[ 1 ][ $i ] );
			$result[ $i ][ 'meanings' ] = array();
			$result[ $i ][ 'examples' ] = array();
			
			for ( $j = 0; $j < count( $things ); $j++ )
			{
				$things[ $j ] = str_replace( array( '&#x266A;', '&#x2666;', '&#x25CF;', '&#x25CA' ), '', $things[ $j ] );
				$things[ $j ] = $this->phonetics( $things[ $j ] );
				$things[ $j ] = ( substr( $things[ $j ], 0, 7 ) == '</font>' ) ? substr( $things[ $j ], 7 ) : $things[ $j ] ;
				$things2 = preg_split( '#;* <font size=-1>\p{L}+\.</font>#', $things[ $j ] );
				
				for ( $k = 0; $k < count( $things2 ); $k++ )
				{
					preg_match( '#<i>(.*?)</i>#', $things2[ $k ], $stuff );
					
					$examples = preg_replace( '#.*?ali.*?<i#', '<i', $things2[ $k ] );
					$examples = str_replace( $stuff[ 1 ], '', $examples );
					$examples = strip_tags( $examples );
					$examples = preg_replace( '#[a-z]+\).*?:#', '', $examples );
					$examples = preg_replace( '#[a-z]+\.[,]*#', '', $examples );
					$examples = preg_replace( '#-.*?\)#', '', $examples );
					$examples = ( !preg_match( '#^[\p{L}]#', $examples ) ) ? substr( $examples, 1 ) : $examples;
					$examples = ltrim( $examples );
					$examples = preg_replace( '#[0-9]+\s*#', '', $examples );
					
					$meaning = str_replace( ':', '', $stuff[ 1 ] );
					$meaning = strip_tags( $meaning );
					
					if ( !empty( $stuff[ 1 ] ) )
					{
						$result[ $i ][ 'meanings' ][] = $meaning;
						$result[ $i ][ 'examples' ][] = $examples;
					}
				}
			}
		}
		return $result;
	}
	/**
	* puts a word in its root form
	* @param string $word
	* @return string
	*/
	function stem( $word )
	{
		$suffix = array( 'ega', 'emu', 'oma', 'ema', 'emi', 'imi', 'ima', 'ami', 'ama', 'na', 'im', 'mi', 'om', 'ov', 'ih', 'eh', 'ah', 'am', 'jo', 'je', 'ja', 'a', 'e', 'i', 'o', 'u' );
		
		$werd = $word;
		
		for ( $i = 0; $i < count( $suffix ); $i++ )
		{
			$last = mb_substr( $werd, -strlen( $suffix[ $i ] ) );
			if ( $last == $suffix[ $i ] )
			{
				$werd = mb_substr( $werd, 0, -strlen( $last ) );
			}
		}
		
		if ( mb_substr( $werd, -2 ) == 'ov' )
		{
			$werd = mb_substr( $werd, 0, -2 );
		}
		
		return $this->stemChange( $werd );
	}
	/**
	* changes thingies around so as to be stemmed properly
	* @param string $word
	* @return string
	*/
	function stemChange( $word )
	{
		$change = 'ženj->žnj dij->dj nvi->nev mij->em mim->em rv->ri *v->*ev';
	
		$arr = array();
		foreach ( mb_split( ' ', $change ) as $chn )
		{
			$chn = mb_split( '->', $chn );
			$arr[ $chn[ 0 ] ] = $chn[ 1 ];
		}
		$change = $arr;
		
		foreach ( array_keys( $change ) as $needle )
		{
			if ( $needle{0} == '*' )
			{
				$word = preg_replace( '#([^aeiou])v$#', '$1' . str_replace( '*', '', $change[ $needle ] ), $word );
				break;
			}elseif ( mb_substr( $word, -mb_strlen( $needle ) ) == $needle )
			{
				$w = mb_substr( $word, 0, -mb_strlen( $needle ) );
				$word = $w . $change[ $needle ];
				break;
			}
		}
		
		return $word;
	}
	/**
	* changes phonetic letters into normal versions
	* @param string $word
	* @return string
	*/
	function phonetics( $word )
	{
		//$word = html_entity_decode( $word,  ENT_COMPAT, 'UTF-8' );
		$word = str_replace( '&nbsp;', ' ', $word );
		preg_match_all( '/&#x([0-9A-F]+);/', $word, $matches );
		
		foreach ( $matches[ 1 ] as $char )
		{
			$c = $this->unichr( eval( "return 0x$char;" ) );
			if ( $c != '' )
			{
				$word = str_replace( '&#x' . $char . ';', $c, $word );
			}
		}
		
		return $word;
	}
	/**
	* unicode supported chr from php.net
	*/
	function unichr($c) {
		if ($c <= 0x7F) 
		{
			return chr($c);
		}else if ($c <= 0x7FF) 
		{
			return chr(0xC0 | $c >> 6) . chr(0x80 | $c & 0x3F);
		}else if ($c <= 0xFFFF)
		{
			return chr(0xE0 | $c >> 12) . chr(0x80 | $c >> 6 & 0x3F) 
					. chr(0x80 | $c & 0x3F);
		}else if ($c <= 0x10FFFF) 
		{
			return chr(0xF0 | $c >> 18) . chr(0x80 | $c >> 12 & 0x3F)
					. chr(0x80 | $c >> 6 & 0x3F)
					. chr(0x80 | $c & 0x3F);
		}else
		{
			return false;
		}
	}
	/**
	* parses search results from practically any query
	* @param resource $result query result
	* @return mixed
	*/
	function parseSearch( &$result )
	{
		global $db, $security;
		
		$Result = array();
		
		while ( $row = $db->sql_fetchrow( $result ) )
		{
// 			print_R( $row ); echo "\n\n<br /><br />";
			$author = $row[ 'mean_author' ];
			$word = $row[ 'word_word' ];
			$authorURI = $security->append_sid( '/avtor/' . $author, TRUE );
		
			if ( !isset( $Result[ $author ] ) )
			{
				$Result[ $author ] = array();
			}
			if ( !isset( $Result[ $author ][ $word ] ) )
			{
				$Result[ $author ][ $word ] = array();
				$Result[ $author ][ $word ][ 'time' ] = $row[ 'mean_time' ];
				$Result[ $author ][ $word ][ 'meanings' ] = array();
				$Result[ $author ][ $word ][ 'examples' ] = array();
				$Result[ $author ][ $word ][ 'U' ] = ( $author == 'SSKJ' ) ? $row[ 'mean_Uauthor' ] : $authorURI;
				$Result[ $author ][ $word ][ 'tags' ] = $this->fetchTags( $word );
				$Result[ $author ][ $word ][ 'query' ] = $row[ 'query' ];
			}
			$Result[ $author ][ $word ][ 'meanings' ][] = $row[ 'mean_meaning' ];
			$Result[ $author ][ $word ][ 'examples' ][] = $row[ 'mean_example' ];
		}
		
		return $Result;
	}
	/**
	* fetches a list of tag associated with a certain word
	*/
	function fetchTags( $word )
	{
		global $db;
		
		// current estimates are that fetching the max count like this is still quicker than finding it by looping through the result an extra time
		$sql = "SELECT MAX( t2w.count ) AS max FROM " . URBANISLOVAR_TAGS . " t, " . URBANISLOVAR_TAG2WORD . " t2w, " . URBANISLOVAR_WORDS . " w WHERE " .
				"t.tag_id = t2w.tag_id AND " . 
				"t2w.word_id = w.word_id AND " .
				"w.word_word = '$word' ";
		
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		$max = $db->sql_fetchfield( 'max' );
		
		$sql = "SELECT MAX( tag_score ) AS max FROM " . URBANISLOVAR_TAGS;
		
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		$max2 = $db->sql_fetchfield( 'max' );
		
		$sql = "SELECT t.*, t2w.* FROM " . URBANISLOVAR_TAGS . " t, " . URBANISLOVAR_TAG2WORD . " t2w, " . URBANISLOVAR_WORDS . " w WHERE " .
				"t.tag_id = t2w.tag_id AND " . 
				"t2w.word_id = w.word_id AND " .
				"w.word_word = '$word' " .
				"ORDER BY t.tag_tag ASC";
		
		if ( !$result = $db->sql_query( $sql ) )
		{
			$this->error();
		}
		
		$return = array();
		while ( $row = $db->sql_fetchrow( $result ) )
		{
			$return[] = array( 'label' => $row[ 'tag_tag' ], 'score' => $row[ 'count' ], 'max' => $max, 'score2' => $row[ 'tag_score' ], 'max2' => $max2 );
		}
		
		return $return;
	}
	/**
	* makes sure words can be added
	*/
	function adding()
	{
		global $db, $errors, $security;
		
		$query = ( isset( $_POST[ 'word' ] ) ) ? $_POST[ 'word' ] : '';
		$meaning = ( isset( $_POST[ 'definition' ] ) ) ? $_POST[ 'definition' ] : '';
		$example = ( isset( $_POST[ 'example' ] ) ) ? $_POST[ 'example' ] : '';
		$name = ( isset( $_POST[ 'name' ] ) ) ? $_POST[ 'name' ] : '';
		$site = ( isset( $_POST[ 'site' ] ) ) ? $_POST[ 'site' ] : '';
		$schetty = $_POST[ 'schetty' ];
		
		if ( !empty( $schetty ) )
		{ // this a bot that doesn't know a hidden field is to stay empty
			$this->error();
		}
		
		if ( empty( $query ) || empty( $meaning ) || empty( $example ) || empty( $name ) )
		{
			if ( $_GET[ 'AJAX_CALL' ] )
			{
				return;
			}else
			{
				$this->gui->add( $query, $meaning, $example, $name, $site, $this->lang[ 'Error_addForm' ] );
				return;
			}
		}
		
		// see if this query already exists (because word and query are the same thing
		$sql =  "SELECT query_id FROM " . URBANISLOVAR_QUERIES . " WHERE query='$query' ";
		if ( !$res = $db->sql_query( $sql ) )
		{
			$errors->report_error( $this->lang[ 'Error_addSQL' ], CRITICAL_ERROR );
		}
		
		if ( !$queryid = $db->sql_fetchfield( 'query_id' ) )
		{ // add the query
			$sql = "INSERT INTO " . URBANISLOVAR_QUERIES . " ( query ) VALUES ( '$query' )";
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( $this->lang[ 'Error_addSQL' ], CRITICAL_ERROR );
			}
			$queryid = $db->sql_nextid();
		}
		
		// see if the word itself already exists
		$sql =  "SELECT word_id FROM " . URBANISLOVAR_WORDS . " WHERE word_word='$query' ";
		if ( !$res = $db->sql_query( $sql ) )
		{
			$errors->report_error( $this->lang[ 'Error_addSQL' ], CRITICAL_ERROR );
		}
		
		if ( !$wordid = $db->sql_fetchfield( 'word_id' ) )
		{ // add the word and properly link to it
			$sql = "INSERT INTO " . URBANISLOVAR_WORDS . " ( word_word ) VALUES ( '$query' )";
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( $this->lang[ 'Error_addSQL' ], CRITICAL_ERROR );
			}
			$wordid = $db->sql_nextid();
			
			$sql = "INSERT INTO " . URBANISLOVAR_Q2W . " ( query_id, word_id ) VALUES ( '$queryid', '$wordid' )";
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( $this->lang[ 'Error_addSQL' ], CRITICAL_ERROR );
			}
			
			mb_internal_encoding( mb_detect_encoding( $query ) );
			$first = urlencode( mb_strtolower( mb_substr( $query, 0, 1 ) ) );
			$sql = "INSERT INTO " . URBANISLOVAR_F2W . " ( first, word_id ) VALUES ( '$first', '$wordid' )";
			if ( !$db->sql_query( $sql ) )
			{
				$errors->report_error( $this->lang[ 'Error_addSQL' ], CRITICAL_ERROR );
			}
		}
		
		// now add the meaning
		$sql = "INSERT INTO " . URBANISLOVAR_MEANS . " ( mean_author, mean_Uauthor, mean_meaning, mean_example, mean_thumbUp, mean_thumbDown, mean_time )VALUES( '$name', '$site', '$meaning', '$example', 0, 0, '" . EXECUTION_TIME . "' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( $this->lang[ 'Error_addSQL' ], CRITICAL_ERROR );
		}
		$meanid = $db->sql_nextid();
		
		$sql = "INSERT INTO " . URBANISLOVAR_W2M . " ( word_id, mean_id ) VALUES ( '$wordid', '$meanid' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( $this->lang[ 'Error_addSQL' ], CRITICAL_ERROR );
		}
		//die();
		header( 'Location: ' . $security->append_sid( '/iskanje/' . $query, TRUE ) );
	}
	/**
	* adds a new tag to a word
	*/
	function addTag( $tag, $word, $check  )
	{
		global $db;
		
		$tag = strtolower( urldecode( $tag ) );
		$word = urldecode( $word );
		
// 		$word = ( isset( $_GET[ 'word' ] ) ) ? strtolower( urldecode( $_GET[ 'word' ] ) ) : '';
// 		$tag = ( isset( $_POST[ 'tag' ] ) ) ? urldecode( $_POST[ 'tag' ] ) : '';
// 		$check = $_POST[ '3dots' ];
		
		if ( empty( $word ) || empty( $tag ) || !empty( $check ) )
		{
			return '';
		}
		
		$sql = "SELECT word_id FROM ". URBANISLOVAR_WORDS . " WHERE word_word='$word' LIMIT 1";
		if ( !$result = $db->sql_query( $sql ) )
		{
			return 'FAIL1';
		}
		$wid = $db->sql_fetchfield( 'word_id' );
		
		$sql = "SELECT * FROM " . URBANISLOVAR_TAGS . " WHERE tag_tag='$tag' LIMIT 1";
		if ( !$result = $db->sql_query( $sql ) )
		{
			return 'FAIL2';
		}
		
		if ( $db->sql_numrows( $result ) > 0 )
		{
			$tid = $db->sql_fetchfield( 'tag_id' );
			
			$sql = "SELECT * FROM " . URBANISLOVAR_TAG2WORD . " WHERE word_id='$wid' AND tag_id='$tid' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				return 'FAIL3';
			}
			
			if ( $db->sql_numrows( $result ) > 0 )
			{
				$sql = "UPDATE " . URBANISLOVAR_TAG2WORD . " SET count=count+1 WHERE tag_id='$tid' AND word_id='$wid' LIMIT 1";
				if ( !$db->sql_query( $sql ) )
				{
					return 'FAIL4';
				}
			}else
			{
				$sql = "UPDATE " . URBANISLOVAR_TAGS . " SET tag_score=tag_score+1 WHERE tag_id='$tid' LIMIT 1";
				if ( !$db->sql_query( $sql ) )
				{
					return 'FAIL5';
				}
				
				$sql = "INSERT INTO " . URBANISLOVAR_TAG2WORD . " ( tag_id, word_id, count ) VALUES ( $tid, $wid, 1 )";
				if ( !$db->sql_query( $sql ) )
				{
					return 'FAIL6';
				}
			}
		}else
		{
			$sql = "INSERT INTO " . URBANISLOVAR_TAGS . " ( tag_tag, tag_score ) VALUES ( '$tag', 1 )";
			if ( !$db->sql_query( $sql ) )
			{
				return 'FAIL7';
			}
			
			$tid = $db->sql_nextid();
			
			$sql = "INSERT INTO " . URBANISLOVAR_TAG2WORD . " ( tag_id, word_id, count ) VALUES ( $tid, $wid, 1 )";
			if ( !$db->sql_query( $sql ) )
			{
				return 'FAIL8';
			}
		}
		
		return 'WORK';
	}
	/**
	* comparison function for alphabetical tag sorting
	*/
	function _tagCompare( $a, $b )
	{
		return ( $a[ 'label' ] > $b[ 'label' ] ) ? 1 : -1;
	}
	
	//
	// End of urbanislovar class
	//
}

?>