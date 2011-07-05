<?php

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

/**

The syntax for entries in this file is that the desired MODE_URL is the key
of an array entry, of which the first index is a regex that matches a desired URL. 
Subpatterns in the regex are $_GET variable values and the subsequent array
indexes are strings for variable names (keys in $_GET) in the same sequence
as the subpatterns. If the variable name is an array it must be of the type
varname => varvalue and will be statically set (still needs to be matched by something).

*/

$URIconf = array(
	'searchresult' => array( '#search/([a-z0-9%]+)#i', 'query' ),
);

?>