function changeDisplay( id )
{
	dir = directories[ getDirId( id ) ];	
	
	if ( dir.fileType[ id ] == 'textpad' )
	{
		refreshFile( id );
		displayTextPad( id );
		return;
	}
	
	x_swapImage( id, dir.execTime, swapImage );
	//dd.elements[ 'image' ].swapImage( rootPath+'cache/'+execTime+'/image_'+id+'.png' );
	
	dd.elements[ 'file_'+id ].hide();
	dd.elements[ 'replacement_'+id ].show();
	
	flickerOff( dd.elements[ 'replacement_'+id ] );
	
	dd.elements[ 'replacement_'+id ].hide();
	
	x_clickies( dir.execTime, id, Clickies );
	
	Rowhide( id, itemHeight, -5, 'newTop( \''+id+'\' )' );
	
// 	if ( getDirId( topId ) != getDirId( id ) )
// 	{
		dd.elements[ 'file_'+topId ].hide();
		dd.elements[ 'replacement_'+topId ].show();
		flickerOff( dd.elements[ 'replacement_'+topId ], 0.5 );
		dd.elements[ 'replacement_'+topId ].show();
		dd.elements[ 'file_'+topId ].setOpacity( 0.5 );
		dd.elements[ 'file_'+topId ].show();
		dd.elements[ 'file_'+topId ].children[ 0 ].hide();
		dd.elements[ 'file_'+topId ].children[ 1 ].hide();
// 	}
	
	d = dd.elements[ 'dir_'+getDirId( id ) ];
	i = dir.fileHash[ id ];
	c = d.children[ i ];
	for ( i = i; i >= 0; i-- )
	{
		d.children[ i ] = d.children[ i-1 ];
	}
	d.children[ 0 ] = c;
}

function swapImage( get )
{
	dd.elements[ 'image' ].swapImage( get );
}

function Rowhide( id, h, delta, run )
{	
	dir = directories[ getDirId( id ) ];
	for ( i = dir.fileHash[ id ]; i < dir.fileIds.length; i++ )
	{
		if ( dir.fileIds[ i ] == id && delta < 0 )
		{
			continue;
		}
		dd.elements[ 'file_'+dir.fileIds [ i ] ].moveBy( 0, delta );
// 		dd.elements[ 'replacement_'+dir.fileIds[ i ] ].moveBy( 0, delta );
	}
	for ( i = Number( getDirId( id ) )+1; i < directories.length; i++ )
	{
		dd.elements[ 'dir_'+i ].moveBy( 0, delta );
	}
	
	if ( ( delta < 0 && h > 0 )||( delta > 0 && h < itemHeight) )
	{
		h += delta;
		setTimeout( 'Rowhide( "'+id+'", '+h+', '+delta+', "'+run+'" )', 10 );
	}else
	{
		for ( i = dir.fileHash[ id ]; i < dir.fileIds.length; i++ )
		{
			if ( dir.fileIds[ i ] == id && delta < 0 )
			{
				continue;
			}
			if ( delta < 0 )
			{
				d = -1;
			}else
			{
				d = 1;
			}
			dd.elements[ 'file_'+dir.fileIds[ i ] ].moveBy( 0, d*10 );
			//dd.elements[ 'replacement_'+dir.fileIds[ i ] ].moveBy( 0, d*10 );
			
			dd.elements[ 'replacement_'+dir.fileIds[ i ] ].hide();
		}
		setTimeout( run, 100 );
	}
}

function newTop( id )
{
	dir = directories[ getDirId( id ) ];
// 	iconOpacity( dir.topId, 0.5 );
	
	Rowhide( dir.topId, 0, 5, 'newTop2( \''+id+'\' )' );
}

function newTop2( id )
{
	iconOpacity( id, 1.0 );
	dir = directories[ getDirId( id ) ];
	
	for ( i = dir.fileHash[ id ]; i > 0; i-- )
	{
		dir.fileIds[ i ] = dir.fileIds[ i-1 ];
	}
	dir.fileIds[ 0 ] = id;
	
	for ( i = 0; i < dir.fileIds.length; i++ )
	{
		dir.fileHash[ dir.fileIds[ i ] ] = i;
	}
	
	if ( id != dir.topId )
	{
		y = dd.elements[ 'file_'+dir.topId ].y;
	}else
	{
		try
		{
			y = dd.elements[ 'file_'+dir.fileIds[ dir.fileHash[ dir.topId ]+1 ] ].y;
		}catch ( err )
		{
			y = dd.elements[ 'file_'+dir.topId ].defy+itemHeight;
		}
	}
	
	dir.topId = id;
	topId = id;
	
	obj = dd.elements[ 'file_'+id ];
	r = dd.elements[ 'replacement_'+id ];
	
	r.moveTo ( obj.x, obj.y );
	obj.attachChild( r );
	obj.moveTo( obj.defx, y-itemHeight );
	
	r.show();
	flickerOn( r );
	
	obj.show();
	dd.elements[ 'shadow_'+id ].hide();
	obj.setOpacity( 1.0 );
	r.setOpacity( 0.5 );
}

