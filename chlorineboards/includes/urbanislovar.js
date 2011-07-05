function calculateScreen ()
{
	try
	{
		screenWidth = window.innerWidth;
		screenHeight = window.innerHeight;
	}catch ( err )
	{
		screenWidth = document.body.offsetWidth;
		screenHeight = document.body.offsetHeight;
	}
}

calculateScreen();

window.onresize = function ()
{
	calculateScreen();
	initiateUS();
}


function initiateUS()
{
	d = dd.elements[ 'blackDim' ];
	d.setZ( 100 );
	d.resizeTo( screenWidth, screenHeight );
	d.moveTo( 0, 0 );
	d.hide();
	
	l = dd.elements[ 'loading' ];
	l.resizeTo( 100, 100 );
	l.moveTo( screenWidth/2-l.w/2, screenHeight/2-l.h/2 );
	l.setZ( 101 );
	l.hide();
	
	if ( taggings.length != 0 )
	{
		for ( i = 0; i < taggings.length; i++ )
		{
// 			alert( i );
			d = dd.elements[ 'tagging_'+i ];
			d.hide();
			d.resizeBy( 0, -d.h );
		}
	}
}

function search()
{
	loadingStart();
	x_search( encodeURIComponent( document.getElementById( 'find' ).value ), search2 );
	return false;
}

function search2( get )
{
// 	str = document.getElementById( 'postTarget' ).document.body.innerHTML;
// 	
// 	start = str.search( /<ACTION>/ )+8;
// 	uri = str.substr( start, str.search( /<\/ACTION>/ )-start );
	
	loadingStop();
	
	window.location.href = get[ 0 ];
}

function loadingStart()
{
	dd.elements[ 'blackDim' ].show();
	dd.elements[ 'loading' ].show();
}

function loadingStop()
{
	dd.elements[ 'blackDim' ].hide();
	dd.elements[ 'loading' ].hide();
}

function displayTagging( id, close, open )
{
	if ( dd.elements[ 'tagging_'+id ].visible )
	{
		openTagging( id, 0, -10, false );
	}else
	{
		openTagging( id, 40, 10, true );
	}
}

function openTagging( id, target, alpha, show )
{
	d = dd.elements[ 'tagging_'+id ];
	
	if ( ( d.h < target && alpha > 0 ) || ( d.h > target && alpha < 0 ) )
	{
		d.resizeBy( 0, alpha );
		setTimeout( 'openTagging( '+id+', '+target+', '+alpha+', '+show+' )', 20 );
	}else
	{
		if ( show )
		{
			d.show();
		}else
		{
			d.hide();
		}
	}
}

function goTag( id, rootPath )
{
	tagging = id;
	getStyleObject( 'tagAdd_'+id ).display = 'none';
	getStyleObject( 'load_'+id ).display = 'inline';
	
	tag = encodeURIComponent( document.getElementById( 'tag_'+id ).value );
	word = encodeURIComponent( document.getElementById( 'word_'+id ).value );
	check = encodeURIComponent( document.getElementById( '3dots_'+id ).value );
	
	x_addTag( tag, word, check, stopTag );
	
	return false;
}

function stopTag( report )
{
	if ( tagging != -1 )
	{
		id = tagging;
		getStyleObject( 'load_'+id ).display = 'none';
		if ( report == 'WORK' )
		{
			getStyleObject( 'tagGood_'+id ).display = 'block';
		}else
		{
			getStyleObject( 'tagBad_'+id ).display = 'block';
		}
		
		setTimeout( "window.location.reload( true )", 1500 );
	}
}

taggings = new Array();
tagging = -1;