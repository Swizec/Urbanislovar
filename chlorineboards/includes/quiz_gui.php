<?php

/**
*     defines the ACP_quiz class
*     @file                quiz_gui.php
*     @see quiz_gui
*/
/**
* gui for the quiz module
*     @class		  quiz_gui
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since       19th March 2007
*     @package		     quiz
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
eval( Varloader::createclass( 'quiz_gui', $vars, $visible ) );
// end class creation

class quiz_gui extends quiz_gui_def
{
	function quiz_gui()
	{
		global $template;
		
		// open up the tpl file
		$template->assign_files( array(
			'quiz' => 'quiz' . tplEx
		) );
	}
	/**
	* does the gui stuff to display a question
	*/
	function display( $question )
	{
		global $template, $basic_gui, $security;
		
		$template->assign_block_vars( 'question', '', array(
			'CORRECTNESS' => $question[ 0 ],
			'ANSWER' => $question[ 1 ],
			'QUESTION' => $question[ 2 ],
			'ERROR' => $question[ 3 ],
			'NUMBER' => $question[ 4 ],
			
			'L_TRUE' => $this->lang[ 'True' ],
			'L_FALSE' => $this->lang[ 'False' ],
			'L_NOSCRIPT' => $this->lang[ 'NoScript' ],
			'L_NAME' => $this->lang[ 'Name' ],
			'L_MAIL' => $this->lang[ 'Mail' ],
			'L_CONGRATZ' => $this->lang[ 'Congratz' ],
			
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=submitquiz' ),
		) );
		$template->assign_switch( 'question', TRUE );
		
		$basic_gui->add_file( 'quiz' );
	}

	//
	// End of quiz-gui class
	//
}


?>