<?php

/**
*     defines the ACP_comments class
*     @file                comments_gui.php
*     @see comments_gui
*/
/**
* gui for the comments module
*     @class		  comments_gui
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.5
*     @since       31st December 2006
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

// class creation
$vars = array( );
$visible = array( );
eval( Varloader::createclass( 'comments_gui', $vars, $visible ) );
// end class creation

class comments_gui extends comments_gui_def
{
	function comments_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'comments' => 'comments' . tplEx
		) );
	}
	/**
	* makes the pretty display
	*/
	function thorough( $comments, $item, $mode, $captcha )
	{
		global $template, $security, $Cl_root_path4template, $board_config, $userdata;
		
		$template->assign_block_vars( 'thorough', '', array(
			'L_BY' => $this->lang[ 'by' ],
			'L_QREPLY' => $this->lang[ 'reply_quick' ],
			'L_TREPLY' => $this->lang[ 'reply_this' ],
			'L_REPLY' => $this->lang[ 'reply' ],
			'L_NICKNAME' => $this->lang[ 'nickname' ],
			'L_EMAIL' => $this->lang[ 'email' ],
			'L_WEBSITE' => $this->lang[ 'website' ],
			'L_TITLE' => $this->lang[ 'title' ],
			
			'ITEMID' =>$item,
			'MODE' => $mode,
			'QUICK' => ( $board_config[ 'comment_allowquick' ] ) ? 1 : 0,
			'ISUSER' => ( $userdata[ 'user_level' ] > GUEST ) ? 1 : 0,
			
			'U_REPLY' => $security->append_sid( '?' . MODE_URL . '=commentreply&item=' . $item . '&m=' . $mode ),
			'U_CAPTCHA' => $Cl_root_path4template . $captcha[ 'image' ],
			'TCAPTCHA' => $captcha[ 'time' ],
		) );
		
		$this->_makerow( $comments, $mode );
		
		$template->assign_switch( 'thorough', TRUE );
		
		// parse and return
		return $template->justcompile( 'comments' );
	}
	/**
	* recursively builds the comments list
	*/
	function _makerow( $comments, $mode, $parent = 0, $level = 0 )
	{
		global $template, $userdata, $users, $security, $board_config, $basic_gui;
		
		// set all the stuff
		$user = array();
		if ( count( $comments[ $parent ] ) == 0 )
		{
			return '';
		}
		foreach ( $comments[ $parent ] as $comment )
		{
			// make the user info
			if ( $comment[ 'user_id' ] <= 1 )
			{ // guest post
				$user[ 'nickname' ] = $comment[ 'nickname' ];
				$user[ 'website' ] = $comment[ 'website' ];
				$user[ 'email' ] = ( $userdata[ 'user_level' ] == ADMIN || $userdata[ 'user_level' ] == SUPER_MOD ) ? $comment[ 'email' ] : '';
				$user[ 'avy' ] = '';
				$user[ 'sig' ] = '';
			}else
			{
				$u = $users->get_userdata( $comment[ 'user_id' ] );
				$user[ 'nickname' ] = $u[ 'username' ];
				$user[ 'website' ] = $security->append_sid( '?' . MODE_URL . '=uProfile_norm&uid=' . $u[ 'user_id' ] );
				$user[ 'email' ] = $u[ 'user_publicmail' ];
				$user[ 'avy' ] = ( $u[ 'user_avatar' ] == '' ) ? $basic_gui->get_URL() . '/images/' . $board_config[ 'uProfile_avydef' ] : $u[ 'user_avatar' ];
				$user[ 'sig' ] = $u[ 'user_signature' ];
			}
			
			// some stuff
			$more = '<a href="#" onclick="commentshow(\'' . $comment[ 'id' ] . '\'); return false" /> ...</a>';
			$excerpt = ( strlen( $comment[ 'content' ] ) <= $board_config[ 'comment_exclength' ] ) ? $comment[ 'content' ] : substr( $comment[ 'content' ], 0, $board_config[ 'comment_exclength' ] ) . '{MORE}';
			$excerpt = str_replace( '{MORE}', $more, strip_tags( $excerpt ) );
			
			$template->assign_block_vars( 'commentrow', '', array(
				'NICKNAME' =>$user[ 'nickname' ],
				'EMAIL' => $user[ 'email' ],
				'SIGNATURE' =>$user[ 'sig' ],
				'TITLE' => ( !empty( $comment[ 'title' ] ) ) ? $comment[ 'title' ] : '...',
				'CONTENT' =>$comment[ 'content' ],
				'TIME' => date( $userdata[ 'user_timeformat' ], $comment[ 'time' ] ),
				'ID' => $comment[ 'id' ],
				'SPACE' => $level*20,
				'EXCERPT' => $excerpt,
				
				'U_WEBSITE' => $this->uri( $user[ 'website' ] ),
				'U_AVY' => $user[ 'avy' ],
				'U_REPLY' => $security->append_sid( '?' . MODE_URL . '=commentreply&item=' . $comment[ 'item_id' ] . '&m=' . $mode . '&p=' . $comment[ 'id' ] ),
				
				'SHOWAVY' => ( $user[ 'avy' ] != '' ) ? 1 : 0,
				'SHOWSIG' => ( $user[ 'sig' ] != '' ) ? 1 : 0,
				'SHOWMAIL' => ( $user[ 'email' ] != '' ) ? 1 : 0,
				'ISUSER' => ( $userdata[ 'user_level' ] > GUEST ) ? 1 : 0,
			) );
			
			$template->assign_switch( 'commentrow', TRUE );
			
			if ( isset( $comments[ $comment[ 'id' ] ] ) )
			{
				$this->_makerow( $comments, $mode, $comment[ 'id' ], $level+1 );
			}
		}
	}
	/**
	* gui for proper replies
	*/
	function reply( $itemid, $mode, $parent, $comment, $captcha )
	{
		global $template, $security, $userdata, $basic_gui;
		
		$template->assign_block_vars( 'reply', '', array(
			'L_REPLY' => $this->lang[ 'reply' ],
			'L_NICKNAME' => $this->lang[ 'nickname' ],
			'L_EMAIL' => $this->lang[ 'email' ],
			'L_WEBSITE' => $this->lang[ 'website' ],
			'L_TITLE' => $this->lang[ 'title' ],
		
			'EDITOR' => $comment[ 'editor_HTML' ],
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=commentreply&item=' . $itemid . '&m=' . $mode . '2&p=' . $parent ),
			'S_TITLE' => $comment[ 'title' ],
			'S_NICKNAME' => $comment[ 'nick' ],
			'S_WEBSITE' => $this->uri( $comment[ 'website' ] ),
			'S_EMAIL' => $comment[ 'mail' ],
			'S_ERROR' => $comment[ 'error' ],
			'S_BACK' => $basic_gui->back_URL,
			
			'U_CAPTCHA' => $captcha[ 'image' ],
			'TCAPTCHA' => $captcha[ 'time' ],
			
			'ISUSER' => ( $userdata[ 'user_level' ] != GUEST ) ? 1 : 0,
		) );
		
		$template->assign_switch( 'reply', TRUE );
		
		$basic_gui->add_file( 'comments' );
	}
	/**
	* parses the url so it works properly
	*/
	function uri( $uri )
	{
		if ( strtolower( substr( $uri, 0, 7 ) ) != 'http://' )
		{
			return 'http://' . $uri;
		}else
		{
			return $uri;
		}
	}

	//
	// End of comments-gui class
	//
}


?>