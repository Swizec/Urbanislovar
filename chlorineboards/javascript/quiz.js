function quiz_answer( number, truth )
{
	x_answer( number, truth, quiz_answer2 );
}

function quiz_answer2( get )
{
	if ( get[ 3 ] != '' )
	{
		document.getElementById( 'question' ).innerHTML = '';
		document.getElementById( 'error' ).innerHTML = get[ 3 ];
	}else if( get[ 5 ] == '' )
	{
		document.getElementById( 'correct' ).innerHTML = get[ 0 ];
		document.getElementById( 'answer' ).innerHTML = get[ 1 ];
		document.getElementById( 'askme' ).innerHTML = get[ 2 ];
		document.getElementById( 'commands' ).innerHTML = get[ 4 ];
	}else
	{
		document.getElementById( 'question' ).innerHTML = '';
		getStyleObject( 'finish' ).display = 'block';
	}
}