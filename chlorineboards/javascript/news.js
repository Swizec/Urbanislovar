function news_calendar_previous( month, year )
{
	month = month-1;
	if ( month <= 0 )
	{
		month = 12;
		year = year-1;
	}
	x_calendar( month, year, news_calendar_show );
}

function news_calendar_next( month, year )
{
	month = month+1;
	if ( month > 12 )
	{
		month = 1;
		year = year+1;
	}
	x_calendar( month, year, news_calendar_show );
}

function news_calendar_show( get )
{
	document.getElementById( 'news_calendar' ).innerHTML = get;
}