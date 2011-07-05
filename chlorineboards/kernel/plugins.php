<?php

/**
*     defines the plugins class
*     @file                plugins.php
*     @see Plugins
*/
/**
* This class takes care of plugins
*     @class		   Security
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.20
*     @since        29th November 2005
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

// vars explanation
// debug :: the debug flag

// create this class
global $Varloader;
$vars = array( 'debug', 'list' );
$visible = array( 'private', 'public' );
eval( Varloader::createclass( 'plugins', $vars, $visible ) );
// end class creation

class Plugins extends plugins_def
{
	
	/**
	* constructor
	* @param bool $debug debugging on or off
	*/
	function Plugins( $debug = FALSE )
	{
		global $errors, $Cl_root_path;
		
		// debug ing
		$errors->debug_info( $debug, 'Plugins', 'Plugins', 'Initiating plugins' );
		$this->list = Array();
		
		// we populate the plugslist
		$d = dir( $Cl_root_path . 'includes/' );
		while ( FALSE !== ( $entry = $d->read() ) )
		{
			// check if it's a plugin
			if ( strtolower( substr( $entry, 0, 5 ) ) == 'plug_' )
			{ // it is so do the honours
				// check that the extension is php
				if ( stristr( $entry, '.' ) != phpEx )
				{
					continue;
				}
				include( $Cl_root_path . 'includes/' . $entry );
				// remove the .php
				$name = str_replace( phpEx, '', $entry );
				// make it global and 'new' it
				$GLOBALS[ $name ] = new $name;
				$this->list[] = $name;
			}
		}
	}

}

?>