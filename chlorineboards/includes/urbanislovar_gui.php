<?php

/**
*     defines the urbanislovar_gui class
*     @file                urbanislovar_gui.php
*     @see urbanislovar_gui
*/
/**
* ui for the urbanislovar module
*     @class		   urbanislovar_gui
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

// class creation
$vars = array( );
$visible = array( );
eval( Varloader::createclass( 'urbanislovar_gui', $vars, $visible ) );
// end class creation

class urbanislovar_gui extends urbanislovar_gui_def
{
	function urbanislovar_gui()
	{
		global $template, $security;
		
		// open up the tpl file
		$template->assign_files( array(
			'urbanislovar' => 'urbanislovar' . tplEx
		) );
		
		$template->assign_vars( array(
			'URBANISLOVAR' => array(
				'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=urbanislovar&' . SUBMODE_URL . '=search' ),
				'U_ADD' => $security->append_sid( '?dodaj', TRUE ),
			)
		) );
	}
	/**
	* shows the main display
	*/
	function main( $tags, $error = '' )
	{
		global $template, $security, $basic_gui;
		
		$template->assign_block_vars( 'urbanislovar', '', array(
			'L_ERROR' => $error,
		) );
		$template->assign_switch( 'urbanislovar', TRUE );
		
		for ( $i = 0; $i < count( $tags );  )
		{
			$tag = $tags[ $i++ ];
			$template->assign_block_vars( 'tags', '', array(
				'TAG' => $tag[ 'label' ],
				'URI' => $security->append_sid( '/oznaka/' . $tag[ 'label' ], TRUE ),
				'SIZE' => 1.50*( 1.0+( 1.5*$tag[ 'score' ] - $tag[ 'max' ] / 2 ) / $tag[ 'max' ] ),
				'OPACITY' => 0.2 + $tag[ 'score2' ] / $tag[ 'max2' ],
				'IEOPACITY' => round( 20 + $tag[ 'score2' ] / $tag[ 'max2' ] * 100 )
			) );
			$template->assign_switch( 'tags', TRUE );
		}
		
		$basic_gui->add_file( 'urbanislovar' );
	}
	/**
	* displays search results
	*/
	function search( $result, $query = '' )
	{
		global $template, $basic_gui, $security, $Cl_root_path4template;
		
		// some farts and giggles
		$egg = '';
		if ( $query == 'odgovor' )
		{
			$egg = '<img src="' . $Cl_root_path4template . 'template/urbanislovar/images/Answer_to_Life.png" />';
		}
		
		$template->assign_block_vars( 'search', '', array(
			'TEST' =>'test',
			'EASTEREGG' => $egg,
		) );
		$template->assign_switch( 'search', TRUE );
		
// 		for ( $i = 0; $i < count( $result ); $i++ )
// 		{
// 			$template->assign_block_vars( 'resultrow', '', array(
// 				'WORD' => $result[ $i ][ 'word_word' ],
// 				'MEANING' => $result[ $i ][ 'word_meaning' ],
// 				'EXAMPLE' => $result[ $i ][ 'word_example' ],
// 				'AUTHOR' => array( 'A' => $result[ $i ][ 'word_author' ], 'U' => $result[ $i ][ 'word_Uauthor' ] ),
// 				'TIME' => date( 'H:i d. m. Y', $result[ $i ][ 'word_time' ] ),
// 			) );
// 		
// 			$template->assign_switch( 'resultrow', TRUE );
// 		}

		$index = 0;
		foreach ( $result as $author => $Arow )
		{
			foreach ( $Arow as $word => $Wrow )
			{
				$template->assign_block_vars( 'resultrow', '', array(
					'WORD' =>$word,
					'AUTHOR' => array( 'A' => $author, 'U' => $Wrow[ 'U' ] ),
					'TIME' => date( 'H:i, d.m.Y', $Wrow[ 'time' ] ),
					'TCOUNT' => count( $Wrow[ 'tags' ] ),
					'URI' => $security->append_sid( '/iskanje/' . $Wrow[ 'query' ], TRUE )
				) );
				$template->assign_switch( 'resultrow', TRUE );
				
				for ( $i = 0; $i < count( $Wrow[ 'meanings' ] ); $i++ )
				{
					$template->assign_block_vars( 'resultrow.expl', '', array(
						'MEANING' => $Wrow[ 'meanings' ][ $i ],
						'EXAMPLE' => $Wrow[ 'examples' ][ $i ],
					) );
					
					$template->assign_switch( 'resultrow.expl', TRUE );
				}
				
				$basic_gui->add_drag( 'tagging_' . $index, 'NO_DRAG' );
				for ( $i = 0; $i < count( $Wrow[ 'tags' ] );  )
				{
					$tag = $Wrow[ 'tags' ][ $i++ ];
					$template->assign_block_vars( 'resultrow.tags', '', array(
						'TAG' => $tag[ 'label' ],
						'URI' => $security->append_sid( '/oznaka/' . $tag[ 'label' ], TRUE ),
						'SIZE' => 1.50*( 1.0+( 1.5*$tag[ 'score' ] - $tag[ 'max' ] / 2 ) / $tag[ 'max' ] ),
						'OPACITY' => 0.2 + $tag[ 'score2' ] / $tag[ 'max2' ],
						'IEOPACITY' => round( 20 + $tag[ 'score2' ] / $tag[ 'max2' ] * 100 )
					) );
					$template->assign_switch( 'resultrow.tags', TRUE );
				}
				
				$index++;
			}
		}
		
// 		print_R( $template->template_vars );
// 		print_R( $template->template_misc );
		
		$basic_gui->add_file( 'urbanislovar' );
	}
	/**
	* shows the adding thingy thing
	*/
	function add( $query = '', $meaning = '', $example = '', $name = '', $site = '', $error = '' )
	{
		global $security, $template, $basic_gui;
		
		$template->assign_block_vars( 'add', '', array(
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=urbanislovar&' . SUBMODE_URL . '=adding' ),
			'S_QUERY' => $query,
			'S_MEANING' => $meaning,
			'S_EXAMPLE' => $example,
			'S_NAME' => $name,
			'S_SITE' => $site,
			'S_ERROR' => $error,
			
			'TOOL' => array(
					'NAME' => $basic_gui->make_tooltip( 'test', 'buttontip', 'tool_name' )
				)
		) );
		
		$template->assign_switch( 'add', TRUE );
		
		$basic_gui->add_file( 'urbanislovar' );
	}


	//
	// End of urbanislovar-gui class
	//
}


?>