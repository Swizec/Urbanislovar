<?php

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// database type
$db_data[ 'type' ] = 'mysql4';
// database username
$db_data[ 'username' ] = 'clb';
// database password
$db_data[ 'password' ] = 'clb';
// database name
$db_data[ 'name' ] = 'clb';
// database host
$db_data[ 'host' ] = 'localhost';
// prefix for table names
$db_data[ 'table_prefix' ] = 'CLdistro_';
// enable plugins
$enableplugins = TRUE;
// admin email, mostly for the error handler
$admin_email = 'swizec@swizec.com';

// do not edit bellow this
define( 'phpEx', '.php' );

define( 'CLB_INSTALLED', TRUE );
?>