function initiateTagCloud( )
{
	if ( directories == undefined )
	{
		return;
	}
	
	screenResize();
	
	try
	{
		//dir = directories[ 0 ];
		//dir.topId = dir.fileIds[ 0 ];
		h = 0;
		for ( d = 0; d < directories.length; d++ )
		{
// 			alert( d );
			dir = directories[ d ];
			dd.elements[ 'dir_'+d ].closing = false;
			dd.elements[ 'dir_'+d ].open = true;
			
			eod = dd.elements[ 'endOfDir_'+d ];
			eod.hide();
			eod.resizeTo( eod.w, 0 );
			
			if ( dir.fileIds.length == 0 )
			{
				continue;
			}
			
			dir.topId = dir.fileIds[ 0 ];
			
			h += dir.fileIds.length*itemHeight;
			dr = dd.elements[ 'dir_'+d ];
			
			for ( i = 0; i < dir.fileIds.length; i++ )
			{
				file = 'file_'+dir.fileIds[ i ];
				shadow = 'shadow_'+dir.fileIds[ i ];
				replacement = 'replacement_'+dir.fileIds[ i ];
				obj = dd.elements[ file ];
			
				obj.addChild( dd.elements[ shadow ] );
				obj.addChild( dd.elements[ replacement ] );
				
				dd.elements[ shadow ].setZ( -3 );
				dd.elements[ shadow ].moveTo( obj.x+30, obj.y+30 );
				dd.elements[ shadow ].hide();
				
				dd.elements[ replacement ].hide();
				dd.elements[ replacement ].setOpacity( 0.5 );
				dd.elements[ replacement ].moveTo( obj.x, obj.y );
				
				dr.addChild( file );
				dr.children[ dr.children.length-1 ].removed = false;
			}
			
// 			if ( d != 0 )
// 			{
// 				swapDir( d );
// 			}
		}
		
		for ( d = 1; d < directories.length; d++ )
		{
			swapDir( d );
		}
		
		dir = directories[ 0 ];
		
		obj = getStyleObject( 'bottomObstruct' );
		h -= 820;
		obj.height = h+'px';
		h -= 5;
		obj.top = -h+'px';
		
		scrollArrows();
		
		dd.elements[ 'advanced' ].resizeTo( 350, 170 );
		dd.elements[ 'advanced' ].hide();
		
		dd.elements[ 'blackDim' ].hide();
		
		dd.elements[ 'Loading' ].hide();
		dd.elements[ 'Loading' ].resizeTo( 100, 100 );
		
		dd.elements[ 'workArea2' ].moveTo( dd.elements[ 'image' ].x, dd.elements[ 'image' ].y );
		dd.elements[ 'workArea2' ].setCursor( 'pointer' );
		
		dd.elements[ 'analysis' ].hide();
		
		dd.elements[ 'messaging' ].hide();
		
		try
		{
			iconOpacity( dir.topId, 1.0 );
		}catch ( err )
		{
		}
		
		showTip();
	}catch ( err )
	{
		alert( err );
	}
	
	topId = '0_0';
}

function getMouseXY(e) {
  if (IE) { // grab the x-y pos.s if browser is IE
    tempX = event.clientX + document.body.scrollLeft
    tempY = event.clientY + document.body.scrollTop
  } else {  // grab the x-y pos.s if browser is NS
    tempX = e.pageX
    tempY = e.pageY
  }  
  // catch possible negative values in NS4
  if (tempX < 0){tempX = 0}
  if (tempY < 0){tempY = 0}  
  // show the position values in the form named Show
  // in the text fields named MouseX and MouseY
  document.Show.MouseX.value = tempX
  document.Show.MouseY.value = tempY
  return true
}

function scrollIcons( e )
{
	try
	{
		x = event.clientX;
		y = event.clientY;
	}catch ( err )
	{
		x = e.pageX;
		y = e.pageY;
	}
	
	//height = fileIds.length*100;
	height = docListHeight();

	if ( y <= 40 || y+document.body.scrollTop >= 790 )
	{
		stopScroll = false;
		
		scroll( x, y+document.body.scrollTop, height );
	}else
	{
		stopScroll = true;
	}
}

function scroll( x, My, height )
{
	delta = 0;
	if ( My <= 40 && scrollTop < 0 )
	{
		delta = 5;
	}
	if ( My >= 790 && scrollTop+height > 820 )
	{
			delta = -5;
	}
	
	if ( delta != 0 && stopScroll == false )
	{
		for ( i = 0; i < directories.length; i++ )
		{
			dd.elements[ 'dir_'+i ].moveBy( 0, delta );
		}
		scrollTop += delta;
		setTimeout( 'scroll( '+x+', '+My+', '+height+' )', 10 );
	}else
	{
		getStyleObject( 'upArr' ).display = 'none';
		getStyleObject( 'downArr' ).display = 'none';
		scrollArrows( height );
	}
}

function scrollStop( e )
{
	stopScroll = true;
}

function showAdvanced( e )
{
	menu = dd.elements[ 'advanced' ];
	dir = directories[ getDirId( topId ) ];
	
	try
	{
		x = event.clientX;
		y = event.clientY;
	}catch ( err )
	{
		x = e.pageX;
		y = e.pageY;
	}
	
	menu.resizeTo( 370, 170 );
	
	if ( getStyleObject( 'advancedword' ).display == 'block' )
	{
		menu.resizeBy( 0, 20 );
	}
	if ( getStyleObject( 'changeName' ).display != '' && getStyleObject( 'changeName' ).display == 'block' )
	{
		menu.resizeBy( 0, 23 );
	}
	
	if ( y+menu.h+document.body.scrollTop >= screenHeight )
	{
		y -= menu.h;
	}
	if ( x+menu.w >= screenWidth )
	{
		x -= menu.w;
	}
	
	y += document.body.scrollTop;
	x += document.body.scrollLeft;
	
	z = dd.elements[ 'file_'+dir.topId ].z;
	if ( z < 100 )
	{
		z = 100;
	}
	
	menu.moveTo( x-5, y-5 );
	menu.setZ( z+2 );
	menu.show();
	
	dd.elements[ 'obstructor' ].setZ( z-1 );
	dd.elements[ 'blackDim' ].setZ( z+1 );
	dd.elements[ 'blackDim' ].show();
	Showing.push( 'advanced' );
}

