<?php

/**
*     defines the quiz class
*     @file                quiz.php
*     @see quiz
*/
/**
* deals with everything partaining to quizes
*     @class		  quiz
*     @author              swizec;
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
// debug :: debug flag

// class creation
$vars = array( 'debug', 'gui' );
$visible = array( 'private', 'private' );
eval( Varloader::createclass( 'quiz', $vars, $visible ) );
// end class creation

class quiz extends quiz_def
{
	/**
	* constructor
	*/
	function quiz( $debug = FALSE )
	{
		global $Cl_root_path, $lang_loader, $Sajax, $basic_gui, $Sajax;
		
		$this->debug = $debug;
		
		// get the gui part
		include( $Cl_root_path . 'includes/quiz_gui' . phpEx );
		$this->gui = new quiz_gui( );
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'quiz' );
		$this->gui->lang = $this->lang;
		
		// some ajax
		$Sajax->add2export( 'quiz->answer', '$number, $true' );
	}
	/**
	* displays the entry point
	*/
	function show()
	{
		global $db, $errors, $Cl_root_path;
		
		//$f = fopen( $Cl_root_path . 'cache/' . $_SERVER[ 'REMOTE_ADDR' ] . '.quiz', 'w' );
		//fclose( $f );
		
		$question = $this->fetch();
		$question[ 0 ] = '';
		
		$this->gui->display( $question );
	}
	/**
	* fetches a question
	*/
	function fetch( $number = 0, $ajax = FALSE )
	{
		global $db, $errors;
		
		$sql = "SELECT q_question, q_number FROM " . QUIZQUESTION_TABLE . " WHERE q_number='$number' LIMIT 1";
		if ( !$result = $db->sql_query( $sql ) )
		{
			if ( $ajax )
			{
				return array( FALSE, '', '', $errors->return_error( 'Could not read database', CRITICAL_ERROR ), '' );
			}else
			{
				$errors->report_error( 'Could not read database', CRITICAL_ERROR );
			}
		}
		$question = $db->sql_fetchrow( $result );
		
		if ( $number > 0 )
		{
			$num = $number-1;
			$sql = "SELECT q_true, q_answer FROM " . QUIZQUESTION_TABLE . " WHERE q_number='$num' LIMIT 1";
			if ( !$result = $db->sql_query( $sql ) )
			{
				if ( $ajax )
				{
					return array( FALSE, '', '', $errors->return_error( 'Could not read database', CRITICAL_ERROR ), '' );
				}else
				{
					$errors->report_error( 'Could not read database', CRITICAL_ERROR );
				}
			}
			$answer = $db->sql_fetchrow( $result );
		}else
		{
			$answer = array( 'q_answer' => '', 'q_true' => FALSE );
		}
		
		return array( $answer[ 'q_true' ], $answer[ 'q_answer' ], $question[ 'q_question' ], '', $question[ 'q_number' ]+1 );
	}
	/**
	* deals with answering
	*/
	function answer( $number, $true )
	{
		global $Cl_root_path, $db;
		
		$true = ( $true == 'true' ) ? 1 : 0;
		
		if ( $number == 1 )
		{
			$f = fopen( $Cl_root_path . 'cache/' . $_SERVER[ 'REMOTE_ADDR' ] . '.quiz', 'w' );
			fclose( $f );
		}
		
		$question = $this->fetch( $number, TRUE );
		
		$f = fopen( $Cl_root_path . 'cache/' . $_SERVER[ 'REMOTE_ADDR' ] . '.quiz', 'a' );
		
		if ( $question[ 0 ] == $true )
		{
			$question[ 0 ] = $this->lang[ 'Correct' ];
			fwrite( $f, "1\n" );
		}else
		{
			$question[ 0 ] = $this->lang[ 'Incorrect' ];
			fwrite( $f, "0\n" );
		}
		fclose( $f );
		
		$question[ 4 ] = '<input type="submit" value="' . $this->lang[ 'True' ] . '" onclick="quiz_answer( ' . $question[ 4 ] . ', true ); return false" /> 
					<input type="submit" value="' . $this->lang[ 'False' ] . '" onclick="quiz_answer( ' . $question[ 4 ] . ', false ); return false" />';
		$question[ 5 ] = '';
					
		if ( $question[ 2 ] == '' )
		{
			$sql = "SELECT COUNT(*) as count FROM " . QUIZQUESTION_TABLE;
			$result = $db->sql_query( $sql );
			$count = $db->sql_fetchfield( 'count' );
			if ( $count != $number )
			{
				$question[ 3 ] = 'An unknown error occured';
			}else
			{
				$results = file_get_contents( $Cl_root_path . 'cache/' . $_SERVER[ 'REMOTE_ADDR' ] . '.quiz' );
				$results = explode( "\n", $results );
				$sum = 0;
				foreach ( $results as $val )
				{
					$sum += $val;
				}
				if ( $sum == $count )
				{
					$question[ 5 ] = 'Finish';
				}else
				{
					$question[ 3 ] = '<h3>' . $this->lang[ 'Again' ] . '</h3>';
				}
			}
		}

		return $question;
	}
	/**
	* deals with submitted personal data
	*/
	function data()
	{
		global $db, $errors;
		
		if ( !isset( $_POST[ 'iquiz' ] ) )
		{
			$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
		}
		
		$name = $_POST[ 'name' ];
		$mail = $_POST[ 'mail' ];
		$time = time();
		
		$sql = "SELECT * FROM " . QUIZWINER_TABLE . " WHERE win_mail='$mail' LIMIT 1";
		$result = $db->sql_query( $sql );
		if ( $db->sql_numrows( $result ) != 0 )
		{
			$errors->report_error( $this->lang[ 'Existant' ], GENERAL_ERROR );
		}
		
		$sql = "INSERT INTO " . QUIZWINER_TABLE . " ( win_name, win_mail, win_time )VALUES( '$name', '$mail', '$time' )";
		if ( !$db->sql_query( $sql ) )
		{
			$errors->report_error( 'Could not insert', CRITICAL_ERROR );
		}
		
		$errors->report_error( $this->lang[ 'Submitted' ], MESSAGE );
	}
	
	//
	// End of quiz class
	//
}

?>