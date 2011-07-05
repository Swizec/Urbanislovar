function fetchMonth( get )
{
	if ( get.length == 3 )
	{
		x_month( get[ 0 ], get[ 1 ], get[ 2 ], fetchMonth );
	}else
	{
		document.getElementById( 'month'+get[ 1 ] ).innerHTML = get[ 0 ];
		if ( undefined === window.months[ get[ 2 ] ] )
		{
			months[ get[ 2 ] ] = new Array();
		}
		months[ get[ 2 ] ][ get[ 3 ] ] = get[ 0 ];
	}
}

function display()
{
	Y = currentY;
	for ( m = currentM, j = 1; m <= currentM+1; m++, j++ )
	{
		if ( m <= 0 )
		{
			Y = Y-1;
			mm = 12;
			chn = +1;
		}else if ( m >= 13 )
		{
			Y = Y+1;
			mm = 1;
			chn = -1;
		}else
		{
			mm = m;
			chn = 0;
		}
		if ( undefined === window.months[ Y ] )
		{
			window.months[ Y ] == new Array();
			fetchMonth( Array( mm, Y, j ) );
		}else if ( undefined === window.months[ Y ][ mm ] )
		{
			fetchMonth( Array( mm, Y, j ) );
		}else
		{
			document.getElementById( 'month'+j ).innerHTML = months[ Y ][ mm ];
		}
		Y = Y + chn;
	}
}

function change( amount )
{
	currentM = currentM + amount;
	if ( currentM <= 0 )
	{
		currentY = currentY - 1;
		currentM = 12;
	}else if ( currentM >= 13 )
	{
		currentY = currentY + 1;
		currentM = 1;
	}
	if ( undefined === window.months[ currentY ] )
	{
		window.months[ currentY ] == new Array();
	}
	display();
}

function showinfo( day, month, year )
{
	x_calendarinfo( day, month, year, showinfo2 );	
}

function showinfo2( get )
{
	document.getElementById( 'date' ).innerHTML = get[ 0 ];
	getStyleObject( 'info' ).display = 'block';
	document.getElementById( 'events_in' ).innerHTML = get[ 1 ];
	document.getElementById( 'shows_in' ).innerHTML = get[ 2 ];
}

function doMove( now, by, target, time )
{
	var gallery = getStyleObject( 'gallery' );
	var gallery2 = getStyleObject( 'galleryout' );
	
	if ( now == target )
	{
		return;
	}
	
	galleryNow = galleryNow+by;
	
	l = now+by;
	gallery2.left = galleryNow+'px';

	galc2 = galc2-by;
	galc4 = galc4-by;
	gallery.clip = "rect( "+galc1+"px, "+galc2+"px, "+galc3+"px, "+galc4+"px )";

	if ( by > 0 )
	{
		if ( l >= target )
		{
			sliding = 0;
			rewinding = 0;
			return;
		}
	}else
	{	if ( l <= target )
		{
			sliding = 0;
			rewinding = 0;
			return;
		}
	}

	setTimeout( "doMove( "+l+", "+by+", "+target+", "+time+" )", time );
}

function moveLeft( width, c1, c2, c3, c4, maxnum, maxim )
{
	if ( sliding >= 0 && galnum+sliding <= maxnum+2 && rewinding == 0 )
	{
		sliding = sliding+1;
	}else
	{
		return;
	}
	if ( !clipset )
	{
		galc1 = c1;
		galc2 = c2;
		galc3 = c3;
		galc4 = c4;
		clipset = true;
		imwidth = width;
		maxwidth = maxim;
	}
	if ( !setPoint( maxnum, 1 ) )
	{
		doMove( 0, -10, -width, 10 );
	}
}

function moveRight( width, c1, c2, c3, c4, maxnum, maxim )
{
	if ( sliding <= 0 && galnum+sliding >= -1 && rewinding == 0 )
	{
		sliding = sliding-1;
	}else
	{
		return;
	}
	if ( !clipset )
	{
		galc1 = c1;
		galc2 = c2;
		galc3 = c3;
		galc4 = c4;
		clipset = true;
		imwidth = width;
		maxwidth = maxim;
	}
	if ( ! setPoint( maxnum, -1 ) )
	{
		doMove( 0, 10, width, 10 );
	}
}

