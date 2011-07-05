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

class TagCloud_en
{
	/**
	* constructor
	*/
	function TagCloud_en( $parent = FALSE )
	{
		$this->father = $parent;
		
		$this->solvedWords = array();
		$this->commonWords = array();
		
		// get the configs
		//die( PATH . '/includes/TagCloud/stem_en.php' );
		include( PATH . '/includes/TagCloud/stem_en.php' );
		include( PATH . '/includes/TagCloud/ignore-small_en.php' );
		include( PATH . '/includes/TagCloud/ignore-large_en.php' );
		$this->commonWords = array_merge( $ignore_large, $ignore_small );
		
		$this->Stemmer = new PorterStemmer();
		//$this->commonWords = mb_split( ' ', $Config[ 'commonWords' ] );
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
		$this->solvedWords = array();
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
		//$this->cloudSource = preg_split( '#[^\p{L}]+#u', str_replace( "'", '', $this->cloudSource ) );
		$this->cloudSource = preg_split( '#\W+#', str_replace( "'", '', $this->father->xml_entity_decode( $this->cloudSource ) ) );
		print_R( $this->cloudSource );
		// because this is splitting an empty word manifests at the end, we pop it off
		array_pop( $this->cloudSource );
		
		// loop through the words now
		foreach ( $this->cloudSource as $number => $word )
		{
			$word = mb_strtolower( $word );
			$Word = $word;
			//echo "$Word::$number\n";
			if ( mb_strlen( $word ) < 2 )
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
			if ( isset( $this->solvedWords[ $word ] ) )
			{
				$word = $this->solvedWords[ $word ];
			}else
			{
				$word = $this->Stemmer->Stem( $word );
				$this->solvedWords[ $Word ] = $word;
			}
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
		usort( $this->statisticsHash, array( 'TagCloud_en', 'cmpStat' ) );
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