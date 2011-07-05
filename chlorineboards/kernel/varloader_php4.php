<?php

/**
*     defines the varloader for php4
*     @file                varloader_php4.php
*      
*     @see Varloader
*/
/**
* This is the creator of classes for php4
*     @class		   Varloader
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0;
*     @since        17th June 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
* 
*/


// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

class Varloader
{
	
	/**
	*  creates the needed class, needed for compatibility between php4 and php5
	* @returns string the code to be eval'd
	* @usage $varloader->createclass( 'myClass', array( 'var1', 'var2' ), array( 'public', 'private' )
	* @param string $classname the name of the new class
	* @param mixed $variables the array of variables to define
	* @param mixed $visibility the array of variables type
	*/
	function createclass( $classname, $variables, $visibility = 'var' )
	{
		// if variables || visibility not array make it
		if ( !is_array( $variables ) )
		{
			$variables = array( $variables );
		}
		if ( !is_array( $visibility ) )
		{
			$visibility = array( $visibility );
		}
		
		// create the thing
		$class = 'class ' . $classname . '_def {';
		foreach ( $variables as $var )
		{
			$class .= 'var $' . $var . ';';
		}
		$class .= '}';
		
		return $class;
		
	}
	
	//
	// End class Varloader
	//

}


?>