function hideAdvanced( )
{
	dd.elements[ 'advanced' ].hide();
}

function exportImage( link, time, size )
{
	dir = directories[ getDirId( topId ) ];
	link = link.replace( /id\=[0-9]*/, 'id='+getItemId( dir.topId ) );
	alert( link );

	dd.elements[ 'obstructor' ].setZ( dd.elements[ 'advanced' ].z-1 );
	dim = dd.elements[ 'blackDim' ];
	dim.setZ( dd.elements[ 'advanced' ].z+1 );
	dim.show();
	
	load = dd.elements[ 'Loading' ];
	load.moveTo( screenWidth/2 - 50, screenHeight/2 - 50 );
	load.setZ( dd.elements[ 'advanced' ].z+2 );
	load.show();
	
	document.getElementById( 'iframe' ).src = link;
	Showing.push( 'Loading' );
	
	//x_checkDoneExport( time, topId, size, checkDoneExport );
	setTimeout( 'x_checkDoneExport( '+time+', '+getItemId( dir.topId )+', '+size+', checkDoneExport )', 1000 );
}

function checkDoneExport( get )
{
	if ( get[ 0 ] != 1 )
	{
		setTimeout( 'x_checkDoneExport( '+get[ 1 ]+', '+get[ 2 ]+', '+get[ 3 ]+', checkDoneExport )', 1000 );
	}else
	{
		document.getElementById( 'iframe' ).src = get[ 1 ];
		closeWhatever();
	}
}

function closeWhatever( )
{
	if ( textPadDisplayed )
	{
		textPadDisplayed = false;
		saveTextPad();
	}
	
	tt_Hide();
	while ( ( name = Showing.pop() ) != undefined )
	{
		dd.elements[ name ].hide();
	}
	dd.elements[ 'blackDim' ].hide();
	dd.elements[ 'obstructor' ].setZ( 10000 );
	
	getStyleObject( 'advancedword' ).display = 'none';
}

function Clickies( get )
{
	document.getElementById( 'workArea2' ).innerHTML = get;
}

function advancedWord( word )
{
	if ( heldKey == 17 )
	{
		if ( displayedWord.indexOf( word ) == -1 )
		{
			displayedWord += ( displayedWord != '' ) ? '<plus> + </plus>'+word : word;
		}
	}else
	{
		displayedWord = word;
	}
	document.getElementById( 'advancedWordB' ).innerHTML = displayedWord;
	getStyleObject( 'advancedword' ).display = 'block';
}

function analyzeWord( cluster )
{
	if ( cluster )
	{
		displayedWord = document.getElementById( 'clusterIn' ).value;
		cluster = 1;
	}else
	{
		cluster = 0;
	}
	
// 	alert( displayedWord );

	dir = directories[ getDirId( topId ) ];
	z = dd.elements[ 'blackDim' ].z;
	dd.elements[ 'blackDim' ].setZ( z+1 );
	dd.elements[ 'obstructor' ].setZ( z-1 );
	
	obj = dd.elements[ 'analysis' ];
	obj.setZ( z+2 );
	obj.moveTo( screenWidth/2 - 400, screenHeight/2 - 300 );
	obj.resizeTo( 0, 10 );
	obj.displaying = true;
	obj.div.innerHTML = '';
	obj.show();
	
	Showing.push( 'analysis' );
	showWindow( 'analysis', 800, 600 );
	setTimeout( 'showLoading( \'analysis\' )', 1000 );
	x_getAnalysis( encodeURIComponent( displayedWord ), getItemId( dir.topId ), dir.execTime, cluster, showAnalysis );
}

function enterCluster()
{
	if ( window.event )
	{
		key = window.event.keyCode;
	}else if ( e )
	{
		key = e.which;
	}
	if ( key == 13 )
	{
		analyzeWord( true );
	}
}

function showWindow( object, tWidth, tHeight )
{
	tt_Hide();
	obj = dd.elements[ object ];
	if ( obj.w < tWidth )
	{
		obj.resizeBy( 40, 0 );
	}else
	{
		if ( obj.h < tHeight )
		{
			obj.resizeBy( 0, 40 );
		}else
		{
			obj.displaying = false;
			return;
		}
	}
	setTimeout( 'showWindow( \''+object+'\', '+tWidth+', '+tHeight+' )', 10 );
}

function showAnalysis( get )
{
	div = dd.elements[ 'analysis' ].div;
	div.innerHTML = get[ 0 ];
}

function showLoading( object )
{
	if ( dd.elements[ object ].div.innerHTML == '' )
	{
		dd.elements[ object ].div.innerHTML = Loading;
	}
}

