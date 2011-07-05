<?php

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// needed so multiple boards can use the same cache
$cache_config[ 'prefix' ] = 'V2VkbmVzZGF5IC0gMDIgTm92ZW1iZXIgMjAwNSAtIDAyOjQ5OjQxIC0gQ0VU';
// array of ip:port for all the cache servers used
$cache_config[ 'ip' ][ 0 ] = '193.77.212.100:11211';
// data larger than this is compressed
$cache_config[ 'compress_treshold' ] = 10240;
// persistant connection
$cache_config[ 'persistant' ] = TRUE;
// do we even want cache
$cache_config[ 'enabled' ] = FALSE;
// what kind of cache 'disk' or 'mem'
$cache_config[ 'type' ] = 'mem';

?>