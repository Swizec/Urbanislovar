function showbug( bugId )
{
	if ( document.getElementById( bugId ).innerHTML == '' )
	{
		document.getElementById( bugId ).innerHTML = '<div align="center"><span class="gen"><b>Loading...</b></span></div>';
		x_showbug( bugId, showbug2 );
	}else
	{
		document.getElementById( bugId ).innerHTML = '';
	}
}

function showbug2( get )
{
	document.getElementById( get[ 0 ] ).innerHTML = get[ 1 ];
}

function clearbugreply( bugId )
{
	document.getElementById( 'reply' + bugId ).value = '';
}

function addbugreply( bugId )
{
	reply = document.getElementById( 'reply' + bugId ).value;
	
	if ( reply != '' )
	{
		clearbugreply( bugId );
		x_addbugreply( bugId, reply, showbug2 );
	}
}