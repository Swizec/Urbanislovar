function initiateInterface()
{
	dd.elements[ 'blackDim' ].hide();
	
	dd.elements[ 'uriShow' ].hide();
}

function Tagcheck_done( time )
{
	x_check_done( time, Tagcheck_done2 );
}

function Tagcheck_done2( get )
{
	if ( get[ 0 ] == 0 )
	{
		setTimeout( 'Tagcheck_done( '+get[ 1 ]+' )', 5000 );
	}else
	{
		window.location.href = get[ 2 ];
	}
}

function callFunny()
{
	x_funny_image( Tagfunny );
}

function Tagfunny( get )
{
	document.getElementById( 'funny_image' ).innerHTML = '<br /><br />'+get;
	setTimeout( 'callFunny()', 4500 );
}

function fileUpload( rootPath, time )
{
	if ( uploadedFiles - finishedUploads >= 3 )
	{
		return;
	}
	node = document.createElement( 'span' );
	node.innerHTML = '<div class="file" id="file_'+uploadedFiles+'"><img src="'+rootPath+'template/TagCloud/images/loading.gif" /></div>';
	addTo = document.getElementById( 'filePad' );
	addTo.insertBefore( node, document.getElementById( 'beforeThis' ) );
	
	pad = getStyleObject( 'filePad' );
	pad.display = 'block';	
	
	x_check_upload( uploadedFiles, time, check_upload );
	uploadedFiles++;
	
	document.getElementById( 'fileinput' ).submit()
}

function uriAdd( rootPath, time )
{
	if ( uploadedFiles - finishedUploads >= 3 )
	{
		return;
	}
	node = document.createElement( 'span' );
	node.innerHTML = '<div class="file" id="file_'+uploadedFiles+'"><img src="'+rootPath+'template/TagCloud/images/loading.gif" /></div>';
	addTo = document.getElementById( 'filePad' );
	addTo.insertBefore( node, document.getElementById( 'beforeThis' ) );
	
	pad = getStyleObject( 'filePad' );
	pad.display = 'block';
	
	uriE = document.getElementById( 'uri' );
	uri = uriE.value;
	uriE.value = '';
	
	x_add_uri( uploadedFiles, time, uri, done_upload );
	uploadedFiles++;
	
	return false;
}

function done_upload( get )
{
	file = document.getElementById( 'file_'+get[ 0 ] );
	file.innerHTML = get[ 3 ];
	finishedUploads++;
	if ( get[ 4 ] != undefined )
	{
		x_addInfo( get[ 4 ][ 'words' ], get[ 4 ][ 'sentences' ], get[ 4 ][ 'paragraphs' ], get[ 4 ][ 'language' ], get[ 2 ], get[ 0 ] );
	}
}

function check_upload( get )
{
	if ( get[ 1 ] == 1 )
	{
		done_upload( get );
	}else
	{
		setTimeout( 'x_check_upload( '+get[ 0 ]+', "'+get[ 2 ]+'", check_upload )', 1000 );
	}
}

// function do_uri( get )
// {
// 	dd.elements[ 'blackDim' ].show();
// 	
// 	dd.elements[ 'uriShow' ].write( decodeURIComponent( get[ 0 ] ) );
// 	dd.elements[ 'uriShow' ].show();
// 	
// 	alert( decodeURIComponent( get[ 1 ] ) );
// }

function removeFile( id, time )
{
	file = document.getElementById( 'fileback_'+id );	
	
	flickerOff( file );
	
	document.getElementById( 'file_'+id ).innerHTML = '';
	
	hide( id, 55 );
	
	x_removeFile( id, time );
}

function hide( id, w )
{
	document.getElementById( 'file_'+id ).style.width = w+'px';
	
	if ( w > 0 )
	{
		w -= 5;
		setTimeout( 'hide( "'+id+'", '+w+' )', 10 );
	}else
	{
		document.getElementById( 'file_'+id ).style.display = 'none';
	}
}

function showinfo( info )
{
	div = document.getElementById( 'fileinfo' );
	div.style.display = 'block';
	div.innerHTML = '<p>'+info+'</p>';
}

function isUploaded()
{
	if ( uploadedFiles != finishedUploads )
	{
		return false;
	}
	return true;
}

var uploadedFiles = 0;
var finishedUploads = 0;