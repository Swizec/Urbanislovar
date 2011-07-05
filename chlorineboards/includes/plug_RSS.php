<?php

/**
*     defines the plug_RSS class
*     @file                plug_RSS.php
*     @see plug_RSS
*/
/**
* this is an RSS plugin
*     @class		   plug_RSS
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.5
*     @since        13th April 2007
*     @package		     plug_RSS
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
$vars = array( 'debug', 'directory', 'generator' );
$visible = array( 'private', 'private', 'private' );
eval( Varloader::createclass( 'plug_RSS', $vars, $visible ) );
// end class creation

class Plug_RSS extends plug_RSS_def
{
	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function Plug_RSS( $debug = FALSE )
	{
		global $Cl_root_path;
		
		$this->directory = $Cl_root_path . 'includes/RSS';
	}
	/**
	* fetches basic config and such when the plugin is actually used
	* first thing to be called upon use
	* @access private
	*/
	function _first()
	{
		global $Cl_root_path;
		
		// check for the storage directory
		if ( !is_writable( $this->directory ) )
		{
			if ( !is_dir( $this->directory ) )
			{
				chmod( $Cl_root_path . 'includes', 0744 );
				mkdir( $this->directory );
				chmod( $Cl_root_path . 'includes', 0544 );
			}else
			{
				chmod( $this->directory, 0744 );
			}
		}
		
		// what's this called
		$this->generator = "Chlorine Boards - RSS plugin v0.1.0";
	}
	/**
	* does what needs to be done upon finishing use
	* @access private
	*/
	function _last()
	{
		global $Cl_root_path;
		
		chmod( $this->directory, 0544 );
	}
	/**
	* adds an item to RSS
	* @param string $channel
	* @param string $title
	* @param string $description
	* @param string $link
	* @param mixed $category
	* @param string $author
	* @param string $comments
	* @param mixed $enclosure
	*/
	function add_item( $channel, $title, $description, $link, $category = array(), $author = '', $comments = '', $enclosure = array() )
	{
		if ( !$this->channel_exists( $channel ) )
		{
			return FALSE;
		}
		
		$this->_first();
		
		// fix up some thingies
		$title = htmlspecialchars( $title );
		$description = htmlspecialchars( $description );
		$link = htmlspecialchars( $link );
		$author = htmlspecialchars( $author );
		$comments = htmlspecialchars( $comments );
		
		$item = "\t\t<item>\n";
		$item .= "\t\t\t<title>$title</title>\n";
		$item .= "\t\t\t<link>$link</link>\n";
		$content .= "\t\t<guid>$link</guid>\n";
		$item .= "\t\t\t<description>$description</description>\n";
		
		if ( is_array( $category ) )
		{
			foreach ( $category as $cat )
			{
				$cat = htmlspecialchars( $cat );
				$item .= "\t\t\t<category>$cat</category>\n";
			}
		}
		if ( $author != '' )
		{
			$item .= "\t\t\t<author>$author</author>\n";
		}
		if ( $comments != '' )
		{
			$item .= "\t\t\t<comments>$comments</comments>\n";
		}
		if ( is_array( $enclosure ) && !empty( $enclosure ) )
		{
			$item .= "\t\t\t<enclosure url=\"${enclosure[ 'url' ]}\" type=\"${enclosure[ 'type' ]}\" length=\"${enclosure[ 'type' ]}\" />\n";
		}
		$item .= "\t\t\t<pubDate>" . date( 'D, d M Y H:i:s T', EXECUTION_TIME ) . "</pubDate>\n";
		
		$item .= "\t\t</item>\n";
		
		// put it in
		$content = file_get_contents( $this->_channel( $channel ) );
		$content = str_replace( "\t</channel>", $item . "\t</channel>", $content );
		$content = preg_replace( "#<lastBuildDate>.*?</lastBuildDate>#i", "<lastBuildDate>" . date( 'D, d M Y', EXECUTION_TIME ) . "</lastBuildDate>", $content );
		
		// write it
		if ( !$f = @fopen( $this->_channel( $channel ), 'w' ) )
		{
			return FALSE;
		}
		if ( !@fwrite( $f, $content ) )
		{
			return FALSE;
		}
		@fclose( $f );
		
		$this->_last();
		
		return TRUE;
	}
	/**
	* tells if a channel exists
	* @param string $channel
	* @return bool
	*/
	function channel_exists( $channel )
	{
		if ( is_readable( $this->directory . '/' . $channel . '.xml' ) )
		{
			return TRUE;
		}
		return FALSE;
	}
	/**
	* creates or recreates a channel
	* @param string $channel
	* @param string $title
	* @param string $description
	* @param string $link
	* @param string $language
	* @param string $copyright
	* @param mixed $category
	* @param string $editor
	* @param string $webmaster
	* @param mixed $image
	*/
	function create_channel( $channel, $title, $description, $link, $language = '', $copyright = '', $category = array(), $editor = '', $webmaster = '', $image = array() )
	{
		global $board_config;
		
		$this->_first();
		
		// fix up some thingies
		$title = htmlspecialchars( $title );
		$description = htmlspecialchars( $description );
		$link = htmlspecialchars( $link );
		$language = htmlspecialchars( $language );
		$copyright = htmlspecialchars( $copyright );
		$editor = htmlspecialchars( $editor );
		$webmaster = htmlspecialchars( $webmaster );
		
		$content = "<?xml version=\"1.0\" encoding=\"" . $board_config[ 'site_charset' ] . "\" ?>\n<rss version=\"2.0\">\n\n";
		$content .= "\t<channel>\n";
		$content .= "\t\t<title>$title</title>\n";
		$content .= "\t\t<link>$link</link>\n";
		$content .= "\t\t<description>$description</description>\n";
		
		if ( $language != '' )
		{
			$content .= "\t\t<language>$language</language>\n";
		}
		if ( $copyright != '' )
		{
			$content .= "\t\t<copyright>$copyright</copyright>\n";
		}
		if ( is_array( $category ) )
		{
			foreach ( $category as $cat )
			{
				$cat = htmlspecialchars( $cat );
				$content .= "\t\t<category>$cat</category>\n";
			}
		}
		if ( $editor != '' )
		{
			$content .= "\t\t<managingEditor>$editor</managingEditor>\n";
		}
		if ( $webmaster != '' )
		{
			$content .= "\t\t<webMaster>$webmaster</webMaster>\n";
		}
		if ( !empty( $image ) )
		{
			$image[ 'description' ] = htmlspecialchars( $image[ 'description' ] );
			$image[ 'title' ] = htmlspecialchars( $image[ 'title' ] );
			$image[ 'link' ] = htmlspecialchars( $image[ 'link' ] );
		
			$content .= "\t\t<image>\n";
			$content .= "\t\t\t<url>${image[ 'url' ]}</url>\n";
			$content .= "\t\t\t<link>${image[ 'link' ]}</link>\n";
			$content .= "\t\t\t<title>${image[ 'title' ]}</title>\n";
			if ( isset( $image[ 'height' ] ) )
			{
				$content .= "\t\t\t<height>${image[ 'height' ]}</height>\n";
			}
			if ( isset( $image[ 'width' ] ) )
			{
				$content .= "\t\t\t<width>${image[ 'width' ]}</width>\n";
			}
			if ( isset( $image[ 'description' ] ) )
			{
				$content .= "\t\t\t<description>${image[ 'description' ]}</description>\n";
			}
			$content .= "\t\t</image>\n";
		}
		$content .= "\t\t<generator>" . $this->generator . "</generator>\n";
		$content .= "\t\t<lastBuildDate>" . date( 'D, d M Y H:i:s T', EXECUTION_TIME ) . "</lastBuildDate>\n";
		
		$content .= "\t</channel>\n\n</rss>";
		
		// write it
		if ( !$f = @fopen( $this->_channel( $channel ), 'w' ) )
		{
			return FALSE;
		}
		if ( !@fwrite( $f, $content ) )
		{
			return FALSE;
		}
		@fclose( $f );
		
		$this->_last();
		
		return TRUE;
	}
	/**
	* get the link to a channel
	* @param string $channel
	* @return string
	*/
	function get_channel( $channel )
	{
		global $basic_gui;
		
		return $basic_gui->get_URL() . '/' . $this->directory . '/' . $channel . '.xml';
	}
	/**
	* produces the path to the channel
	* @access private
	*/
	function _channel ( $channel )
	{
		return $this->directory . '/' . $channel . '.xml';
	}
	/**
	* returns the channel's data
	* @param string $channel
	*/
	function channel_data( $channel )
	{
		if ( !$this->channel_exists( $channel ) )
		{
			return array();
		}
		
		$content = file_get_contents( $this->_channel( $channel ) );
		
		// remove the items
		$content = preg_replace( "#<item>.*?</item>#s", '', $content );
		preg_match( '#<title>(.*?)</title>#s', $content, $title );
		$title = $title[ 1 ];
		preg_match( '#<description>(.*?)</description>#s', $content, $description );
		$description = $description[ 1 ];
		preg_match( '#<link>(.*?)</link>#s', $content, $link );
		$link = $link[ 1 ];
		preg_match( '#<copyright>(.*?)</copyright>#s', $content, $copyright );
		$copyright = $copyright[ 1 ];
		preg_match( '#<managingEditor>(.*?)</managingEditor>#s', $content, $editor );
		$editor = $editor[ 1 ];
		preg_match( '#<webMaster>(.*?)</webMaster>#s', $content, $webmaster );
		$webmaster = $webmaster[ 1 ];
		preg_match( '#<lastBuildDate>(.*?)</lastBuildDater>#s', $content, $lastbuild );
		$lastbuild = $lastbuild[ 1 ];
		preg_match_all( '#<category>(.*?)</category>#s', $content, $category );
		$category = $category[ 1 ];
		
		return array(
				'title' => $this->htmlspecialchars_decode( $title ),
				'description' => $this->htmlspecialchars_decode( $description ),
				'link' => $this->htmlspecialchars_decode( $link ),
				'copyright' => $this->htmlspecialchars_decode( $copyright ),
				'editor' => $this->htmlspecialchars_decode( $editor ),
				'webmaster' => $this->htmlspecialchars_decode( $webmaster ),
				'lastbuild' => $this->htmlspecialchars_decode( $lastbuild ),
				'category' => $this->htmlspecialchars_decode( $category ),
			);
	}
	/**
	* decodes html special chars because the function doesn't exist in earlier PHP's
	* @access private
	*/
	function htmlspecialchars_decode( $str )
	{
		$trans = get_html_translation_table( HTML_SPECIALCHARS );
		
		$decode = array();
		foreach ( $trans as $char=>$entity ) 
		{
			$decode[ $entity ] = $char;
		}
		
		$str = strtr( $str, $decode );
		
		return $str;
	}
	
	//
	// End of Plug_RSS class
	//
}


?>