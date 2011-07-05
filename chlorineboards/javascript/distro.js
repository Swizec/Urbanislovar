function addfromlist( from, to )
{
	text = document.getElementById( from ).value;
	dest = document.getElementById( to ).value;
	if ( dest.indexOf( text + ';' ) == -1 )
	{
		document.getElementById( to ).value = dest + text + ';';
	}
}