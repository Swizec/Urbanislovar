<?php


/**
*    quiz language [English]
*     @author              swizec;
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

$lang[ 'Sidebar_title' ] = 'Quiz game';
$lang[ 'Side_questions' ] = 'Questions';
$lang[ 'Side_winners' ] = 'Winners';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised.';
$lang[ 'Wrong_form' ] = 'The form was wrongly submitted';

$lang[ 'Win_title' ] = 'Winners';
$lang[ 'Win_explain' ] = 'Here you can see the list of people who have so far won the quiz and submitted their personal information. When there are 100 the list stops refreshing.<br />So far there have been <strong style="color: red">%s</strong> winners.';
$lang[ 'Win_time' ] = 'Time of win';
$lang[ 'Win_name' ] = 'Winner name';
$lang[ 'Win_mail' ] = 'Winner e-mail';

$lang[ 'Q_title' ] = 'Questions';
$lang[ 'Q_explain' ] = 'Here you can administer the questions displayed in the quiz by moving them around, adding new ones, changing them et cetera.';
$lang[ 'Q_true' ] = 'True';
$lang[ 'Q_question' ] = 'Question';
$lang[ 'Q_answer' ] = 'Answer';
$lang[ 'Q_remove' ] = 'Remove';
$lang[ 'Q_up'] = 'Up';
$lang[ 'Q_down' ] = 'Down';
$lang[ 'Q_modified' ] = 'Your changes have been succesfully saved';

?>