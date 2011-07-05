storeDescriptions = Array();

function Cdescription( id )
{
	/*if ( storeDescriptions[ id ] == undefined )
	{
		storeDescriptions[ id ] = document.getElementById( id ).innerHTML;
	}
	x_fetchdesc( id, changedesc );*/
	
	desc = document.getElementById( id+'_d' );
	if ( desc.style.display != 'block' )
	{
		desc.style.display = 'block';
	}else
	{
		desc.style.display = 'none';
	}
}

function changedesc( get )
{
	if ( document.getElementById( get[ 0 ] ).innerHTML == get[ 1 ] )
	{
		document.getElementById( get[ 0 ] ).innerHTML = storeDescriptions[ get[ 0 ] ];
	}else
	{
		document.getElementById( get[ 0 ] ).innerHTML = get[ 1 ];
	}
}

function showimage( image, image2, id, root )
{
	//document.getElementById( 'shownimage' ).innerHTML = '<a href="'+image2+'" target="_blank"><img src="'+image+'" /></a>';
	document.getElementById( 'shownimage' ).innerHTML = '<a href="#" onclick="ShowPopUp( '+id+', \''+root+'\' ); return false" ><img src="'+image+'" /></a>';
}