function scrollResult( what )
{
	if ( scrollingResult[ what ] != 0 && scrollingResult[ what ] != undefined )
	{
		document.getElementById( what ).scrollTop += scrollingResult[ what ];
		setTimeout( 'scrollResult( \''+what+'\' )', 10 );
	}
}

function startScrollResult( what, by )
{
	scrollingResult[ what ] = by;
	scrollResult( what );
}

function stopScrollResult( what )
{
	scrollingResult[ what ] = 0;
}

function changeScroll( what )
{
	obj = getStyleObject( what );
	if ( obj.overflow != 'auto' )
	{
		obj.overflow = 'auto';
	}else
	{
		obj.overflow = 'hidden';
	}
}

function showTip()
{
	if ( !readCookie( 'showedInstructions' ) )
	{
		Tip = dd.elements[ 'Tip' ];
		Tip.resizeTo( 800, 250 );
		Tip.setZ( 10002 );
		Tip.moveTo( screenWidth/2 - 400, screenHeight/2 - 125 );
		
		dd.elements[ 'blackDim' ].setZ( 10001 );
		dd.elements[ 'blackDim' ].show();
		Tip.show();
		
		Showing.push( 'Tip' );
		
		createCookie( 'showedInstructions', 'yes', 1 );
	}else
	{
		dd.elements[ 'Tip' ].hide();
	}
}

function swapDir( dir )
{
	d = dd.elements[ 'dir_'+dir ];
	Dir = directories[ dir ];
	if ( d.closing == false )
	{
		if ( d.open )
		{
			try
			{
				dd.elements[ 'replacement_'+Dir.topId ].hide();
			}catch ( e )
			{
				return;
			}
			closeDir( dir, d.children.length-1, 1, 5, 0, d.y+itemHeight/2 );
		}else
		{
			for ( i = 0; i < d.children.length; i++ )
			{
				d.children[ i ].moveTo( d.x, d.y+itemHeight/2 );
			}
			d.opened = true;
			openDir( dir, d.y+d.children.length*itemHeight, d.children.length-1, 1, 5, 0 );
		}
	}
}

function closeDir( dir, cameto, delta, divisor, counter, y )
{
	d = dd.elements[ 'dir_'+dir ];
	d.closing = true;
	last = d.children.length-1;
	counter++;
	
	if ( d.children[ last ].y <= y )
	{
		dir++;
		try
		{
			delt = (d.y+itemHeight)-dd.elements[ 'dir_'+dir ].y;
			delt = delt+itemHeight/2;
			
			for ( ; dir < directories.length; dir++ )
			{
				dd.elements[ 'dir_'+dir ].moveBy( 0, -delt );
			}
		}catch ( err )
		{
		}
		d.children[ last ].hide();
		d.closing = false;
		d.open = false;
		scrollArrows();
		return;
	}

	for ( i = last; i >= cameto; i-- )
	{
		if ( d.children[ i ].removed )
		{
			continue;
		}
		d.children[ i ].moveBy( 0, -delta );
		d.children[ i ].setZ( 3 );
		try
		{
			if ( d.children[ last ].y <= d.children[ cameto-1 ].y )
			{
				d.children[ cameto-1 ].hide();
				if ( cameto >= 1 )
				{
					cameto -= 1;
				}
			}
		}catch ( err )
		{
		}
	}
	for ( i = 0; i < cameto; i++ )
	{
		if ( d.children[ i ].removed )
		{
			continue;
		}
		d.children[ i ].moveBy( 0, -(delta/divisor) );
		d.children[ i ].setZ( 3 );
		if ( d.children[ i ].y <= y )
		{
			d.children[ i ].hide();
		}
	}
	
	for ( i = dir+1; i < directories.length; i++ )
	{
		dd.elements[ 'dir_'+i ].moveBy( 0, -delta );
	}
	
	if ( delta < 10 )
	{
		delta += 1;
	}
	if ( divisor > 2 && counter % 10 == 0 )
	{
		divisor -= 1;
	}
	
	setTimeout( 'closeDir( '+dir+', '+cameto+', '+delta+', '+divisor+', '+counter+', '+y+' )', 15 );
}

function openDir( dir, y, cameto, delta, divisor, counter )
{
	d = dd.elements[ 'dir_'+dir ];
	d.closing = true;
	last = d.children.length-1;
	counter++;
	
	if ( d.children[ last ].y >= y )
	{
		dir++;
		try
		{
			delt = (d.y+itemHeight*d.children.length+itemHeight)-dd.elements[ 'dir_'+dir ].y;
			for ( ; dir < directories.length; dir++ )
			{
				dd.elements[ 'dir_'+dir ].moveBy( 0, delt );
			}
		}catch ( err )
		{
		}
		d.closing = false;
		d.open = true;
		scrollArrows();
		return;
	}
	
	for ( i = last; i >= cameto; i-- )
	{
		c = d.children[ i ];
		if ( c == undefined )
		{
			continue;
		}
		if ( c.removed )
		{
			continue;
		}
		if ( !c.visible )
		{
			c.show();
			for ( i = 0; i < c.children.length; c.children[ i++ ].hide() );
			//c.children[ 0 ].hide();
			//c.children[ 1 ].hide();
		}
		if ( c.y < d.y+i*itemHeight+itemHeight )
		{
			c.moveBy( 0, delta );
			c.setZ( 3 );
		}
	}
	
	for ( i = 0; i < cameto; i++ )
	{
		c = d.children[ i ];
		if ( c.removed )
		{
			continue;
		}
		
		if ( !c.visible )
		{
			c.show();
			for ( i = 0; i < c.children.length; c.children[ i++ ].hide() );
			//c.children[ 0 ].hide();
		}
		if ( c.y < d.y+i*itemHeight+itemHeight )
		{
			c.moveBy( 0, (delta/divisor) );
			c.setZ( 3 );
		}
	}
	
	try
	{
		if ( d.children[ last ].y >= d.children[ cameto-1 ].y+itemHeight )
		{
			cameto -= 1;
		}
	}catch ( err )
	{
	}
	
	for ( i = dir+1; i < directories.length; i++ )
	{
		dd.elements[ 'dir_'+i ].moveBy( 0, delta );
	}
	
	if ( delta < 10 )
	{
		delta += 1;
	}
	if ( divisor > 2 && counter % 10 == 0 )
	{
		divisor -= 1;
	}
	
	setTimeout( 'openDir( '+dir+', '+y+', '+cameto+', '+delta+', '+divisor+', '+counter+' )', 15 );
}

