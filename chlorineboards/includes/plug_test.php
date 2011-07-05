<?php

///////////////////////////////////////////////////////////////////
//                                                               //
//     file:               plugins.php                           //
//     scripter:             swizec                              //
//     contact:         swizec@swizec.com                        //
//     started on:      29th November 2005                       //
//     version:               0.1.0                              //
//                                                               //
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
//                                                               //
// This program is free software; you can redistribute it        //
// and/or modify it under the terms of the GNU General Public    //
// License as published by the Free Software Foundation;         //
// either version 2 of the License, or (at your option)          //
// any later version.                                            //
//                                                               //
///////////////////////////////////////////////////////////////////

//
// This class takes care of plugins
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// vars explanation
// debug :: the debug flag

// create this class
global $Varloader;;
$vars = array( 'debug' );
$visible = array( 'private' );
eval( Varloader::createclass( 'test', $vars, $visible ) );
// end class creation

class plug_test extends test_def
{
	
	function cmpstr( $str1, $str2 )
{
	$ret[ 'str1' ] = array();
	$ret[ 'str2' ] = array();
	
	// the simplest check first so we conserve CPU 'n' stuff
	if ( $str1 == $str2 )
	{
		return $ret;
	}
	
	// multiline
	$str1 = str_replace( "\n", ' ', str_replace( "\r", "\n", str_replace( "\r\n", "\n", $str1 ) ) );
	$str2 = str_replace( "\n", ' ', str_replace( "\r", "\n", str_replace( "\r\n", "\n", $str2 ) ) );
	
	// first explode the two into words :)
	$str1 = explode( ' ', $str1 );
	$str2 = explode( ' ', $str2 );
	
	// gy by the shorter string and truncate the big one
	$l1 = count( $str1 ); // minor speed enhancement
	$l2 = count( $str2 ); // minor speed enhancement 
	if ( $l1 > $l2 )
	{
		$l = $l2;
		$e = 1;
		$extra = array_slice( $str1, $l-1 ); // get what we need
		array_splice( $str1, $l-1 ); // remove what we don't need
	}elseif( $l2 > $l2 )
	{
		$l = $l1;
		$e = 2;
		$extra = array_slice( $str2, $l-1 ); // get what we need
		array_splice( $str2, $l-1 ); // remove what we don't need
	}else
	{
		$l = $l1;
		$extra = array();
	}
	
	
	// go through this
	$i = 0;
	while ( $l > 0 )
	{
		if ( $str1[ 0 ] != $str2[ 0 ] )
		{
			// the one that is longer is the one with the diff
			if ( count( $str1 ) > count( $str2 ) )
			{
				$ret[ 'str1' ][ $i ] = $str1[ 0 ];
				array_shift( $str1 );
			}else
			{
				$ret[ 'str2' ][ $i ] = $str2[ 0 ];
				array_shift( $str2 );
			}
		}else
		{
			array_shift( $str1 );
			array_shift( $str2 );
		}
		$i++;
		$l--;
	}
	
	$l = $i;
	
	// go through the extra and add it
	for ( $i = 0; $i < count( $extra ); $i++ )
	{
		$ret[ 'str' . $e ][ $i+$l-1 ] = $extra[ $i ];
	}
	
	return $ret;
}

}

?>