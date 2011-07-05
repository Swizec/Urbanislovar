<?php
/**
*     decides what varloader class to use.
*     @file                varloader.php
*      
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.2.3;
*     @since        6th June 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/


// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

if ( $bleh )
{
	die();
}

global $Cl_root_path;

// check the php version and launch accordingly
if ( PHPVER >= 5.0 && !defined( 'VARLOADER_DONE' ) )
{
	include( $Cl_root_path . 'kernel/varloader_php5' . phpEx );
	/**
	* The decision for which varloader to use has been made
	*/
	define( 'VARLOADER_DONE', TRUE );
}else
{
	include( $Cl_root_path . 'kernel/varloader_php4' . phpEx );
	/**
	* The decision for which varloader to use has been made
	*/
	define( 'VARLOADER_DONE', TRUE );
}

?>