function scrollArrows( height )
{
	if ( height == undefined )
	{
		height = docListHeight();
	}
	getStyleObject( 'downArr' ).display = 'none';
	getStyleObject( 'upArr' ).display = 'none';
	if ( scrollTop+height > 820 )
	{
		getStyleObject( 'downArr' ).display = 'block';
	}
	if ( scrollTop < 0 )
	{
		getStyleObject( 'upArr' ).display = 'block';
	}
}

function renameSession()
{
	x_sessionName( directories[ getDirId( topId ) ].execTime, renameSession2 );
}

function renameSession2( get )
{
	document.getElementById( 'sessionName' ).value = decodeURIComponent( get );
	menu = dd.elements[ 'advanced' ];
	if ( getStyleObject( 'changeName' ).display != 'block' )
	{
		menu.resizeBy( 0, 23 );
		if ( menu.y+menu.h >= screenHeight )
		{
			menu.moveBy( 0, -23 );
		}
		getStyleObject( 'changeName' ).display = 'block';
	}
}

function renameSession3()
{
	did = getDirId( topId );
	name = document.getElementById( 'sessionName' ).value;
	document.getElementById( 'sessionNameTip_'+did ).innerHTML = name;
	x_changeSessionName( directories[ did ].execTime, encodeURIComponent( name ) );
	renameSessionC( name );
}

function renameSessionC( name )
{
	getStyleObject( 'changeName' ).display = 'none';
	dd.elements[ 'advanced' ].resizeBy( 0, -18 );
	
	if ( name )
	{
		message( LsessionRenamed+'"'+name+'"' );
	}
}

function renameSessionS( e )
{
	if ( window.event )
	{
		key = window.event.keyCode;
	}else if ( e )
	{
		key = e.which;
	}
	if ( key == 13 )
	{
		renameSession3();
	}
}

function saveSession()
{
	load = dd.elements[ 'Loading' ];
	load.moveTo( screenWidth/2 - 50, screenHeight/2 - 50 );
	load.setZ( dd.elements[ 'advanced' ].z+2 );
	load.show();
	
	dd.elements[ 'blackDim' ].setZ( dd.elements[ 'advanced' ].z+1 );
	
	Showing.push( 'Loading' );
	
	x_saveSession( directories[ getDirId( topId ) ].execTime, saveSession2 );
}

function saveSession2( get )
{
	message( get );
}

function renameDocument()
{
	x_documentName( directories[ getDirId( topId ) ].execTime, getItemId( topId ), renameDocument2 );
}

function renameDocument2( get )
{
	document.getElementById( 'documentName' ).value = decodeURIComponent( get );
	menu = dd.elements[ 'advanced' ];
	if ( getStyleObject( 'changeName2' ).display != 'block' )
	{
		menu.resizeBy( 0, 23 );
		if ( menu.y+menu.h >= screenHeight )
		{
			menu.moveBy( 0, -23 );
		}
		getStyleObject( 'changeName2' ).display = 'block';
	}
}

function renameDocument3()
{
// 	did = getDirId( topId );
	name = document.getElementById( 'documentName' ).value;
	document.getElementById( 'documentNameTip_'+topId ).innerHTML = name;
	x_changeDocumentName( directories[ getDirId( topId ) ].execTime, encodeURIComponent( name ), getItemId( topId ) );
	renameDocumentC( name );
}

function renameDocumentC( name )
{
	getStyleObject( 'changeName2' ).display = 'none';
	dd.elements[ 'advanced' ].resizeBy( 0, -18 );
	
	if ( name )
	{
		message( LdocumentRenamed+'"'+name+'"' );
	}
}

function renameDocumentS( e )
{
	if ( window.event )
	{
		key = window.event.keyCode;
	}else if ( e )
	{
		key = e.which;
	}
	if ( key == 13 )
	{
		renameDocument3();
	}
}

function message( msg, callback, justmsg )
{
	m = dd.elements[ 'messaging' ];
	b = dd.elements[ 'blackDim' ];
	
	dd.elements[ 'Loading' ].hide();
	
	b.setZ( b.z+2 );
	
	m.resizeTo( 300, 50 );
	m.moveTo( screenWidth/2-150, screenHeight/2-25 );
	m.setZ( b.z+1 );
	m.div.innerHTML = '<p>'+msg+'</p>';
	m.show();
	
	Showing.push( 'messaging' );
	
	if ( typeof( justmsg ) != 'undefined' )
	{
		setTimeout( 'closeMessage()', 2000 );
	}else
	{
		setTimeout( 'closeWhatever()', 2000 );
	}
	
	if ( typeof( callback ) != 'undefined' && callback != '' )
	{
		setTimeout( callback, 2010 );
	}
}

