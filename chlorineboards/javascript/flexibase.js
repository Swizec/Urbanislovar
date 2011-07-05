function perpage()
{
	val = document.getElementById( 'perpage' ).value;
	re = new RegExp( /[^0-9]/ );
	if ( re.test( val ) )
	{
		document.getElementById( 'perpage' ).value = valperpage;
	}else
	{
		if ( val.length == 0 )
		{
			val = 1;
		}
		valperpage = val;
		populatepage();
	}
}

function populatepage()
{
	select = document.getElementById( 'page' );
	select.options.length = 0;
	for ( i = 1; i <= Math.ceil( valcount/valperpage ); i++ )
	{
		select.options[ i-1 ] = new Option( i, i-1 );
	}
	page = Math.floor( valstart/valperpage )+1;
	
	pag = '';
	url = valurl.replace( /SHOWNUM/, valperpage );
	for ( i = page-1; ( i > 0 )&&( i > page-4 ); i-- )
	{
		uri = url.replace( /SHOWFROM/, (i-1)*valperpage );
		pag = '<a href="'+uri+'">'+i+'</a> '+pag;
	}
	pag = pag+'<b>'+page+'</b>';
	for ( i = page+1; ( i < page+4)&&( i <= Math.ceil( valcount/valperpage ) ); i++ )
	{
		uri = url.replace( /SHOWFROM/, (i-1)*valperpage );
		pag = pag+' <a href="'+uri+'">'+i+'</a>';
	}
	document.getElementById( 'showpage' ).innerHTML = pag;
	
	if ( (page-2)*valperpage >= 0 )
	{
		urlB = url.replace( /SHOWFROM/, (page-2)*valperpage );
	}else
	{
		urlB = '';
	}
	if ( (page)*valperpage < valcount )
	{
		urlF = url.replace( /SHOWFROM/, (page)*valperpage );
	}else
	{
		urlF = '';
	}
	
	document.getElementById( 'back' ).href = urlB;
	document.getElementById( 'forth' ).href = urlF;
}

function changepage( page )
{
	url = valurl.replace( /SHOWNUM/, valperpage );
	url = url.replace( /SHOWFROM/, page*valperpage );
	
	window.location = url;
}

function setthumb( get )
{
	document.getElementById( get[ 0 ] ).src = get[ 1 ];
}

function showsearch()
{
	document.getElementById( 'search' ).style.display = 'table';
}

function performsearch(  )
{
	args = '';
	for ( i in performsearch.arguments )
	{
		arg = performsearch.arguments[ i ];
		val = document.getElementById( arg ).value;
		if ( val == '' )
		{
			continue;
		}
		args = args+':::'+arg+'::'+val;
	}
	url = valsurl.replace( /SEARCHARGS/, args );
	window.location = url;
}