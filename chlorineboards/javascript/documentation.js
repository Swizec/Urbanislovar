function displayedit( get )
{
	document.getElementById( get[ 0 ] ).innerHTML = get[ 1 ];
}

function editarticle2( article_id )
{
	title = document.getElementById( 'title' ).value;
	text = document.getElementById( 'text' ).value;
	x_editarticle2( article_id, title, text, displayedit );
}