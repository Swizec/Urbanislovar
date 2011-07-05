function hide_category( cat_id )
{
	if ( document.getElementById( cat_id ).innerHTML == '' )
	{
		eval( "document.getElementById( cat_id ).innerHTML = category_HTML_" + cat_id + ";" );
	}else
	{
		eval( "category_HTML_" + cat_id + " = document.getElementById( cat_id ).innerHTML" );
		document.getElementById( cat_id ).innerHTML = '';
	}
}

function hide_threads()
{
	if ( document.getElementById( "threads" ).innerHTML == '' )
	{
		document.getElementById( "threads" ).innerHTML = threads_HTML;
	}else
	{
		threads_HTML = document.getElementById( "threads" ).innerHTML;
		document.getElementById( "threads" ).innerHTML = '';
	}
}

function hide_posts()
{
	if ( document.getElementById( "posts" ).innerHTML == '' )
	{
		document.getElementById( "posts" ).innerHTML = posts_HTML;
	}else
	{
		posts_HTML = document.getElementById( "posts" ).innerHTML;
		document.getElementById( "posts" ).innerHTML = '';
	}
}