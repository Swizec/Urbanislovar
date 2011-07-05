<?php

/**
*     defines the plug_urbanislovar class
*     @file                plug_urbanislovar.php
*     @see plug_urbanislovar
*/
/**
* this is an urbanislovar plugin
*     @class		   plug_urbanislovar
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        16th August 2007
*     @package		     plug_urbanislovar
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
$vars = array( 'debug' );
$visible = array( 'private' );
eval( Varloader::createclass( 'plug_urbanislovar', $vars, $visible ) );
// end class creation

class Plug_urbanislovar extends plug_urbanislovar_def
{
	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function Plug_urbanislovar( $debug = FALSE )
	{
		global $Cl_root_path, $mod_loader;
		
		// we mainly do this so we can have search thingies in the header
		$mods = $mod_loader->getmodule( 'urbanislovar', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->execute_modules( 0, 'urbanislovar' );
	}
	
	//
	// End of Plug_urbanislovar class
	//
}


?>