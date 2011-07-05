<?php

/**
*     defines the comments class
*     @file                comments.php
*     @see comments
*/
/**
* deals with everything partaining to comments
*     @class		  comments
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.2.7
*     @since        31st December 2006
*     @package		     comments
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
eval( Varloader::createclass( 'comments', $vars, $visible ) );
// end class creation

class comments extends comments_def
{
	/**
	* constructor
	*/
	function comments( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax, $basic_gui, $Sajax;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/comments_gui' . phpEx );
		$this->gui = new comments_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'comments' );
		$this->gui->lang = $this->lang;
		
		// some ajax
		$Sajax->add2export( 'comments->postquick', '$parent, $nick, $mail, $website, $title, $content, $itemid, $mode, $captcha, $ctime' );
	}
	/**
	* this is a function that returns the mini display under each newspost
	*/
	function news_post( $news_id, $single )
	{
		if ( $single )
		{
			$this->thorough( $news_id, 'news' );
		}else
		{
			$this->quicky( $news_id, 'news' );
		}
	}
	/**
	* this function returns the display under each comic
	*/
	function comic( $comic_id )
	{
		$this->thorough( $comic_id, 'comic' );
	}
	/**
	* this function returns the display under each flexibase item
	*/
	function flexibase( $item_id )
	{
		$this->thorough( $item_id, 'flexibase' );
	}
	/**
	* this function returns the display for showevent items, designed for singular view
	*/
	function showevent( $showevent_id )
	{
		$this->thorough( $showevent_id, 'showevent' );
	}
	/**
	* displays a quicky, usually just a line linking to more comments
	*/
	function quicky( $id, $what )
	{
		global $mod_loader, $cache, $db, $errors, $security;
		
		$nums = array();
		$fetch = FALSE;
		if ( !$nums = $cache->pull( 'comments_' . $what . 'counts' ) )
		{ // we need to fetch it
			$fetch = TRUE;
		}elseif ( !isset( $nums[ $id ] ) )
		{ // need to fetch it
			$fetch = TRUE;
		}
		if ( $fetch )
		{ // do the actual fetching
			$sql = "SELECT COUNT(*) AS number FROM " . COMMENTS_TABLE . " WHERE item_id='$id' AND what='$what'";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Failed to read from db', CRITICAL_ERROR );
			}
			// store the result
			$row = $db->sql_fetchrow( $result );
			$nums[ $id ] = $row[ 'number' ];
			$cache->push( 'comments_newscounts', $nums[ $id ], TRUE );
		}
		
		// now make the lovely string
		if ( $what == 'news' )
		{
			$u = $security->append_sid( '?' . MODE_URL . '=news&id=' . $id );
		}
		$ret = sprintf( $this->lang[ $what . '_numcomments' ], $u, $nums[ $id ] );
		
		$mod_loader->port_vars( array( $what . '_add' => $ret ) );
	}
	/**
	* displays all the comments in a lovely tree and gives the ability to post more
	*/
	function thorough( $id, $what )
	{
		global $mod_loader, $cache, $db, $errors, $security, $plug_captcha, $cache, $userdata, $board_config;
		
		// first all the comments are needed
		$fetch = FALSE;
		$comments = array();
		if ( !$comments = $cache->pull( 'comments_' . $what ) )
		{
			$fetch = TRUE;
		}elseif( !isset( $comments[ $id ] ) )
		{
			$fetch = TRUE;
		}
		if ( $fetch )
		{ // fetching has to be done
			$sql = "SELECT * FROM " . COMMENTS_TABLE . " WHERE item_id='$id' AND what='$what' ORDER BY time ASC";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Failed to read the db', CRITICAL_ERROR );
			}
			$comments[ $id ] = $db->sql_fetchrowset( $result );
			// reorganize
			$comments[ $id ] = $this->_group( $comments[ $id ] );
			// now store it
			$cache->push( $comments, 'comments_' . $what, TRUE );
		}
		
		if ( $userdata[ 'user_level' ] == GUEST && $board_config[ 'comment_allowquick' ] )
		{ // need the captcha info for quick replies
			$cap = $plug_captcha->random( 100, 40, 4 );
			$captcha = array();
			$captcha[ 'image' ] = $cap[ 0 ];
			$captcha[ 'time' ] = $cap[ 2 ];
			// save the solution
			$cache->push( 'captcha_sol' . $cap[ 2 ], $cap[ 1 ], FALSE, ESSENTIAL );
		}else
		{
			$captcha = array();
		}
		
		// now call the gui stuff and get its output
		$out = $this->gui->thorough( $comments[ $id ], $id, $what, $captcha );
		
		// return the stuff
		$mod_loader->port_vars( array( $what . '_add' => $out ) );
	}
	/**
	* groups comments by parent id
	*/
	function _group( $array )
	{
		// empty array to use
		$ret = array();
		
		if ( count( $array ) > 0 )
		{
			foreach ( $array as $com )
			{
				if ( !isset( $ret[ $com[ 'parent_id' ] ] ) )
				{ // make a new slot
					$ret[ $com[ 'parent_id' ] ] = array();
				}
				$ret[ $com[ 'parent_id' ] ][] = $com;
			}
		}else
		{
			return $array;
		}
		
		// return it
		return $ret;
	}
	/**
	* posts a quick reply
	*/
	function postquick( $parent, $nick, $mail, $website, $title, $content, $itemid, $mode, $captcha, $ctime )
	{
		global $userdata, $db, $basic_gui, $cache, $Cl_root_path, $board_config;
		
		if ( !$board_config[ 'comment_allowquick' ] )
		{ // no quickposting
			return array( 1, $parent );
		}
		
		if ( $userdata[ 'user_id' ] > 1 )
		{ // user, disregard some stuff
			$unempty = array( 'title', 'content' );
			$nick = '';
			$mail = '';
			$website = '';
		}else
		{ // everything must be filled
			if ( !$board_config[ 'comment_allowguest' ] )
			{ // no guests
				return array( 0, $parent, $this->lang[ 'guest' ] );
			}
			$unempty = array( 'nick', 'mail', 'title', 'content' );
			if ( !eregi("^[a-z]+[a-z0-9_-]*(([.]{1})|([a-z0-9_-]*))[a-z0-9_-]+[@]{1}[a-z0-9_-]+[.](([a-z]{2,3})|([a-z]{3}[.]{1}[a-z]{2}))$", urldecode( $mail ) ) )
			{
				return array( 0, $parent, $this->lang[ 'email2' ] );
			}
		}
		// check if anything's empty
		foreach ( $unempty as $un )
		{
			if ( empty( $$un ) )
			{
				return array( 0, $parent, $this->lang[ 'empty' ] );
			}else
			{
				$$un = urldecode( $$un );
			}
		}
		// check the captcha
		if ( $userdata[ 'user_id' ] <= 1 )
		{
			$sol = $cache->pull( 'captcha_sol' . $ctime, ESSENTIAL );
			if ( $sol != urldecode( str_replace( 'PLUS', '+', $captcha ) ) )
			{
				return array( 0, $parent, $this->lang[ 'captcha' ] );
			}
			// it's safe to clean up now
			unlink( $Cl_root_path . 'cache/captcha_' . $ctime . '.png' );
			$cache->delete( 'captcha_sol' . $ctime );
		}
		
		$content = str_replace( "\n", '<br />', $basic_gui->gennuline( $content ) );
		
		// make the query
		$uid = $userdata[ 'user_id' ];
		$time = time();
		$sql = "INSERT INTO " . COMMENTS_TABLE . " ( parent_id, item_id, what, user_id, nickname, website, email, time, title, content )VALUES( '$parent', '$itemid', '$mode', '$uid', '$nick', '$website', '$mail', '$time', '$title', '$content' )";
		// use it
		$db->sql_query( $sql );
		
		return array( 1, $parent );
	}
	/**
	* deals with large proper replies
	*/
	function reply( $error = '', $post = array()  )
	{
		global $mod_loader, $db, $userdata, $errors, $plug_captcha, $basic_gui, $cache, $board_config;
		
		if ( $userdata[ 'user_level' ] == GUEST && !$board_config[ 'comment_allowguest' ] )
		{ // no guests
			$errors->report_error( $this->lang[ 'guest' ], GENERAL_ERROR );
		}
		
		$mode = $_GET[ 'm' ];
		// see if posting
		if ( substr( $mode, -1 ) == '2' && count( $post ) == 0 )
		{ // something was posted, do it
			$this->postreply( $mode );
			return;
		}
		
		// fetch editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'comment', 'quickpost' => FALSE, 'def_text' => $post[ 'comment' ] ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$comment = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// fill in some info
		$p = $_GET[ 'p' ];
		if ( $p )
		{ // preset title
			$sql = "SELECT title FROM " . COMMENTS_TABLE . " WHERE id='$p' LIMIT 1";
			$result = $db->sql_query( $sql );
			$row = $db->sql_fetchrow( $result );
			$comment[ 'title' ] = 'Re: ' . $row[ 'title' ];
		}
		$comment[ 'error' ] = $error;
		if ( count( $post ) > 0 )
		{ // set the data back
			foreach ( array( 'website', 'nick', 'title', 'mail' ) as $k )
			{
				$comment[ $k ] = $post[ $k ];
			}
		}
		
		// set the captcha stuff
		$cap = $plug_captcha->random( 200, 100, 5 );
		$captcha = array();
		$captcha[ 'image' ] = $cap[ 0 ];
		$captcha[ 'time' ] = $cap[ 2 ];
		// save the solution
		$cache->push( 'captcha_sol' . $cap[ 2 ], $cap[ 1 ], FALSE, ESSENTIAL );
		
		$this->gui->reply( intval( $_GET[ 'item' ] ), $_GET[ 'm' ], intval( $_GET[ 'p' ] ), $comment, $captcha );
	}
	/**
	* posts the large proper replies
	*/
	function postreply( $mode )
	{
		global $userdata, $errors, $cache, $db, $security;
	
		// basic check
		if ( !isset( $_POST[ 'replymebaby' ] ) )
		{
			$errors->report_error( $this->lang[ 'wrong_form' ], GENERAL_ERROR );
		}
	
		if ( $userdata[ 'user_level' ] == GUEST )
		{
			$mandatory = array( 'nick', 'mail', 'title', 'comment' );
			$nick = $_POST[ 'nick' ];
			$mail = $_POST[ 'mail' ];
			$website = $_POST[ 'website' ];
			// check the email, very basic
			if ( eregi("^[a-z]+[a-z0-9_-]*(([.]{1})|([a-z0-9_-]*))[a-z0-9_-]+[@]{1}[a-z0-9_-]+[.](([a-z]{2,3})|([a-z]{3}[.]{1}[a-z]{2}))$", $mail ) )
			{
				$this->reply( $this->lang[ 'email2' ], $_POST );
				return;
			}
		}else
		{
			$mandatory = array( 'title', 'comment' );
			$nick = '';
			$mail = '';
			$website = '';
		}
		$mandatory = array();
		foreach ( $mandatory as $m )
		{
			if ( !isset( $_POST[ $m ] ) || empty( $_POST[ $m ] ) )
			{ // no empties, error
				$this->reply( $this->lang[ 'empty' ], $_POST );
				return;
			}
		}
		// check the captcha
		$sol = $cache->pull( 'captcha_sol' . $_POST[ 'captchatime' ], ESSENTIAL );
		// it's safe to clean up now
		if ( is_file( $Cl_root_path . 'cache/captcha_' . $ctime . '.png'  ) )
		{
			unlink( $Cl_root_path . 'cache/captcha_' . $ctime . '.png' );
		}
		$cache->delete( 'captcha_sol' . $ctime );
		if ( $sol != $_POST[ 'captcha' ] )
		{ // the check failed
			$this->reply( $this->lang[  'captcha' ], $_POST );
			return;
		}
		// some id set up
		$what = str_replace( '2', '', $mode );
		$itemid = $_GET[ 'item' ];
		// make the sql
		$parent = intval( $_GET[ 'p' ] );
		$uid = $userdata[ 'user_id' ];
		$time = time();
		$title = $_POST[ 'title' ];
		$content = $_POST[ 'comment' ];
		$sql = "INSERT INTO " . COMMENTS_TABLE . " ( parent_id, item_id, what, user_id, nickname, website, email, time, title, content )VALUES( '$parent', '$itemid', '$what', '$uid', '$nick', '$website', '$mail', '$time', '$title', '$content' )";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Insert failure', CRITICAL_ERROR );
		}
		// guess it worked
		$uri = $security->append_sid( $_POST[ 'Uback' ] );
		$errors->report_error( sprintf( $this->lang[ 'replied' ], $uri ), MESSAGE );
	}
	
	//
	// End of filebrowser class
	//
}

?>