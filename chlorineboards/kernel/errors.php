<?php

/**
*     defines the Errors class
*     @file                errors.php
*     @see Errors
*/
/**
* Error class for Chlorine Boards
*     @class		   Errors
*     @author              Dking
*     @author		   Swizec
*     @contact          swizec@swizec.com
*     @contact          yaggles@gmail.com 
*     @version               0.4.2;
*     @since        18th May 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
* @changes // modified by swizec to work a tad better
*	  // added gui error reporting and multi lang support
*	  // added the return_error function
*	  // added the error handler thing
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}
/**
* this is a wrapper for the function within the class
* won't seem to work otherwise
* @see Errors::error_handler
*/
function error_handler( $err_num, $err_str, $err_file='?', $err_line='?' )
{
	global $errors;
	
	$errors->error_handler( $err_num, $err_str, $err_file, $err_line );
}

$vars = array( 'admin_email' );
$visible = array( 'private' );
eval( Varloader::createclass( 'Errors', $vars, $visible ) );
class Errors extends Errors_def
{
	function Errors( $admin_email, $handler = TRUE )
	{
		$this->admin_email = $admin_email;
		if ( $handler )
		{
			set_error_handler( 'error_handler' );
		}
	}
	/**
	* This function reports erros
	* @usage $errors->report_error( 'handler not recognised', GENERAL_ERROR, 'Template', 'compile', __LINE__, ERROR_RAW );
	* @param string $text the text for the errror message
	* @param string $type the type of error, there are constants for this
	* @param string $class what class raised the error
	* @param string $funct what function raised the error
	* @param integer $line what line raised the error
	* @param integer $method what method to use, either ERROR_GUI or ERROR_RAW
	*/
	function report_error( $text, $type, $class = '', $funct = '', $line = '', $method = ERROR_GUI )
	{
		global $basic_lang, $template, $db, $basic_gui;
		
		//This just shows the error on the page with the
		//text being what was defined when you called
		//the function.
		if ( $method == ERROR_RAW || !is_object( $template ) )
		{
			switch( $type )
			{	
				//The normal general error is for
				//when something minor happens.
				case GENERAL_ERROR:
					$report = '<b>' . $basic_lang[ 'General_error' ] . '</b><br /><br />';
					break;
				//The critical error is for when the
				//whole site crashes or something
				//else like that...
				case CRITICAL_ERROR:
					$report = '<h3>' . $basic_lang[ 'Critical_error' ] . '</h3><br /><br />';
					break;
			}
			$report .= ( !empty( $class ) ) ? $class . '&nbsp;&nbsp;-->&nbsp;&nbsp;' : '';
			$report .= ( !empty( $funct ) ) ? $funct . '&nbsp;&nbsp;::&nbsp;&nbsp;' : '';
			$report .= '<br />' . $text;
			$report .= ( !empty( $line ) ) ? ' ' . $basic_lang[ 'on_line' ] . ' <b>' . $line . '</b>' : '';
			// append the sql error if there is any
			if ( class_exists( 'sql_db' ) && isset( $db ) )
			{
				$sql_error = $db->sql_error();
				if ( !empty( $sql_error[ 'message' ] ) )
				{
					$report .= '<br /><br />SQL line:<br />' . $db->last_query . '<br /><br />Error ' . $sql_error[ 'code' ] . ':<br />' . $sql_error[ 'message' ];
				}
			}
			die ( "$report" );
		}elseif( $method == ERROR_GUI )
		{
			// get the needed vars
			global $board_config, $basic_gui;
			
			// load template file
			$template->assign_files( array(
				'error' => 'error' . tplEx
			) );
			
			// get title
			switch( $type )
			{	
				//The normal general error is for
				//when something minor happens.
				case GENERAL_ERROR:
					$title = $basic_lang[ 'General_error' ];
					break;
				//The critical error is for when the
				//whole site crashes or something
				//else like that...
				case CRITICAL_ERROR:
					$title = $basic_lang[ 'Critical_error' ];
					break;
				case MESSAGE:
					$title = $basic_lang[ 'Message' ];
					break;
			}
			
			// append the sql error if there is any
			$sql_error = $db->sql_error();
			if ( !empty( $sql_error[ 'message' ] ) )
			{
				$sqltext = '<br /><br />SQL line:<br />' . $db->last_query . '<br /><br />Error ' . $sql_error[ 'code' ] . ':<br />' . $sql_error[ 'message' ];
			}
			
			// pass the vars
			$template->assign_vars( array( 'ERROR' => array(
				'TITLE' => $title,
				'TEXT' => $text,
				'CLASS' => $class,
				'FUNCT' => $funct,
				'LINE' => $line,
				'SQLTEXT' => $sqltext,
				
				'L_CLASS' => $basic_lang[ 'Class' ],
				'L_FUNCT' => $basic_lang[ 'Function' ],
				'L_LINE' => $basic_lang[ 'Line' ],
			) ) );
			
			// output it
			if ( $basic_gui )
			{
				$basic_gui->add_file( 'error' );
				$basic_gui->make_page();
			}else
			{
				echo $template->justcompile( 'error' );
			}
			
			// and die
			die();
		}
	}
	/**
	* this makes an error in much the same way as the code above except that instead of dying it returns the stuff
	* @see Errors::report_error
	* @return string the HTML of the error report
	*/
	function return_error( $text, $type, $class = '', $funct = '', $line = '', $method = ERROR_GUI )
	{
		global $basic_lang, $template, $db;
		
		//This just shows the error on the page with the
		//text being what was defined when you called
		//the function.
		if ( $method == ERROR_RAW || !is_object( $template ) )
		{
			switch( $type )
			{	
				//The normal general error is for
				//when something minor happens.
				case GENERAL_ERROR:
					$report = '<b>' . $basic_lang[ 'General_error' ] . '</b><br /><br />';
					break;
				//The critical error is for when the
				//whole site crashes or something
				//else like that...
				case CRITICAL_ERROR:
					$report = '<h3>' . $basic_lang[ 'Critical_error' ] . '</h3><br /><br />';
					break;
			}
			$report .= ( !empty( $class ) ) ? $class . '&nbsp;&nbsp;-->&nbsp;&nbsp;' : '';
			$report .= ( !empty( $funct ) ) ? $funct . '&nbsp;&nbsp;::&nbsp;&nbsp;' : '';
			$report .= '<br />' . $text;
			$report .= ( !empty( $line ) ) ? ' ' . $basic_lang[ 'on_line' ] . ' <b>' . $line . '</b>' : '';
			return $report;
		}elseif( $method == ERROR_GUI )
		{
			// get the needed vars
			global $board_config, $basic_gui;
			
			// load template file
			$template->assign_files( array(
				'error' => 'error' . tplEx
			) );
			
			// get title
			switch( $type )
			{	
				//The normal general error is for
				//when something minor happens.
				case GENERAL_ERROR:
					$title = $basic_lang[ 'General_error' ];
					break;
				//The critical error is for when the
				//whole site crashes or something
				//else like that...
				case CRITICAL_ERROR:
					$title = $basic_lang[ 'Critical_error' ];
					break;
				case MESSAGE:
					$title = $basic_lang[ 'Message' ];
					break;
			}
			
			// append the sql error if there is any
			$sql_error = $db->sql_error();
			if ( !empty( $sql_error[ 'message' ] ) )
			{
				$sqltext = '<br /><br />SQL line:<br />' . $db->last_query . '<br /><br />Error ' . $sql_error[ 'code' ] . ':<br />' . $sql_error[ 'message' ];
			}
			
			// pass the vars
			$template->assign_vars( array( 'ERROR' => array(
				'TITLE' => $title,
				'TEXT' => $text,
				'CLASS' => $class,
				'FUNCT' => $funct,
				'LINE' => $line,
				'SQLTEXT' => $sqltext,
				
				'L_CLASS' => $basic_lang[ 'Class' ],
				'L_FUNCT' => $basic_lang[ 'Function' ],
				'L_LINE' => $basic_lang[ 'Line' ],
			) ) );
			
			// return it
			return $template->justcompile( 'error' );
		}
	}
	/**
	* This does basically the same this as above...
	* Just a little bit less.
	* It only prints a little line that serves for debugging purposes
	* @usage $errors->debug_info( $this->debug, 'Template', 'compile', 'start compile' );
	* @param bool $debug the class' debug flag
	* @param string $class name of the class
	* @param string $function name of the function
	* @param string $text info to output
	*/
	function debug_info( $debug, $class, $function, $text )
	{
		//Just echo the class name, function name, and the text if needed
		if ( $debug )
		{
			echo $class . '&nbsp;&nbsp;-->&nbsp;&nbsp;' . $function . '&nbsp;&nbsp;::&nbsp;&nbsp;' . $text . '<br />';
		}
	}
	/**
	* this becomes the error handler after the initialisation
	* @copyright provided by a guy called Gen Okami
	* @changes fixed to match ClB standards by Swizec
	*/
	function error_handler( $err_num, $err_str, $err_file='?', $err_line='?' )
	{
		global $error_handler;
		
		//$_SERVER['DOCUMENT_ROOT']
		//'SCRIPT_FILENAME'
		//'SERVER_SIGNATURE'
		//'SCRIPT_NAME'
		//'REQUEST_URI'
		
		//'HTTP_ACCEPT_CHARSET'
		//'HTTP_ACCEPT_ENCODING'
		//'HTTP_ACCEPT_LANGUAGE'
		
		//'HTTP_REFERER'
		
		//'REMOTE_ADDR'
		//'REMOTE_HOST'
		
		if ( $err_num & error_reporting() == 0 ) 
		{
			return;
		} //if all is coo = get out
		
		$error = '';
		
		// uhm... stuff
		if ( strpos( $err_file, '/database/' ) !== FALSE )
		{
			return;
		}
		
		switch( $err_num )
		{
			case E_ERROR: 
				$err = 'FATAL';
				break;
			case E_WARNING:
				$err = 'ERROR'; 
				break;
			case E_NOTICE:
				$err = 'NOTICE';
				break;
		}
		
		$err_msg = "PHP	ERROR {$err_file} {$err_line}\n";
		$err_msg .= "{$err_file} Line: {$err_line}\n";
		$err_msg .= "{$err}({$err_num})\n{$err_str}\n\n";
		
		if( !empty( $_SERVER[ 'DOCUMENT_ROOT' ] ) )
		{
			$err_msg .= "DOCUMENT_ROOT => {$_SERVER['DOCUMENT_ROOT']}\n";
		}
		if( !empty( $_SERVER[ 'SCRIPT_FILENAME' ] ) )
		{
			$err_msg .= "SCRIPT_FILENAME => {$_SERVER['SCRIPT_FILENAME']}\n";
		}
		if( !empty( $_SERVER[ 'SERVER_SIGNATURE' ] ) )
		{
			$err_msg .= "SERVER_SIGNATURE => {$_SERVER['SERVER_SIGNATURE']}\n";
		}
		if( !empty( $_SERVER[ 'SCRIPT_NAME' ] ) )
		{
			$err_msg .= "SCRIPT_NAME => {$_SERVER['SCRIPT_NAME']}\n";
		} 		
		if( !empty( $_SERVER[ 'REQUEST_URI' ] ) )
		{
			$err_msg .= "REQUEST_URI => {$_SERVER['REQUEST_URI']}\n";
		}
		
		$err_msg.= "<br>\n";
		
		if( !empty( $_SERVER[ 'HTTP_ACCEPT_CHARSET' ] ) )
		{
			$err_msg .= "HTTP_ACCEPT_CHARSET => {$_SERVER['HTTP_ACCEPT_CHARSET']}\n";
		}
		if( !empty( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) )
		{
			$err_msg .= "HTTP_ACCEPT_ENCODING => {$_SERVER['HTTP_ACCEPT_ENCODING']}\n";
		}
		if( !empty( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) )
		{
			$err_msg.= "HTTP_ACCEPT_LANGUAGE => {$_SERVER['HTTP_ACCEPT_LANGUAGE']}\n";
		}
		
		$err_msg.= "\n";
		
		if(!empty($_SERVER['HTTP_REFERER'])){
				$err_msg.= "HTTP_REFERER => {$_SERVER['HTTP_REFERER']}\n";}
		
		$err_msg.= "\n";
		
		if( !empty( $_SERVER[ 'REMOTE_ADDR' ] ) )
		{
			$err_msg.= "REMOTE_ADDR => {$_SERVER['REMOTE_ADDR']}\n";
		}
		if( !empty( $_SERVER[ 'REMOTE_HOST' ] ) )
		{
			$err_msg.= "REMOTE_HOST => {$_SERVER['REMOTE_HOST']}\n";
		}
		
		$err_msg.= "\n";		
		$system = @get_browser();
		if( !empty( $system->platform ) )
		{
			$err_msg.= "Platform => {$system->platform}\n";
		}
		if( !empty( $system->parent ) )
		{
			$err_msg.= "System Parent => {$system->parent}\n";
		}
		
// 		if( file_exists( $error_handler[ 'file' ] ) )
// 		{
// 			$error_handler[ 'die_msg' ] = file_get_contents( $error_handler[ 'file' ] );
// 		}
		if ( $err != 'NOTICE' )
		{
			@mail( $this->admin_email, "Site PHP Error: {$err_file} :: {$err_line}", $err_msg );
			$this->report_error( $err_msg . '<br />An error has occured. An e-mail has been sent to the administrator of the website.', CRITICAL_ERROR );
		}
	}
	
}
?>