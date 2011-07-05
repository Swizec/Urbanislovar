<?php

if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

class trimm
{
	function trimm()
	{
	}
	
	function main( $value, $length = 10 )
	{
		return substr( $value, 0, $length );
	}
}

?>