function closeMessage()
{
	m = dd.elements[ 'messaging' ];
	b = dd.elements[ 'blackDim' ];
	
	dd.elements[ 'Loading' ].hide();
	
	b.setZ( b.z-2 );
	
	m.hide();
	
	Showing.pop( 'messaging' );
}

function removeDocument()
{
	dir = directories[ getDirId( topId ) ];

	load = dd.elements[ 'Loading' ];
	load.moveTo( screenWidth/2 - 50, screenHeight/2 - 50 );
	load.setZ( dd.elements[ 'advanced' ].z+2 );
	load.show();
	
	dd.elements[ 'blackDim' ].setZ( dd.elements[ 'advanced' ].z+1 );
	
	Showing.push( 'Loading' );
	documentRemoving = dir.topId;
	
	x_removeDocument( getItemId( dir.topId ), dir.execTime, removeDocument2 );
}

function removeDocument2( get, id )
{
	if ( get != '' )
	{
		message( get );
	}
	
	if ( typeof( id ) == 'undefined' )
	{
		id = topId;
	}
	
	dir = directories[ getDirId( id ) ];
	
	dd.elements[ 'file_'+id ].hide();
	dd.elements[ 'replacement_'+id ].show();
	flickerOff( dd.elements[ 'replacement_'+id ] );
	dd.elements[ 'replacement_'+id ].hide();
	removingDocument = id;
	Rowhide( id, itemHeight, -5, 'removeDocument3( )' );
}

function removeDocument3( )
{
	id = removingDocument;
	
	dd.elements[ 'dir_' + getDirId( id ) ].children[ getFileId( id ) ].removed = true;
	dir = directories[ getDirId( id ) ];
	dd.elements[ 'file_'+id ].del();
	
	for ( i = dir.fileHash[ id ]; i < dir.fileIds.length-1; i++ )
	{
		dir.fileIds[ i ] = dir.fileIds[ i+1 ];
		dir.fileHash[ dir.fileIds[ i ] ] = i;
	}
	dir.fileIds.pop;
	dir.fileHash.pop;
	
	if ( id != topId )
	{
		if ( movingfile != false )
		{
			if ( movingfile[ 0 ] < directories.length-1 )
			{
				slideDirs( movingfile[ 0 ], movingfile[ 1 ], movingfile[ 2 ], movingfile[ 3 ], movingfile[ 4 ], movingfile[ 5 ], movingfile[ 6 ], movingfile[ 7 ] );
			}else
			{
				continueMove( movingfile[ 0 ], movingfile[ 4 ], movingfile[ 5 ], movingfile[ 6 ], movingfile[ 7 ] );
			}
		}
		return;
	}
	
	dir.topId = dir.fileIds[ 0 ];
	
	dd.elements[ 'image' ].swapImage( rootPath+'cache/'+dir.execTime+'/image_'+getItemId( dir.topId )+'.png' );
	x_clickies( dir.execTime, getItemId( dir.topId ), Clickies );
	
	iconOpacity( dir.topId, 1.0 );
}

function iconOpacity( id, opacity )
{
	icon = document.getElementById( 'icon_'+id );	
	
	if ( typeof icon.style.opacity == 'string' )
	{
		icon.style.opacity = opacity;
	}else
	{
		icon.filters.alpha.opacity = opacity*100;
	}
}

function removeSession( )
{
	dir = directories[ getDirId( topId ) ];

	load = dd.elements[ 'Loading' ];
	load.moveTo( screenWidth/2 - 50, screenHeight/2 - 50 );
	load.setZ( dd.elements[ 'advanced' ].z+2 );
	load.show();
	
	dd.elements[ 'blackDim' ].setZ( dd.elements[ 'advanced' ].z+1 );
	
	Showing.push( 'Loading' );
	
	x_removeSession( dir.execTime, removeSession2 );
}

function removeSession2( get )
{
	message( get );
}

function newTextPad()
{
	dir = directories[ getDirId( topId ) ];
	
	x_newTextPad( dir.execTime, newTextPad2 );
}

function newTextPad2( get )
{
	message( get, 'window.location.reload( true )' );
}

function displayTextPad( id )
{
	dd.elements[ 'blackDim' ].show();

	z = dd.elements[ 'file_'+dir.topId ].z;
	if ( z < 100 )
	{
		z = 100;
	}
	
	dd.elements[ 'blackDim' ].setZ( z+1 );
	dd.elements[ 'obstructor' ].setZ( z-1 );
	
	obj = dd.elements[ 'analysis' ];
	obj.setZ( z+2 );
	obj.moveTo( screenWidth/2 - 400, screenHeight/2 - 300 );
	obj.resizeTo( 0, 10 );
	obj.displaying = true;
	obj.div.innerHTML = '';
	obj.show();
	
	textPadId = id;
	
	Showing.push( 'analysis' );
	showWindow( 'analysis', 800, 600 );
	setTimeout( 'showLoading( \'analysis\' )', 1000 );
	x_getTextPad( getItemId( id ), directories[ getDirId( id ) ].execTime, displayTextPad2 );
}

