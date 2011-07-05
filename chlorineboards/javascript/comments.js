function commentshow( id )
{
	document.getElementById( id+'_small' ).style.display = 'none';
	document.getElementById( id+'_big' ).style.display = 'block';
}

function commenthide( id )
{
	document.getElementById( id+'_big' ).style.display = 'none';
	document.getElementById( id+'_small' ).style.display = 'block';
	document.getElementById( id+'_quick' ).style.display = 'none';
}

function quickreply( id )
{
	disp = document.getElementById( id+'_quick' ).style.display;
	if ( disp == 'none' )
	{
		document.getElementById( id+'_quick' ).style.display = 'block';
	}else
	{
		document.getElementById( id+'_quick' ).style.display = 'none';
	}
}

function postquick( id )
{
	document.getElementById( id+'_submit' ).style.display = 'none';
	try
	{
		nickname = encodeURIComponent( document.getElementById( id+'_nick' ).value );
		mail = encodeURIComponent( document.getElementById( id+'_mail' ).value );
		website = encodeURIComponent( document.getElementById( id+'_website' ).value );
		captcha = encodeURIComponent( document.getElementById( id+'_captcha' ).value );
		ctime = document.getElementById( id+'_ctime' ).value;
	}
	catch ( err )
	{
		nickname = '';
		mail = '';
		website = '';
		ctime = 0;
		captcha = '';
	}
	title = encodeURIComponent( document.getElementById( id+'_title' ).value );
	content = encodeURIComponent( document.getElementById( id+'_content' ).value );
	item = document.getElementById( id+'_itemid' ).value;
	mode = document.getElementById( id+'_mode' ).value;
	
	captcha = captcha.replace( /\+/, 'PLUS' );
	
	x_postquick( id, nickname, mail, website, title, content, item, mode, captcha, ctime, postquick2 );
}

function postquick2( get )
{
	if ( get[ 0 ] == 1 )
	{
		document.getElementById( get[ 1 ]+'_quick' ).style.display = 'none';
		window.location.reload( false );
	}else
	{
		document.getElementById( get[ 1 ]+'_error' ).innerHTML = get[ 2 ]+'<br />';
		document.getElementById( get[ 1 ]+'_submit' ).style.display = 'block';
	}
}