function setPoint( max, change )
{
	galnum = galnum+change;
	rewound = false;
	if ( galnum <= 0 )
	{
		rewind( change );
		galnum = max;
		rewound = true;
	}
	if ( galnum > max )
	{
		rewind( change );
		galnum = 1;
		rewound = true;
	}
	document.getElementById( 'pointer' ).innerHTML = '( '+galnum+' / '+max+' )';
	return rewound;
}

function rewind( change )
{
	rewinding = 1;
	if ( change > 0 )
	{
		doMove( 0, 20, (maxwidth-imwidth), 0 );
	}else
	{
		doMove( 0, -20, -(maxwidth-imwidth), 0 );
	}
}

function showinvite()
{
	getStyleObject( 'invite' ).display = 'block';
	getStyleObject( 'invite_wait' ).display = 'none';
}

function submitinvite( id )
{
	name = encodeURIComponent( document.getElementById( 'invite_name' ).value );
	mail = encodeURIComponent( document.getElementById( 'invite_mail' ).value );
	all = document.getElementById( 'invite_all' ).checked;
	if ( all == false )
	{
		all = 0;
	}else
	{
		all = 1;
	}
	getStyleObject( 'invite' ).display = 'none';
	getStyleObject( 'invite_wait' ).display = 'block';
	x_submitinvite( name, mail, all, id, sentinvite );
}

function sentinvite( get )
{
	if ( get[ 0 ] )
	{
		getStyleObject( 'invite_wait' ).display = 'none';
		document.getElementById( 'invite' ).innerHTML = get[ 1 ];
		alert( get[ 1 ] );
	}else
	{
		getStyleObject( 'invite_wait' ).display = 'none';
		alert( get[ 1 ] );
	}
}

function sendinvites()
{
	getStyleObject( 'msgs' ).display = 'none';
	getStyleObject( 'sending' ).display = 'block';
	sending( 0, 0, 0 );
}

function sending( success, fail, id )
{
	msg = document.getElementById( "lang_"+langs[ id ] ).value;
	subject = document.getElementById( "lang_S"+langs[ id ] ).value;
	x_sendmail( names[ id ], mails[ id ], items[ id ], langs[ id ],times[ id ], subject, msg, alls[ id ], success, fail, id, ids[ id ], names.length, sentmail );
}

function sentmail( get )
{
	document.getElementById( "status" ).innerHTML = get[ 4 ];
	if ( get[ 0 ]+get[ 1 ] < get[ 3 ] )
	{
		sending( get[ 0 ], get[ 1 ], get[ 2 ] );
	}
}

function timeSelChange( which )
{
	return;
	var from = new Array();
	from[ 0 ] = document.getElementById( 'time_from_stamp_day' ).value;
	from[ 1 ] = document.getElementById( 'time_from_stamp_month' ).value;
	from[ 2 ] = document.getElementById( 'time_from_stamp_year' ).value;
	
	var to = new Array();
	to[ 0 ] = document.getElementById( 'time_to_stamp_day' ).value;
	to[ 1 ] = document.getElementById( 'time_to_stamp_month' ).value;
	to[ 2 ] = document.getElementById( 'time_to_stamp_year' ).value;
	
	from[ 3 ] = from[ 0 ]+from[ 1 ]*31+from[ 2 ]*356;
	to[ 3 ] = to[ 0 ]+to[ 1 ]*31+to[ 2 ]*356;
	
	mid = to[ 3 ] - from[ 3 ];

	if ( mid < 0 )
	{
		if ( which == 1 )
		{
			document.getElementById( 'time_to_stamp_day' ).value = from[ 0 ];
			document.getElementById( 'time_to_stamp_month' ).value = from[ 1 ];
			document.getElementById( 'time_to_stamp_year' ).value = from[ 2 ];
		}else
		{
			document.getElementById( 'time_from_stamp_day' ).value = to[ 0 ];
			document.getElementById( 'time_from_stamp_month' ).value = to[ 1 ];
			document.getElementById( 'time_from_stamp_year' ).value = to[ 2 ];
		}
	}
}

var galleryNow = 0;
var galc1 = 0;
var galc2 = 0;
var galc3 = 0;
var galc4 = 0;
var clipset = false;
var galnum = 1;
var imwidth = 0;
var maxwidth = 0;
var sliding = 0;
var rewinding = 0;