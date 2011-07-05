<?php

/**
* Tag cloud creation class
*     @class		   TagCloud
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        12th February 2007
* @filesource
*/

class TagCloud_sl
{
	/**
	* constructor
	*/
	function TagCloud_sl( $parent = FALSE )
	{
		$this->father = $parent;
	
		$this->commonWords = array();
		$this->solvedWords = array();
		
		// get the configs
		include( PATH . '/includes/TagCloud/Tag_Config_sl.php' );
		foreach ( $Config as $var => $val )
		{
			$this->$var = $val;
		}
		
		$this->commonWords = mb_split( ' ', $Config[ 'commonWords' ] );
	}
	/**
	* initiate
	*/
	function initiate( $source = '', $file = TRUE )
	{
		if ( $file )
		{ // source is a file, read it in
			if ( !$this->cloudSource = file_get_contents( $source ) )
			{
				echo 'Empty file';
				return FALSE;
			}
		}else
		{ // source just is
			$this->cloudSource = $source;
		}
		
		//mb_internal_encoding( mb_detect_encoding( $this->cloudSource ) );
		
		$this->statistics = array();
		$this->statisticsHash = array();
		$this->maxStat = 0;
		
	}
	/**
	* the main function that does everything needed
	*/
	function main()
	{
		// split into words
		$this->cloudSource = preg_split( '#[^\p{L}]+#u', $this->father->xml_entity_decode( $this->cloudSource ) );
		// because this is splitting an empty word manifests at the end, we pop it off
		array_pop( $this->cloudSource );
		
		// create the configs
		$this->suffix = mb_split( ' ', $this->suffix );
		$this->prefix = mb_split( ' ', $this->prefix );
		$arr = array();
		foreach ( mb_split( ' ', $this->change ) as $chn )
		{
			$chn = mb_split( '->', $chn );
			$arr[ $chn[ 0 ] ] = $chn[ 1 ];
		}
		$this->change = $arr;
		
		// loop through the words now
		foreach ( $this->cloudSource as $number => $word )
		{
			$Word = $word;
			$word = mb_strtolower( $word );
			if ( mb_strlen( $word ) < 3 )
			{ // the reason these two conditionals aren't merged is that we want to avoid the search through the long array of common words if at all possible
				$this->addToStat( $word, $Word, $number, FALSE );
				continue;
			}
			if ( in_array( $word, $this->commonWords ) )
			{ // don't need this one
				$this->addToStat( $word, $Word, $number, FALSE );
				continue;
			}
			// stem the word
			$word = $this->stem( mb_strtolower( $word ) );
			// add it to the stats
			$this->addToStat( $word, $Word, $number, TRUE );
		}
		
		// order the stats
		$this->orderStats();
		
		$this->output( );
	}
	/**
	* adds word to stats
	*/
	function addToStat( $word, $Word, $number, $realWord )
	{
		if ( !isset( $this->statistics[ $word ] ) )
		{
			$this->statistics[ $word ][ 'count' ] = ( $realWord ) ? 1 : 0;
			$this->statistics[ $word ][ 'output' ] = $Word;
			$this->statistics[ $word ][ 'where' ] = array( $number );
		}else
		{
			$this->statistics[ $word ][ 'where' ][] = $number;
			if ( $realWord )
			{
				$this->statistics[ $word ][ 'count' ]++;
				if ( $this->statistics[ $word ][ 'count' ] > $this->maxStat )
				{
					$this->maxStat = $this->statistics[ $word ][ 'count' ];
				}
			}
		}
	}
	/**
	* finds the stem of the word
	* @param string $word word to stem
	* @return string stem of $word
	*/
	/*function stem( $word )
	{
		// see if we already have it
		if ( isset( $this->solvedWords[ $word ] ) )
		{
			return  $this->solvedWords[ $word ];
		}
		$Word = $word;
		
		// duplicate letters cause trouble for whatever reason
		if ( mb_substr( $word, -1 ) == mb_substr( $word, mb_strlen( $word )-2, 1 )  )
		{
			//echo $word . '::' . mb_substr( $word, -1 ) . '::' . mb_substr( $word, mb_strlen( $word )-2, 1 ) . '::' . mb_substr( $word, 0, -1 );
			//die();
			$word = mb_substr( $word, 0, -2 );
		}
		
		// loop through suffixes and remove when found
		// declensed words mainly
		$testword = 'odpad';
		$debug = FALSE;
		if ( $debug )
		{
			if ( mb_substr( $word, 0, strlen( $testword ) ) == $testword )
			{
				echo "<br />";
			}
		}
		
		$found = TRUE;
		while ( $found )
		{
			$found = FALSE;
			if ( $debug )
			{
				if ( mb_substr( $word, 0, strlen( $testword ) ) == $testword )
				{
					echo "$word<br />";
				}
			}
			
			foreach ( $this->suffix as $suffix )
			{	
				// changes
				foreach ( array_keys( $this->change ) as $needle )
				{
					if ( mb_substr( $word, -mb_strlen( $needle ) ) == $needle )
					{
						$w = mb_substr( $word, 0, -mb_strlen( $needle ) );
						$word = $w . $this->change[ $needle ];
						if ( $debug )
						{
							if ( mb_substr( $word, 0, strlen( $testword ) ) == $testword )
							{
								echo "$word<br />";
							}
						}
						break;
					}
				}		
				// suffixes
				if ( mb_substr( $word, -mb_strlen( $suffix ) ) == $suffix )
				{ // the suffix is there, get rid of it
					$word = mb_substr( $word, 0, -mb_strlen( $suffix ) );
					$found = TRUE;
					break;
				}
			}
		}
		
		// loop through prefixes and remove when found
		foreach ( $this->prefix as $prefix )
		{
			if ( mb_substr( $word, 0, mb_strlen( $prefix ) ) == $prefix )
			{
				$word = mb_substr( $word, mb_strlen( $prefix ) );
			}
		}
		
		// store it
		$this->solvedWords[ $Word ] = $word;
		
		// we return the result
		return $word;
	}*/
	/**
	* finds the stem of a word
	* this is an attempt to make a more accurate and faster algorithm
	* @param string $word word to stem
	* @return string stem of $word
	*/
	function stem( $word )
	{
		if ( isset( $this->solvedWords[ $word ] ) )
		{
			return  $this->solvedWords[ $word ];
		}
		$Word = $word;
		
		
		$word = $this->_stemOne( $word );
		
		$word = $this->_stemChanges( $word );
		
		$word = $this->_stemTwo( $word );
		
		$word = $this->_stemChanges( $word );
		
		$word = $this->_stemTwo( $word );
		
		$word = $this->_stemChanges( $word );
		
		$word = $this->_stemThree( $word );
		
		$word = $this->_stemChanges( $word );
		
		$word = $this->_stemTwo( $word );
		
		
		$this->solvedWords[ $Word ] = $word;
		
		return $word;
	}
	/**
	* stems one letter
	* @access private
	*/
	function _stemOne( $word )
	{
		$last = mb_substr( $word, -1 );
		if ( in_array( $last, array( 'a', 'e', 'i', 'o', 'u' ) ) )
		{
			$word = mb_substr( $word, 0, -1 );
		}
		
		return $word;
	}
	/**
	* stems two letters
	* @access private
	*/
	function _stemTwo( $word )
	{
		if ( mb_strlen( $word ) > 4 )
		{
			$last = mb_substr( $word, -2 );
			if ( in_array( $last, array( 'ij', 'em', 'mi', 'al', 'el', 'nj', 'no', 'eg', 'av', 'an', 'ih', 'ki', 'om', 'in', 'ne', 'a�', 'te', 'iv', 'jo', 'sk', 'na', 'eh', 'i�', 'ma', 'il', 'va', 'am', 'ic', 'ce', 'vi', 'e�', '�a', 'ca', 'ec', 'et', 'es', 'it', 'en', 'lj', 'ah', 'ik', 'a�', 'ja', 'la', 'im', 'ni', '�k', 'jt', 'ek', 'ok', 'at', 'je', 'e�', 'ev', 'ov', 'li', 'ar', 'on' ) ) )
			{
				$word = mb_substr( $word, 0, -2 );
			}
		}
		
		return $word;
	}
	/**
	* stems three letters
	* @access private
	*/
	function _stemThree( $word )
	{
		if ( mb_strlen( $word ) > 6 )
		{
			$last = mb_substr( $word, -3 );
			if ( in_array( $last, array( 'erj', 'ljo', 'enj', 'lji', 'stv', 'ovi', 'ama', 'ega', 'imi', 'ost', 'lje', 'oma', 'i�n', 'nic', 'nim', 'ima', 'isk', 'lja', 'emu', 'ami', 'lju', 'ej�' ) ) )
			{
				mb_substr( $word, 0, -3 );
			}
		}
		
		return $word;
	}
	/**
	* performs phonographic changes
	* @access private
	*/
	function _stemChanges( $word )
	{
		foreach ( array_keys( $this->change ) as $needle )
		{
			if ( mb_substr( $word, -mb_strlen( $needle ) ) == $needle )
			{
				$w = mb_substr( $word, 0, -mb_strlen( $needle ) );
				$word = $w . $this->change[ $needle ];
				break;
			}
		}
		
		return $word;
	}
	/**
	* sanitizes configuration into a more usable form
	* @param string $what variable to sanitize
	* @return string prints out the new config to use
	*/
	function sanitize( $what )
	{
		$new = array();
		
		//print_R( $this->$what );
		//die();
		
		if ( !is_array( $this->$what ) )
		{
			$haystack = mb_split( ' ', $this->$what );
		}else
		{
			$haystack = $this->$what;
		}
		
		// first loop through them 
		foreach ( $haystack as $set )
		{
			if ( empty( $set ) )
			{ // this can happen with multiple spacing
				continue;
			}
			if ( !in_array( $set, $new ) )
			{ // add it to the new array if unique
				$new[] = $set;
			}
		}
		// now sort by length
		usort( $new, array( 'TagCloud_sl', 'cmpLength' ) );
		
		echo count( $new ) . "\n\n";
		
		// output
		print_R( implode( ' ', $new ) );
	}
	/**
	* random testing purposes only
	*/
	function currentRandomTest( $testdata )
	{
		$sub = 'i�';
		$string = 'mi�';
		$subb = mb_substr( $string, -mb_strlen( $sub ) );
		echo $subb . "\n";
		echo $sub . "\n";
		echo intval( $sub == $subb );
	}
	/**
	* creates the output :)
	*/
	function output( )
	{
		$this->father->statisticsHash = $this->statisticsHash;
		$this->father->statistics = $this->statistics;
		$this->father->maxStat = $this->maxStat;
	}
	/**
	* orders the statistics via a hashing array
	*/
	function orderStats()
	{
		// make some vars
		$this->statisticsHash = array_keys( $this->statistics );
		// this is a nice trick that does what was a php based sorting algorithm ... those never seem to work well
		usort( $this->statisticsHash, array( 'TagCloud_sl', 'cmpStat' ) );
	}
	
	function cmpLength( $a, $b )
	{
		$a = mb_strlen( $a, mb_detect_encoding( $a ) );
		$b = mb_strlen( $b, mb_detect_encoding( $b ) );
		
		if ($a == $b) 
		{
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}

	function cmpStat( $a, $b )
	{
		$a = $this->statistics[ $a ][ 'count' ];
		$b = $this->statistics[ $b ][ 'count' ];
		
		if ($a == $b) 
		{
			return 0;
		}
		return ($a < $b) ? 1 : -1;
	}

	//
	// End of TagCloud class
	//
}

?>