<?php

/**
*     defines the ACP_quiz class
*     @file                ACP_quiz.php
*     @see ACP_quiz
*/
/**
* ACP panel for administration of the quiz game
*     @class		   ACP_quiz
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        16th March 2007
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
// debug :: debug flag

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'ACP_quiz', $vars, $visible ) );
// end class creation

class ACP_quiz extends ACP_quiz_def
{
	/**
	* constructor
	*/
	function ACP_quiz( $debug = FALSE )
	{
		global $Cl_root_path, $basic_gui, $lang_loader, $security;
		
		$this->debug = $debug;
	
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_quiz' );
		
		// make the two urls
		$url1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_quiz&s=questions' );
		$url2 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_quiz&s=winners' );
		
		// add to page
		// add to page
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $url1 . '">' . $this->lang[ 'Side_questions' ] . '</a><br /><a href="' . $url2 . '">' . $this->lang[ 'Side_winners' ] . '</a></span>' );
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $template, $errors, $Cl_root_path;
		
		$template->assign_files( array(
			'ACP_quiz' => 'ACP/quiz' . tplEx
		) );
		
		// get the subsubmode
		$sub = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : 'add';
		
			
		switch( $sub )
		{
			case 'winners':
				$this->winners();
				break;
			case 'questions':
				$this->questions();
				break;
			case 'questions2':
				$this->questions2();
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* shows the winners and their info and such
	*/
	function winners()
	{
		global $db, $template, $userdata, $errors;
		
		$sql = "SELECT * FROM " . QUIZWINER_TABLE . " ORDER BY win_time ASC";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Cannot read database', CRITICAL_ERROR );
		}
		$winners = $db->sql_fetchrowset( $result );
		
		if ( count( $winners ) > 0  )
		{
			foreach ( $winners as $winner )
			{
				$template->assign_block_vars( 'winnerlist', '', array(
					'NAME' => $winner[ 'win_name' ],
					'TIME' => date( $userdata[ 'user_timeformat' ], $winner[ 'win_time' ] ),
					'MAIL' => $winner[ 'win_mail' ]
				) );
				$template->assign_switch( 'winnerlist', TRUE );
			}
		}
		
		$template->assign_block_vars( 'winners', '', array(
			'L_TITLE' => $this->lang[ 'Win_title' ],
			'L_EXPLAIN' => sprintf( $this->lang[ 'Win_explain' ], count( $winners ) ),
			'L_NAME' => $this->lang[ 'Win_name' ],
			'L_MAIL' => $this->lang[ 'Win_mail' ],
			'L_TIME' => $this->lang[ 'Win_time' ],
		) );
		$template->assign_switch( 'winners', TRUE );
	}
	/**
	*  for managing questions ya
	*/
	function questions()
	{
		global $db, $template, $errors, $security;
		
		$sql = "SELECT * FROM " . QUIZQUESTION_TABLE . " ORDER BY q_number ASC";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Cannot read database', CRITICAL_ERROR );
		}
		$questions = $db->sql_fetchrowset( $result );
		$questions[] = array( 'q_id' => 0, 'q_question' => '', 'q_answer' => '', 'q_true' => FALSE );
		
		$i = 0;
		foreach ( $questions as $question )
		{
			$template->assign_block_vars( 'questionlist', '', array(
				'ID' => $question[ 'q_id' ],
				'QUESTION' => htmlspecialchars( $question[ 'q_question' ] ),
				'ANSWER' => htmlspecialchars( $question[ 'q_answer' ] ),
				'TRUE' => ( $question[ 'q_true' ] ) ? 'checked' : '',
				'TRUE2' => ( $question[ 'q_true' ] ) ? 1 : 0,
			) );
			$template->assign_switch( 'questionlist', TRUE );
			$i++;
		}
		
		$template->assign_block_vars( 'questions', '', array(
			'L_TITLE' => $this->lang[ 'Q_title' ],
			'L_EXPLAIN' => $this->lang[ 'Q_explain' ],
			'L_QUESTION' => $this->lang[ 'Q_question' ],
			'L_ANSWER' => $this->lang[ 'Q_answer' ],
			'L_TRUE' => $this->lang[ 'Q_true' ],
			'L_REMOVE' => $this->lang[ 'Q_remove' ],
			'L_UP' => $this->lang[ 'Q_up' ],
			'L_DOWN' => $this->lang[ 'Q_down' ],
			
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_quiz&s=questions2' )
		) );
		$template->assign_switch( 'questions', TRUE );
	}
	/**
	* the form has been submitted
	*/
	function questions2()
	{
		global $errors;
		
		if ( isset( $_POST[ 'isubmit' ] ) )
		{
			$this->submit();
			return;
		}
		if ( isset( $_POST[ 'iremove' ] ) )
		{
			$this->remove();
		}
		if ( isset( $_POST[ 'imoveup' ] ) )
		{
			$this->move( -1 );
		}
		if ( isset( $_POST[ 'imovedown' ] ) )
		{
			$this->move( 1 );
		}
		
		$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
	}
	/**
	* deals with changes and additions of questions
	*/
	function submit()
	{
		global $db, $errors;
		
		foreach ( $_POST[ 'question' ] as $i => $void )
		{
			$question = isset( $_POST[ 'question' ][ $i ] ) ? strval( $_POST[ 'question' ][ $i ] ) : '';
			$answer = isset( $_POST[ 'answer' ][ $i ] ) ? strval( $_POST[ 'answer' ][ $i ] ) : '';
			$true = isset( $_POST[ 'true' ][ $i ] ) ? intval( $_POST[ 'true' ][ $i ] ) : 0;
			$oquestion = $_POST[ 'oquestion' ][ $i ];
			$oanswer = $_POST[ 'oanswer' ][ $i ];
			$otrue = $_POST[ 'otrue' ][ $i ];
			
			if ( $i == 0 )
			{
				if ( !empty( $question ) && !empty( $answer ) )
				{
					$sql = "SELECT COUNT(*) as count FROM " . QUIZQUESTION_TABLE;
					$result = $db->sql_query( $sql );
					$count = $db->sql_fetchfield( 'count' );
					$sql = "INSERT INTO " . QUIZQUESTION_TABLE . " ( q_number, q_question, q_answer, q_true )VALUES( '$count', '$question', '$answer', '$true' )";
					if ( !$db->sql_query( $sql ) )
					{
						$errors->report_error( 'Could not insert', CRITICAL_ERROR );
					}
				}
			}elseif ( $oquestion != $question || $oanswer != $answer || $otrue != $true )
			{
				$sql = "UPDATE " . QUIZQUESTION_TABLE . " SET q_question = '$question', q_answer = '$answer', q_true = $true  WHERE q_id='$i' LIMIT 1";
				if ( !$db->sql_query( $sql ) )
				{
					$errors->report_error( 'Could not update', CRITICAL_ERROR );
				}
			}
		}
		
		$errors->report_error( $this->lang[ 'Q_modified' ], MESSAGE );
	}
	/**
	* removes a question
	*/
	function remove()
	{
		global $db, $errors;

		$id = array_keys( $_POST[ 'iremove' ] );
		$id = $id[ 0 ];
		
		$sql = "DELETE FROM " . QUIZQUESTION_TABLE . " WHERE q_id='$id' LIMIT 1";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not update', CRITICAL_ERROR );
		}
		
		$errors->report_error( $this->lang[ 'Q_modified' ], MESSAGE );
	}
	/**
	* moves a question
	*/
	function move( $moveby )
	{
		global $db, $errors;
		
		if ( isset( $_POST[ 'imovedown' ] ) )
		{
			$id = array_keys( $_POST[ 'imovedown' ] );
		}else
		{
			$id = array_keys( $_POST[ 'imoveup' ] );
		}
		$id = $id[ 0 ];
		
		$sql = "SELECT q_number FROM " . QUIZQUESTION_TABLE . " WHERE q_id='$id' LIMIT 1";
		$result = $db->sql_query( $sql );
		$number = $db->sql_fetchfield( 'q_number' );
		
		$num = $number+$moveby;
		
		$sql = "UPDATE " . QUIZQUESTION_TABLE . " SET q_number='$number' WHERE q_number='$num' LIMIT 1";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not update', CRITICAL_ERROR );
		}
		
		$sql = "UPDATE " . QUIZQUESTION_TABLE . " SET q_number='$num' WHERE q_id='$id' LIMIT 1";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not update', CRITICAL_ERROR );
		}
		
		$errors->report_error( $this->lang[ 'Q_modified' ], MESSAGE );
	}
	
	//
	// End of ACP_quiz class
	//
}

?>