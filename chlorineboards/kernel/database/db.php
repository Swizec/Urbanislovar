<?php

/**
*     decides what DBAL interface to load
*	original file from phpBB2
*     @file                db.php
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.4.2
*     @since        8th June 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
*     @subpackage	     ClB_DBAL
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/

/**
* this iniciates the database according to configuration
*/
function initiate_db( )
{
	global $cache, $Cl_root_path, $errors;
	global $db_data;

	$path = $Cl_root_path . 'kernel/database/';

	switch( $db_data[ 'type' ] )
	{
		case 'mysql':
			include( $path . 'mysql' . phpEx );
			break;

		case 'mysql4':
			include( $path . 'mysql4' . phpEx );
			break;

		case 'postgres':
			include( $path . 'postgres7' . phpEx );
			break;

		case 'mssql':
			include( $path . 'mssql' . phpEx );
			break;

		case 'oracle':
			include( $path . 'oracle' . phpEx );
			break;

		case 'msaccess':
			include( $path . 'msaccess' . phpEx );
			break;

		case 'mssql-odbc':
			include( $path . 'mssql-odbc' . phpEx );
			break;
		default:
			$errors->report_error( 'No database type specified in config', CRITICAL_ERROR );
			break;
	}

	// Make the database connection.
	global $db;
	$db = new sql_db( $db_data[ 'host' ], $db_data[ 'username' ], $db_data[ 'password' ], $db_data[ 'name' ], false);
	if(!$db->db_connect_id)
	{
		$errors->report_error( 'Could not connect to the database', CRITICAL_ERROR );
	}
}

?>