function displayTextPad2( get )
{
	div = dd.elements[ 'analysis' ].div;
	div.innerHTML = get[ 0 ];
	textPadDisplayed = true;
}

function saveTextPad()
{
	if ( id != removedTextPad )
	{
		contents = document.getElementById( 'textpad' ).value;
		contents = encodeURIComponent( contents );
		
		x_saveTextPad( getItemId( id ), directories[ getDirId( id ) ].execTime, contents, saveTextPad2 );
	}
}

function saveTextPad2( get )
{
	message( get );
}

function renameTextPad1( name )
{
	obj = document.getElementById( 'textPadName' );
	obj.innerHTML = '<input type="text" value="'+name+'" style="width: 50%; height: 20px; font-weight: bold; font-size: 16px" onkeydown="renameTextPadS()" onblur="renameTextPadS()" id="textPadName_e" />';
	obj.focus();
}

function renameTextPadS( e )
{
	if ( !e ) 
	{
		var e = window.event;
	}
	key = e.which;
	
	if ( key == 13 || e.type == 'blur' )
	{
		obj = document.getElementById( 'textPadName' );
		
		name = document.getElementById( 'textPadName_e' ).value;
		
		obj.innerHTML = '<h1 onclick="renameTextPad1( \''+name+'\' )" style="cursor: pointer">'+name+'</h1>';
		
		document.getElementById( 'documentNameTip_'+textPadId ).innerHTML = name;
		x_changeDocumentName( directories[ getDirId( textPadId ) ].execTime, encodeURIComponent( name ), getItemId( textPadId ) );
		message( LtextpadRenamed, '', true );
	}
}

function removeTextPad( id, time )
{
	x_removeTextPad( id, time, removeTextPad2 );
}

function removeTextPad2( get )
{
	message( get[ 0 ] );
	
	removedTextPad = getDirId( topId )+'_'+get[ 1 ];
	
	removeDocument2( '', removedTextPad );
}

function moveFile( dir, file )
{
	dd.elements[ 'dir_'+dir ].setOpacity( 1.0 );
	
	if ( dir != getDirId( file ) )
	{
		dd.elements[ 'file_'+file ].hide();
		x_moveFile( getItemId( file ), directories[ getDirId( file ) ].execTime, directories[ dir ].execTime, dir, getDirId( file ), moveFile2 );
	}else
	{
		refreshFile( file );
	}
}

function moveFile2( get )
{
	dir = get[ 0 ];
	file = get[ 1 ];
	indx = directories[ dir ].fileIds.length;
	oldid = get[ 4 ];
	olddir = get[ 5 ];

	target = dd.elements[ 'endOfDir_'+dir ];
	target.write( get[ 2 ] );

// 	document.getElementById( 'endOfDir_'+dir ).innerHTML = get[ 2 ];
	
	for ( d = Number( dir )+1; d < directories.length; d++ )
	{
		obj = dd.elements[ 'dir_'+d ];
		obj.moveBy( 0, -itemHeight );
		for ( i = 0; i < obj.children.length; i++ )
		{
			obj.children[ i ].moveBy( 0, -itemHeight );
			obj.children[ i ].children[ 0 ].moveTo( obj.children[ i ].x+30, obj.children[ i ].y+30 );
		}
	}
	
	document.getElementById( 'sessionFilesTip_'+dir ).innerHTML = get[ 6 ];
	document.getElementById( 'sessionFilesTip_'+olddir ).innerHTML = get[ 7 ];
	
	directories[ dir ].fileIds[ indx ] = dir+'_'+file;
	directories[ dir ].fileHash[ dir+'_'+file ] = indx;
	directories[ dir ].fileType[ dir+'_'+file ] = get[ 3 ];
	
	f = 'file_'+dir+'_'+file;
	s = 'shadow_'+dir+'_'+file;
	r = 'replacement_'+dir+'_'+file;
	
// 	alert( document.getElementById( f ).innerHTML );
	
	ADD_DHTML( f );
	ADD_DHTML( s+NO_DRAG );
	ADD_DHTML( r+NO_DRAG );
	
	movingfile = new Array( dir, itemHeight, 5, 0, indx, oldid, olddir, file );
	
	removeDocument2( '', olddir+'_'+oldid );
}

function addFile( id )
{
	movingfile = false;
	dir = dd.elements[ 'dir_'+getDirId( id ) ];
	
	o = dir.children[ dir.children.length-2 ];
	dd.elements[ 'file_'+id ].setZ( o.z );
	dd.elements[ 'file_'+id ].hide();
	
	if ( dir.open == false )
	{
		dd.elements[ 'file_'+id ].moveTo( o.x, o.y );
	}else
	{
		dd.elements[ 'file_'+id ].moveTo( o.x, o.y+itemHeight );

		dd.elements[ 'file_'+id ].show();
		dd.elements[ 'replacement_'+id ].hide();
		dd.elements[ 'shadow_'+id ].hide();
		
		flickerOn( dd.elements[ 'file_'+id ] );
		
		dd.elements[ 'replacement_'+id ].show();
	}
}

function slideDirs( border, max, alpha, h, indx, oldid, olddir, file )
{
	if ( h < max )
	{
		for ( i = Number( border )+1; i < directories.length; i++ )
		{
			dd.elements[ 'dir_'+i ].moveBy( 0, alpha );
		}
		
		h += alpha;
		
		setTimeout( 'slideDirs( '+border+', '+max+', '+alpha+', '+h+', '+indx+', '+oldid+', '+olddir+', '+file+' )', 10 );
	}else
	{
		continueMove( border, indx, oldid, olddir, file );
	}
}

function continueMove( dir, indx, oldid, olddir, fileid )
{
	d = dir;
	dir = directories[ d ];
	
	file = 'file_'+dir.fileIds[ indx ];
	shadow = 'shadow_'+dir.fileIds[ indx ];
	replacement = 'replacement_'+dir.fileIds[ indx ];
	obj = dd.elements[ file ];
// 	obj.moveTo( 100, 100 );
// 	alert( obj );
// 	return;

// 	removeDocument2( '', olddir+'_'+oldid );
	
	dd.elements[ shadow ].setZ( -3 );
	dd.elements[ shadow ].moveTo( obj.x+30, obj.y+30 );
	dd.elements[ shadow ].hide();
	
	dd.elements[ replacement ].hide();
	dd.elements[ replacement ].setOpacity( 0.5 );
	dd.elements[ replacement ].moveTo( obj.x, obj.y );
	
	obj.addChild( dd.elements[ shadow ] );
	obj.addChild( dd.elements[ replacement ] );
	
	dr = dd.elements[ 'dir_'+d ];
	dr.addChild( file );
	dr.children[ dr.children.length-1 ].removed = false;
	
	addFile( d+'_'+fileid );
// 	movingfile = d+'_'+fileid;
}

function newScrapBook()
{
	x_newScrapBook( newScrapBook2 );
}

function newScrapBook2( get )
{
	message( get, 'window.location.reload( true )' );
}

//
// helper functions
//

function dirObj( time )
{
	this.fileIds = new Array();
	this.fileHash = new Array();
	this.fileType = new Array();
	this.topId = 0;
	this.execTime = time;
}

function getFileId( id )
{
	i = id.split( '_' );
	return directories[ i[ 0 ] ].fileHash[ id ];
}

function getDirTop( name )
{
	top = name.split( '_' );
	return top[ 2 ];
}

function getDirId( id )
{
	id = id.split( '_' );
	return id[ 0 ];
}

function getItemId( id )
{
	id = id.split( '_' );
	return id[ 1 ];
}

function docListHeight()
{
	height = 0;
	for ( i = 0; i < directories.length; i++ )
	{
		height += ( dd.elements[ 'dir_'+i ].open ) ? dd.elements[ 'dir_'+i ].children.length*100+100 : 100;
	}
	
	return height;
}

function dragOverDir( id, obj )
{
	d = dd.elements[ 'dir_'+id ];
		
	x = obj.x;
	y = obj.y;
	w = obj.w;
	h = obj.h;
	
	if ( ( x >= d.x && y >= d.y && x <= d.x+d.w && y <= d.y+d.h ) ||
		( x+w >= d.x && y >= d.y && x+w <= d.x+d.w && y <= d.y+d.h ) ||
		( x+w >= d.x && y+h >= d.y && x+w <= d.x+d.w && y+h <= d.y+d.h ) ||
		( x >= d.x && y+h >= d.y && x <= d.x+d.w && y+h <= d.y+d.h ) )
	{
		return true;
	}else
	{
		return false;
	}
}

function dragOverDirF( obj )
{
	for ( i = 0; i < directories.length; i++ )
	{
		if ( dragOverDir( i, obj ) )
		{
			return i;
		}
	}
	
	return -1;
}

function refreshFile( id )
{
	obj = dd.elements[ 'file_'+id ];
	replacement = name.replace( /file/, 'replacement' );
	dd.elements[ replacement ].hide();
	dd.elements[ replacement ].moveTo( obj.x, obj.y );
	obj.attachChild( dd.elements[ replacement ] );
	
	try
	{
		dd.obj.moveTo( obj.prevx, obj.prevy );
	}catch ( err )
	{
		dd.obj.moveTo( obj.defx, obj.defy );
	}
	
	
	icon = name.replace( /file/, 'icon' );
	icon = document.getElementById( icon );	
	
	if ( parseInt( name.replace( /file_/, '' ) ) != directories[ getDirId( topId ) ].topId )
	{
		if ( typeof icon.style.opacity == 'string' )
		{
			icon.style.opacity = '0.5';
		}else
		{
			icon.filters.alpha.opacity = '50';
		}
	}else
	{
		if ( typeof icon.style.opacity == 'string' )
		{
			icon.style.opacity = '1.0';
		}else
		{
			icon.filters.alpha.opacity = '100';
		}
	}
	obj.setZ( obj.defz );
}

var topId = 0;
var Replacementhtml = '';
var flicker1 = new Array( 0.0, 0.05, 0.1, 0.15, 0.2, 0.25, 0.3, 0.35, 0.4, 0.45, 0.5, 0.55, 0.6, 0.65, 0.7, 0.75, 0.8, 0.85, 0.9, 0.95, 1.0 );
var scrollTop = 0;
var stopScroll = true;
var Showing = new Array();
var displayedWord = '';
var scrollingResult = new Array();
var documentRemoving = false;
var textPadDisplayed = false;
var textPadId = 0;
var removingDocument = '';
var movingfile = false;
var removedTextPad = 0;
var itemHeight = 100;